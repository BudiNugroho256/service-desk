<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // User Management
            'users.view', 'users.create', 'users.update', 'users.delete',

            // Divisi Management
            'divisions.view', 'divisions.create', 'divisions.update', 'divisions.delete',

            // Ticket Management
            'tickets.view', 'tickets.create', 'tickets.update', 'tickets.delete', 'tickets.view-own', 'tickets.view-assigned',

            // Priorities Management
            'priorities.view', 'priorities.create', 'priorities.update', 'priorities.delete',

            // Layanan Management
            'layanans.view', 'layanans.create', 'layanans.update', 'layanans.delete',

            // Rootcause Management
            'rootcauses.view', 'rootcauses.create', 'rootcauses.update', 'rootcauses.delete',

            // Solusi Management
            'solusi.view', 'solusi.create', 'solusi.update', 'solusi.delete',

            // Permintaan Management
            'permintaan.view', 'permintaan.create', 'permintaan.update', 'permintaan.delete',

            // Report Management
            'reports.view', 'reports.create', 'reports.update', 'reports.delete',

            // Notifications
            'notifications.view', 'notifications.update', 'notifications.delete',

            // Roles & Permissions (UI for managing them)
            'roles.view', 'roles.create', 'roles.update', 'roles.delete',
            'permissions.view', 'permissions.create', 'permissions.update', 'permissions.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
