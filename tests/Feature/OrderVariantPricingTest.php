<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductSize;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderVariantPricingTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orders;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orders = app(OrderService::class);

        $collection = Collection::create([
            'title' => 'Spring Collection',
            'description' => 'Pieces for the season',
            'release_date' => now()->subWeek(),
            'visible' => true,
        ]);

        $this->product = Product::create([
            'collection_id' => $collection->id,
            'title' => 'Silk Dress',
            'description' => 'A dress made of silk',
            'price' => 1000,
            'sale_price' => 750,
            'currency' => 'EGP',
            'quantity' => 5,
            'visible' => true,
        ]);

        ProductSize::create(['product_id' => $this->product->id, 'size_label' => 'M', 'quantity' => 3]);
        ProductColor::create(['product_id' => $this->product->id, 'name' => 'Ivory', 'hex_code' => '#fffff0']);
    }

    private function cartLine(array $overrides = []): \Illuminate\Support\Collection
    {
        return collect([array_merge([
            'product_id' => $this->product->id,
            'quantity' => 2,
            'size_label' => 'M',
            'color_name' => 'Ivory',
        ], $overrides)]);
    }

    public function test_the_order_is_charged_at_the_sale_price(): void
    {
        $order = $this->orders->createOrder(null, $this->cartLine(), null, 'Cairo', 'Maadi', 'Egypt', [
            'email' => 'buyer@example.com',
            'name' => 'A Buyer',
            'phone' => '01000000000',
        ]);

        $item = $order->items->first();

        $this->assertSame('750.00', $item->price);
        $this->assertSame('1500.00', $item->subtotal);
        $this->assertSame(1500.0, (float) $order->subtotal);
    }

    public function test_the_order_item_records_the_chosen_colour(): void
    {
        $order = $this->orders->createOrder(null, $this->cartLine(), null, 'Cairo', 'Maadi', 'Egypt', [
            'email' => 'buyer@example.com',
            'name' => 'A Buyer',
            'phone' => '01000000000',
        ]);

        $item = $order->items->first();

        $this->assertSame('Ivory', $item->color_name);
        $this->assertSame('Size: M / Colour: Ivory', $item->variant_label);
    }

    public function test_the_snapshot_keeps_both_the_list_and_sale_price(): void
    {
        $order = $this->orders->createOrder(null, $this->cartLine(), null, 'Cairo', 'Maadi', 'Egypt', [
            'email' => 'buyer@example.com',
            'name' => 'A Buyer',
            'phone' => '01000000000',
        ]);

        $snapshot = $order->items->first()->product_snapshot;

        // assertEquals, not assertSame: the snapshot round-trips through JSON,
        // where a whole float comes back as an int.
        $this->assertEquals(1000.0, $snapshot['price']);
        $this->assertEquals(750.0, $snapshot['sale_price']);
        $this->assertTrue($snapshot['was_on_sale']);
        $this->assertSame('Ivory', $snapshot['color_name']);
    }

    public function test_validation_reports_a_colour_that_is_no_longer_offered(): void
    {
        $errors = $this->orders->validateCartItems($this->cartLine(['color_name' => 'Chartreuse']));

        $this->assertCount(1, $errors);
        $this->assertStringContainsString('Chartreuse', $errors[0]);
        $this->assertStringContainsString('Ivory', $errors[0]);
    }

    public function test_validation_reports_the_remaining_stock_for_a_size(): void
    {
        $errors = $this->orders->validateCartItems($this->cartLine(['quantity' => 10]));

        $this->assertCount(1, $errors);
        $this->assertStringContainsString('Only 3 left', $errors[0]);
        $this->assertStringContainsString('Silk Dress', $errors[0]);
    }

    public function test_a_valid_cart_produces_no_errors(): void
    {
        $this->assertSame([], $this->orders->validateCartItems($this->cartLine()));
    }
}
