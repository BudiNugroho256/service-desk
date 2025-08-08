<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Mail\Traits\ThreadableMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketAssignedStatus extends Mailable
{
    use Queueable, SerializesModels, ThreadableMail;

    public Ticket $ticket;
    public string $systemComment;
    public ?string $picComment;
    public int $idTicketTracking;

    public function __construct(Ticket $ticket, string $systemComment, ?string $picComment = null, int $idTicketTracking)
    {
        $this->ticket = $ticket;
        $this->systemComment = $systemComment;
        $this->picComment = $picComment;
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

        $picTiket = $this->ticket->pic?->nama_user ?? 'Belum Ada';

        $permintaan = '';
        if (!empty($this->ticket->request?->nama_permintaan)) {
            $permintaan = "<li><strong>Permintaan:</strong> {$this->ticket->request->nama_permintaan}</li>";
        }

        $body = "
            <h2>📌 Tiket Diassign - {$this->ticket->id_ticket_type}</h2>
            <p>Halo <strong>{$userName}</strong>,</p>
            <p>Tiket Anda telah diassign ke <strong>{$picTiket} dan sedang dalam persiapan pekerjaan.</strong>:</p>

            <p><strong>Judul:</strong> {$this->ticket->ticket_title}</p>
            <p><strong>Deskripsi Tiket:</strong></p>
            <div style='border:1px solid #ccc;padding:10px;margin-bottom:20px;'>
                {$this->ticket->ticket_description}
            </div>

            <p>Dengan detail tiket sebagai berikut:</p>
            <ul>
                <li><strong>ID Tiket:</strong> {$this->ticket->id_ticket_type}</li>
                <li><strong>Status:</strong> {$this->ticket->ticket_status}</li>
                <li><strong>Layanan:</strong> {$layanan}</li>
                {$permintaan}
                <li><strong>Prioritas:</strong> {$prioritas}</li>
                <li><strong>Tracking Point:</strong> {$this->systemComment}</li>
            </ul>

            <hr>
            <p>Diassign pada: " . now()->format('Y-m-d H:i') . "</p>
            <p>Terima kasih,<br>Service Desk System</p>
        ";

        return $this->subject("Re: [Ticket #{$this->ticket->id_ticket}] {$this->ticket->ticket_title}")
                    ->html($body)
                    ->withSymfonyMessage(function ($message) {
                        $this->applyThreadHeaders($message, $this->ticket->id_ticket, false, 'assigned');
                    });
    }
}