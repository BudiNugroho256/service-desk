<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permintaan extends Model
{
    use HasFactory;

    protected $table = 'tblm_permintaan';
    protected $primaryKey = 'id_permintaan';

    protected $fillable = [
        'id_layanan', 
        'nama_permintaan', 
        'permintaan_description',
    ];

    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'id_layanan');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'id_permintaan');
    }
}