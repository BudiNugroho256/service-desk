<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class TicketNotification extends Notification
{
    use Queueable;

    protected $ticketId;
    protected $trackingId;
    protected $ticketTypeId;
    protected $type;
    protected $namaUser; // ✅ Add this

    public function __construct($ticketId, $trackingId = null, $type = 'ticket_created', $namaUser = '-', $ticketTypeId = null)
    {
        $this->ticketId = $ticketId;
        $this->trackingId = $trackingId;
        $this->ticketTypeId = $ticketTypeId;
        $this->type = $type;
        $this->namaUser = $namaUser; // ✅ Store nama_user
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        \Log::info('Creating database notification', [
            'ticketId' => $this->ticketId,
            'trackingId' => $this->trackingId,
            'type' => $this->type,
            'namaUser' => $this->namaUser,
        ]);

        return [
            'notification_type' => $this->type,
            'message' => $this->generateMessage(),
            'id_ticket' => $this->ticketId,
            'id_ticket_tracking' => $this->trackingId,
            'id_ticket_type' => $this->ticketTypeId ?? null,
            'nama_user' => $this->namaUser,
        ];
    }


    protected function generateMessage()
    {
        $ticketRef = $this->ticketTypeId ?: $this->ticketId;

        return match ($this->type) {
            'ticket_created' => "🆕 NEW TICKET: {$ticketRef} dari {$this->namaUser} telah masuk.",
            'ticket_update' => "💬 TICKET UPDATE: Komentar baru pada Ticket {$ticketRef} dari {$this->namaUser}.",
            'ticket_escalation' => "⚠️ TICKET ESCALATED: {$ticketRef} dari {$this->namaUser} telah di eskalasi.",
            default => "🔔 Notifikasi lainnya untuk ticket {$ticketRef}.",
        };
    }

}