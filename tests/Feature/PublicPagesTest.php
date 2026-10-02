<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Support\MakesHomecareData;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PublicPagesTest extends TestCase
{
    use DatabaseTransactions, MakesHomecareData;

    public function test_home_page_is_public_and_shows_featured_services(): void
    {
        $featured = $this->makeService(['is_featured' => true, 'name' => 'Perawatan Luka Premium']);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Perawatan Luka Premium')
            ->assertSee('Homecare');
    }

    public function test_home_page_shows_emergency_disclaimer(): void
    {
        $this->get('/')->assertOk()->assertSee('119');
    }

    public function test_services_catalog_groups_active_services_only(): void
    {
        $active = $this->makeService(['name' => 'Fisioterapi Rumah']);
        $this->makeService(['name' => 'Layanan Nonaktif', 'is_active' => false]);

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('Fisioterapi Rumah')
            ->assertDontSee('Layanan Nonaktif');
    }

    public function test_service_detail_is_reachable_by_slug(): void
    {
        $service = $this->makeService(['name' => 'Kunjungan Dokter Uji']);

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertSee('Kunjungan Dokter Uji')
            ->assertSee($service->formattedPrice(), false);
    }

    public function test_unknown_service_slug_returns_404(): void
    {
        $this->get('/layanan/tidak-ada-slug-ini')->assertNotFound();
    }

    public function test_static_public_pages_are_reachable(): void
    {
        foreach ([
            route('how-it-works'),
            route('faq'),
            route('contact'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_guest_is_redirected_from_patient_area(): void
    {
        $this->get(route('akun.dashboard'))->assertRedirect(route('login'));
    }
}
