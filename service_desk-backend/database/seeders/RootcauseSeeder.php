<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RootcauseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tblm_rootcause')->insert([
            [
                'id_layanan' => 1, // Jaringan & Infrastruktur
                'nama_rootcause' => 'Router Overheating',
                'rootcause_description' => 'Router mengalami panas berlebih karena ventilasi tidak optimal.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_layanan' => 2, // Keamanan Siber
                'nama_rootcause' => 'Password Lemah',
                'rootcause_description' => 'Penggunaan kata sandi sederhana menyebabkan akun mudah dibobol.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_layanan' => 3, // Aplikasi
                'nama_rootcause' => 'Bug pada Modul Login',
                'rootcause_description' => 'Fungsi autentikasi tidak menangani input invalid dengan benar.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_layanan' => 4, // Aplikasi
                'nama_rootcause' => 'Database Timeout',
                'rootcause_description' => 'Query terlalu kompleks menyebabkan waktu tunggu terlalu lama.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_layanan' => 5, // Keamanan Siber
                'nama_rootcause' => 'Antivirus Tidak Aktif',
                'rootcause_description' => 'Antivirus dinonaktifkan secara manual oleh pengguna.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}