<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsAndClockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed base setup
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    /**
     * Test admin can save google_analytics_id successfully and page loads it.
     */
    public function test_admin_can_save_google_analytics_id()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)
            ->post(route('admin.theme.update'), [
                'site_logo_text' => 'Branding text',
                'theme_primary_color' => '#8b5cf6',
                'theme_bg_color' => '#0f172a',
                'theme_sidebar_color' => '#020617',
                'google_analytics_id' => 'G-ANALYTICSTEST',
            ]);

        $response->assertRedirect();
        $this->assertEquals('G-ANALYTICSTEST', Setting::get('google_analytics_id'));

        // Check if analytics tag is loaded on pages
        $pageResponse = $this->actingAs($admin)->get(route('dashboard'));
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('G-ANALYTICSTEST');
        $pageResponse->assertSee('googletagmanager.com/gtag/js');
    }

    /**
     * Test pages exclude analytics tag when the Measurement ID is not configured.
     */
    public function test_pages_exclude_analytics_tag_when_empty()
    {
        Setting::set('google_analytics_id', '');

        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertDontSee('googletagmanager.com/gtag/js');
    }

    /**
     * Test online users count displays the count of active sessions.
     */
    public function test_online_users_count_displays_active_sessions()
    {
        // Log in a user to trigger rendering layout app.blade.php
        $student = User::where('role', 'student')->first();

        // Seed a dummy active session in the database
        \DB::table('sessions')->insert([
            'id' => 'dummy_session_123',
            'user_id' => $student->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'payload_data',
            'last_activity' => now()->subMinutes(1)->getTimestamp()
        ]);

        $response = $this->actingAs($student)->get(route('dashboard'));
        $response->assertStatus(200);
        
        // Assert we see the clock and online indicator
        $response->assertSee('Online');
        $response->assertSee('currently online');
        $response->assertSee('live-clock');
    }
}
