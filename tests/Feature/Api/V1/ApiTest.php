<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\Support\MakesHomecareData;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use DatabaseTransactions, MakesHomecareData;

    public function test_services_endpoint_is_public_and_uses_envelope(): void
    {
        $service = $this->makeService(['name' => 'Fisioterapi API']);

        $response = $this->getJson('/api/v1/services');

        $response->assertOk()
            ->assertJsonStructure([
                'success', 'message', 'data' => [
                    '*' => ['id', 'slug', 'name', 'category', 'price', 'duration_minutes'],
                ],
            ])
            ->assertJsonPath('success', true);

        $this->assertSame('Fisioterapi API', $response->json('data.0.name'));
    }

    public function test_service_detail_by_slug(): void
    {
        $service = $this->makeService();

        $this->getJson('/api/v1/services/'.$service->slug)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', $service->slug);
    }

    public function test_protected_endpoints_require_token(): void
    {
        $this->getJson('/api/v1/homecare')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);

        $this->postJson('/api/v1/homecare', [])
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_patient_can_create_request_via_api(): void
    {
        $user = $this->makePatientUser();
        $profile = $user->patientProfiles()->firstOrFail();
        $address = $profile->addresses()->firstOrFail();
        $service = $this->makeService();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/homecare', [
            'patient_profile_id' => $profile->id,
            'patient_address_id' => $address->id,
            'service_ids' => [$service->id],
            'complaint' => 'Butuh perawatan luka pasca operasi di rumah setiap hari.',
            'preferred_date' => now()->addDays(2)->toDateString(),
            'preferred_time_window' => 'morning',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'submitted');

        $code = $response->json('data.code');
        $this->assertMatchesRegularExpression('/^HC-\d{6}-\d{4}$/', $code);

        $this->getJson('/api/v1/homecare/'.$code)
            ->assertOk()
            ->assertJsonPath('data.code', $code);

        $this->getJson('/api/v1/homecare/'.$code.'/status')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_api_validation_errors_use_envelope(): void
    {
        $user = $this->makePatientUser();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/homecare', [
            'complaint' => 'pendek',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['success', 'message', 'errors']);
    }

    public function test_patient_cannot_access_another_patients_request_via_api(): void
    {
        $owner = $this->makePatientUser();
        $intruder = $this->makePatientUser();
        $request = $this->makeSubmittedRequest($owner);

        Sanctum::actingAs($intruder);

        $this->getJson('/api/v1/homecare/'.$request->code)->assertForbidden();
    }

    public function test_api_register_and_login_flow(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Pasien API',
            'email' => 'api@example.com',
            'phone' => '081234567890',
            'password' => 'rahasia-api-1',
            'password_confirmation' => 'rahasia-api-1',
        ])->assertCreated()->assertJsonPath('success', true);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'api@example.com',
            'password' => 'rahasia-api-1',
        ]);

        $login->assertOk()->assertJsonPath('success', true);
        $this->assertNotEmpty($login->json('data.token'));

        $token = $login->json('data.token');

        $this->withToken($token)->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'api@example.com');

        $this->withToken($token)->getJson('/api/v1/patients')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->withToken($token)->postJson('/api/v1/auth/logout')
            ->assertOk();

        // Guard menahan cache user antar-request dalam satu test process;
        // bersihkan agar permintaan berikutnya benar-benar diautentikasi ulang.
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    }

    public function test_unknown_api_route_returns_json_404_envelope(): void
    {
        $this->getJson('/api/v1/tidak-ada')
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }
}
