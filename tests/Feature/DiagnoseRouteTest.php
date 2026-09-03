<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiagnoseRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_diagnose_runs_and_the_dry_run_passes_on_a_migrated_schema(): void
    {
        $admin = User::factory()->admin()->create();
        Collection::create([
            'title' => 'Spring Collection',
            'description' => 'Pieces for the season',
            'release_date' => now()->subWeek(),
            'visible' => true,
        ]);

        $response = $this->actingAs($admin)->get('/dev/cmd/diagnose');

        $response->assertOk();
        $response->assertSee('PASSED -- rolled back cleanly');
        $response->assertSee('[product_colors] =&gt; ok', false);
        $response->assertSee('[sale_price] =&gt; ok', false);
    }

    public function test_diagnose_is_closed_to_non_admins(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/dev/cmd/diagnose')->assertForbidden();
    }
}
