<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Attachment;
use App\Services\AttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Upload & unduhan dokumen privat. Tidak ada akses langsung ke
 * /storage/private — semua lewat policy.
 */
class AttachmentController extends Controller
{
    public function __construct(private readonly AttachmentService $attachments) {}

    public function store(StoreAttachmentRequest $request): RedirectResponse
    {
        $attachment = $this->attachments->store(
            $request->resolveAttachable(),
            $request->file('file'),
            $request->validated('collection'),
            $request->user(),
        );

        return back()->with('success', 'Dokumen «'.$attachment->original_name.'» berhasil diunggah.')
            ->with('attachment_id', $attachment->id);
    }

    public function download(Request $request, Attachment $attachment): StreamedResponse
    {
        Gate::authorize('view', $attachment);

        abort_unless($attachment->existsOnDisk(), 404, 'Berkas tidak ditemukan.');

        $disk = Storage::disk($attachment->disk);

        return response()->streamDownload(function () use ($disk, $attachment) {
            echo $disk->get($attachment->path);
        }, $attachment->original_name, [
            'Content-Type' => $attachment->mime_type ?? 'application/octet-stream',
        ]);
    }

    public function destroy(Request $request, Attachment $attachment): RedirectResponse
    {
        Gate::authorize('delete', $attachment);

        $this->attachments->delete($attachment, $request->user());

        return back()->with('success', 'Dokumen dihapus.');
    }
}
