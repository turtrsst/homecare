<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Untuk akun tenaga kesehatan yang mendapat tugas kunjungan. */
class StaffTaskAssigned extends Notification
{
    use Queueable;

    public function __construct(public readonly Appointment $appointment) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->appointment->loadMissing(['request.patient']);

        return (new MailMessage)
            ->subject('Tugas Kunjungan Homecare — '.$this->appointment->formattedSchedule())
            ->greeting('Halo, '.$notifiable->firstName().'!')
            ->line('Anda ditugaskan pada kunjungan homecare:')
            ->line('Pasien: '.$this->appointment->request->patient->name)
            ->line('Jadwal: '.$this->appointment->formattedSchedule())
            ->action('Lihat Tugas', url('/tugas/'.$this->appointment->id))
            ->line('Mohon hadir tepat waktu dan membawa perlengkapan sesuai layanan.');
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'task_assigned',
            'title' => 'Tugas baru: kunjungan '.$this->appointment->formattedSchedule(),
            'message' => 'Pengajuan '.$this->appointment->request->code.' — pasien '.$this->appointment->request->patient->name,
            'url' => '/tugas/'.$this->appointment->id,
        ];
    }
}
