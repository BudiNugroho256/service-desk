<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure permissions are created first
        $allPermissions = Permission::all();

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $petugasIt = Role::firstOrCreate(['name' => 'Petugas IT']);
        $endUser = Role::firstOrCreate(['name' => 'End User']);

        // Assign all permissions to Admin
        $admin->syncPermissions($allPermissions);

        // Assign specific permissions to Petugas IT
        $petugasIt->syncPermissions([
            // User Management
            'users.view', 'users.create', 'users.update', 'users.delete',

            // Divisi Management
            'divisions.view', 'divisions.create', 'divisions.update', 'divisions.delete',

            // Ticket Management
            'tickets.view', 'tickets.create', 'tickets.update', 'tickets.delete',

            // Priorities Management
            'priorities.view', 'priorities.create', 'priorities.update', 'priorities.delete',

            // Layanan Management
            'layanans.view', 'layanans.create', 'layanans.update', 'layanans.delete',

            // Rootcause Management
            'rootcauses.view', 'rootcauses.create', 'rootcauses.update', 'rootcauses.delete',

            // Solusi Management
            'solusi.view', 'solusi.create', 'solusi.update', 'solusi.delete',

            // Notifications
            'notifications.view', 'notifications.update', 'notifications.delete',
        ]);

        // Assign basic permissions to End User
        $endUser->syncPermissions([
            'tickets.create',
            'notifications.view',
        ]);
    }
}