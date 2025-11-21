<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $collections = \App\Models\Collection::all();

        if ($collections->isEmpty()) {
            $this->command->warn('No collections found. Running CollectionSeeder first...');
            $this->call(CollectionSeeder::class);
            $collections = \App\Models\Collection::all();
        }

        foreach ($collections as $collection) {
            // Create some one-of-a-kind pieces
            $oneOfAKindProducts = \App\Models\Product::factory()
                ->count(3)
                ->oneOfAKind()
                ->create([
                    'collection_id' => $collection->id,
                ]);

            // Create some multi-size pieces
            $multiSizeProducts = \App\Models\Product::factory()
                ->count(2)
                ->multiSize()
                ->create([
                    'collection_id' => $collection->id,
                ]);

            // Create images for all products
            $allProducts = $oneOfAKindProducts->merge($multiSizeProducts);
            foreach ($allProducts as $product) {
                // Create 1-3 images per product
                $imageCount = fake()->numberBetween(1, 3);
                for ($i = 0; $i < $imageCount; $i++) {
                    \App\Models\ProductImage::create([
                        'product_id' => $product->id,
                        'path' => 'picsum/600x800-' . $product->id . '-' . $i . '.jpg',
                        'alt_text' => $product->title . ' - Image ' . ($i + 1),
                        'order' => $i + 1,
                    ]);
                }
            }

            // Create product sizes for multi-size products
            $multiSizeProductsQuery = $collection->products()->where('is_one_of_a_kind', false)->get();

            foreach ($multiSizeProductsQuery as $product) {
                $sizes = ['XS', 'S', 'M', 'L', 'XL'];
                foreach ($sizes as $size) {
                    \App\Models\ProductSize::create([
                        'product_id' => $product->id,
                        'size_label' => $size,
                        'quantity' => fake()->numberBetween(1, 5),
                    ]);
                }
            }
        }
    }
}
