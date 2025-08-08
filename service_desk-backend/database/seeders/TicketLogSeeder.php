<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\User;
use Faker\Factory as Faker;

class TicketLogSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $userIds = User::pluck('id_user')->toArray();
        $tickets = Ticket::all();

        foreach ($tickets as $ticket) {
            foreach (range(1, rand(1, 3)) as $i) {
                TicketLog::create([
                    'id_ticket' => $ticket->id_ticket,
                    'id_ticket_type' => $ticket->id_ticket_type,
                    'ticket_type' => $ticket->ticket_type,
                    'last_updated_on' => $ticket->last_updated_on,
                    'last_updated_by' => $ticket->last_updated_by,
                    'escalation_date' => $ticket->escalation_date,
                    'escalation_to' => $ticket->escalation_to,

                    'id_pic_ticket' => $ticket->id_pic_ticket,
                    'tingkat_dampak' => $ticket->priority?->tingkat_dampak,
                    'tingkat_urgensi' => $ticket->priority?->tingkat_urgensi,
                    'tingkat_priority' => $ticket->priority?->tingkat_priority,
                    'nama_user' => $ticket->endUser?->nama_user,
                    'nama_divisi' => $ticket->divisi?->nama_divisi,
                    'sla_duration_normal' => $ticket->priority?->sla_duration_normal,
                    'sla_duration_escalation' => $ticket->priority?->sla_duration_escalation,

                    'ticket_status' => $ticket->ticket_status,
                    'assigned_status' => $ticket->assigned_status,
                    'assigned_date' => $ticket->assigned_date,
                    'closed_date' => $ticket->closed_date,

                    'ticket_title' => $ticket->ticket_title,
                    'ticket_description' => $ticket->ticket_description,
                    'resolusi_description' => $ticket->resolusi_description,

                    'rootcause_awal' => $ticket->rootcause_awal,
                    'solusi_awal' => $ticket->solusi_awal,
                    'tp_pic_ticket' => $ticket->tp_pic_ticket,
                    'tp_pic_company' => $ticket->tp_pic_company,
                    'tp_accepted_date' => $ticket->tp_accepted_date,
                    'tp_sla_duration' => $ticket->tp_sla_duration,
                    'tp_rootcause' => $ticket->tp_rootcause,
                    'tp_solusi' => $ticket->tp_solusi,
                    'tp_closed_date' => $ticket->tp_closed_date,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}