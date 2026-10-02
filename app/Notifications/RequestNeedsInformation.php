<?php

namespace App\Notifications;

use App\Models\HomecareRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestNeedsInformation extends Notification
{
    use Queueable;

    public function __construct(
        public readonly HomecareRequest $request,
        public readonly string $questions,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kami Butuh Informasi Tambahan — '.$this->request->code)
            ->greeting('Halo, '.$notifiable->firstName().'!')
            ->line('Untuk memproses pengajuan homecare '.$this->request->code.', tim kami membutuhkan informasi tambahan:')
            ->line($this->questions)
            ->action('Lengkapi Informasi', url('/akun/pengajuan/'.$this->request->code))
            ->line('Setelah Anda melengkapi, pengajuan akan kembali diproses.');
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'request_needs_information',
            'title' => 'Perlu informasi tambahan untuk '.$this->request->code,
            'message' => $this->questions,
            'url' => '/akun/pengajuan/'.$this->request->code,
            'request_code' => $this->request->code,
        ];
    }
}
