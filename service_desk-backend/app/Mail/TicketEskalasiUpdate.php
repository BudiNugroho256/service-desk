<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Http\Controllers\TicketController;
use App\Mail\Traits\ThreadableMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketEskalasiUpdate extends Mailable
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
        $ticket = $this->ticket;
        $userName = $ticket->endUser?->nama_user ?? 'User';

        // ------------------------------------------------
        // 🔵 Basic Fields for Layanan, Prioritas, etc.
        // ------------------------------------------------
        $layanan = $ticket->layanan
            ? "{$ticket->layanan->group_layanan} - {$ticket->layanan->nama_layanan}"
            : 'Belum Ada';

        $prioritas = $ticket->priority
            ? "{$ticket->priority->tingkat_priority} (Dampak: {$ticket->priority->tingkat_dampak}, Urgensi: {$ticket->priority->tingkat_urgensi})"
            : 'Belum Ada';

        $picTiket = $ticket->pic?->nama_user ?? 'Belum Ada';

        // ------------------------------------------------
        // 🔵 SLA calculation including Vendor SLA
        // ------------------------------------------------
        $normalSla = $ticket->priority?->sla_duration_normal ?? 0;
        $internalSla = $ticket->escalation_to ? ($ticket->priority?->sla_duration_escalation ?? 0) : 0;
        $vendorSla = $ticket->id_eskalasi_pihak_ketiga ? ($ticket->eskalasiPihakKetiga?->tp_sla_duration ?? 0) : 0;

        $totalSla = $normalSla + $internalSla + $vendorSla;

        $dueDate = $ticket->progress_date
            ? (new TicketController)->calculateDueDateSkippingHolidays(
                $ticket->progress_date,
                $totalSla
            )
            : null;

        $dueDateFormatted = $dueDate ? $dueDate->format('Y-m-d H:i') : 'Tidak tersedia';

        // ------------------------------------------------
        // 🔵 BUILD ESCALATION MESSAGE (INTERNAL or THIRD PARTY)
        // ------------------------------------------------
        if ($ticket->id_eskalasi_pihak_ketiga) {

            // ⭐ THIRD-PARTY ESCALATION
            $vendor = $ticket->eskalasiPihakKetiga;
            $vendorPic = $vendor->tp_pic_ticket ?? '-';
            $vendorCompany = $vendor->pihakKetiga->nama_perusahaan ?? '-';
            $vendorDesc = $vendor->tp_problem_description ?? '-';

            $escalationMessage = "
                <p>
                    Tiket Anda telah <strong>dilanjutkan ke Pihak Ketiga</strong> untuk penanganan lebih lanjut.
                </p>

                <ul>
                    <li><strong>PIC Vendor:</strong> {$vendorPic}</li>
                    <li><strong>Perusahaan Vendor:</strong> {$vendorCompany}</li>
                    <li><strong>Catatan untuk Vendor:</strong> {$vendorDesc}</li>
                </ul>
            ";

        } else {

            // ⭐ INTERNAL ESCALATION
            $internalTarget = $ticket->escalationTo?->nama_user ?? 'Belum Ada';

            $escalationMessage = "
                <p>
                    Tiket Anda telah dialihkan dari <strong>{$picTiket}</strong><br>
                    ke <strong>{$internalTarget}</strong> untuk penanganan lebih lanjut.
                </p>
            ";
        }

        // ------------------------------------------------
        // 🔵 FINAL EMAIL BODY
        // ------------------------------------------------
        $body = "
            <h2>📤 Tiket Dialihkan - {$ticket->id_ticket_type}</h2>

            <p>Halo <strong>{$userName}</strong>,</p>

            {$escalationMessage}

            <p><strong>Judul:</strong> {$ticket->ticket_title}</p>

            <p><strong>Deskripsi Tiket:</strong></p>
            <div style='border:1px solid #ccc;padding:10px;margin-bottom:20px;'>{$ticket->ticket_description}</div>

            <p><strong>Detail tiket:</strong></p>
            <ul>
                <li><strong>ID Tiket:</strong> {$ticket->id_ticket_type}</li>
                <li><strong>Status:</strong> {$ticket->ticket_status}</li>
                <li><strong>Layanan:</strong> {$layanan}</li>
                <li><strong>Prioritas:</strong> {$prioritas}</li>
                <li><strong>PIC Awal:</strong> {$picTiket}</li>
                <li><strong>Tracking Point:</strong> {$this->systemComment}</li>
                <li><strong>Perkiraan Selesai (SLA Total {$totalSla} hari):</strong> {$dueDateFormatted}</li>
            </ul>

            <hr>
            <p>Pesan ini dikirim pada: " . now()->format('Y-m-d H:i') . "</p>
            <p>Terima kasih,<br>Service Desk System</p>
        ";

        return $this->subject("Re: [Ticket #{$ticket->id_ticket}] {$ticket->ticket_title}")
            ->html($body)
            ->withSymfonyMessage(function ($message) {
                $this->applyThreadHeaders($message, $this->ticket->id_ticket, false, 'eskalasi');
            });
    }
}