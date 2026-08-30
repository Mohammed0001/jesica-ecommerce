<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Renders the pages end to end. Blade problems (a mis-parsed directive, a
 * missing variable, a relation that is not loaded) only surface here.
 */
class StorefrontRenderTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

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
        ProductSize::create(['product_id' => $this->product->id, 'size_label' => 'L', 'quantity' => 2]);
        ProductColor::create(['product_id' => $this->product->id, 'name' => 'Ivory', 'hex_code' => '#fffff0', 'order' => 0]);
        ProductColor::create(['product_id' => $this->product->id, 'name' => 'Onyx', 'hex_code' => '#101010', 'order' => 1]);
    }

    public function test_home_page_renders_the_sale_and_a_clickable_card(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('25% Off');
        // The card element itself is the anchor.
        $response->assertSee('class="product-card"', false);
        $response->assertSee(route('products.show', $this->product), false);
    }

    public function test_the_page_title_is_composed_without_leaking_blade_directives(): void
    {
        $response = $this->get('/');

        $response->assertSee('Home | JESSICA RIAD');
        $response->assertDontSee('@else', false);
        $response->assertDontSee('@endif', false);
        $response->assertDontSee('@hasSection', false);
    }

    public function test_the_intro_loader_is_gated_on_first_visit_only(): void
    {
        $response = $this->get('/');

        $response->assertSee('jr_intro_shown', false);
        $response->assertSee('global-loader', false);
    }

    public function test_product_listing_renders_prices_and_the_stretched_link(): void
    {
        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('product-card-link', false);
        $response->assertSee('sale-price', false);
        // A product with two sizes and two colours must not add blind.
        $response->assertSee('data-needs-options="1"', false);
    }

    public function test_product_page_renders_colour_swatches_and_sale_price(): void
    {
        $response = $this->get(route('products.show', $this->product));

        $response->assertOk();
        $response->assertSee('mq-swatch', false);
        $response->assertSee('data-color="Ivory"', false);
        $response->assertSee('data-color="Onyx"', false);
        $response->assertSee('name="color_name"', false);
        $response->assertSee('25% off');
    }

    public function test_collection_page_renders(): void
    {
        $response = $this->get(route('collections.show', $this->product->collection));

        $response->assertOk();
        $response->assertSee('25% Off');
    }

    public function test_search_page_renders_the_effective_price(): void
    {
        $response = $this->get(route('search', ['q' => 'Silk']));

        $response->assertOk();
        $response->assertSee('750.00');
        $response->assertSee('25% off');
    }

    public function test_cart_page_renders_size_and_colour(): void
    {
        $this->post(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 1,
            'size_label' => 'M',
            'color_name' => 'Ivory',
        ]);

        $response = $this->get(route('cart.index'));

        $response->assertOk();
        $response->assertSee('Size: M');
        $response->assertSee('Colour: Ivory');
    }

    public function test_flash_errors_reach_the_storefront_layout(): void
    {
        // Adding without a size is rejected; the message must be visible on the
        // page the customer lands back on, not swallowed by the layout.
        $response = $this->from(route('products.show', $this->product))
            ->post(route('cart.add'), ['product_id' => $this->product->id, 'quantity' => 1])
            ->assertRedirect(route('products.show', $this->product));

        $this->followRedirects($response)
            ->assertSee('Please choose a size for &quot;Silk Dress&quot;', false)
            ->assertSee('flash flash--error', false);
    }

    public function test_missing_page_renders_the_custom_404(): void
    {
        $response = $this->get('/products/no-such-product');

        $response->assertNotFound();
        $response->assertSee('Page not found');
        $response->assertSee('Browse collections');
    }

    public function test_admin_product_form_renders_sale_and_colour_fields(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('name="sale_price"', false)
            ->assertSee('name="sale_starts_at"', false)
            ->assertSee('colorRepeater', false);

        $this->actingAs($admin)
            ->get(route('admin.products.edit', $this->product))
            ->assertOk()
            ->assertSee('value="Ivory"', false)
            ->assertSee('value="Onyx"', false)
            ->assertSee('name="sale_price"', false);
    }
}
