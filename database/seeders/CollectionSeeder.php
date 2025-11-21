<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create featured collections
        $collections = [
            [
                'title' => 'Ethereal Dreams',
                'slug' => 'ethereal-dreams',
                'description' => 'A sophisticated collection that captures the essence of modern femininity. Each piece embodies timeless elegance with contemporary flair, designed for the discerning woman who appreciates luxury and craftsmanship.',
                'release_date' => now()->subMonths(6)->format('Y-m-d'),
                'visible' => true,
                'image_path' => 'collections/ethereal-dreams.jpg',
            ],
            [
                'title' => 'Urban Poetry',
                'slug' => 'urban-poetry',
                'description' => 'Where street style meets haute couture. This collection celebrates the dynamic energy of city life through innovative silhouettes and unexpected fabric combinations that tell the story of modern urban living.',
                'release_date' => now()->subMonths(3)->format('Y-m-d'),
                'visible' => true,
                'image_path' => 'collections/urban-poetry.jpg',
            ],
            [
                'title' => 'Minimalist Elegance',
                'slug' => 'minimalist-elegance',
                'description' => 'Clean lines and pure forms define this collection. Every piece is carefully crafted to embody the beauty of simplicity, creating versatile wardrobe essentials that transcend seasons and trends.',
                'release_date' => now()->subMonths(1)->format('Y-m-d'),
                'visible' => true,
                'image_path' => 'collections/minimalist-elegance.jpg',
            ],
            [
                'title' => 'Artisan Heritage',
                'slug' => 'artisan-heritage',
                'description' => 'A tribute to traditional craftsmanship techniques passed down through generations. This collection celebrates the artistry of handmade creation with contemporary silhouettes and luxurious materials.',
                'release_date' => now()->subMonths(4)->format('Y-m-d'),
                'visible' => true,
                'image_path' => 'collections/artisan-heritage.jpg',
            ],
        ];

        foreach ($collections as $collection) {
            \App\Models\Collection::create($collection);
        }
    }
}
