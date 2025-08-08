<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TicketTracking;
use App\Models\Ticket;
use App\Models\User;
use Faker\Factory as Faker;
use Carbon\Carbon;

class TicketTrackingSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $tickets = Ticket::all();
        $userIds = User::pluck('id_user')->toArray();
        $statusMap = [
            'Created' => 'Open',
            'Assigned' => 'Open',
            'On Progress' => 'On Progress',
            'Closed' => 'Closed',
            'Cancelled' => 'Cancelled',
        ];

        foreach ($tickets as $ticket) {
            // Determine realistic tracking status based on ticket fields
            $trackingStatus = 'Created';

            if ($ticket->progress_date) {
                $trackingStatus = 'On Progress';
            } elseif ($ticket->assigned_date && $ticket->id_pic_ticket) {
                $trackingStatus = 'Assigned';
            }

            if ($ticket->ticket_status === 'Closed') {
                $trackingStatus = 'Closed';
            } elseif ($ticket->ticket_status === 'Cancelled') {
                $trackingStatus = 'Cancelled';
            }
            
            // Optional: generate dynamic comment
            $systemComment = match ($trackingStatus) {
                'Created' => 'Seeder: Tiket telah dibuat',
                'Assigned' => 'Seeder: Tiket diassign ke ' . ($ticket->pic?->nama_user ?? '-'),
                'On Progress' => 'Seeder: Tiket sedang dikerjakan oleh ' . ($ticket->pic?->nama_user ?? '-'),
                'Closed' => 'Seeder: Tiket telah selesai',
                'Cancelled' => 'Seeder: Tiket dibatalkan',
                default => 'Seeder: Status tidak dikenal',
            };

            TicketTracking::create([
                'id_ticket' => $ticket->id_ticket,
                'id_ticket_type' => $ticket->id_ticket_type,
                'id_pic_ticket' => $ticket->id_pic_ticket,
                'ticket_type' => $ticket->ticket_type,
                'tracking_status' => $trackingStatus,
                'ticket_comment' => $systemComment,
                'comment_created_on' => Carbon::parse($ticket->created_on)->addDays(rand(0, 5)), // Simulate near creation
                'pic_comment' => null,
                'user_comment' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}