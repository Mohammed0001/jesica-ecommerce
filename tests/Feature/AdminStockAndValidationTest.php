<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the stock/sold-out rules and the create-and-edit validation that the
 * admin forms depend on.
 */
class AdminStockAndValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Collection $collection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->collection = Collection::create([
            'title' => 'Spring Collection',
            'description' => 'Pieces for the season',
            'release_date' => now()->subWeek(),
            'visible' => true,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Silk Dress',
            'description' => 'A dress made of silk',
            'price' => 1000,
            'currency' => 'EGP',
            'collection_id' => $this->collection->id,
            'quantity' => 5,
            'visible' => 1,
        ], $overrides);
    }

    // ------------------------------------------------------------------
    // Availability
    // ------------------------------------------------------------------

    public function test_a_product_with_stock_but_no_sizes_is_not_sold_out(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $this->payload())
            ->assertSessionHasNoErrors();

        $product = Product::firstOrFail();

        $this->assertFalse($product->is_one_of_a_kind);
        $this->assertCount(0, $product->sizes);
        $this->assertTrue($product->isAvailable(), 'A sized product with no size rows must fall back to its quantity.');
        $this->assertFalse($product->isSoldOut());
        $this->assertTrue(Product::available()->whereKey($product->id)->exists());
    }

    public function test_a_product_with_no_sizes_and_no_quantity_is_sold_out(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->payload(['quantity' => 0]));

        $product = Product::firstOrFail();

        $this->assertTrue($product->isSoldOut());
        $this->assertFalse(Product::available()->whereKey($product->id)->exists());
    }

    public function test_sizes_decide_availability_once_they_exist(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->payload([
            'quantity' => 5,
            'sizes' => [
                ['size_label' => 'S', 'quantity' => 0],
                ['size_label' => 'M', 'quantity' => 0],
            ],
        ]))->assertSessionHasNoErrors();

        $product = Product::firstOrFail()->load('sizes');

        // Every size is empty, so the product's own quantity must not rescue it.
        $this->assertCount(2, $product->sizes);
        $this->assertTrue($product->isSoldOut());
        $this->assertFalse(Product::available()->whereKey($product->id)->exists());

        $product->sizes()->where('size_label', 'M')->update(['quantity' => 3]);
        $product->load('sizes');

        $this->assertTrue($product->isAvailable());
        $this->assertTrue(Product::available()->whereKey($product->id)->exists());
    }

    public function test_saving_a_product_warns_when_it_will_read_as_sold_out(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $this->payload(['quantity' => 0]))
            ->assertSessionHas('warning');

        $this->assertStringContainsString('SOLD OUT', session('warning'));
        $this->assertStringContainsString('Set Quantity above 0', session('warning'));
    }

    // ------------------------------------------------------------------
    // Sizes repeater
    // ------------------------------------------------------------------

    public function test_sizes_are_created_updated_and_pruned(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->payload([
            'sizes' => [
                ['size_label' => 'S', 'quantity' => 2],
                ['size_label' => 'M', 'quantity' => 4],
                // Blank repeater slots must be ignored.
                ['size_label' => '', 'quantity' => 9],
                // Duplicate labels collapse: the table is uniquely indexed.
                ['size_label' => 's', 'quantity' => 7],
            ],
        ]))->assertSessionHasNoErrors();

        $product = Product::firstOrFail()->load('sizes');
        $this->assertSame(['S', 'M'], $product->sizes->pluck('size_label')->all());
        $this->assertSame(2, $product->sizes->firstWhere('size_label', 'S')->quantity);

        $this->actingAs($this->admin)->put(route('admin.products.update', $product), $this->payload([
            'sizes' => [
                ['size_label' => 'M', 'quantity' => 1],
                ['size_label' => 'L', 'quantity' => 6],
            ],
        ]))->assertSessionHasNoErrors();

        $product->refresh()->load('sizes');
        $this->assertSame(['M', 'L'], $product->sizes->pluck('size_label')->all());
        $this->assertSame(1, $product->sizes->firstWhere('size_label', 'M')->quantity);
    }

    public function test_a_negative_size_quantity_is_rejected_with_a_useful_message(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->payload([
            'sizes' => [['size_label' => 'M', 'quantity' => -1]],
        ]))->assertSessionHasErrors('sizes.0.quantity');

        $this->assertStringContainsString(
            'Use 0 to show that size as unavailable',
            session('errors')->first('sizes.0.quantity')
        );
    }

    // ------------------------------------------------------------------
    // Orders must not clobber the manual sold-out flag
    // ------------------------------------------------------------------

    public function test_ordering_a_product_without_size_rows_does_not_flag_it_sold_out(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->payload(['quantity' => 10]));
        $product = Product::firstOrFail();

        $order = Order::create([
            'user_id' => $this->admin->id,
            'order_number' => 'TEST-1',
            'status' => 'pending',
            'total_amount' => 1000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 1000,
            'subtotal' => 1000,
        ]);

        app(OrderService::class)->decrementStock($order->fresh()->load('items'));

        $this->assertFalse(
            $product->refresh()->is_sold_out,
            'Placing an order must not permanently flag a product sold out just because it has no size rows.'
        );
    }

    // ------------------------------------------------------------------
    // Validation
    // ------------------------------------------------------------------

    public function test_two_products_can_be_saved_without_an_sku(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $this->payload(['sku' => '']))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $this->payload(['title' => 'Linen Dress', 'sku' => '']))
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Product::count());
        $this->assertNull(Product::firstOrFail()->sku);
    }

    public function test_a_duplicate_sku_is_reported_against_the_field(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->payload(['sku' => 'JR-001']));

        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $this->payload(['title' => 'Linen Dress', 'sku' => 'JR-001']))
            ->assertSessionHasErrors('sku');

        $this->assertStringContainsString('SKUs must be unique', session('errors')->first('sku'));
        $this->assertSame(1, Product::count());
    }

    public function test_a_product_keeps_its_own_sku_on_update(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->payload(['sku' => 'JR-001']));
        $product = Product::firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product), $this->payload(['sku' => 'JR-001', 'price' => 1200]))
            ->assertSessionHasNoErrors();

        $this->assertSame('1200.00', $product->refresh()->price);
    }

    public function test_clearing_the_collection_on_update_is_rejected_rather_than_crashing(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->payload());
        $product = Product::firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product), $this->payload(['collection_id' => '']))
            ->assertSessionHasErrors('collection_id');

        $this->assertStringContainsString('must sit in a collection', session('errors')->first('collection_id'));
        $this->assertSame($this->collection->id, $product->refresh()->collection_id);
    }

    public function test_a_blank_quantity_on_update_leaves_stock_alone(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->payload(['quantity' => 7]));
        $product = Product::firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product), $this->payload(['quantity' => '']))
            ->assertSessionHasNoErrors();

        $this->assertSame(7, $product->refresh()->quantity, 'A blank quantity box must not zero the stock.');
    }

    public function test_a_product_can_be_unpublished_from_the_edit_form(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->payload(['visible' => 1]));
        $product = Product::firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product), $this->payload(['visible' => 0]))
            ->assertSessionHasNoErrors();

        $this->assertFalse($product->refresh()->visible);
    }

    // ------------------------------------------------------------------
    // Collections
    // ------------------------------------------------------------------

    private function collectionPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Autumn Collection',
            'description' => 'Pieces for the season',
            'release_date' => now()->addMonth()->format('Y-m-d'),
            'visible' => 1,
        ], $overrides);
    }

    public function test_a_collection_can_be_unpublished_from_the_edit_form(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.collections.update', $this->collection), $this->collectionPayload([
                'title' => $this->collection->title,
                'visible' => 0,
            ]))
            ->assertSessionHasNoErrors();

        $this->assertFalse($this->collection->refresh()->visible);
    }

    public function test_a_collection_created_with_the_box_unticked_stays_hidden(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.collections.store'), $this->collectionPayload(['visible' => 0]))
            ->assertSessionHasNoErrors();

        $this->assertFalse(Collection::where('title', 'Autumn Collection')->firstOrFail()->visible);
    }

    public function test_a_bad_release_date_is_rejected_with_a_useful_message(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.collections.store'), $this->collectionPayload(['release_date' => 'sometime']))
            ->assertSessionHasErrors('release_date');

        $this->assertStringContainsString('Use the date picker', session('errors')->first('release_date'));
    }

    public function test_a_collection_holding_soft_deleted_products_cannot_be_deleted(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->payload());
        $product = Product::firstOrFail();
        $product->delete();

        $this->actingAs($this->admin)
            ->delete(route('admin.collections.destroy', $this->collection))
            ->assertSessionHas('error');

        $this->assertStringContainsString('deleted product', session('error'));
        $this->assertDatabaseHas('collections', ['id' => $this->collection->id]);
        $this->assertNotNull(Product::withTrashed()->find($product->id));
    }
}
