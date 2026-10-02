<?php

namespace App\Http\Controllers\Operational;

use App\Enums\Profession;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operational\StoreStaffRequest;
use App\Models\HealthcareStaff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(Request $request): View
    {
        $staff = HealthcareStaff::query()
            ->with('user')
            ->withCount('assignments')
            ->orderBy('name')
            ->paginate(20);

        return view('operational.staff.index', [
            'staff' => $staff,
            'professions' => collect(Profession::cases()),
        ]);
    }

    public function create(): View
    {
        return view('operational.staff.create', [
            'staff' => new HealthcareStaff(['is_active' => true]),
            'professions' => collect(Profession::cases()),
            'linkableUsers' => $this->linkableUsers(),
            'formRoute' => route('operasional.petugas.store'),
        ]);
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['user_id'])) {
            $this->promoteUser((int) $data['user_id']);
        }

        HealthcareStaff::create($data);

        return redirect()->route('operasional.petugas.index')
            ->with('success', 'Tenaga kesehatan «'.$data['name'].'» ditambahkan.');
    }

    public function edit(HealthcareStaff $staff): View
    {
        return view('operational.staff.edit', [
            'staff' => $staff,
            'professions' => collect(Profession::cases()),
            'linkableUsers' => $this->linkableUsers($staff->user_id),
            'formRoute' => route('operasional.petugas.update', $staff),
        ]);
    }

    public function update(StoreStaffRequest $request, HealthcareStaff $staff): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['user_id'])) {
            $this->promoteUser((int) $data['user_id']);
        }

        $staff->update($data);

        return redirect()->route('operasional.petugas.index')
            ->with('success', 'Data «'.$staff->name.'» diperbarui.');
    }

    public function destroy(HealthcareStaff $staff): RedirectResponse
    {
        if ($staff->assignments()->whereIn('status', ['assigned', 'active'])->exists()) {
            $staff->update(['is_active' => false]);

            return back()->with('success', 'Petugas masih punya tugas berjalan sehingga dinonaktifkan.');
        }

        $staff->delete();

        return back()->with('success', 'Data tenaga kesehatan dihapus.');
    }

    /** User yang bisa dihubungkan ke profil nakes (belum punya profil staff). */
    private function linkableUsers(?int $current = null)
    {
        return User::query()
            ->where(fn ($q) => $q->where('role', UserRole::MedicalStaff->value)->orWhere('id', $current))
            ->whereNotIn('id', HealthcareStaff::query()
                ->whereNotNull('user_id')
                ->when($current, fn ($q) => $q->where('user_id', '!=', $current))
                ->select('user_id'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    /** Pastikan user yang dihubungkan ber-role tenaga kesehatan. */
    private function promoteUser(int $userId): void
    {
        User::whereKey($userId)->update(['role' => UserRole::MedicalStaff->value]);
    }
}
