<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PihakKetiga;

class PihakKetigaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['nama_perusahaan' => 'PT Telkom Indonesia'],
            ['nama_perusahaan' => 'PT PLN Persero'],
            ['nama_perusahaan' => 'PT Kimia Farma'],
            ['nama_perusahaan' => 'PT Pertamina'],
            ['nama_perusahaan' => 'PT Garuda Indonesia'],
            ['nama_perusahaan' => 'PT Astra International'],
            ['nama_perusahaan' => 'PT BRI'],
        ];

        foreach ($data as $item) {
            PihakKetiga::create($item);
        }
    }
}
