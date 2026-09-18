<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_screen_is_accessible(): void
    {
        $response = $this->get("/admin/login");
        $response->assertStatus(200);
    }

    public function test_authenticated_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create([
            "email" => "admin@consorci.local",
            "role" => "superadmin",
        ]);

        $response = $this->actingAs($admin)->get("/admin");
        $response->assertStatus(200);
    }

    public function test_authenticated_admin_can_access_resource_indexes(): void
    {
        $admin = User::factory()->create([
            "email" => "admin2@consorci.local",
            "role" => "superadmin",
        ]);

        $routes = [
            "/admin/educational-centers",
            "/admin/erasmus-projects",
            "/admin/mobility-calls",
            "/admin/host-partners",
            "/admin/applications",
            "/admin/mobilities",
            "/admin/mobility-payments",
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($admin)->get($route);
            $response->assertStatus(200);
        }
    }
}
