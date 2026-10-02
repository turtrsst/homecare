<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_users_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->patient()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('akun.dashboard'));
    }

    public function test_operational_users_land_on_operational_dashboard(): void
    {
        $coordinator = User::factory()->coordinator()->create();

        $this->post(route('login'), [
            'email' => $coordinator->email,
            'password' => 'password',
        ])->assertRedirect(route('operasional.dashboard'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'salah-total',
        ]);

        $this->assertGuest();
    }

    public function test_inactive_users_are_blocked_from_logging_in(): void
    {
        $user = User::factory()->inactive()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
    }
}
