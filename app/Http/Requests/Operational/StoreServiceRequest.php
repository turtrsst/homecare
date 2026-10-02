<?php

namespace App\Http\Requests\Operational;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', \App\Models\HomecareService::class);
    }

    public function rules(): array
    {
        $service = $this->route('service');

        return [
            'code' => ['required', 'string', 'max:32', Rule::unique('homecare_services', 'code')->ignore($service?->id)],
            'name' => ['required', 'string', 'max:191'],
            'icon' => ['nullable', 'string', 'max:32', Rule::in(array_keys(\App\Models\HomecareService::ICON_OPTIONS))],
            'thumbnail' => ['nullable', 'string', 'max:191'],
            'category' => ['nullable', 'string', 'max:96'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:720'],
            'price' => ['required', 'numeric', 'min:0', 'max:100000000'],
            'price_note' => ['nullable', 'string', 'max:191'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'Kode layanan',
            'name' => 'Nama layanan',
            'icon' => 'Ikon',
            'thumbnail' => 'Thumbnail',
            'category' => 'Kategori',
            'short_description' => 'Deskripsi singkat',
            'description' => 'Deskripsi lengkap',
            'duration_minutes' => 'Perkiraan durasi (menit)',
            'price' => 'Tarif',
            'price_note' => 'Catatan tarif',
            'is_active' => 'Status aktif',
            'is_featured' => 'Layanan unggulan',
            'sort_order' => 'Urutan tampil',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
        ]);
    }
}
