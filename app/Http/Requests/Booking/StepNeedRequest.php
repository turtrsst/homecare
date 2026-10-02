<?php

namespace App\Http\Requests\Booking;

use Illuminate\Validation\Rule;

class StepNeedRequest extends BookingStepRequest
{
    public function rules(): array
    {
        $collections = array_keys(config('homecare.uploads.collections', []));

        return [
            'complaint' => ['required', 'string', 'min:10', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'files' => ['nullable', 'array', 'max:5'],
            'files.*' => [
                'file',
                'max:' . (int) config('homecare.uploads.max_size_kb', 5120),
                'mimes:jpg,jpeg,png,webp,pdf',
                'mimetypes:image/jpeg,image/png,image/webp,application/pdf',
            ],
            'collection' => ['nullable', 'string', Rule::in($collections)],
        ];
    }

    public function attributes(): array
    {
        return [
            'complaint' => 'Kebutuhan / keluhan',
            'notes' => 'Catatan tambahan',
            'files' => 'lampiran',
            'files.*' => 'lampiran',
            'collection' => 'Jenis dokumen',
        ];
    }

    public function messages(): array
    {
        return [
            'complaint.required' => 'Ceritakan terlebih dahulu kebutuhan atau keluhan pasien.',
            'complaint.min' => 'Jelaskan kebutuhan/keluhan minimal 10 karakter agar tim kami dapat mempersiapkan pelayanan dengan baik.',
            'files.array' => 'Maksimal 5 berkas dalam satu unggahan.',
            'files.max' => 'Maksimal 5 lampiran untuk satu pengajuan.',
            'files.*.max' => 'Ukuran setiap lampiran maksimal ' . round((int) config('homecare.uploads.max_size_kb', 5120) / 1024) . ' MB.',
            'files.*.mimes' => 'Lampiran harus berupa gambar (JPG, PNG, WEBP) atau dokumen PDF.',
            'files.*.mimetypes' => 'Lampiran harus berupa gambar (JPG, PNG, WEBP) atau dokumen PDF.',
            'collection.in' => 'Jenis dokumen tidak dikenali.',
        ];
    }
}
