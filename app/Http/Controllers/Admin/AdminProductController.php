<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\Collection;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

                $this->syncColors($product, $request->input('colors', []));
                $this->storeImages($product, $request);

                return $product;
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Admin product creation failed', [
                'title' => $request->input('title'),
                'exception' => $e,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Could not create "' . $request->input('title') . '": ' . $e->getMessage());
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product "' . $product->title . '" created successfully.');
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

        $data = $this->productAttributes($request);
        $data['collection_id'] = $request->input('collection_id') ?: null;
        // Keep the existing slug when the title has not changed so live links
        // survive; otherwise re-derive it, de-duplicated against other products.
        $data['slug'] = $request->title === $product->title
            ? $product->slug
            : Product::generateUniqueSlug($request->title, $product->id);

        try {
            DB::transaction(function () use ($request, $product, $data) {
                $this->removeImages($product, $request->input('remove_images', []));
                $this->appendImages($product, $request);
                $this->replaceMainImage($product, $request);
                $this->syncColors($product, $request->input('colors', []));

                $product->update($data);
            });
        } catch (\Throwable $e) {
            Log::error('Admin product update failed', [
                'product_id' => $product->id,
                'exception' => $e,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Could not save "' . $product->title . '": ' . $e->getMessage());
        }

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Product "' . $product->title . '" updated successfully.');
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

        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'sale_starts_at' => 'nullable|date',
            'sale_ends_at' => $saleEndsRules,
            'collection_id' => ($product ? 'nullable' : 'required') . '|exists:collections,id',
            'currency' => "required|string|in:$currencies",
            'sku' => 'nullable|string|max:255',
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
            'description.required' => 'Add a description. It appears on the product page under "Description".',
            'price.required' => 'Set a price. Use 0 if the product is not for direct sale.',
            'price.numeric' => 'The price must be a number, for example 1250.00 (no currency symbol).',
            'sale_price.numeric' => 'The sale price must be a number, for example 999.00 (no currency symbol).',
            'sale_price.lt' => 'The sale price must be lower than the regular price. Leave it empty to take the product off sale.',
            'sale_ends_at.after' => 'The sale end date must come after the sale start date.',
            'collection_id.required' => 'Choose the collection this product belongs to.',
            'collection_id.exists' => 'That collection no longer exists. Refresh the page and pick another one.',
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
     * Map the request onto the product columns
     */
    private function productAttributes(Request $request): array
    {
        $data = $request->only([
            'title',
            'description',
            'price',
            'currency',
            'collection_id',
            'sku',
            'quantity',
            'size_chart_id',
            'story',
        ]);

        // An empty sale price clears the sale entirely rather than storing 0.
        $salePrice = $request->input('sale_price');
        $data['sale_price'] = ($salePrice === null || $salePrice === '') ? null : $salePrice;
        $data['sale_starts_at'] = $data['sale_price'] === null ? null : ($request->input('sale_starts_at') ?: null);
        $data['sale_ends_at'] = $data['sale_price'] === null ? null : ($request->input('sale_ends_at') ?: null);

        $data['quantity'] = $request->input('quantity', 1);
        $data['visible'] = $request->boolean('visible');
        $data['is_one_of_a_kind'] = $request->boolean('is_one_of_a_kind');
        $data['is_sold_out'] = $request->boolean('is_sold_out');

        return $data;
    }

    /**
     * Replace the product colours with the rows submitted by the form.
     *
     * Rows without a name are blank repeater slots and are skipped; duplicate
     * names collapse into one because the table is uniquely indexed.
     */
    private function syncColors(Product $product, array $rows): void
    {
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
     * Store uploads for a brand new product
     */
    private function storeImages(Product $product, Request $request): void
    {
        if ($request->hasFile('images')) {
            $imageService = app(ImageService::class);

            foreach ($request->file('images') as $index => $file) {
                $path = $this->compress($imageService, $file);
                $product->images()->create(['path' => $path, 'order' => $index]);
            }
        } elseif ($request->hasFile('image')) {
            $path = $this->compress(app(ImageService::class), $request->file('image'));
            $product->images()->create(['path' => $path, 'order' => 0]);
        }
    }

    /**
     * Delete the gallery images the admin ticked for removal
     */
    private function removeImages(Product $product, array $imageIds): void
    {
        foreach ($imageIds as $imageId) {
            $image = $product->images()->find($imageId);

            if ($image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }
        }
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
     * Swap out the first (main) image
     */
    private function replaceMainImage(Product $product, Request $request): void
    {
        if (!$request->hasFile('image')) {
            return;
        }

        $imageService = app(ImageService::class);
        $firstImage = $product->images()->orderBy('order')->first();

        if ($firstImage) {
            Storage::disk('public')->delete($firstImage->path);
            $firstImage->delete();
        }

        $path = $this->compress($imageService, $request->file('image'));
        $product->images()->create(['path' => $path, 'order' => 0]);
    }

    /**
     * Compress one upload, turning low-level failures into a message that
     * names the offending file instead of a bare 500.
     */
    private function compress(ImageService $imageService, $file): string
    {
        try {
            return $imageService->compressAndStore($file, 'products');
        } catch (\Throwable $e) {
            Log::error('Product image processing failed', [
                'file' => $file->getClientOriginalName(),
                'exception' => $e,
            ]);

            throw ValidationException::withMessages([
                'images' => sprintf(
                    'Could not process "%s" (%s). The file may be corrupt or in an unsupported format: %s',
                    $file->getClientOriginalName(),
                    $this->humanFileSize($file->getSize() ?: 0),
                    $e->getMessage()
                ),
            ]);
        }
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
