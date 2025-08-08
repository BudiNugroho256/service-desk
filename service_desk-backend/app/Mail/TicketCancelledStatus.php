<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Mail\Traits\ThreadableMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketCancelledStatus extends Mailable
{
    use Queueable, SerializesModels, ThreadableMail;

    public Ticket $ticket;
    public string $systemComment;
    public ?string $picComment;
    public ?string $cancelComment;
    public int $idTicketTracking;

    public function __construct(Ticket $ticket, string $systemComment, ?string $picComment = null, ?string $cancelComment = null, int $idTicketTracking)
    {
        $this->ticket = $ticket;
        $this->systemComment = $systemComment;
        $this->picComment = $picComment;
        $this->cancelComment = $cancelComment;
        $this->idTicketTracking = $idTicketTracking;
    }

    public function build()
    {
        $userName = $this->ticket->endUser?->nama_user ?? 'User';
        $layanan = $this->ticket->layanan
            ? "{$this->ticket->layanan->group_layanan} - {$this->ticket->layanan->nama_layanan}"
            : 'Belum Ada';

        $prioritas = $this->ticket->priority
            ? "{$this->ticket->priority->tingkat_priority} (Dampak: {$this->ticket->priority->tingkat_dampak}, Urgensi: {$this->ticket->priority->tingkat_urgensi})"
            : 'Belum Ada';

        $jenisTiket = $this->ticket->ticket_type ?? 'Belum Ditentukan';
        $picTiket = $this->ticket->pic?->nama_user ?? 'Belum Ada';
        $picEskalasi = $this->ticket->escalationTo?->nama_user ?? 'Belum Ada';
        $description = $this->ticket->ticket_description;

        $body = "
            <h2>🚫 Tiket Dibatalkan</h2>
            <p>Halo <strong>{$userName}</strong>,</p>
            <p>Tiket Anda telah dibatalkan.</p>
        ";

        if (!empty($this->cancelComment)) {
            $body .= "
                <p><strong>📄 Alasan Pembatalan:</strong></p>
                <div style='border:1px solid #ccc;padding:10px;margin-bottom:20px;'>
                    {$this->cancelComment}
                </div>
            ";
        }

        $body .= "
            <p><strong>Judul:</strong> {$this->ticket->ticket_title}</p>
            <p><strong>Deskripsi Tiket:</strong></p>
            <div style='border: 1px solid #ccc; padding: 10px; margin-bottom: 20px;'>
                {$this->ticket->ticket_description}
            </div>
            <p><strong>Status:</strong> {$this->ticket->ticket_status}</p>
            <p><strong>Tracking Point:</strong> {$this->systemComment}</p>
            <hr>
            <p>Dibatalkan pada: " . now()->format('Y-m-d H:i') . "</p>
            <p>Terima kasih,<br>Service Desk System</p>
        ";

        return $this->subject("Re: [Ticket #{$this->ticket->id_ticket}] {$this->ticket->ticket_title}")
                    ->html($body)
                    ->withSymfonyMessage(function ($message) {
                        $this->applyThreadHeaders($message, $this->ticket->id_ticket, false, 'cancelled');
                    });
    }
}
