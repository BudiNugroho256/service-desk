<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Admin user
        $admin = User::create([
            'id_divisi' => 2,
            'nama_user' => 'Admin ETI',
            'nik_user' => 'admin.eti@example.com',
            'email' => 'admin.eti@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admineti'),
            'remember_token' => Str::random(10),
        ]);
        $admin->syncRoles('Admin');

        $juan = User::create([
            'id_divisi' => 2,
            'nama_user' => 'Juan Alexander',
            'nik_user' => 'juan.alexander@example.com',
            'email' => 'juan.alexander@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('anakorang'),
            'remember_token' => Str::random(10),
        ]);
        $juan->syncRoles('Petugas IT');

        $budi = User::create([
            'id_divisi' => 2,
            'nama_user' => 'Budi Nugroho',
            'nik_user' => 'budi.nugroho@example.com',
            'email' => 'budi.nugroho@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('anakorang'),
            'remember_token' => Str::random(10),
        ]);
        $budi->syncRoles('Petugas IT');

        $arin = User::create([
            'id_divisi' => 2,
            'nama_user' => 'Arin Suryana',
            'nik_user' => 'arin.suryana@example.com',
            'email' => 'arin.suryana@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('anakorang'),
            'remember_token' => Str::random(10),
        ]);
        $arin->syncRoles('End User');

        // Random 10 users
        foreach (range(1, 10) as $i) {
            $role = $faker->randomElement(['Admin', 'Petugas IT', 'End User']);

            $user = User::create([
                'id_divisi' => rand(1, 5),
                'nama_user' => $faker->name,
                'nik_user' => $faker->unique()->numerify('123456######'),
                'email' => $faker->unique()->safeEmail,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
            ]);

            $user->syncRoles($role);
        }
    }
}