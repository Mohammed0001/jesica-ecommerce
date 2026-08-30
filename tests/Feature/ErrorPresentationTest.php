<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Tests\TestCase;

class ErrorPresentationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Production-like: the friendly pages only take over with debug off.
        config(['app.debug' => false]);

        Route::get('/__test/boom', fn () => throw new \RuntimeException('inner detail'));
        Route::get('/__test/unavailable', fn () => throw new ServiceUnavailableHttpException());
    }

    public function test_a_server_error_renders_the_custom_page_with_a_reference(): void
    {
        $response = $this->get('/__test/boom');

        $response->assertStatus(500);
        $response->assertSee('Something broke on our side');
        // The raw exception message must not leak to the visitor.
        $response->assertDontSee('inner detail');
        $response->assertSee('quote reference');
    }

    public function test_a_503_keeps_its_own_page_rather_than_the_500_page(): void
    {
        $response = $this->get('/__test/unavailable');

        $response->assertStatus(503);
        $response->assertSee('Down for maintenance');
        $response->assertDontSee('Something broke on our side');
    }

    public function test_json_callers_get_a_structured_body_not_an_html_page(): void
    {
        $response = $this->getJson('/__test/boom');

        $response->assertStatus(500);
        $response->assertJsonStructure(['success', 'message', 'reference']);
        $this->assertFalse($response->json('success'));
        // Debug is off, so the internal message stays out of the payload.
        $this->assertNull($response->json('detail'));
    }

    public function test_a_missing_model_reads_as_a_missing_item_for_json_callers(): void
    {
        $response = $this->postJson(route('cart.add'), [
            'product_id' => 999999,
            'quantity' => 1,
        ]);

        // Validation catches it first, and says so specifically.
        $response->assertStatus(422);
        $this->assertStringContainsString(
            'no longer in the store',
            $response->json('errors.product_id.0')
        );
    }

    public function test_the_404_page_offers_a_way_forward(): void
    {
        $response = $this->get('/definitely-not-a-page');

        $response->assertNotFound();
        $response->assertSee('Page not found');
        $response->assertSee('Search the store');
    }
}
