<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketPriority extends Model
{
    use HasFactory;

    protected $table = 'tblm_ticket_priority';
    protected $primaryKey = 'id_ticket_priority';

    protected $fillable = [
        'tingkat_priority',
        'tingkat_dampak',
        'tingkat_urgensi',
        'sla_duration_normal',
        'sla_duration_escalation',
        'sla_duration_thirdparty',
        'ticket_priority_description',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'id_ticket_priority');
    }
}
