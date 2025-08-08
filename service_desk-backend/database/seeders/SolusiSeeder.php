<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SolusiSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tblm_solusi')->insert([
            [
                'id_layanan' => 1,
                'nama_solusi' => 'Ganti RAM',
                'solusi_description' => 'RAM diganti karena rusak permanen',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_layanan' => 2,
                'nama_solusi' => 'Install ulang Windows',
                'solusi_description' => 'Sistem operasi error dan perlu diinstal ulang',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_layanan' => 3,
                'nama_solusi' => 'Kalibrasi Monitor',
                'solusi_description' => 'Monitor dikalibrasi ulang untuk menampilkan warna yang akurat',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_layanan' => 4,
                'nama_solusi' => 'Update Antivirus',
                'solusi_description' => 'Antivirus diperbarui untuk melindungi dari ancaman terbaru',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_layanan' => 5,
                'nama_solusi' => 'Kencangkan Kabel SATA',
                'solusi_description' => 'Masalah koneksi hard drive diperbaiki dengan mengencangkan kabel',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
