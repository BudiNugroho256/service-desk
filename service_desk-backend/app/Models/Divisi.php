<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    use HasFactory;

    protected $table = 'tblm_divisi';
    protected $primaryKey = 'id_divisi';

    protected $fillable = [
        'nama_divisi',
        'kode_divisi',
        'divisi_alias',
        'lantai_divisi',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'id_divisi');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'id_divisi');
    }
}
