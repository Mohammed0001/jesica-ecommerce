<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssetCheckerService
{
    /**
     * Get all missing assets from views and database
     */
    public function getMissingAssets(): array
    {
        $missingAssets = [];

        // Check Blade view files
        $viewFiles = $this->getBladeFiles();
        foreach ($viewFiles as $file) {
            $content = File::get($file);
            $assets = $this->extractAssetsFromContent($content);

            foreach ($assets as $asset) {
                if (!$this->assetExists($asset)) {
                    $missingAssets[] = [
                        'file' => str_replace(resource_path(), '', $file),
                        'asset' => $asset,
                        'type' => $this->getAssetType($asset),
                        'source' => 'view'
                    ];
                }
            }
        }

        // Check database images
        $dbMissingAssets = $this->checkDatabaseImages();
        $missingAssets = array_merge($missingAssets, $dbMissingAssets);

        return $missingAssets;
    }

    /**
     * Get statistics about asset checking
     */
    public function getAssetStatistics(): array
    {
        $viewFiles = $this->getBladeFiles();
        $totalAssets = 0;

        foreach ($viewFiles as $file) {
            $content = File::get($file);
            $assets = $this->extractAssetsFromContent($content);
            $totalAssets += count($assets);
        }

        $missingAssets = $this->getMissingAssets();

        return [
            'total_files_checked' => count($viewFiles),
            'total_assets_found' => $totalAssets,
            'missing_assets_count' => count($missingAssets),
            'missing_by_type' => collect($missingAssets)->groupBy('type')->map->count(),
            'last_checked' => now()->toISOString(),
        ];
    }

    /**
     * Get all Blade template files
     */
    private function getBladeFiles(): array
    {
        $viewsPath = resource_path('views');
        return File::allFiles($viewsPath);
    }

    /**
     * Extract asset references from file content
     */
    private function extractAssetsFromContent(string $content): array
    {
        $assets = [];
        $patterns = [
            '/\<img[^>]+src\s*=\s*["\']([^"\']+)["\']/i',
            '/background-image\s*:\s*url\(["\']?([^"\')\s]+)["\']?\)/i',
            '/asset\s*\(\s*["\']([^"\']+)["\']\s*\)/i',
            '/Storage::url\s*\(\s*["\']([^"\']+)["\']\s*\)/i',
            '/storage_path\s*\(\s*["\']([^"\']+)["\']\s*\)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                $assets = array_merge($assets, $matches[1]);
            }
        }

        return array_unique($assets);
    }

    /**
     * Check if an asset file exists
     */
    private function assetExists(string $asset): bool
    {
        $asset = ltrim($asset, '/');

        if (File::exists(public_path($asset))) {
            return true;
        }

        if (Storage::disk('public')->exists($asset)) {
            return true;
        }

        if (Str::startsWith($asset, 'storage/')) {
            $storagePath = str_replace('storage/', '', $asset);
            if (Storage::disk('public')->exists($storagePath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get asset type for categorization
     */
    private function getAssetType(string $asset): string
    {
        $extension = pathinfo($asset, PATHINFO_EXTENSION);

        if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
            return 'image';
        } elseif (in_array(strtolower($extension), ['css'])) {
            return 'css';
        } elseif (in_array(strtolower($extension), ['js'])) {
            return 'js';
        }

        return 'other';
    }

    /**
     * Check database for missing image files
     */
    private function checkDatabaseImages(): array
    {
        $missingAssets = [];

        try {
            $imageTables = [
                'products' => ['image', 'image_url', 'featured_image'],
                'collections' => ['image', 'image_url', 'featured_image'],
                'users' => ['avatar', 'profile_image'],
                'product_images' => ['image_path', 'url'],
            ];

            foreach ($imageTables as $table => $columns) {
                if (!$this->tableExists($table)) {
                    continue;
                }

                foreach ($columns as $column) {
                    if (!$this->columnExists($table, $column)) {
                        continue;
                    }

                    $images = DB::table($table)
                        ->whereNotNull($column)
                        ->where($column, '!=', '')
                        ->pluck($column)
                        ->unique();

                    foreach ($images as $image) {
                        if (!$this->assetExists($image)) {
                            $missingAssets[] = [
                                'file' => "Database: {$table}.{$column}",
                                'asset' => $image,
                                'type' => 'database_image',
                                'source' => 'database'
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently handle database errors
        }

        return $missingAssets;
    }

    /**
     * Check if table exists
     */
    private function tableExists(string $table): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable($table);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if column exists in table
     */
    private function columnExists(string $table, string $column): bool
    {
        try {
            return DB::getSchemaBuilder()->hasColumn($table, $column);
        } catch (\Exception $e) {
            return false;
        }
    }
}
