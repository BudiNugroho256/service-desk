<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketStillOpenNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $senderName;
    public int $ticketId;
    public string $ticketStatus;

    public function __construct(string $senderName, int $ticketId, string $ticketStatus)
    {
        $this->senderName = $senderName;
        $this->ticketId = $ticketId;
        $this->ticketStatus = $ticketStatus;
    }

    public function build()
    {
        $body = "
            <h2>🔴 Tiket Sebelumnya Belum Selesai</h2>
            <p>Halo <strong>{$this->senderName}</strong>,</p>

            <p>Kami tidak dapat memproses tiket baru karena tiket Anda sebelumnya masih dalam status:</p>

            <p><strong>Ticket #{$this->ticketId} — {$this->ticketStatus}</strong></p>

            <p>Mohon tunggu hingga tiket tersebut diselesaikan oleh tim kami.</p>

            <p>Setelah tiket selesai, Anda dapat membuat tiket baru seperti biasa.</p>

            <p>Mohon membuat email baru untuk membuat tiket karena email anda saat ini invalid dan tidak akan masuk sistem aplikasi service desk.</p>

            <hr>
            <p>Salam,<br>Service Desk System</p>
        ";

        return $this->subject("🔴 Tiket Belum Selesai")
                    ->html($body);
    }
}