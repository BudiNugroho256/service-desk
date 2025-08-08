<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvalidSubjectNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $senderName;
    public string $originalSubject;

    public function __construct(string $senderName, string $originalSubject)
    {
        $this->senderName = $senderName;
        $this->originalSubject = $originalSubject;
    }

    public function build()
    {
        $body = "
            <h2>⚠️ Subjek Email Tidak Valid</h2>
            <p>Halo <strong>{$this->senderName}</strong>,</p>
            <p>Kami menerima email Anda dengan subjek:</p>
            <p><em>{$this->originalSubject}</em></p>
            <p>Namun, sistem tidak dapat memproses email ini karena format subjek tidak sesuai.</p>
            <p>Untuk membuat tiket baru, mohon kirim email dengan format subjek berikut:</p>
            <div style='border: 1px dashed #888; padding: 10px; margin: 10px 0;'>
                <strong>Open Ticket - Judul Permasalahan Anda</strong>
            </div>
            <p>Contoh:</p>
            <div style='border: 1px solid #ccc; padding: 10px; margin-bottom: 20px;'>
                Open Ticket - Tidak bisa login ke sistem
            </div>
            <p>Terima kasih atas perhatian Anda.</p>
            <hr>
            <p>Salam,<br>Service Desk System</p>
        ";

        return $this->subject("❌ Subjek Email Tidak Valid")
                    ->html($body);
    }
}