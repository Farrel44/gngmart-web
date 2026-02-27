<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Filament\Pages\Auth\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Test admin authentication via Filament panel.
 *
 * Filament menggunakan Livewire untuk authentication,
 * sehingga perlu menggunakan Livewire::test() untuk form submission.
 */
class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    public function test_admin_can_authenticate(): void
    {
        /** @var Admin $admin */
        $admin = Admin::factory()->create();

        // Filament menggunakan Livewire untuk login form
        Livewire::test(Login::class)
            ->fillForm([
                'email' => $admin->email,
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_admin_cannot_authenticate_with_invalid_password(): void
    {
        /** @var Admin $admin */
        $admin = Admin::factory()->create();

        Livewire::test(Login::class)
            ->fillForm([
                'email' => $admin->email,
                'password' => 'wrong-password',
            ])
            ->call('authenticate')
            ->assertHasFormErrors();

        $this->assertGuest('admin');
    }

    public function test_admin_can_logout(): void
    {
        /** @var Admin $admin */
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->post('/admin/logout');

        $this->assertGuest('admin');
    }

    public function test_admin_dashboard_requires_authentication(): void
    {
        // Filament dashboard is at /admin, not /admin/dashboard
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_authenticated_admin_can_access_dashboard(): void
    {
        /** @var Admin $admin */
        $admin = Admin::factory()->create();

        // Filament dashboard is at /admin
        $response = $this->actingAs($admin, 'admin')->get('/admin');

        $response->assertStatus(200);
    }

    public function test_authenticated_admin_is_redirected_from_login_page(): void
    {
        /** @var Admin $admin */
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get('/admin/login');

        // Filament redirects authenticated admins to /admin
        $response->assertRedirect('/admin');
    }

    // ========================================
    // GAP: Regular User Cannot Access Admin
    // ========================================

    public function test_regular_user_cannot_access_admin_panel(): void
    {
        $user = \App\Models\User::factory()->create();

        // Regular user (web guard) should not be able to access admin panel
        $response = $this->actingAs($user)->get('/admin');

        // Filament should redirect non-admin users to admin login
        $response->assertRedirect('/admin/login');
    }
}
