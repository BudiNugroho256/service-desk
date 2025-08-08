<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Report;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        Report::create([
            'nama_report'        => 'Laporan Tiket Tertutup',
            'inisial_report'     => 'mon_closed',
            'report_description' => 'Menampilkan semua tiket yang telah ditutup',
            'ukuran_kertas'      => 'A3',
            'layout_kertas'      => 'Landscape',
            'query_report'       => 'SELECT * FROM tblt_tickets WHERE status = "closed";',
        ]);

        Report::create([
            'nama_report'        => 'Laporan Aktivitas User',
            'inisial_report'     => 'user_activity',
            'report_description' => 'Menampilkan log aktivitas user',
            'ukuran_kertas'      => 'A4',
            'layout_kertas'      => 'Portrait',
            'query_report'       => 'SELECT * FROM user_logs ORDER BY created_at DESC;',
        ]);

        Report::create([
            'nama_report'        => 'Rekapitulasi Tiket per Layanan',
            'inisial_report'     => 'ticket_by_service',
            'report_description' => 'Menampilkan jumlah tiket berdasarkan layanan',
            'ukuran_kertas'      => 'A4',
            'layout_kertas'      => 'Landscape',
            'query_report'       => 'SELECT layanan_id, COUNT(*) as jumlah FROM tblt_tickets GROUP BY layanan_id;',
        ]);

        Report::create([
            'nama_report'        => 'Laporan Durasi Penyelesaian',
            'inisial_report'     => 'duration_report',
            'report_description' => 'Mengukur rata-rata durasi penyelesaian tiket',
            'ukuran_kertas'      => 'A4',
            'layout_kertas'      => 'Portrait',
            'query_report'       => 'SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, closed_at)) as rata_durasi FROM tblt_tickets WHERE status = "closed";',
        ]);

        Report::create([
            'nama_report'        => 'Laporan User Aktif Bulanan',
            'inisial_report'     => 'monthly_active_users',
            'report_description' => 'Menampilkan user yang aktif tiap bulan',
            'ukuran_kertas'      => 'A3',
            'layout_kertas'      => 'Landscape',
            'query_report'       => 'SELECT user_id, COUNT(*) as aktivitas FROM user_logs WHERE MONTH(created_at) = MONTH(CURDATE()) GROUP BY user_id;',
        ]);
    }
}
