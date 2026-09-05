<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductSize;
use App\Models\Collection;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminProductController extends Controller
{
    /**
     * Image formats accepted on product uploads. There is deliberately no
     * size cap: large originals are downscaled in the browser and again by
     * ImageService, and PHP limits are raised in .user.ini.
     */
    private const IMAGE_MIMES = 'jpeg,png,jpg,gif,webp,avif,bmp,heic,heif';

    /**
     * Files written to storage while handling the current request. Storage
     * takes no part in the database transaction, so if the transaction rolls
     * back these have to be unlinked by hand or they linger forever.
     *
     * @var string[]
     */
    private array $writtenFiles = [];

    /**
     * Display a listing of products
     */
    public function index()
    {
        $products = Product::with(['collection', 'images', 'sizes', 'colors'])
            ->latest()
            ->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        $collections = Collection::all();

        return view('admin.products.create', compact('collections'));
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $request->validate(
            $this->rules($request),
            $this->validationMessages(),
            $this->attributeNames()
        );

        $data = $this->productAttributes($request);
        // Two products may share a title, so never trust Str::slug alone.
        $data['slug'] = Product::generateUniqueSlug($request->title);

        try {
            $product = DB::transaction(function () use ($request, $data) {
                $product = Product::create($data);

                $this->syncColors($product, $request);
                $this->syncSizes($product, $request);
                $this->storeImages($product, $request);

                return $product;
            });
        } catch (ValidationException $e) {
            $this->discardWrittenFiles();

            throw $e;
        } catch (\Throwable $e) {
            $this->discardWrittenFiles();

            Log::error('Admin product creation failed', [
                'title' => $request->input('title'),
                'exception' => $e,
            ]);

            return back()
                ->withInput()
                ->with('error', $this->explainFailure($e, 'create "' . $request->input('title') . '"'));
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product "' . $product->title . '" created successfully.')
            ->with('warning', $this->stockWarning($product));
    }

    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        $product->load(['collection', 'images', 'sizes', 'colors']);

        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product
     */
    public function edit(Product $product)
    {
        $collections = Collection::all();
        $product->load(['images', 'sizes', 'colors']);

        return view('admin.products.edit', compact('product', 'collections'));
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $request->validate(
            $this->rules($request, $product),
            $this->validationMessages(),
            $this->attributeNames()
        );

        $data = $this->productAttributes($request, $product);
        // Keep the existing slug when the title has not changed so live links
        // survive; otherwise re-derive it, de-duplicated against other products.
        $data['slug'] = $request->title === $product->title
            ? $product->slug
            : Product::generateUniqueSlug($request->title, $product->id);

        // Files are deleted only once the transaction has actually committed.
        // Storage is not transactional: deleting inside the transaction meant a
        // later SQL failure rolled the image rows back while the files on disk
        // were already gone, leaving the gallery pointing at nothing.
        $orphanedFiles = [];

        try {
            DB::transaction(function () use ($request, $product, $data, &$orphanedFiles) {
                // Write the columns first so a constraint violation (duplicate
                // SKU, bad collection) aborts before any image work happens.
                $product->update($data);

                $orphanedFiles = array_merge(
                    $orphanedFiles,
                    $this->removeImages($product, $request->input('remove_images', [])),
                    $this->replaceMainImage($product, $request),
                );

                $this->appendImages($product, $request);
                $this->syncColors($product, $request);
                $this->syncSizes($product, $request);
            });
        } catch (ValidationException $e) {
            $this->discardWrittenFiles();

            // Per-file upload problems are already phrased for the admin and
            // belong against their field, not squashed into a generic banner.
            throw $e;
        } catch (\Throwable $e) {
            $this->discardWrittenFiles();

            Log::error('Admin product update failed', [
                'product_id' => $product->id,
                'exception' => $e,
            ]);

            return back()
                ->withInput()
                ->with('error', $this->explainFailure($e, 'save "' . $product->title . '"'));
        }

        foreach ($orphanedFiles as $path) {
            Storage::disk('public')->delete($path);
        }

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Product "' . $product->title . '" updated successfully.')
            ->with('warning', $this->stockWarning($product->fresh()->load('sizes')));
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product)
    {
        $title = $product->title;

        // If product has order history, soft delete to preserve order data
        if ($product->orderItems()->exists()) {
            $product->update(['visible' => false]);
            $product->delete(); // soft delete

            return redirect()->route('admin.products.index')
                ->with('success', '"' . $title . '" was removed from the store. Its data is retained because it appears in past orders.');
        }

        // No order history: safe to hard delete images and product
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        $product->forceDelete();

        return redirect()->route('admin.products.index')
            ->with('success', '"' . $title . '" was permanently deleted.');
    }

    /**
     * Toggle product visibility
     */
    public function toggleVisibility(Product $product)
    {
        $nowVisible = !$product->getAttribute('visible');
        $product->update(['visible' => $nowVisible]);

        return redirect()->back()
            ->with('success', '"' . $product->title . '" is now ' . ($nowVisible ? 'visible to customers' : 'hidden from the store') . '.');
    }

    /**
     * Validation rules shared by store and update.
     *
     * @param  Request       $request  The incoming request.
     * @param  Product|null  $product  The product being updated, if any.
     */
    private function rules(Request $request, ?Product $product = null): array
    {
        $currencies = implode(',', array_keys(config('currencies.rates')));

        // Only compare the two sale dates when there is a start date to
        // compare against; an end date on its own is perfectly valid.
        $saleEndsRules = ['nullable', 'date'];
        if ($request->filled('sale_starts_at')) {
            $saleEndsRules[] = 'after:sale_starts_at';
        }

        // The SKU column carries a unique index, so a clash has to be caught
        // here rather than surfacing as a raw SQL integrity error. Soft-deleted
        // products still occupy the index, hence no withoutTrashed().
        $skuRule = Rule::unique('products', 'sku');
        if ($product) {
            $skuRule = $skuRule->ignore($product->id);
        }

        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'sale_starts_at' => 'nullable|date',
            'sale_ends_at' => $saleEndsRules,
            // products.collection_id is NOT NULL, so it is required on update
            // too. Allowing "no collection" through only produced a 500.
            'collection_id' => ['required', 'exists:collections,id'],
            'currency' => "required|string|in:$currencies",
            'sku' => ['nullable', 'string', 'max:255', $skuRule],
            'quantity' => 'nullable|integer|min:0',
            'is_one_of_a_kind' => 'boolean',
            'is_sold_out' => 'boolean',
            'size_chart_id' => 'nullable|exists:size_charts,id',
            'story' => 'nullable|string',
            'image' => 'nullable|image|mimes:' . self::IMAGE_MIMES,
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:' . self::IMAGE_MIMES,
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'integer|exists:product_images,id',
            'colors' => 'nullable|array',
            'colors.*.name' => 'nullable|string|max:60',
            'colors.*.hex_code' => ['nullable', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'colors.*.is_available' => 'nullable|boolean',
            'sizes' => 'nullable|array',
            'sizes.*.size_label' => 'nullable|string|max:40',
            'sizes.*.quantity' => 'nullable|integer|min:0',
            'visible' => 'boolean',
        ];
    }

    /**
     * Error messages that say what went wrong and how to fix it
     */
    private function validationMessages(): array
    {
        return [
            'title.required' => 'Give the product a title. It is what customers search for.',
            'title.max' => 'The title is too long. Keep it under 255 characters and put the detail in the description.',
            'description.required' => 'Add a description. It appears on the product page under "Description".',
            'price.required' => 'Set a price. Use 0 if the product is not for direct sale.',
            'price.numeric' => 'The price must be a number, for example 1250.00 (no currency symbol).',
            'sale_price.numeric' => 'The sale price must be a number, for example 999.00 (no currency symbol).',
            'sale_price.lt' => 'The sale price must be lower than the regular price. Leave it empty to take the product off sale.',
            'sale_ends_at.after' => 'The sale end date must come after the sale start date.',
            'collection_id.required' => 'Choose the collection this product belongs to. Every product must sit in a collection, so "no collection" is not an option -- create a collection first if none fits.',
            'collection_id.exists' => 'That collection no longer exists -- it was probably deleted in another tab. Refresh the page and pick another one.',
            'price.min' => 'The price cannot be negative. Use 0 for a product that is not sold directly.',
            'sale_price.min' => 'The sale price cannot be negative. Leave it empty to take the product off sale.',
            'sale_starts_at.date' => 'The sale start date is not a real date. Use the date picker, or clear the field.',
            'sale_ends_at.date' => 'The sale end date is not a real date. Use the date picker, or clear the field.',
            'currency.required' => 'Pick the currency this product is priced in.',
            'sku.unique' => 'Another product already uses this SKU, and SKUs must be unique. Change it, or clear the field to leave this product without one. (A deleted product can still be holding the code.)',
            'sku.max' => 'The SKU is too long. Keep it under 255 characters.',
            'currency.in' => 'That currency is not configured. Pick one of: ' . implode(', ', array_keys(config('currencies.rates'))) . '.',
            'quantity.integer' => 'Quantity must be a whole number of units, for example 3.',
            'quantity.min' => 'Quantity cannot be negative. Use 0 to mark the product out of stock.',
            'size_chart_id.exists' => 'That size chart no longer exists. Pick another one or choose "No size chart".',
            'image.image' => 'The main image must be a picture file (' . str_replace(',', ', ', self::IMAGE_MIMES) . ').',
            'image.mimes' => 'The main image format is not supported. Use one of: ' . str_replace(',', ', ', self::IMAGE_MIMES) . '.',
            'images.*.image' => 'Every gallery upload must be a picture file (' . str_replace(',', ', ', self::IMAGE_MIMES) . ').',
            'images.*.mimes' => 'One of the gallery images uses an unsupported format. Use one of: ' . str_replace(',', ', ', self::IMAGE_MIMES) . '.',
            'remove_images.*.exists' => 'One of the images you asked to remove no longer exists. Refresh the page and try again.',
            'colors.*.name.max' => 'Colour names must be 60 characters or fewer.',
            'colors.*.hex_code.regex' => 'Colour codes must look like #1a2b3c or #abc.',
            'sizes.*.size_label.max' => 'Size labels must be 40 characters or fewer, for example "M" or "IT 42".',
            'sizes.*.quantity.integer' => 'Each size needs a whole number of units in stock, for example 3.',
            'sizes.*.quantity.min' => 'A size cannot have negative stock. Use 0 to show that size as unavailable.',
        ];
    }

    /**
     * Friendlier field names inside validation messages
     */
    private function attributeNames(): array
    {
        return [
            'collection_id' => 'collection',
            'size_chart_id' => 'size chart',
            'sale_price' => 'sale price',
            'sale_starts_at' => 'sale start date',
            'sale_ends_at' => 'sale end date',
            'is_one_of_a_kind' => 'one of a kind flag',
            'is_sold_out' => 'sold out flag',
        ];
    }

    /**
     * Map the request onto the product columns.
     *
     * @param  Product|null  $product  The product being updated, if any; used
     *                                 to fall back to stored values rather than
     *                                 to defaults when a field is left blank.
     */
    private function productAttributes(Request $request, ?Product $product = null): array
    {
        $data = $request->only([
            'title',
            'description',
            'price',
            'currency',
            'collection_id',
            'size_chart_id',
            'story',
        ]);

        // An empty sale price clears the sale entirely rather than storing 0.
        $salePrice = $request->input('sale_price');
        $data['sale_price'] = ($salePrice === null || $salePrice === '') ? null : $salePrice;
        $data['sale_starts_at'] = $data['sale_price'] === null ? null : ($request->input('sale_starts_at') ?: null);
        $data['sale_ends_at'] = $data['sale_price'] === null ? null : ($request->input('sale_ends_at') ?: null);

        // An empty SKU has to become NULL. The column is uniquely indexed and
        // MySQL treats '' as a real value, so a second product saved with a
        // blank SKU used to blow up with a duplicate-key error.
        $sku = trim((string) $request->input('sku', ''));
        $data['sku'] = $sku === '' ? null : $sku;

        // A blank quantity box means "leave it alone", not "zero". Casting ''
        // to 0 quietly took products out of stock on every save.
        $quantity = $request->input('quantity');
        $data['quantity'] = ($quantity === null || $quantity === '')
            ? ($product?->quantity ?? 1)
            : (int) $quantity;

        $data['size_chart_id'] = $request->input('size_chart_id') ?: null;
        $data['visible'] = $request->boolean('visible');
        $data['is_one_of_a_kind'] = $request->boolean('is_one_of_a_kind');
        $data['is_sold_out'] = $request->boolean('is_sold_out');

        return $data;
    }

    /**
     * Turn a low-level failure into something an admin can act on.
     *
     * Raw driver messages ("SQLSTATE[23000]... Duplicate entry") say nothing
     * about which field to change, so the known ones are translated and the
     * rest fall back to the original text plus the log reference.
     */
    private function explainFailure(\Throwable $e, string $action): string
    {
        $message = $e->getMessage();

        if ($e instanceof \Illuminate\Database\QueryException) {
            if (str_contains($message, 'products_sku_unique')) {
                return "Could not {$action}: another product already uses that SKU. Give this one a different SKU, or clear the field.";
            }

            if (str_contains($message, 'products_slug_unique')) {
                return "Could not {$action}: the web address for this title is already taken. Change the title slightly and save again.";
            }

            if (str_contains($message, 'collection_id') && str_contains($message, 'cannot be null')) {
                return "Could not {$action}: no collection was selected. Every product must belong to a collection.";
            }

            if (str_contains($message, 'foreign key constraint')) {
                return "Could not {$action}: it points at a collection or size chart that no longer exists. Refresh the page and pick a current one.";
            }

            return "Could not {$action}: the database rejected the change. Nothing was saved. Details: " . $message;
        }

        return "Could not {$action}: " . $message . ' Nothing was saved.';
    }

    /**
     * Explain, in the admin's terms, why a saved product will read as sold out.
     *
     * Availability is derived from several fields at once, so a product can
     * look fine in the form and still show "Sold out" on the storefront. Say
     * which field caused it and what to change.
     */
    private function stockWarning(Product $product): ?string
    {
        if ($product->isAvailable()) {
            return null;
        }

        $name = '"' . $product->title . '"';

        if ($product->is_sold_out) {
            return "{$name} is showing as SOLD OUT on the storefront because \"Mark as Sold Out\" is ticked. Untick it to put the product back on sale.";
        }

        if ($product->is_one_of_a_kind) {
            return "{$name} is showing as SOLD OUT because it is a one-of-a-kind product with a quantity of 0. Set Quantity to 1 or more.";
        }

        if ($product->sizes->isNotEmpty()) {
            return "{$name} is showing as SOLD OUT because every size is at 0 stock. Give at least one size a quantity above 0.";
        }

        return "{$name} is showing as SOLD OUT because its quantity is 0 and no sizes have been added. Set Quantity above 0, or add sizes with stock.";
    }

    /**
     * Replace the product colours with the rows submitted by the form.
     *
     * Rows without a name are blank repeater slots and are skipped; duplicate
     * names collapse into one because the table is uniquely indexed.
     */
    private function syncColors(Product $product, Request $request): void
    {
        // A request that never mentions colours must not wipe them.
        if (!$request->has('colors')) {
            return;
        }

        $rows = (array) $request->input('colors', []);
        $keep = [];
        $order = 0;
        $seen = [];

        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $key = mb_strtolower($name);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $color = $product->colors()->updateOrCreate(
                ['name' => $name],
                [
                    'hex_code' => $row['hex_code'] ?? null,
                    'order' => $order++,
                    'is_available' => (bool) ($row['is_available'] ?? true),
                ]
            );

            $keep[] = $color->id;
        }

        // Built off the bare model rather than the ordered relation, so the
        // DELETE never carries an ORDER BY that some drivers reject.
        ProductColor::where('product_id', $product->id)
            ->whereNotIn('id', $keep ?: [0])
            ->delete();

        $product->load('colors');
    }

    /**
     * Replace the product sizes with the rows submitted by the form.
     *
     * Rows without a label are blank repeater slots and are skipped; duplicate
     * labels collapse into one because the table is uniquely indexed. Stock for
     * a sized product lives here rather than on the product's own quantity
     * column, which is why the form had to grow these fields: without them
     * there was no way to give a sized product any stock at all, and every such
     * product read as sold out on the storefront.
     */
    private function syncSizes(Product $product, Request $request): void
    {
        // A request that never mentions sizes must not wipe them.
        if (!$request->has('sizes')) {
            return;
        }

        $keep = [];
        $seen = [];

        foreach ((array) $request->input('sizes', []) as $row) {
            $label = trim((string) ($row['size_label'] ?? ''));

            if ($label === '') {
                continue;
            }

            $key = mb_strtolower($label);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $quantity = $row['quantity'] ?? null;

            $size = $product->sizes()->updateOrCreate(
                ['size_label' => $label],
                ['quantity' => ($quantity === null || $quantity === '') ? 0 : (int) $quantity],
            );

            $keep[] = $size->id;
        }

        // Built off the bare model rather than the relation, so the DELETE
        // never carries an ORDER BY that some drivers reject.
        ProductSize::where('product_id', $product->id)
            ->whereNotIn('id', $keep ?: [0])
            ->delete();

        $product->load('sizes');
    }

    /**
     * Store uploads for a brand new product
     */
    private function storeImages(Product $product, Request $request): void
    {
        $imageService = app(ImageService::class);
        $order = 0;

        // The main image goes first when both fields are used. This used to be
        // an elseif, which silently discarded the main image whenever gallery
        // images were uploaded at the same time.
        if ($request->hasFile('image')) {
            $path = $this->compress($imageService, $request->file('image'));
            $product->images()->create(['path' => $path, 'order' => $order++]);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $this->compress($imageService, $file);
                $product->images()->create(['path' => $path, 'order' => $order++]);
            }
        }
    }

    /**
     * Delete the gallery image rows the admin ticked for removal.
     *
     * @return string[]  Storage paths to unlink once the transaction commits.
     */
    private function removeImages(Product $product, array $imageIds): array
    {
        $paths = [];

        foreach ($imageIds as $imageId) {
            $image = $product->images()->find($imageId);

            if ($image) {
                $paths[] = $image->path;
                $image->delete();
            }
        }

        return $paths;
    }

    /**
     * Append newly uploaded gallery images after the existing ones
     */
    private function appendImages(Product $product, Request $request): void
    {
        if (!$request->hasFile('images')) {
            return;
        }

        $imageService = app(ImageService::class);
        $maxOrder = $product->images()->max('order') ?? -1;

        foreach ($request->file('images') as $index => $file) {
            $path = $this->compress($imageService, $file);
            $product->images()->create(['path' => $path, 'order' => $maxOrder + 1 + $index]);
        }
    }

    /**
     * Swap out the first (main) image.
     *
     * @return string[]  Storage paths to unlink once the transaction commits.
     */
    private function replaceMainImage(Product $product, Request $request): array
    {
        if (!$request->hasFile('image')) {
            return [];
        }

        $imageService = app(ImageService::class);
        $firstImage = $product->images()->orderBy('order')->first();
        $paths = [];

        // Compress before deleting anything: a corrupt upload throws here, and
        // the product should keep the image it already had.
        $path = $this->compress($imageService, $request->file('image'));

        if ($firstImage) {
            $paths[] = $firstImage->path;
            $firstImage->delete();
        }

        $product->images()->create(['path' => $path, 'order' => 0]);

        return $paths;
    }

    /**
     * Compress one upload, turning low-level failures into a message that
     * names the offending file instead of a bare 500.
     */
    private function compress(ImageService $imageService, $file): string
    {
        try {
            $path = $imageService->compressAndStore($file, 'products');
            $this->writtenFiles[] = $path;

            return $path;
        } catch (\Throwable $e) {
            Log::error('Product image processing failed', [
                'file' => $file->getClientOriginalName(),
                'exception' => $e,
            ]);

            throw ValidationException::withMessages([
                'images' => sprintf(
                    'Could not process "%s" (%s). The file may be corrupt, or in a format this server '
                    . 'cannot read (HEIC and AVIF often are). Re-save it as JPEG or PNG and upload again. Details: %s',
                    $file->getClientOriginalName(),
                    $this->humanFileSize($file->getSize() ?: 0),
                    $e->getMessage()
                ),
            ]);
        }
    }

    /**
     * Unlink files written by a request that then failed
     */
    private function discardWrittenFiles(): void
    {
        foreach ($this->writtenFiles as $path) {
            Storage::disk('public')->delete($path);
        }

        $this->writtenFiles = [];
    }

    /**
     * Format a byte count for display in an error message
     */
    private function humanFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);

        return round($bytes / (1024 ** $power), 1) . ' ' . $units[$power];
    }
}
