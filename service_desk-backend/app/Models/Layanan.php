<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    use HasFactory;

    protected $table = 'tblm_layanan';
    protected $primaryKey = 'id_layanan';

    protected $fillable = [
        'id_user_assigned',
        'group_layanan',
        'nama_layanan',
        'status_layanan',
    ];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'id_user_assigned');
    }

    public function solusi()
    {
        return $this->hasMany(Solusi::class, 'id_layanan');
    }

    public function requests()
    {
        return $this->hasMany(Request::class, 'id_layanan');
    }

    public function rootcause()
    {
        return $this->hasMany(Rootcause::class, 'id_layanan');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'id_layanan');
    }
}
