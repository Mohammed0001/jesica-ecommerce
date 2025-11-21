<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assets:check {--verbose : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for missing asset files referenced in Blade views and database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking asset files...');

        $missingAssets = [];
        $checkedFiles = 0;
        $totalAssets = 0;

        // Check Blade view files
        $viewFiles = $this->getBladeFiles();
        $this->info("📁 Found " . count($viewFiles) . " Blade files to check");

        foreach ($viewFiles as $file) {
            $content = File::get($file);
            $assets = $this->extractAssetsFromContent($content);

            foreach ($assets as $asset) {
                $totalAssets++;
                if (!$this->assetExists($asset)) {
                    $missingAssets[] = [
                        'file' => str_replace(resource_path(), '', $file),
                        'asset' => $asset,
                        'type' => $this->getAssetType($asset)
                    ];
                }
            }
            $checkedFiles++;
        }

        // Check database image fields
        $dbMissingAssets = $this->checkDatabaseImages();
        $missingAssets = array_merge($missingAssets, $dbMissingAssets);

        // Display results
        $this->displayResults($missingAssets, $checkedFiles, $totalAssets);

        return count($missingAssets) === 0 ? 0 : 1;
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
            // <img src="..."
            '/\<img[^>]+src\s*=\s*["\']([^"\']+)["\']/i',
            // background-image: url(...)
            '/background-image\s*:\s*url\(["\']?([^"\')\s]+)["\']?\)/i',
            // asset('...')
            '/asset\s*\(\s*["\']([^"\']+)["\']\s*\)/i',
            // Storage::url('...')
            '/Storage::url\s*\(\s*["\']([^"\']+)["\']\s*\)/i',
            // storage_path('...')
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
        // Clean the asset path
        $asset = ltrim($asset, '/');

        // Check public path
        $publicPath = public_path($asset);
        if (File::exists($publicPath)) {
            return true;
        }

        // Check storage disk
        if (Storage::disk('public')->exists($asset)) {
            return true;
        }

        // Check if it's a storage path
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

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $cssExtensions = ['css'];
        $jsExtensions = ['js'];

        if (in_array(strtolower($extension), $imageExtensions)) {
            return 'image';
        } elseif (in_array(strtolower($extension), $cssExtensions)) {
            return 'css';
        } elseif (in_array(strtolower($extension), $jsExtensions)) {
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
            // Common tables and columns that might contain image paths
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
                                'type' => 'database_image'
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            if ($this->option('verbose')) {
                $this->warn("Database check error: " . $e->getMessage());
            }
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

    /**
     * Display results in a formatted table
     */
    private function displayResults(array $missingAssets, int $checkedFiles, int $totalAssets): void
    {
        $this->newLine();

        if (empty($missingAssets)) {
            $this->info("✅ All assets found! Checked {$totalAssets} assets in {$checkedFiles} files.");
            return;
        }

        $this->error("❌ Found " . count($missingAssets) . " missing assets:");
        $this->newLine();

        // Group by type
        $groupedAssets = collect($missingAssets)->groupBy('type');

        foreach ($groupedAssets as $type => $assets) {
            $this->warn("📁 {$type} files:");

            $tableData = $assets->map(function ($asset) {
                return [
                    'File' => $asset['file'],
                    'Missing Asset' => $asset['asset']
                ];
            })->toArray();

            $this->table(['File', 'Missing Asset'], $tableData);
            $this->newLine();
        }

        $this->error("Total missing assets: " . count($missingAssets));
        $this->info("💡 Tip: Use 'php artisan storage:link' if storage assets are missing");
    }
}
