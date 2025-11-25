<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UnratedTicketNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $senderName;
    public int $ticketId;

    public function __construct(string $senderName, int $ticketId)
    {
        $this->senderName = $senderName;
        $this->ticketId = $ticketId;
    }

    public function build()
    {
        $body = "
            <h2>🟡 Anda Belum Memberikan Rating</h2>
            <p>Halo <strong>{$this->senderName}</strong>,</p>

            <p>Sebelum membuat tiket baru, mohon berikan rating terlebih dahulu untuk tiket Anda sebelumnya:</p>

            <p><strong>Ticket #{$this->ticketId}</strong></p>

            <p>Silakan buka email penyelesaian tiket yang telah dikirim sebelumnya untuk memberikan penilaian.</p>

            <p>Setelah memberikan rating, Anda dapat membuat tiket baru seperti biasa.</p>

            <p>Mohon membuat email baru untuk membuat tiket karena email anda saat ini invalid dan tidak akan masuk sistem aplikasi service desk.</p>

            <hr>
            <p>Salam,<br>Service Desk System</p>
        ";

        return $this->subject("🟡 Mohon Berikan Rating Terlebih Dahulu")
                    ->html($body);
    }
}