<?php

namespace App\Notifications;

use App\Models\HomecareRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi umum perubahan status pengajuan — in-app (database) + email.
 * Kanal lain (WhatsApp/SMS/push) dapat ditambahkan tanpa mengubah alur bisnis.
 */
class RequestStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public readonly HomecareRequest $request,
        public readonly string $statusLabel,
        public readonly ?string $extraMessage = null,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Status Pengajuan Homecare '.$this->request->code)
            ->greeting('Halo, '.$notifiable->firstName().'!')
            ->line('Status pengajuan homecare Anda ('.$this->request->code.') kini: '.$this->statusLabel.'.');

        if ($this->extraMessage) {
            $mail->line($this->extraMessage);
        }

        return $mail
            ->action('Lihat Pengajuan', route('akun.pengajuan.show', $this->request->code))
            ->line('Terima kasih telah mempercayai layanan homecare kami.');
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'request_status',
            'title' => 'Pengajuan '.$this->request->code.': '.$this->statusLabel,
            'message' => $this->extraMessage ?? 'Status pengajuan homecare Anda diperbarui.',
            'url' => route('akun.pengajuan.show', $this->request->code),
            'request_code' => $this->request->code,
        ];
    }
}
