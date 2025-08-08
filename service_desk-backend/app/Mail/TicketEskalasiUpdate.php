<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Http\Controllers\TicketController;
use App\Mail\Traits\ThreadableMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TicketEskalasiUpdate extends Mailable
{
    use Queueable, SerializesModels, ThreadableMail;

    public Ticket $ticket;
    public string $systemComment;
    public ?string $picComment;
    public int $idTicketTracking;

    public function __construct(Ticket $ticket, string $systemComment, ?string $picComment = null, int $idTicketTracking)
    {
        $this->ticket = Ticket::with('escalationTo')->find($ticket->id_ticket); // safest

        Log::info('TicketEskalasiUpdate: Ticket loaded', [
            'id_ticket' => $this->ticket->id_ticket,
            'escalation_to' => $this->ticket->escalation_to,
            'pic_eskalasi' => optional($this->ticket->escalationTo)->nama_user,
        ]);

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

        $permintaan = '';
        if (!empty($this->ticket->request?->nama_permintaan)) {
            $permintaan = "<li><strong>Permintaan:</strong> {$this->ticket->request->nama_permintaan}</li>";
        }

        $picTiket = $this->ticket->pic?->nama_user ?? 'Belum Ada';
        $picEskalasi = $this->ticket->escalationTo?->nama_user ?? 'Belum Ada';

        $priorityDays = $this->ticket->priority
            ? ($this->ticket->escalation_to ? $this->ticket->priority->sla_duration_escalation : $this->ticket->priority->sla_duration_normal)
            : 0;

        $dueDate = $this->ticket->progress_date
            ? (new TicketController)->calculateDueDateSkippingHolidays(
                $this->ticket->progress_date,
                $priorityDays
            )
            : null;

        $dueDateFormatted = $dueDate ? $dueDate->format('Y-m-d H:i') : 'Tidak tersedia';
        $analisisAwal = $this->ticket->rootcause_awal ?? 'Belum Diisi';

        $body = "
            <h2>📤 Tiket Dialihkan ke PIC Eskalasi - {$this->ticket->id_ticket_type}</h2>
            <p>Halo <strong>{$userName}</strong>,</p>
            <p>
                Tiket Anda sedang diproses oleh <strong>{$picEskalasi}</strong> setelah dialihkan dari <strong>{$picTiket}</strong>.
            </p>
        ";

        if (!empty($analisisAwal)) {
            $body .= "
                <p><strong>Analisis Awal:</strong></p>
                <div style='border:1px solid #ccc;padding:10px;margin-bottom:20px;'>
                    {$analisisAwal}
                </div>
            ";
        }

        $body .= "
            <p><strong>Judul:</strong> {$this->ticket->ticket_title}</p>

            <p><strong>Deskripsi Tiket:</strong></p>
            <div style='border:1px solid #ccc;padding:10px;margin-bottom:20px;'>{$this->ticket->ticket_description}</div>

            <p>Dengan detail tiket sebagai berikut:</p>
            <ul>
                <li><strong>ID Tiket:</strong> {$this->ticket->id_ticket_type}</li>
                <li><strong>Status:</strong> {$this->ticket->ticket_status}</li>
                <li><strong>Layanan:</strong> {$layanan}</li>
                <li><strong>Prioritas:</strong> {$prioritas}</li>
                {$permintaan}
                <li><strong>PIC Tiket:</strong> {$picTiket}</li>
                <li><strong>PIC Eskalasi:</strong> {$picEskalasi}</li>
                <li><strong>Perkiraan Selesai:</strong> {$dueDateFormatted}</li>
                <li><strong>Tracking Point:</strong> {$this->systemComment}</li>
            </ul>

            <hr>
            <p>Diproses mulai: " . now()->format('Y-m-d H:i') . "</p>
            <p>Terima kasih,<br>Service Desk System</p>
        ";

        return $this->subject("Re: [Ticket #{$this->ticket->id_ticket}] {$this->ticket->ticket_title}")
            ->html($body)
            ->withSymfonyMessage(function ($message) {
                $this->applyThreadHeaders($message, $this->ticket->id_ticket, false, 'eskalasi');
            });
    }
}