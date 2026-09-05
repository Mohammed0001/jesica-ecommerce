<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AdminCollectionController extends Controller
{
    /**
     * Image formats accepted on collection uploads, matching the product form.
     */
    private const IMAGE_MIMES = 'jpeg,png,jpg,gif,webp,avif,bmp,heic,heif';

    /**
     * Display a listing of collections
     */
    public function index()
    {
        $collections = Collection::withCount(['products'])
            ->latest()
            ->paginate(20);

        return view('admin.collections.index', compact('collections'));
    }

    /**
     * Show the form for creating a new collection
     */
    public function create()
    {
        return view('admin.collections.create');
    }

    /**
     * Store a newly created collection
     */
    public function store(Request $request)
    {
        $request->validate(
            $this->rules(),
            $this->validationMessages(),
            $this->attributeNames()
        );

        $data = $request->only(['title', 'description', 'release_date']);
        $data['slug'] = Collection::generateUniqueSlug($request->title);
        $data['visible'] = $request->boolean('visible');

        // Files written before the transaction commits have to be cleaned up by
        // hand if it rolls back: Storage takes no part in the transaction.
        $writtenFiles = [];

        try {
            $collection = DB::transaction(function () use ($request, $data, &$writtenFiles) {
                $imageService = app(ImageService::class);

                if ($request->hasFile('image')) {
                    $data['image_path'] = $this->compress($imageService, $request->file('image'));
                    $writtenFiles[] = $data['image_path'];
                }

                $collection = Collection::create($data);

                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $index => $file) {
                        $path = $this->compress($imageService, $file);
                        $writtenFiles[] = $path;
                        $collection->images()->create(['path' => $path, 'order' => $index]);
                    }
                }

                if ($request->hasFile('pdf')) {
                    $path = $this->storePdf($request->file('pdf'));
                    $writtenFiles[] = $path;
                    $collection->update(['pdf_path' => $path]);
                }

                return $collection;
            });
        } catch (ValidationException $e) {
            $this->discard($writtenFiles);

            throw $e;
        } catch (\Throwable $e) {
            $this->discard($writtenFiles);

            Log::error('Admin collection creation failed', [
                'title' => $request->input('title'),
                'exception' => $e,
            ]);

            return back()
                ->withInput()
                ->with('error', $this->explainFailure($e, 'create "' . $request->input('title') . '"'));
        }

        return redirect()->route('admin.collections.index')
            ->with('success', 'Collection "' . $collection->title . '" created successfully.');
    }

    /**
     * Display the specified collection
     */
    public function show(Collection $collection)
    {
        $collection->load(['products.images']);

        return view('admin.collections.show', compact('collection'));
    }

    /**
     * Show the form for editing the specified collection
     */
    public function edit(Collection $collection)
    {
        $collection->load(['products.images']);

        return view('admin.collections.edit', compact('collection'));
    }

    /**
     * Update the specified collection
     */
    public function update(Request $request, Collection $collection)
    {
        $request->validate(
            $this->rules(true),
            $this->validationMessages(),
            $this->attributeNames()
        );

        $data = $request->only(['title', 'description', 'release_date']);
        $data['slug'] = $request->title === $collection->title
            ? $collection->slug
            : Collection::generateUniqueSlug($request->title, $collection->id);
        // Read straight from the request: the form posts an explicit 0 for an
        // unticked box. Defaulting to the stored value, as this used to, made
        // "unpublish" impossible from the edit form.
        $data['visible'] = $request->boolean('visible');

        $writtenFiles = [];
        $orphanedFiles = [];

        try {
            DB::transaction(function () use ($request, $collection, $data, &$writtenFiles, &$orphanedFiles) {
                $imageService = app(ImageService::class);

                if ($request->hasFile('image')) {
                    $data['image_path'] = $this->compress($imageService, $request->file('image'));
                    $writtenFiles[] = $data['image_path'];

                    if ($collection->image_path) {
                        $orphanedFiles[] = $collection->image_path;
                    }
                }

                $collection->update($data);

                foreach ((array) $request->input('remove_images', []) as $imageId) {
                    $image = $collection->images()->find($imageId);

                    if ($image) {
                        $orphanedFiles[] = $image->path;
                        $image->delete();
                    }
                }

                if ($request->hasFile('images')) {
                    $maxOrder = $collection->images()->max('order') ?? -1;

                    foreach ($request->file('images') as $index => $file) {
                        $path = $this->compress($imageService, $file);
                        $writtenFiles[] = $path;
                        $collection->images()->create(['path' => $path, 'order' => $maxOrder + 1 + $index]);
                    }
                }

                if ($request->hasFile('pdf')) {
                    $path = $this->storePdf($request->file('pdf'));
                    $writtenFiles[] = $path;

                    if ($collection->pdf_path) {
                        $orphanedFiles[] = $collection->pdf_path;
                    }

                    $collection->update(['pdf_path' => $path]);
                }
            });
        } catch (ValidationException $e) {
            $this->discard($writtenFiles);

            throw $e;
        } catch (\Throwable $e) {
            $this->discard($writtenFiles);

            Log::error('Admin collection update failed', [
                'collection_id' => $collection->id,
                'exception' => $e,
            ]);

            return back()
                ->withInput()
                ->with('error', $this->explainFailure($e, 'save "' . $collection->title . '"'));
        }

        // Only now that the rows are committed is it safe to unlink what they
        // used to point at.
        $this->discard($orphanedFiles);

        return redirect()->route('admin.collections.show', $collection)
            ->with('success', 'Collection "' . $collection->title . '" updated successfully.');
    }

    /**
     * Remove the specified collection
     */
    public function destroy(Collection $collection)
    {
        $title = $collection->title;

        // products.collection_id cascades on delete, so anything still pointing
        // here would be destroyed with it. Soft-deleted products count too:
        // they are invisible to the relation but very much still in the table,
        // and they may be referenced by past orders.
        $live = $collection->products()->count();
        $trashed = Product::onlyTrashed()->where('collection_id', $collection->id)->count();

        if ($live > 0 || $trashed > 0) {
            $parts = [];
            if ($live > 0) {
                $parts[] = $live . ' ' . ($live === 1 ? 'product' : 'products');
            }
            if ($trashed > 0) {
                $parts[] = $trashed . ' deleted ' . ($trashed === 1 ? 'product' : 'products') . ' kept for order history';
            }

            return redirect()->back()->with('error', sprintf(
                '"%s" still holds %s, and deleting it would delete them too. Move those products to another collection first, or hide this collection instead of deleting it.',
                $title,
                implode(' and ', $parts)
            ));
        }

        $files = array_filter(array_merge(
            [$collection->image_path, $collection->pdf_path],
            $collection->images->pluck('path')->all()
        ));

        try {
            DB::transaction(function () use ($collection) {
                foreach ($collection->images as $image) {
                    $image->delete();
                }

                $collection->delete();
            });
        } catch (\Throwable $e) {
            Log::error('Admin collection deletion failed', [
                'collection_id' => $collection->id,
                'exception' => $e,
            ]);

            return redirect()->back()
                ->with('error', $this->explainFailure($e, 'delete "' . $title . '"'));
        }

        $this->discard($files);

        return redirect()->route('admin.collections.index')
            ->with('success', '"' . $title . '" was deleted.');
    }

    /**
     * Toggle collection visibility
     */
    public function toggleVisibility(Collection $collection)
    {
        $nowVisible = !$collection->getAttribute('visible');
        $collection->update(['visible' => $nowVisible]);

        return redirect()->back()->with(
            'success',
            '"' . $collection->title . '" is now ' . ($nowVisible ? 'visible to customers' : 'hidden from the store') . '.'
        );
    }

    /**
     * Validation rules shared by store and update.
     *
     * @param  bool  $editing  Whether an existing collection is being updated.
     */
    private function rules(bool $editing = false): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'release_date' => 'required|date',
            'image' => 'nullable|image|mimes:' . self::IMAGE_MIMES,
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:' . self::IMAGE_MIMES,
            'pdf' => 'nullable|file|mimes:pdf|max:20480',
            'visible' => 'boolean',
        ];

        if ($editing) {
            $rules['remove_images'] = 'nullable|array';
            $rules['remove_images.*'] = 'integer|exists:collection_images,id';
        }

        return $rules;
    }

    /**
     * Error messages that say what went wrong and how to fix it
     */
    private function validationMessages(): array
    {
        $formats = str_replace(',', ', ', self::IMAGE_MIMES);

        return [
            'title.required' => 'Give the collection a title. It is the heading customers see on the collection page.',
            'title.max' => 'The title is too long. Keep it under 255 characters.',
            'description.required' => 'Add a description. It appears under the collection title on the storefront.',
            'release_date.required' => 'Set a release date. It is used to order collections and is shown on the collection page.',
            'release_date.date' => 'The release date is not a real date. Use the date picker, for example 2026-09-30.',
            'image.image' => 'The cover image must be a picture file (' . $formats . ').',
            'image.mimes' => 'The cover image format is not supported. Use one of: ' . $formats . '.',
            'images.*.image' => 'Every gallery upload must be a picture file (' . $formats . ').',
            'images.*.mimes' => 'One of the gallery images uses an unsupported format. Use one of: ' . $formats . '.',
            'pdf.mimes' => 'The lookbook must be a PDF file. Export it as PDF and upload again.',
            'pdf.max' => 'The lookbook PDF is larger than 20 MB. Compress it or export it at a lower resolution.',
            'remove_images.*.exists' => 'One of the images you asked to remove no longer exists. Refresh the page and try again.',
        ];
    }

    /**
     * Friendlier field names inside validation messages
     */
    private function attributeNames(): array
    {
        return [
            'image' => 'cover image',
            'images' => 'gallery images',
            'pdf' => 'lookbook PDF',
            'release_date' => 'release date',
        ];
    }

    /**
     * Compress one upload, turning low-level failures into a message that
     * names the offending file instead of a bare 500.
     */
    private function compress(ImageService $imageService, UploadedFile $file): string
    {
        try {
            return $imageService->compressAndStore($file, 'collections');
        } catch (\Throwable $e) {
            Log::error('Collection image processing failed', [
                'file' => $file->getClientOriginalName(),
                'exception' => $e,
            ]);

            throw ValidationException::withMessages([
                'images' => sprintf(
                    'Could not process "%s" (%s). The file may be corrupt, or in a format this server cannot read (HEIC and AVIF often are). Re-save it as JPEG or PNG and upload again. Details: %s',
                    $file->getClientOriginalName(),
                    $this->humanFileSize($file->getSize() ?: 0),
                    $e->getMessage()
                ),
            ]);
        }
    }

    /**
     * Store the lookbook, reporting a write failure against the field
     */
    private function storePdf(UploadedFile $file): string
    {
        $path = $file->store('collections/pdfs', 'public');

        if (!$path) {
            throw ValidationException::withMessages([
                'pdf' => sprintf(
                    'Could not save "%s" (%s). The upload directory may not be writable; check storage permissions and try again.',
                    $file->getClientOriginalName(),
                    $this->humanFileSize($file->getSize() ?: 0)
                ),
            ]);
        }

        return $path;
    }

    /**
     * Unlink files that are no longer referenced by any row
     *
     * @param  string[]  $paths
     */
    private function discard(array $paths): void
    {
        foreach (array_filter($paths) as $path) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Turn a low-level failure into something an admin can act on
     */
    private function explainFailure(\Throwable $e, string $action): string
    {
        $message = $e->getMessage();

        if ($e instanceof \Illuminate\Database\QueryException) {
            if (str_contains($message, 'collections_slug_unique')) {
                return "Could not {$action}: the web address for this title is already taken. Change the title slightly and save again.";
            }

            if (str_contains($message, 'foreign key constraint')) {
                return "Could not {$action}: products still reference this collection. Move them to another collection first.";
            }

            return "Could not {$action}: the database rejected the change. Nothing was saved. Details: " . $message;
        }

        return "Could not {$action}: " . $message . ' Nothing was saved.';
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
