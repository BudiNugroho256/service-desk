<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
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
            TicketSeeder::class,
            TicketLogSeeder::class,
            TicketTrackingSeeder::class,
            ReportSeeder::class,
        ]);
        
    }
}
