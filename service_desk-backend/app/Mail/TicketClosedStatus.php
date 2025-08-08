<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Mail\Traits\ThreadableMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketClosedStatus extends Mailable
{
    use Queueable, SerializesModels, ThreadableMail;

    public Ticket $ticket;
    public string $systemComment;
    public ?string $picComment;
    public ?string $solusiComment;
    public int $idTicketTracking;

    public function __construct(Ticket $ticket, string $systemComment, ?string $picComment = null, ?string $solusiComment = null, int $idTicketTracking)
    {
        $this->ticket = $ticket;
        $this->systemComment = $systemComment;
        $this->picComment = $picComment;
        $this->solusiComment = $solusiComment;
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
        $jenisTiket = $this->ticket->ticket_type ?? 'Belum Ditentukan';
        $picTiket = $this->ticket->pic?->nama_user ?? 'Belum Ada';
        $picEskalasi = $this->ticket->escalationTo?->nama_user ?? 'Belum Ada';
        $description = $this->ticket->ticket_description;

        $solusi = $this->ticket->solusi?->nama_solusi ?? '-';
        $solusiDesc = $this->ticket->solusi?->solusi_description ?? '-';
        $rootcause = $this->ticket->rootcause?->nama_rootcause ?? '-';
        $rootcauseDesc = $this->ticket->rootcause?->rootcause_description ?? '-';

        $body = "
            <h2>✅ Tiket Selesai - {$this->ticket->id_ticket_type}</h2>
            <p>Halo <strong>{$userName}</strong>,</p>
            <p>Tiket Anda telah selesai dikerjakan.</p>
        ";

        
        if (!empty($this->solusiComment)) {
            $body .= "
                <p><strong>Catatan dari PIC:</strong></p>
                <div style='border:1px solid #ccc;padding:10px;margin-bottom:20px;'>
                    {$this->solusiComment}
                </div>
            ";
        }

        $body .= "
            <p><strong>Root Cause: </strong>{$rootcause}</p>
            <div style='border:1px solid #ccc;padding:10px;margin-bottom:20px;'>
                {$rootcauseDesc}
            </div>

            <p><strong>Solusi: </strong>{$solusi}</p>
            <div style='border:1px solid #ccc;padding:10px;margin-bottom:20px;'>
                {$solusiDesc}
            </div>
        ";

        if (!empty($this->ticket->link_pendukung)) {
            $body .= "
                <p><strong>Link Pendukung:</strong></p>
                <div style='border:1px solid #ccc;padding:10px;margin-bottom:20px;'>
                    <a href=\"{$this->ticket->link_pendukung}\" target=\"_blank\">{$this->ticket->link_pendukung}</a>
                </div>
            ";
        }

        if (!empty($this->ticket->screenshot_pendukung)) {
            $body .= "
                <p><strong>Screenshot Pendukung:</strong></p>
                <div style='border:1px solid #ccc;padding:10px;margin-bottom:20px;'>
                    <img src=\"{$this->ticket->screenshot_pendukung}\" alt=\"Screenshot Pendukung\" style=\"max-width:100%; height:auto;\" />
                </div>
            ";
        }

        if (!empty($this->ticket->teknisi_tambahan)) {
            $teknisiTambahan = is_array($this->ticket->teknisi_tambahan)
                ? implode(', ', $this->ticket->teknisi_tambahan)
                : implode(', ', json_decode($this->ticket->teknisi_tambahan, true) ?? []);

            $body .= "
                <p><strong>Teknisi Tambahan:</strong></p>
                <div style='border:1px solid #ccc;padding:10px;margin-bottom:20px;'>
                    {$teknisiTambahan}
                </div>
            ";
        }

        $body .= "
            <p><strong>Judul:</strong> {$this->ticket->ticket_title}</p>
            <p><strong>Deskripsi Tiket:</strong></p>
            <div style='border:1px solid #ccc;padding:10px;margin-bottom:20px;'>
                {$description}
            </div>

            <p>Dengan detail tiket sebagai berikut:</p>
            <ul>
                <li><strong>ID Tiket:</strong> {$this->ticket->id_ticket_type}</li>
                <li><strong>Status:</strong> {$this->ticket->ticket_status}</li>
                <li><strong>Jenis Tiket:</strong> {$jenisTiket}</li>
                <li><strong>Layanan:</strong> {$layanan}</li>
                <li><strong>Prioritas:</strong> {$prioritas}</li>
                {$permintaan}
                <li><strong>PIC Tiket:</strong> {$picTiket}</li>
                <li><strong>PIC Eskalasi:</strong> {$picEskalasi}</li>
                <li><strong>Tracking Point:</strong> {$this->systemComment}</li>
            </ul>
            <hr>
            <p>Selesai pada: " . now()->format('Y-m-d H:i') . "</p>
            <p>Terima kasih,<br>Service Desk System</p>
        ";

        return $this->subject("Re: [Ticket #{$this->ticket->id_ticket}] {$this->ticket->ticket_title}")
                    ->html($body)
                    ->withSymfonyMessage(function ($message) {
                        $this->applyThreadHeaders($message, $this->ticket->id_ticket, false, 'closed');
                    });
    }
}