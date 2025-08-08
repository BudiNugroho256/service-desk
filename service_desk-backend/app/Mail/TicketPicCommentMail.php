<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Mail\Traits\ThreadableMail;

class TicketPicCommentMail extends Mailable
{
    use Queueable, SerializesModels, ThreadableMail;

    public Ticket $ticket;
    public string $picComment;
    public int $idTicketTracking;

    public function __construct(Ticket $ticket, string $picComment, int $idTicketTracking)
    {
        $this->ticket = $ticket;
        $this->picComment = $picComment;
        $this->idTicketTracking = $idTicketTracking;
    }

    public function build()
    {
        $userName = $this->ticket->endUser?->nama_user ?? 'User';
        $ticketTitle = $this->ticket->ticket_title;
        $now = now()->format('Y-m-d H:i');

        $body = "
            <p>Halo <strong>{$userName}</strong>,</p>
            <p>Berikut komentar terbaru dari PIC untuk tiket Anda:</p>
            <p><strong>🧑 Komentar PIC:</strong></p>
            <div style='border:1px solid #ccc;padding:10px;margin-bottom:20px;'>
                {$this->picComment}
            </div>

            <hr>
            <p>Waktu komentar: {$now}</p>
            <p>Terima kasih,<br>Service Desk System</p>
        ";

        return $this->subject("Re: [Ticket #{$this->ticket->id_ticket}] {$this->ticket->ticket_title}")
                    ->html($body)
                    ->withSymfonyMessage(function ($message) {
                        $this->applyThreadHeaders($message, $this->ticket->id_ticket, false, "comment-{$this->idTicketTracking}");
                    });
    }
}
