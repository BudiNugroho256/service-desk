<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketTrackingStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public Ticket $ticket;
    public string $comment;

    public function __construct(Ticket $ticket, string $comment)
    {
        $this->ticket = $ticket;
        $this->comment = $comment;
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

        // 🟡 Find image src paths inside the description
        preg_match_all('/<img[^>]+src="([^"]+)"/', $description, $matches);
        $cidMap = [];

        foreach ($matches[1] as $src) {
            // Ensure it's a local path and resolve it
            $localPath = public_path(parse_url($src, PHP_URL_PATH));

            if (file_exists($localPath)) {
                $cid = $this->embed($localPath);
                $cidMap[$src] = $cid;
            }
        }

        // Replace image URLs with CIDs
        foreach ($cidMap as $original => $cid) {
            $description = str_replace($original, $cid, $description);
        }

        $body = "
            <h2>📍 Update Tiket</h2>
            <p>Halo <strong>{$userName}</strong>,</p>
            <p>Tiket Anda telah diperbarui dengan detail berikut:</p>
            <ul>
                <li><strong>ID Tiket:</strong> {$this->ticket->id_ticket_type}</li>
                <li><strong>Judul:</strong> {$this->ticket->ticket_title}</li>
                <li><strong>Status:</strong> {$this->ticket->ticket_status}</li>
                <li><strong>Jenis Tiket:</strong> {$jenisTiket}</li>
                <li><strong>Layanan:</strong> {$layanan}</li>
                <li><strong>Prioritas:</strong> {$prioritas}</li>
                <li><strong>PIC Tiket:</strong> {$picTiket}</li>
                <li><strong>PIC Eskalasi:</strong> {$picEskalasi}</li>
                <li><strong>Tracking Point:</strong> {$this->comment}</li>
            </ul>

            <p><strong>Deskripsi Tiket:</strong></p>
            <div style='border: 1px solid #ccc; padding: 10px; margin-bottom: 20px;'>
                {$description}
            </div>

            <hr>
            <p>Diupdate pada: " . now()->format('Y-m-d H:i') . "</p>
            <p>Terima kasih,<br>Service Desk System</p>
        ";

        return $this->subject('📍 Update Tiket: ' . $this->ticket->id_ticket_type)
                    ->html($body);
    }

}