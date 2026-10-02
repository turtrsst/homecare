<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        $destination = match (true) {
            $user->role->value === 'medical_staff' => route('tugas.index'),
            $user->isOperational() => route('operasional.dashboard'),
            default => route('akun.dashboard'),
        };

        return redirect()->intended($destination)
            ->with('success', 'Halo, '.$user->firstName().'! Anda berhasil masuk.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to(rtrim(route('home'), '/').'/')
            ->with('success', 'Anda telah keluar. Sampai jumpa!');
    }
}
