<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Kabar gembira untuk pasien: jadwal + petugas ditetapkan.
 * Menyertakan "siapa yang datang, kapan, ke mana, apa yang disiapkan".
 */
class AppointmentScheduled extends Notification
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
        $this->appointment->loadMissing(['request.patient', 'assignments.staff']);

        $staffNames = $this->appointment->assignments
            ->filter(fn ($a) => $a->status->value !== 'cancelled')
            ->map(fn ($a) => $a->staff->name.' ('.$a->staff->profession->label().')')
            ->implode(', ') ?: 'tenaga kesehatan kami';

        return (new MailMessage)
            ->subject('Jadwal Kunjungan Homecare — '.$this->appointment->request->code)
            ->greeting('Halo, '.$notifiable->firstName().'!')
            ->line('Kunjungan homecare untuk '.$this->appointment->request->patient->name.' telah dijadwalkan:')
            ->line('Waktu: '.$this->appointment->formattedSchedule())
            ->line('Petugas: '.$staffNames)
            ->line('Yang perlu disiapkan: tempat yang nyaman untuk pemeriksaan, kartu identitas/berobat, serta obat dan hasil pemeriksaan terakhir (jika ada).')
            ->action('Lihat Detail', route('akun.pengajuan.show', $this->appointment->request->code))
            ->line('Bila ada perubahan, hubungi kami segera. Untuk kondisi darurat hubungi '.config('homecare.emergency_number').' atau IGD terdekat.');
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'appointment_scheduled',
            'title' => 'Kunjungan terjadwal: '.$this->appointment->formattedSchedule(),
            'message' => 'Pengajuan '.$this->appointment->request->code.' telah mendapatkan jadwal dan petugas.',
            'url' => route('akun.pengajuan.show', $this->appointment->request->code),
            'request_code' => $this->appointment->request->code,
        ];
    }
}
