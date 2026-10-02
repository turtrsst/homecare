<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:191', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:32', 'regex:/^[0-9+\-\s]+$/'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama lengkap',
            'email' => 'Email',
            'phone' => 'Nomor telepon',
            'password' => 'Kata sandi',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, tanda +, dan strip.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
        ];
    }

    public function createUserPayload(): array
    {
        return [
            'name' => $this->validated('name'),
            'email' => $this->validated('email'),
            'phone' => $this->validated('phone'),
            'password' => $this->validated('password'),
            'role' => UserRole::Patient,
        ];
    }
}
