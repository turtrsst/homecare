<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request)
    {
        try {
            $user = DB::transaction(function () use ($request) {
                $user = User::create($request->createUserPayload());

                // Profil pasien "Diri Sendiri" dibuat otomatis agar wizard
                // bisa langsung dimulai tanpa langkah administratif tambahan.
                $user->patientProfiles()->create([
                    'name' => $user->name,
                    'relationship' => 'diri_sendiri',
                ]);

                return $user;
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Perlindungan race condition email unik → pesan ramah.
            throw ValidationException::withMessages([
                'email' => 'Email ini baru saja terdaftar. Silakan masuk.',
            ]);
        }

        $this->audit->log('REGISTERED', $user, 'Akun pasien terdaftar', [], $user);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('akun.dashboard')
            ->with('success', 'Selamat datang, '.$user->firstName().'! Akun Anda siap digunakan.');
    }
}
