<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermintaanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tblm_permintaan')->insert([
            [
                'id_layanan' => 1,
                'nama_permintaan' => 'Permintaan Instalasi Software',
                'permintaan_description' => 'User membutuhkan aplikasi tertentu diinstal di perangkat kerja',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_layanan' => 2,
                'nama_permintaan' => 'Permintaan Upgrade RAM',
                'permintaan_description' => 'Perangkat terasa lambat dan membutuhkan upgrade memori',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_layanan' => 3,
                'nama_permintaan' => 'Permintaan Kalibrasi Monitor',
                'permintaan_description' => 'Tampilan warna pada monitor tidak sesuai dan perlu disesuaikan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_layanan' => 4,
                'nama_permintaan' => 'Permintaan Update Antivirus',
                'permintaan_description' => 'Sistem antivirus perlu diperbarui agar tetap aman',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_layanan' => 5,
                'nama_permintaan' => 'Permintaan Pemeriksaan Koneksi HDD',
                'permintaan_description' => 'Hard disk tidak terbaca, perlu pemeriksaan koneksi kabel',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
