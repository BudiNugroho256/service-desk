<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DivisiSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            LayananSeeder::class,
            TicketPrioritySeeder::class,
            SolusiSeeder::class,
            RootcauseSeeder::class,
            PermintaanSeeder::class,
            RatingSeeder::class,
            PihakKetigaSeeder::class,
            TicketSeeder::class,
            TicketLogSeeder::class,
            TicketTrackingSeeder::class,
            ReportSeeder::class,
        ]);
        
    }
}
