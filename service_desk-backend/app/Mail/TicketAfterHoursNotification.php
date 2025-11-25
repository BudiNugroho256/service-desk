<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketAfterHoursNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $senderName;
    public int $ticketId;
    public string $scheduledDate;

    public function __construct(string $senderName, int $ticketId, string $scheduledDate)
    {
        $this->senderName = $senderName;
        $this->ticketId = $ticketId;
        $this->scheduledDate = $scheduledDate;
    }

    public function build()
    {
        $body = "
            <h2>🕒 Tiket Diterima di Luar Jam Kerja</h2>
            <p>Halo <strong>{$this->senderName}</strong>,</p>
            
            <p>Kami telah menerima tiket Anda dengan nomor:</p>
            <p><strong>#{$this->ticketId}</strong></p>

            <p>Namun tiket ini dibuat di luar jam operasional layanan kami (08:00 - 17:00).</p>

            <p>Tiket Anda akan mulai diproses pada:</p>

            <div style='border: 1px dashed #888; padding: 10px; margin: 10px 0;'>
                <strong>{$this->scheduledDate}</strong>
            </div>

            <p>Mohon menunggu hingga waktu tersebut. Tim kami akan segera menangani tiket Anda.</p>

            <p>Terima kasih atas pengertiannya.</p>

            <hr>
            <p>Salam,<br>Service Desk System</p>
        ";

        return $this->subject('🕒 Tiket Akan Diproses Pada Jam Kerja Berikutnya')
                    ->html($body);
    }
}