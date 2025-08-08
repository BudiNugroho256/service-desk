<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Layanan;

class LayananSeeder extends Seeder
{
    public function run(): void
    {
        $layanans = [
            [
                'group_layanan' => 'Jaringan & Infrastruktur',
                'nama_layanan' => 'Pemeliharaan Jaringan Kantor',
                'status_layanan' => 'Aktif',
            ],
            [
                'group_layanan' => 'Keamanan Siber',
                'nama_layanan' => 'Audit Keamanan Sistem',
                'status_layanan' => 'Non-Aktif',
            ],
            [
                'group_layanan' => 'Aplikasi',
                'nama_layanan' => 'Pengembangan Fitur Baru',
                'status_layanan' => 'Aktif',
            ],
            [
                'group_layanan' => 'Aplikasi',
                'nama_layanan' => 'Maintenance Aplikasi Legacy',
                'status_layanan' => 'Non-Aktif',
            ],
            [
                'group_layanan' => 'Keamanan Siber',
                'nama_layanan' => 'Penanganan Insiden Keamanan',
                'status_layanan' => 'Aktif',
            ],
        ];

        foreach ($layanans as $data) {
            Layanan::create([
                'id_user_assigned' => null,
                'group_layanan' => $data['group_layanan'],
                'nama_layanan' => $data['nama_layanan'],
                'status_layanan' => $data['status_layanan'],
            ]);
        }
    }
}
