<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PaymentOptionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed base setup
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    /**
     * Test user registration redirects to payment page instead of login.
     */
    public function test_registration_redirects_to_payment_page()
    {
        $response = $this->post('/register', [
            'name' => 'New Student',
            'email' => 'newstudent@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('register.payment'));
        
        $this->assertDatabaseHas('users', [
            'email' => 'newstudent@example.com',
            'role' => 'student',
            'is_approved' => false,
        ]);
    }

    /**
     * Test payment page displays the correct UPI merchant details and instructions.
     */
    public function test_payment_page_renders_with_correct_details()
    {
        Setting::set('payment_upi_id', 'testmerchant@upi');
        Setting::set('payment_upi_name', 'Test Merchant Academy');
        Setting::set('payment_instructions', 'Scan to pay ₹500 enrollment fee.');

        $response = $this->get(route('register.payment'));

        $response->assertStatus(200);
        $response->assertSee('testmerchant@upi');
        $response->assertSee('Test Merchant Academy');
        $response->assertSee('Scan to pay ₹500 enrollment fee.');
    }

    /**
     * Test admin can update payment details and upload QR code image.
     */
    public function test_admin_can_update_payment_settings_and_upload_qr()
    {
        $admin = User::where('role', 'admin')->first();

        // Prepare fake file upload
        $file = UploadedFile::fake()->image('test_qr.png');

        $response = $this->actingAs($admin)
            ->post(route('admin.theme.update'), [
                'site_logo_text' => 'New Logo text',
                'theme_primary_color' => '#123456',
                'theme_bg_color' => '#654321',
                'theme_sidebar_color' => '#112233',
                'payment_qr_code' => $file,
                'payment_upi_name' => 'Admin Test Payee',
                'payment_upi_id' => 'admintest@upi',
                'payment_instructions' => 'Updated instruction message.',
            ]);

        $response->assertRedirect();
        
        // Assert setting variables are updated
        $this->assertEquals('Admin Test Payee', Setting::get('payment_upi_name'));
        $this->assertEquals('admintest@upi', Setting::get('payment_upi_id'));
        $this->assertEquals('Updated instruction message.', Setting::get('payment_instructions'));

        // Assert file exists in public/uploads and path is saved
        $qrPath = Setting::get('payment_qr_code');
        $this->assertNotNull($qrPath);
        $fullPath = public_path($qrPath);
        $this->assertFileExists($fullPath);

        // Cleanup the actual test uploaded file
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
    }
}
