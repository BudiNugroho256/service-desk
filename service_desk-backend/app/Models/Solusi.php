<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solusi extends Model
{
    use HasFactory;

    protected $table = 'tblm_solusi';
    protected $primaryKey = 'id_solusi';

    protected $fillable = [
        'id_layanan', 
        'nama_solusi', 
        'solusi_description',
    ];

    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'id_layanan');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'id_solusi');
    }
}
