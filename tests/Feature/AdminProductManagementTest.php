<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductManagementTest extends TestCase
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

    public function test_creating_two_products_with_the_same_title_succeeds(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->payload())
            ->assertRedirect(route('admin.products.index'));

        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->payload())
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame(
            ['silk-dress', 'silk-dress-2'],
            Product::orderBy('id')->pluck('slug')->all()
        );
    }

    public function test_colours_are_created_updated_and_pruned(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->payload([
            'colors' => [
                ['name' => 'Ivory', 'hex_code' => '#fffff0', 'is_available' => 1],
                ['name' => 'Onyx', 'hex_code' => '#101010', 'is_available' => 1],
                // Blank rows are the empty repeater slots; they must be ignored.
                ['name' => '', 'hex_code' => '#123456', 'is_available' => 1],
            ],
        ]));

        $product = Product::firstOrFail();
        $this->assertSame(['Ivory', 'Onyx'], $product->colors->pluck('name')->all());

        // Drop Onyx, recolour Ivory, add Sand.
        $this->actingAs($this->admin)->put(route('admin.products.update', $product), $this->payload([
            'colors' => [
                ['name' => 'Ivory', 'hex_code' => '#f0ead6', 'is_available' => 0],
                ['name' => 'Sand', 'hex_code' => '#c2b280', 'is_available' => 1],
            ],
        ]))->assertSessionHasNoErrors();

        $product->refresh()->load('colors');

        $this->assertSame(['Ivory', 'Sand'], $product->colors->pluck('name')->all());
        $this->assertSame('#f0ead6', $product->colors->firstWhere('name', 'Ivory')->hex_code);
        $this->assertFalse($product->colors->firstWhere('name', 'Ivory')->is_available);
        $this->assertSame(['Sand'], $product->available_colors->pluck('name')->all());
    }

    public function test_duplicate_colour_names_collapse_into_one_row(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->payload([
            'colors' => [
                ['name' => 'Ivory', 'hex_code' => '#fffff0', 'is_available' => 1],
                ['name' => 'ivory', 'hex_code' => '#eeeeee', 'is_available' => 1],
            ],
        ]))->assertSessionHasNoErrors();

        $this->assertSame(['Ivory'], Product::firstOrFail()->colors->pluck('name')->all());
    }

    public function test_a_sale_price_at_or_above_the_price_is_rejected_with_a_useful_message(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $this->payload(['sale_price' => 1500]));

        $response->assertSessionHasErrors('sale_price');
        $this->assertStringContainsString(
            'must be lower than the regular price',
            session('errors')->first('sale_price')
        );
    }

    public function test_a_sale_end_before_its_start_is_rejected(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->payload([
            'sale_price' => 750,
            'sale_starts_at' => now()->addWeek()->format('Y-m-d\TH:i'),
            'sale_ends_at' => now()->addDay()->format('Y-m-d\TH:i'),
        ]))->assertSessionHasErrors('sale_ends_at');
    }

    public function test_an_end_date_without_a_start_date_is_accepted(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->payload([
            'sale_price' => 750,
            'sale_ends_at' => now()->addWeek()->format('Y-m-d\TH:i'),
        ]))->assertSessionHasNoErrors();

        $this->assertTrue(Product::firstOrFail()->isOnSale());
    }

    public function test_clearing_the_sale_price_also_clears_the_sale_window(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->payload([
            'sale_price' => 750,
            'sale_ends_at' => now()->addWeek()->format('Y-m-d\TH:i'),
        ]));

        $product = Product::firstOrFail();
        $this->assertNotNull($product->sale_ends_at);

        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product), $this->payload(['sale_price' => '']))
            ->assertSessionHasNoErrors();

        $product->refresh();

        $this->assertNull($product->sale_price);
        $this->assertNull($product->sale_ends_at);
        $this->assertFalse($product->isOnSale());
    }

    public function test_the_slug_is_kept_when_the_title_does_not_change(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), $this->payload());
        $product = Product::firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product), $this->payload(['price' => 1200]));

        $this->assertSame('silk-dress', $product->refresh()->slug);
    }

    public function test_a_large_image_upload_is_accepted(): void
    {
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('Image processing needs the GD extension.');
        }

        Storage::fake('public');

        // 4000x3000: far past any previous cap, and downscaled on the server.
        $image = UploadedFile::fake()->image('shoot.jpg', 4000, 3000);

        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $this->payload(['images' => [$image]]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.products.index'));

        $this->assertCount(1, Product::firstOrFail()->images);
    }
}
