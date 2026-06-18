<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuspensionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test approved, non-suspended user can log in.
     */
    public function test_approved_user_can_login()
    {
        $user = User::factory()->create([
            'role' => 'student',
            'email' => 'approved@example.com',
            'password' => bcrypt('password'),
            'is_approved' => true,
            'is_suspended' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'approved@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test unapproved user login is blocked with correct message.
     */
    public function test_unapproved_user_cannot_login()
    {
        $user = User::factory()->create([
            'role' => 'student',
            'email' => 'pending@example.com',
            'password' => bcrypt('password'),
            'is_approved' => false,
            'is_suspended' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'pending@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email' => 'Your account is pending administrator approval.']);
        $this->assertGuest();
    }

    /**
     * Test suspended user login is blocked with correct message.
     */
    public function test_suspended_user_cannot_login()
    {
        $user = User::factory()->create([
            'role' => 'student',
            'email' => 'suspended@example.com',
            'password' => bcrypt('password'),
            'is_approved' => true,
            'is_suspended' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'suspended@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email' => 'Your account is suspended.']);
        $this->assertGuest();
    }

    /**
     * Test that if an active user is suspended mid-session, they are logged out on next request.
     */
    public function test_suspended_user_is_logged_out_instantly()
    {
        $user = User::factory()->create([
            'role' => 'student',
            'is_approved' => true,
            'is_suspended' => false,
        ]);

        // Log in and verify authenticated session
        $this->actingAs($user);
        $response = $this->get('/dashboard');
        $response->assertStatus(200);

        // Suspend the user
        $user->update(['is_suspended' => true]);

        // Send next request
        $nextResponse = $this->get('/dashboard');

        // Verify redirection to login with the correct error
        $nextResponse->assertRedirect('/login');
        $nextResponse->assertSessionHasErrors(['email' => 'Your account is suspended.']);
        $this->assertGuest();
    }
}
