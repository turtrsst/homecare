<?php

namespace App\Http\Requests;

use App\Models\Appointment;
use App\Models\HomecareRequest;
use App\Models\ServiceRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Upload dokumen sensitif: disk private, MIME + ukuran dibatasi,
 * otorisasi per entitas tujuan (attachable).
 */
class StoreAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $attachable = $this->resolveAttachable();

        if (! $attachable) {
            return false;
        }

        $user = $this->user();

        return match (true) {
            $attachable instanceof HomecareRequest => $user->can('view', $attachable),
            $attachable instanceof Appointment => $user->can('view', $attachable),
            $attachable instanceof ServiceRecord => $user->can('view', $attachable->appointment),
            default => false,
        };
    }

    public function rules(): array
    {
        $max = (int) config('homecare.uploads.max_size_kb', 5120);

        return [
            'attachable_type' => ['required', Rule::in(['homecare_request', 'appointment', 'service_record'])],
            'attachable_id' => ['required', 'integer'],
            'collection' => ['required', Rule::in(array_keys(config('homecare.uploads.collections', [])))],
            'file' => [
                'required', 'file',
                'max:'.$max,
                'mimes:jpg,jpeg,png,webp,pdf',
                'mimetypes:image/jpeg,image/png,image/webp,application/pdf',
            ],
        ];
    }

    public function attributes(): array
    {
        return ['file' => 'Berkas', 'collection' => 'Jenis dokumen'];
    }

    public function messages(): array
    {
        $max = (int) config('homecare.uploads.max_size_kb', 5120);

        return [
            'file.mimes' => 'Format berkas harus JPG, PNG, WEBP, atau PDF.',
            'file.max' => "Ukuran berkas maksimal ".round($max / 1024).' MB.',
            'file.mimetypes' => 'Format berkas tidak dikenali. Unggah ulang sebagai JPG, PNG, WEBP, atau PDF.',
        ];
    }

    public function resolveAttachable(): ?\Illuminate\Database\Eloquent\Model
    {
        return match ($this->input('attachable_type')) {
            'homecare_request' => HomecareRequest::find($this->input('attachable_id')),
            'appointment' => Appointment::find($this->input('attachable_id')),
            'service_record' => ServiceRecord::find($this->input('attachable_id')),
            default => null,
        };
    }
}
