<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductSize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantsAndSaleTest extends TestCase
{
    use RefreshDatabase;

    private function collection(): Collection
    {
        return Collection::create([
            'title' => 'Test Collection',
            'description' => 'Desc',
            'release_date' => now()->subDay(),
            'visible' => true,
        ]);
    }

    private function product(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'collection_id' => $this->collection()->id,
            'title' => 'Silk Dress',
            'description' => 'A dress',
            'price' => 1000,
            'currency' => 'EGP',
            'quantity' => 5,
            'visible' => true,
        ], $overrides));
    }

    public function test_products_with_the_same_title_get_distinct_slugs(): void
    {
        $first = $this->product();
        $second = $this->product();
        $third = $this->product();

        $this->assertSame('silk-dress', $first->slug);
        $this->assertSame('silk-dress-2', $second->slug);
        $this->assertSame('silk-dress-3', $third->slug);
    }

    public function test_slug_generation_skips_ids_it_is_told_to_ignore(): void
    {
        $product = $this->product();

        $this->assertSame(
            'silk-dress',
            Product::generateUniqueSlug('Silk Dress', $product->id)
        );
    }

    public function test_slug_falls_back_when_the_title_has_no_latin_characters(): void
    {
        $product = $this->product(['title' => '؟؟؟']);

        $this->assertSame('product', $product->slug);
    }

    public function test_a_live_sale_price_becomes_the_effective_price(): void
    {
        $product = $this->product(['sale_price' => 750]);

        $this->assertTrue($product->isOnSale());
        $this->assertSame(750.0, $product->effective_price);
        $this->assertSame(25, $product->discount_percentage);
    }

    public function test_a_sale_outside_its_window_is_not_applied(): void
    {
        $future = $this->product([
            'sale_price' => 750,
            'sale_starts_at' => now()->addDay(),
        ]);

        $past = $this->product([
            'sale_price' => 750,
            'sale_ends_at' => now()->subDay(),
        ]);

        $this->assertFalse($future->isOnSale());
        $this->assertSame(1000.0, $future->effective_price);

        $this->assertFalse($past->isOnSale());
        $this->assertSame(1000.0, $past->effective_price);
    }

    public function test_a_sale_price_at_or_above_the_list_price_is_ignored(): void
    {
        $this->assertFalse($this->product(['sale_price' => 1000])->isOnSale());
        $this->assertFalse($this->product(['sale_price' => 1200])->isOnSale());
        $this->assertFalse($this->product(['sale_price' => 0])->isOnSale());
    }

    public function test_on_sale_scope_matches_only_live_sales(): void
    {
        $live = $this->product(['sale_price' => 750]);
        $this->product(['sale_price' => 750, 'sale_starts_at' => now()->addDay()]);
        $this->product();

        $this->assertSame([$live->id], Product::onSale()->pluck('id')->all());
    }

    public function test_available_colors_excludes_the_ones_marked_unavailable(): void
    {
        $product = $this->product();

        ProductColor::create(['product_id' => $product->id, 'name' => 'Ivory', 'hex_code' => '#fffff0', 'order' => 0]);
        ProductColor::create(['product_id' => $product->id, 'name' => 'Onyx', 'hex_code' => '#101010', 'order' => 1, 'is_available' => false]);

        $product->load('colors');

        $this->assertSame(['Ivory'], $product->available_colors->pluck('name')->all());
    }

    public function test_swatch_brightness_drives_the_light_outline_flag(): void
    {
        $product = $this->product();

        $light = ProductColor::create(['product_id' => $product->id, 'name' => 'Ivory', 'hex_code' => '#fffff0']);
        $dark = ProductColor::create(['product_id' => $product->id, 'name' => 'Onyx', 'hex_code' => '#101010']);
        $noHex = ProductColor::create(['product_id' => $product->id, 'name' => 'Unset']);

        $this->assertTrue($light->is_light);
        $this->assertFalse($dark->is_light);
        $this->assertSame('#d9d9d9', $noHex->swatch_color);
    }

    public function test_adding_to_cart_requires_a_colour_and_names_the_options(): void
    {
        $product = $this->product(['is_one_of_a_kind' => true]);
        ProductColor::create(['product_id' => $product->id, 'name' => 'Ivory']);
        ProductColor::create(['product_id' => $product->id, 'name' => 'Onyx']);

        $response = $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('Ivory, Onyx', $response->json('message'));
    }

    public function test_adding_to_cart_rejects_a_colour_that_is_not_offered(): void
    {
        $product = $this->product(['is_one_of_a_kind' => true]);
        ProductColor::create(['product_id' => $product->id, 'name' => 'Ivory']);

        $response = $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'color_name' => 'Chartreuse',
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('Chartreuse', $response->json('message'));
    }

    public function test_the_same_product_in_two_colours_is_two_cart_lines(): void
    {
        $product = $this->product(['is_one_of_a_kind' => true, 'quantity' => 10]);
        ProductColor::create(['product_id' => $product->id, 'name' => 'Ivory']);
        ProductColor::create(['product_id' => $product->id, 'name' => 'Onyx']);

        $this->postJson(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1, 'color_name' => 'Ivory'])
            ->assertOk();
        $this->postJson(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1, 'color_name' => 'Onyx'])
            ->assertOk();

        $this->assertCount(2, session('cart'));
    }

    public function test_the_cart_stores_the_sale_price_not_the_list_price(): void
    {
        $product = $this->product(['is_one_of_a_kind' => true, 'sale_price' => 750]);

        $this->postJson(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1])->assertOk();

        $line = collect(session('cart'))->first();
        $this->assertSame(750.0, $line['price']);
    }

    public function test_out_of_stock_size_error_names_the_size_and_the_count(): void
    {
        $product = $this->product();
        ProductSize::create(['product_id' => $product->id, 'size_label' => 'M', 'quantity' => 2]);

        $response = $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 5,
            'size_label' => 'M',
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('Only 2 left', $response->json('message'));
        $this->assertStringContainsString('size M', $response->json('message'));
    }
}
