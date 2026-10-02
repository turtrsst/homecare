<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Services\AuditService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    /** POST /api/v1/auth/register */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create($request->createUserPayload());
            $user->patientProfiles()->create([
                'name' => $user->name,
                'relationship' => 'diri_sendiri',
            ]);

            return $user;
        });

        $this->audit->log('REGISTERED', $user, 'Registrasi via API', [], $user);

        $token = $user->createToken('mobile')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'Registrasi berhasil', Response::HTTP_CREATED);
    }

    /**
     * POST /api/v1/auth/login
     *
     * Stateless: tidak memakai sesi web — klien API murni bekerja dengan
     * Bearer token. Rate limiting ditangani middleware throttle di route.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [], ['email' => 'Email', 'password' => 'Kata sandi']);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => trans('auth.failed')]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Akun Anda sedang dinonaktifkan. Hubungi rumah sakit untuk bantuan.',
            ]);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'Login berhasil');
    }

    /** POST /api/v1/auth/logout */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        // Token Sanctum asli dihapus; pengguna yang tersaut lewat sesi web
        // (TransientToken) cukup dikeluarkan dari guard web.
        if ($token instanceof \Laravel\Sanctum\PersonalAccessToken) {
            $token->delete();
        }

        // Bila request membawa Bearer token tetapi guard menyelesaikan user
        // lewat sesi, cabut juga token pada header agar benar-benar tidak sah.
        if ($bearer = $request->bearerToken()) {
            \Laravel\Sanctum\PersonalAccessToken::findToken($bearer)?->delete();
        }

        Auth::guard('web')->logout();

        return ApiResponse::success(null, 'Anda telah keluar.');
    }

    /** GET /api/v1/auth/me */
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(new UserResource($request->user()->load('patientProfiles')));
    }
}
