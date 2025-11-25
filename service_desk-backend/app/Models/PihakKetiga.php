<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PihakKetiga extends Model
{
    use HasFactory;

    protected $table = 'tblm_pihak_ketiga';
    protected $primaryKey = 'id_pihak_ketiga';

    protected $fillable = [
        'nama_perusahaan',
        'perusahaan_description'
    ];

    public function eskalasiPihakKetiga()
    {
        return $this->hasMany(EskalasiPihakKetiga::class, 'id_pihak_ketiga', 'id_pihak_ketiga');
    }

}
