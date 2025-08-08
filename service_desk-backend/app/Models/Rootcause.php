<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rootcause extends Model
{
    use HasFactory;

    protected $table = 'tblm_rootcause';
    protected $primaryKey = 'id_rootcause';

    protected $fillable = [
        'id_layanan', 
        'nama_rootcause', 
        'rootcause_description',
    ];

    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'id_layanan');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'id_rootcause');
    }
}

