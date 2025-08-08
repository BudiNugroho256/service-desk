<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketPrioritySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tblm_ticket_priority')->insert([
            [
                'tingkat_priority' => 'P1',
                'tingkat_dampak' => 'Critical',
                'tingkat_urgensi' => 'Very Urgent',
                'sla_duration_normal' => 1,
                'sla_duration_escalation' => 1,
                'sla_duration_thirdparty' => 1,
                'ticket_priority_description' => 'Level Executive - Urgent Business-Critical Issues',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tingkat_priority' => 'P2',
                'tingkat_dampak' => 'High',
                'tingkat_urgensi' => 'Urgent',
                'sla_duration_normal' => 2,
                'sla_duration_escalation' => 1,
                'sla_duration_thirdparty' => 2,
                'ticket_priority_description' => 'Level Director - High Priority Incidents',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tingkat_priority' => 'P3',
                'tingkat_dampak' => 'Medium',
                'tingkat_urgensi' => 'Medium',
                'sla_duration_normal' => 2,
                'sla_duration_escalation' => 2,
                'sla_duration_thirdparty' => 2,
                'ticket_priority_description' => 'Level Manager - SM, Aplikasi Pendukung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tingkat_priority' => 'P4',
                'tingkat_dampak' => 'Low',
                'tingkat_urgensi' => 'Low',
                'sla_duration_normal' => 3,
                'sla_duration_escalation' => 3,
                'sla_duration_thirdparty' => 3,
                'ticket_priority_description' => 'Level Supervisor, Perangkat Umum',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tingkat_priority' => 'P5',
                'tingkat_dampak' => 'Low',
                'tingkat_urgensi' => 'Low',
                'sla_duration_normal' => 3,
                'sla_duration_escalation' => 3,
                'sla_duration_thirdparty' => 3,
                'ticket_priority_description' => 'Level Staff, Layanan TI Lainnya',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
