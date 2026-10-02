<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operational\StoreServiceRequest;
use App\Models\HomecareService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $services = HomecareService::query()
            ->withCount('requestItems')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('operational.services.index', ['services' => $services]);
    }

    public function create(): View
    {
        return view('operational.services.create', [
            'service' => new HomecareService(['is_active' => true, 'duration_minutes' => 60, 'sort_order' => 0]),
            'formRoute' => route('operasional.layanan.store'),
        ]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['name']);

        $service = HomecareService::create($data);

        return redirect()->route('operasional.layanan.index')
            ->with('success', 'Layanan «'.$service->name.'» ditambahkan.');
    }

    public function edit(HomecareService $service): View
    {
        return view('operational.services.edit', [
            'service' => $service,
            'formRoute' => route('operasional.layanan.update', $service),
        ]);
    }

    public function update(StoreServiceRequest $request, HomecareService $service): RedirectResponse
    {
        $data = $request->validated();

        if ($data['name'] !== $service->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $service->id);
        }

        $service->update($data);

        return redirect()->route('operasional.layanan.index')
            ->with('success', 'Layanan «'.$service->name.'» diperbarui.');
    }

    public function destroy(HomecareService $service): RedirectResponse
    {
        if ($service->requestItems()->exists()) {
            // Jangan hapus layanan yang punya riwayat — nonaktifkan saja.
            $service->update(['is_active' => false]);

            return back()->with('success', 'Layanan memiliki riwayat sehingga dinonaktifkan (tidak dihapus).');
        }

        $service->delete();

        return back()->with('success', 'Layanan dihapus.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'layanan';
        $slug = $base;
        $i = 2;

        while (HomecareService::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
