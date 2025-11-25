<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EskalasiPihakKetiga extends Model
{
    use HasFactory;

    protected $table = 'tblt_eskalasi_pihak_ketiga';
    protected $primaryKey = 'id_eskalasi_pihak_ketiga';

    protected $fillable = [
        'id_pihak_ketiga',
        'tp_pic_ticket',
        'tp_problem_description',
        'tp_sla_duration'
    ];

    public function pihakKetiga()
    {
        return $this->belongsTo(PihakKetiga::class, 'id_pihak_ketiga', 'id_pihak_ketiga');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'id_eskalasi_pihak_ketiga', 'id_eskalasi_pihak_ketiga');
    }
}