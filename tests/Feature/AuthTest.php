<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_admin_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_owner_can_authenticate_and_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@lulu.com',
            'role' => 'owner',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@lulu.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_customer_user_cannot_access_admin_console(): void
    {
        $customer = User::factory()->create([
            'email' => 'customer@lulu.com',
            'role' => 'customer',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($customer)->get('/admin');
        $response->assertRedirect('/admin/login');
    }

    public function test_customer_can_register_on_storefront(): void
    {
        $response = $this->post('/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+251911998877',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'role' => 'customer',
        ]);
    }
}
