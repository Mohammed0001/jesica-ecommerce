<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Collection;
use App\Models\Product;

class PagesAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that essential public pages are accessible
     */
    public function test_public_pages_are_accessible(): void
    {
        $publicRoutes = [
            '/' => 'home',
            '/collections' => 'collections.index',
            '/cart' => 'cart.index',
            '/login' => 'login',
            '/register' => 'register',
        ];

        foreach ($publicRoutes as $url => $routeName) {
            $response = $this->get($url);

            // Accept 200 (OK) or 302 (redirect) responses
            $this->assertContains($response->status(), [200, 302],
                "Route {$routeName} ({$url}) should return 200 or 302, got {$response->status()}"
            );
        }
    }

    /**
     * Test collection and product detail pages
     */
    public function test_dynamic_pages_are_accessible(): void
    {
        // Create test data
        $collection = Collection::factory()->create([
            'slug' => 'test-collection',
            'visible' => true
        ]);

        $product = Product::factory()->create([
            'slug' => 'test-product',
            'visible' => true,
            'collection_id' => $collection->id
        ]);

        // Test collection detail page
        $response = $this->get("/collections/{$collection->slug}");
        $this->assertContains($response->status(), [200, 302]);

        // Test product detail page
        $response = $this->get("/products/{$product->slug}");
        $this->assertContains($response->status(), [200, 302]);
    }

    /**
     * Test authenticated user pages
     */
    public function test_authenticated_pages_redirect_or_accessible(): void
    {
        $user = User::factory()->create();

        $authRoutes = [
            '/profile' => 'profile.edit',
            '/orders' => 'orders.index',
            '/checkout' => 'checkout.index',
        ];

        // Test without authentication (should redirect to login)
        foreach ($authRoutes as $url => $routeName) {
            $response = $this->get($url);

            // Should redirect to login or return 404 if route doesn't exist
            $this->assertContains($response->status(), [302, 404],
                "Route {$routeName} ({$url}) should redirect unauthenticated users or return 404"
            );
        }

        // Test with authentication
        $this->actingAs($user);

        foreach ($authRoutes as $url => $routeName) {
            $response = $this->get($url);

            // Should be accessible or redirect (but not to login)
            $this->assertContains($response->status(), [200, 302, 404],
                "Authenticated route {$routeName} ({$url}) should be accessible"
            );

            // If redirecting, shouldn't be back to login
            if ($response->status() === 302) {
                $this->assertNotEquals(route('login'), $response->headers->get('Location'));
            }
        }
    }

    /**
     * Test admin pages require proper authorization
     */
    public function test_admin_pages_require_authorization(): void
    {
        $adminRoutes = [
            '/admin/dashboard' => 'admin.dashboard',
            '/admin/products' => 'admin.products.index',
            '/admin/collections' => 'admin.collections.index',
        ];

        // Test without authentication
        foreach ($adminRoutes as $url => $routeName) {
            $response = $this->get($url);

            // Should redirect or return 403/404
            $this->assertContains($response->status(), [302, 403, 404],
                "Admin route {$routeName} ({$url}) should not be accessible to guests"
            );
        }

        // Test with regular user
        $user = User::factory()->create();
        $this->actingAs($user);

        foreach ($adminRoutes as $url => $routeName) {
            $response = $this->get($url);

            // Should return 403 or redirect
            $this->assertContains($response->status(), [302, 403, 404],
                "Admin route {$routeName} ({$url}) should not be accessible to regular users"
            );
        }
    }

    /**
     * Test that pages return valid HTML
     */
    public function test_pages_return_valid_html(): void
    {
        $routes = ['/', '/collections', '/cart', '/login'];

        foreach ($routes as $route) {
            $response = $this->get($route);

            if ($response->status() === 200) {
                // Check that response contains HTML
                $this->assertStringContainsString('<html', $response->getContent());
                $this->assertStringContainsString('</html>', $response->getContent());

                // Check for basic structure
                $this->assertStringContainsString('<head>', $response->getContent());
                $this->assertStringContainsString('<body>', $response->getContent());
            }
        }
    }

    /**
     * Test that essential meta tags are present
     */
    public function test_pages_have_essential_meta_tags(): void
    {
        $response = $this->get('/');

        if ($response->status() === 200) {
            $content = $response->getContent();

            // Check for essential meta tags
            $this->assertStringContainsString('<meta charset=', $content);
            $this->assertStringContainsString('viewport', $content);
            $this->assertStringContainsString('<title>', $content);
        }
    }

    /**
     * Test API endpoints if they exist
     */
    public function test_api_endpoints_basic_functionality(): void
    {
        $apiRoutes = [
            '/api/cart/count',
            '/api/products/search',
        ];

        foreach ($apiRoutes as $route) {
            $response = $this->get($route);

            // API routes should return JSON or 404 if not implemented
            $this->assertContains($response->status(), [200, 404, 405],
                "API route {$route} should return valid response or 404/405 if not implemented"
            );

            if ($response->status() === 200) {
                // Should return JSON
                $this->assertJson($response->getContent());
            }
        }
    }
}
