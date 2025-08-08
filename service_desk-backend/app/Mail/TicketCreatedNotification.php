<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\User;
use App\Mail\Traits\ThreadableMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketCreatedNotification extends Mailable
{
    use Queueable, SerializesModels, ThreadableMail;

    public Ticket $ticket;
    public User $user;
    public string $systemComment;
    public int $idTicketTracking;

    public function __construct(Ticket $ticket, User $user, string $systemComment, int $idTicketTracking)
    {
        $this->ticket = $ticket;
        $this->user = $user;
        $this->systemComment = $systemComment;
        $this->idTicketTracking = $idTicketTracking;
    }

    public function build()
    {
        $body = "
            <h2>🎫 Tiket Berhasil Masuk</h2>
            <p>Halo <strong>{$this->user->nama_user}</strong>,</p>
            <p>Tiket Anda berhasil masuk dan sudah kami terima dengan detail berikut:</p>
            <p><strong>Judul:</strong> {$this->ticket->ticket_title}</p>
            <p><strong>Deskripsi Tiket:</strong></p>
            <div style='border: 1px solid #ccc; padding: 10px; margin-bottom: 20px;'>
                {$this->ticket->ticket_description}
            </div>
            <p><strong>Status:</strong> {$this->ticket->ticket_status}</p>
            <p><strong>Tracking Point:</strong> {$this->systemComment}</p>
            <hr>
            <p>Dibuat pada: " . $this->ticket->created_on->format('Y-m-d H:i') . "</p>
            <p>Terima kasih,<br>Service Desk System</p>
        ";

        return $this->subject("[Ticket #{$this->ticket->id_ticket}] {$this->ticket->ticket_title}")
                    ->html($body)
                    ->withSymfonyMessage(function ($message) {
                        $this->applyThreadHeaders($message, $this->ticket->id_ticket, true);
                    });
    }
}