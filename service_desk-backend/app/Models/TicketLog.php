<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketLog extends Model
{
    protected $table = 'tblt_ticket_log';
    protected $primaryKey = 'id_ticket_log';

    protected $fillable = [
        'id_ticket',
        'id_ticket_type',
        'ticket_type',
        'last_updated_on',
        'last_updated_by',
        'escalation_date',
        'escalation_to',
        'id_pic_ticket',
        'tingkat_dampak',
        'tingkat_urgensi',
        'tingkat_priority',
        'nama_user',
        'nama_divisi',
        'sla_duration_normal',
        'sla_duration_escalation',
        'ticket_status',
        'assigned_status',
        'assigned_date',
        'closed_date',
        'ticket_title',
        'ticket_description',
        'resolusi_description',
        'rootcause_awal',
        'solusi_awal',
        'tp_pic_ticket',
        'tp_pic_company',
        'tp_accepted_date',
        'tp_sla_duration',
        'tp_rootcause',
        'tp_solusi',
        'tp_closed_date',
        'change_summary',
    ];

    protected $casts = [
        'last_updated_on' => 'datetime',
        'escalation_date' => 'datetime',
        'assigned_date' => 'datetime',
        'closed_date' => 'datetime',
        'tp_accepted_date' => 'datetime',
        'tp_closed_date' => 'datetime',
    ];    

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'id_ticket');
    }

    public function picUser()
    {
        return $this->belongsTo(User::class, 'id_pic_ticket');
    }

    public function escalationUser()
    {
        return $this->belongsTo(User::class, 'escalation_to');
    }

    public function lastUpdatedBy()
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }
}
