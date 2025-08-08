<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $table = 'tblm_user';
    protected $primaryKey = 'id_user';

    protected $fillable = [
        'id_divisi',
        'nama_user',
        'nik_user',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'id_divisi');
    }

    public function layanan()
    {
        return $this->hasMany(Layanan::class, 'id_user_assigned');
    }

    public function ticketsAsPIC()
    {
        return $this->hasMany(Ticket::class, 'id_pic_ticket');
    }

    public function ticketsAsEndUser()
    {
        return $this->hasMany(Ticket::class, 'id_end_user');
    }

    public function ticketsCreated()
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    public function ticketsLastUpdated()
    {
        return $this->hasMany(Ticket::class, 'last_updated_by');
    }

    public function ticketsEscalationTo()
    {
        return $this->hasMany(Ticket::class, 'escalation_to');
    }

}
