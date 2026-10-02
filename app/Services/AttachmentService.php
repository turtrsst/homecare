<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Penyimpanan dokumen sensitif: disk private, nama file di-random,
 * metadata MIME/ukuran dicatat. Unduhan selalu lewat pemeriksaan policy.
 */
class AttachmentService
{
    public function __construct(private readonly AuditService $audit) {}

    public function store(
        Model $attachable,
        UploadedFile $file,
        string $collection = 'other',
        ?User $uploader = null,
    ): Attachment {
        $disk = config('homecare.uploads.disk', 'private');
        $directory = 'attachments/'.Str::lower(class_basename($attachable)).'-'.$attachable->getKey().'/'.$collection;

        // Nama file di-random; nama asli hanya disimpan sebagai metadata.
        $path = DB::transaction(function () use ($attachable, $file, $collection, $uploader, $disk, $directory) {
            $path = $file->store($directory, ['disk' => $disk]);

            $attachment = $attachable->attachments()->create([
                'collection' => $collection,
                'uploaded_by' => $uploader?->id,
                'disk' => $disk,
                'path' => $path,
                'original_name' => substr($file->getClientOriginalName(), 0, 191),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);

            $this->audit->log('UPLOADED_ATTACHMENT', $attachable, "Dokumen diunggah: {$collection}", [
                'attachment_id' => $attachment->id,
                'collection' => $collection,
                'size' => $file->getSize(),
            ], $uploader);

            return $path;
        });

        return Attachment::query()->where('path', $path)->firstOrFail();
    }

    public function delete(Attachment $attachment, ?User $user = null): void
    {
        if ($attachment->existsOnDisk()) {
            \Illuminate\Support\Facades\Storage::disk($attachment->disk)->delete($attachment->path);
        }

        $this->audit->log('DELETED_ATTACHMENT', $attachment->attachable, "Dokumen dihapus: {$attachment->original_name}", [], $user);

        $attachment->delete();
    }

    /**
     * Berkas sementara saat wizard berjalan: disimpan di `booking-drafts/{userId}`
     * dan baru dipindah ke lokasi permanen ketika pengajuan benar-benar dikirim.
     *
     * @return array<string, mixed>|null
     */
    public function storeDraft(UploadedFile $file, User $uploader, string $collection = 'other'): ?array
    {
        if (! $file->isValid()) {
            return null;
        }

        $path = $file->store('booking-drafts/'.$uploader->id, ['disk' => config('homecare.uploads.disk', 'private')]);

        if (! is_string($path) || $path === '') {
            return null;
        }

        return [
            'path' => $path,
            'original_name' => substr($file->getClientOriginalName(), 0, 191),
            'mime_type' => $file->getClientMimeType(),
            'size' => (int) $file->getSize(),
            'collection' => $collection,
        ];
    }

    /**
     * Pindahkan seluruh lampiran draft ke lokasi permanen dan buat record Attachment.
     *
     * @param  array<int, array<string, mixed>>  $drafts
     */
    public function promoteDrafts(Model $attachable, array $drafts, ?User $uploader = null): void
    {
        if ($uploader === null || $drafts === []) {
            return;
        }

        $disk = config('homecare.uploads.disk', 'private');
        $filesystem = Storage::disk($disk);
        $prefix = 'booking-drafts/'.$uploader->id.'/';
        $collections = array_keys(config('homecare.uploads.collections', []));

        foreach ($drafts as $draft) {
            $path = is_array($draft) ? (string) ($draft['path'] ?? '') : '';

            if ($path === '' || ! str_starts_with($path, $prefix) || ! $filesystem->exists($path)) {
                continue;
            }

            $collection = in_array($draft['collection'] ?? '', $collections, true)
                ? $draft['collection']
                : 'other';

            $directory = 'attachments/'.Str::lower(class_basename($attachable)).'-'.$attachable->getKey().'/'.$collection;
            $target = $directory.'/'.basename($path);

            $filesystem->makeDirectory($directory);

            if (! $filesystem->move($path, $target)) {
                continue;
            }

            $attachment = $attachable->attachments()->create([
                'collection' => $collection,
                'uploaded_by' => $uploader->id,
                'disk' => $disk,
                'path' => $target,
                'original_name' => substr((string) ($draft['original_name'] ?? basename($target)), 0, 191),
                'mime_type' => $draft['mime_type'] ?? 'application/octet-stream',
                'size' => (int) ($draft['size'] ?? 0),
            ]);

            $this->audit->log('UPLOADED_ATTACHMENT', $attachable, "Dokumen diunggah: {$collection}", [
                'attachment_id' => $attachment->id,
                'collection' => $collection,
                'original_name' => $attachment->original_name,
            ], $uploader);
        }

        $filesystem->deleteDirectory('booking-drafts/'.$uploader->id);
    }

    public function discardDrafts(?User $uploader): void
    {
        if ($uploader === null) {
            return;
        }

        Storage::disk(config('homecare.uploads.disk', 'private'))
            ->deleteDirectory('booking-drafts/'.$uploader->id);
    }
}
