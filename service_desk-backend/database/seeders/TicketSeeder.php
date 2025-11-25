<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketPriority;
use App\Models\Rating;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class TicketSeeder extends Seeder
{
    public function calculateDueDateSkippingHolidays($startDate, $workingDays)
    {
        $path = storage_path('app/calendar.min.json');

        if (!file_exists($path)) {
            return null;
        }

        $json = file_get_contents($path);
        $holidays = json_decode($json, true);

        $date = \Carbon\Carbon::parse($startDate);
        $addedDays = 0;

        while ($addedDays < $workingDays) {
            $date->addDay();

            $isWeekend = $date->isWeekend();
            $isHoliday = isset($holidays[$date->format('Y-m-d')]) &&
                        ($holidays[$date->format('Y-m-d')]['holiday'] ?? false);

            if (!$isWeekend && !$isHoliday) {
                $addedDays++;
            }
        }

        return $date;
    }

    public function run(): void
    {
        $faker = Faker::create();
        $adminIds = User::role('Admin')->pluck('id_user')->toArray();
        $petugasItIds = User::role('Petugas IT')->pluck('id_user')->toArray();
        $allUserIds = User::pluck('id_user')->toArray();
        $ratingIds = Rating::pluck('id_rating')->toArray();

        // Early return if role users are missing
        if (empty($adminIds) || empty($petugasItIds)) {
            $this->command->warn("⚠️ Skipping TicketSeeder: No Admin or Petugas IT users found.");
            return;
        }

        // Early return if no ratings
        if (empty($ratingIds)) {
            $this->command->warn("⚠️ Skipping TicketSeeder: No ratings found. Run RatingSeeder first.");
            return;
        }

        $priorities = TicketPriority::all()->keyBy('id_ticket_priority');

        foreach (range(1, 200) as $i) {
            $type = $faker->randomElement(['Request', 'Incident']);
            $idTicketType = $this->generateCustomTicketTypeId($type);

            // Pick users
            $picUser = $faker->randomElement($adminIds);          // Only Admin
            $endUser = $faker->randomElement($allUserIds);        // Any user
            $creator = $faker->randomElement($allUserIds);        // Any user
            $updater = $faker->randomElement($allUserIds);        // Any user
            $escalator = $faker->optional()->randomElement($petugasItIds);
            if ($escalator) {
                $picClosed = $faker->randomElement([$escalator, $picUser]);
            } else {
                $picClosed = $picUser;
            }

            // Pick priority and related SLA
            $priorityId = $faker->numberBetween(1, 5);
            $priority = $priorities[$priorityId] ?? null;

            // Generate timeline
            $createdOn = Carbon::parse($faker->dateTimeBetween('-30 days', '-10 days'));

            $assignedDate = $faker->dateTimeBetween(
                $createdOn->clone()->addMinutes(5),
                $createdOn->clone()->addDays(5)
            );
            $assignedDate = $assignedDate ? Carbon::parse($assignedDate) : null;

            $progressDate = $assignedDate ? $faker->dateTimeBetween(
                $assignedDate->clone()->addMinutes(5),
                $assignedDate->clone()->addDays(5)
            ) : null;
            $progressDate = $progressDate ? Carbon::parse($progressDate) : null;

            $acceptedDate = $progressDate ? $faker->dateTimeBetween(
                $progressDate->clone()->addMinutes(5),
                $progressDate->clone()->addDays(5)
            ) : null;
            $acceptedDate = $acceptedDate ? Carbon::parse($acceptedDate) : null;

            $closedDate = $acceptedDate ? $faker->dateTimeBetween(
                $acceptedDate->clone()->addMinutes(5),
                $acceptedDate->clone()->addDays(5)
            ) : null;
            $closedDate = $closedDate ? Carbon::parse($closedDate) : null;

            $totalSla = $priority?->sla_duration_normal + ($escalator ? $priority?->sla_duration_escalation : 0);
            $dueDate = ($progressDate && $totalSla)
                ? $this->calculateDueDateSkippingHolidays($progressDate, $totalSla)
                : null;

            Ticket::create([
                'id_ticket_priority' => $priorityId,
                'id_pic_ticket' => $picUser,
                'id_end_user' => $endUser,
                'id_divisi' => $faker->numberBetween(1, 5),
                'id_layanan' => $faker->numberBetween(1, 5),
                'id_solusi' => $faker->numberBetween(1, 5),
                'id_rootcause' => $faker->numberBetween(1, 5),
                'id_permintaan' => $faker->numberBetween(1, 5),

                // ⭐ New: random rating 1–5
                'id_rating' => $faker->randomElement($ratingIds),

                'created_on' => $createdOn,
                'created_by' => $creator,
                'last_updated_on' => $faker->dateTimeBetween($createdOn, 'now'),
                'last_updated_by' => $updater,
                'escalation_date' => $faker->optional()->dateTimeBetween($createdOn, 'now'),
                'escalation_to' => $escalator,
                'ticket_closed_by' => $picClosed,

                'ticket_status' => $closedDate ? 'Closed' : $faker->randomElement(['Open', 'On Progress', 'Cancelled']),
                'assigned_status' => $assignedDate ? 'Assigned' : 'Unassigned',
                'assigned_date' => $assignedDate,
                'progress_date' => $progressDate,
                'closed_date' => $closedDate,
                'due_date' => $dueDate,

                'id_ticket_type' => $idTicketType,
                'ticket_type' => $type,

                'ticket_title' => $faker->sentence(4),
                'ticket_description' => $faker->paragraph(4),
                'resolusi_description' => $faker->paragraph(2),

                'rootcause_awal' => $faker->sentence(3),
                'solusi_awal' => $faker->sentence(3),

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function generateCustomTicketTypeId(string $type): string
    {
        $year = now()->year;
        $prefix = $type === 'Request' ? 'REQ' : 'INC';

        $lastNumber = Ticket::where(function ($query) use ($year) {
                $query->where('id_ticket_type', 'like', "REQ{$year}%")
                    ->orWhere('id_ticket_type', 'like', "INC{$year}%");
            })
            ->get()
            ->map(function ($ticket) {
                return (int) substr($ticket->id_ticket_type, 7);
            })
            ->max();

        $newNumber = $lastNumber + 1;

        return $prefix . $year . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}