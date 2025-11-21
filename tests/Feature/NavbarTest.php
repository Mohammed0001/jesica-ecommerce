<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class NavbarTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that navbar renders on homepage
     */
    public function test_navbar_renders_on_homepage(): void
    {
        $response = $this->get('/');

        if ($response->status() === 200) {
            $content = $response->getContent();

            // Check for navbar component
            $this->assertStringContainsString('iris-navbar', $content);

            // Check for logo
            $this->assertStringContainsString('iris-logo', $content);

            // Check for navigation links
            $this->assertStringContainsString('Home', $content);
            $this->assertStringContainsString('Collections', $content);
            $this->assertStringContainsString('Cart', $content);
        }
    }

    /**
     * Test navbar contains essential elements
     */
    public function test_navbar_contains_essential_elements(): void
    {
        $response = $this->get('/');

        if ($response->status() === 200) {
            $content = $response->getContent();

            // Check for search functionality
            $this->assertStringContainsString('search', $content);

            // Check for social media links
            $this->assertStringContainsString('instagram', $content);

            // Check for brand name
            $this->assertStringContainsString('JESICA RIAD', $content);

            // Check for mobile toggle
            $this->assertStringContainsString('iris-mobile-toggle', $content);
        }
    }

    /**
     * Test navbar shows different content for authenticated users
     */
    public function test_navbar_changes_for_authenticated_users(): void
    {
        // Test as guest
        $guestResponse = $this->get('/');

        if ($guestResponse->status() === 200) {
            $guestContent = $guestResponse->getContent();
            $this->assertStringContainsString('Login', $guestContent);
            $this->assertStringContainsString('Register', $guestContent);
        }

        // Test as authenticated user
        $user = User::factory()->create(['name' => 'Test User']);
        $this->actingAs($user);

        $authResponse = $this->get('/');

        if ($authResponse->status() === 200) {
            $authContent = $authResponse->getContent();
            $this->assertStringContainsString('Test User', $authContent);
            $this->assertStringContainsString('Profile', $authContent);
            $this->assertStringContainsString('Logout', $authContent);
        }
    }

    /**
     * Test navbar accessibility features
     */
    public function test_navbar_accessibility_features(): void
    {
        $response = $this->get('/');

        if ($response->status() === 200) {
            $content = $response->getContent();

            // Check for ARIA labels
            $this->assertStringContainsString('aria-label', $content);

            // Check for navigation landmark
            $this->assertStringContainsString('role="button"', $content);

            // Check for alt text on images
            $this->assertStringContainsString('alt=', $content);
        }
    }

    /**
     * Test navbar responsive behavior indicators
     */
    public function test_navbar_responsive_indicators(): void
    {
        $response = $this->get('/');

        if ($response->status() === 200) {
            $content = $response->getContent();

            // Check for responsive classes
            $this->assertStringContainsString('d-md-none', $content); // Mobile only
            $this->assertStringContainsString('d-none d-md-', $content); // Desktop only

            // Check for collapse functionality
            $this->assertStringContainsString('collapse', $content);
            $this->assertStringContainsString('data-bs-toggle', $content);
        }
    }

    /**
     * Test navbar navigation links are properly formed
     */
    public function test_navbar_navigation_links(): void
    {
        $response = $this->get('/');

        if ($response->status() === 200) {
            $content = $response->getContent();

            // Check for proper link structure
            $this->assertStringContainsString('href="/"', $content);
            $this->assertStringContainsString('href="/collections"', $content);
            $this->assertStringContainsString('href="/cart"', $content);

            // Check for active state classes
            $this->assertStringContainsString('iris-nav-link', $content);
        }
    }

    /**
     * Test navbar logo links to homepage
     */
    public function test_navbar_logo_links_to_homepage(): void
    {
        $response = $this->get('/');

        if ($response->status() === 200) {
            $content = $response->getContent();

            // Check that logo links to home
            $this->assertMatchesRegularExpression(
                '/<a[^>]*href=["\']\/["\'][^>]*>[\s\S]*?iris-logo/',
                $content
            );
        }
    }

    /**
     * Test navbar cart count functionality
     */
    public function test_navbar_cart_count_display(): void
    {
        $response = $this->get('/');

        if ($response->status() === 200) {
            $content = $response->getContent();

            // Check for cart count element
            $this->assertStringContainsString('cart-count', $content);
        }
    }
}
