<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReplyIgnoredNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $senderName;
    public int $ticketId;
    public string $status;     // 'Closed' atau 'Cancelled'
    public string $closedAt;   // formatted datetime

    public function __construct(string $senderName, int $ticketId, string $status, string $closedAt)
    {
        $this->senderName = $senderName;
        $this->ticketId   = $ticketId;
        $this->status     = $status;
        $this->closedAt   = $closedAt;
    }

    public function build()
    {
        $body = "
            <h2>ℹ️ Balasan Tidak Diproses</h2>
            <p>Halo <strong>{$this->senderName}</strong>,</p>
            <p>Kami menerima balasan Anda untuk <strong>Ticket #{$this->ticketId}</strong>, 
            namun sistem tidak dapat memprosesnya karena tiket tersebut berstatus 
            <strong>{$this->status}</strong> sejak <strong>{$this->closedAt}</strong>.</p>

            <p>Jika masih ada kendala, silakan buat tiket baru dengan format subjek berikut:</p>
            <div style='border:1px dashed #888; padding:10px; margin:10px 0;'>
                <strong>Open Ticket - Judul Permasalahan Anda</strong>
            </div>
            <p>Contoh:</p>
            <div style='border:1px solid #ccc; padding:10px; margin-bottom:20px;'>
                Open Ticket - Tidak bisa login ke sistem
            </div>

            <p>Terima kasih atas pengertiannya.</p>
            <hr>
            <p>Salam,<br>Service Desk System</p>
        ";

        return $this->subject("Balasan Tidak Diproses untuk Ticket #{$this->ticketId}")
                    ->html($body);
    }
}
