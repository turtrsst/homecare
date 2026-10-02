<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get(route('register'))->assertOk();
    }

    public function test_new_users_can_register_and_get_a_patient_profile(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Keluarga Baru',
            'email' => 'baru@example.com',
            'phone' => '081234567890',
            'password' => 'rahasia-aman-1',
            'password_confirmation' => 'rahasia-aman-1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('akun.dashboard'));

        $user = \App\Models\User::whereEmail('baru@example.com')->firstOrFail();

        $this->assertSame(UserRole::Patient, $user->role);
        $this->assertTrue($user->patientProfiles()->where('relationship', 'diri_sendiri')->exists());
    }

    public function test_registration_requires_matching_password_confirmation(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Salah Konfirmasi',
            'email' => 'salah@example.com',
            'password' => 'rahasia-aman-1',
            'password_confirmation' => 'berbeda-sekali-1',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }
}
