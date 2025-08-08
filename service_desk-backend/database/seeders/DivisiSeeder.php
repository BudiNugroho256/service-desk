<?php

namespace Database\Seeders;

use App\Models\Divisi;
use Illuminate\Database\Seeder;

class DivisiSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            ['nama_divisi' => 'Human Capital', 'kode_divisi' => 'HK001', 'divisi_alias' => 'HC', 'lantai_divisi' => 8],
            ['nama_divisi' => 'Engineering dan Teknologi Informasi', 'kode_divisi' => 'HK002', 'divisi_alias' => 'ETI', 'lantai_divisi' => 8],
            ['nama_divisi' => 'Pengembangan Bisnis & Manajemen Portofolio', 'kode_divisi' => 'HK003', 'divisi_alias' => 'PBMP', 'lantai_divisi' => 3],
            ['nama_divisi' => 'Quality, Health, Safety, Security, and Environment', 'kode_divisi' => 'HK004', 'divisi_alias' => 'QHSSE', 'lantai_divisi' => 3],
            ['nama_divisi' => 'Corporate Planning', 'kode_divisi' => 'HK005', 'divisi_alias' => 'CP', 'lantai_divisi' => 5],
            ['nama_divisi' => 'Akuntansi & Keuangan', 'kode_divisi' => 'HK006', 'divisi_alias' => 'AK', 'lantai_divisi' => 5],
            ['nama_divisi' => 'Sipil Umum', 'kode_divisi' => 'HK007', 'divisi_alias' => 'SU', 'lantai_divisi' => 6],
            ['nama_divisi' => 'Gedung', 'kode_divisi' => 'HK008', 'divisi_alias' => 'GDG', 'lantai_divisi' => 6],
            ['nama_divisi' => 'Engineering, Procurement, and Construction', 'kode_divisi' => 'HK009', 'divisi_alias' => 'EPC', 'lantai_divisi' => 7],
            ['nama_divisi' => 'Perencanaan Jalan Tol', 'kode_divisi' => 'HK010', 'divisi_alias' => 'PJT', 'lantai_divisi' => 3],
            ['nama_divisi' => 'Pembangunan Jalan Tol', 'kode_divisi' => 'HK011', 'divisi_alias' => 'PBJT', 'lantai_divisi' => 3],
            ['nama_divisi' => 'Operasi & Pemeliharaan Jalan Tol', 'kode_divisi' => 'HK012', 'divisi_alias' => 'OPJT', 'lantai_divisi' => 9],
            ['nama_divisi' => 'Pengelolaan Risiko', 'kode_divisi' => 'HK013', 'divisi_alias' => 'PR', 'lantai_divisi' => 10],
            ['nama_divisi' => 'Sistem & Kepatuhan', 'kode_divisi' => 'HK014', 'divisi_alias' => 'SK', 'lantai_divisi' => 10],
            ['nama_divisi' => 'Legal', 'kode_divisi' => 'HK015', 'divisi_alias' => 'LGL', 'lantai_divisi' => 8],
            ['nama_divisi' => 'Sekretaris Perusahaan', 'kode_divisi' => 'HK016', 'divisi_alias' => 'SP', 'lantai_divisi' => 12],
            ['nama_divisi' => 'Satuan Pengawasan Intern', 'kode_divisi' => 'HK017', 'divisi_alias' => 'SPI', 'lantai_divisi' => 12],
        ];

        foreach ($divisions as $division) {
            Divisi::create($division);
        }
    }
}