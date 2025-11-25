<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $table = 'tblt_ticket';
    protected $primaryKey = 'id_ticket';

    protected $fillable = [
        'id_ticket_priority',
        'id_pic_ticket',
        'id_end_user',
        'id_divisi',
        'id_layanan',
        'id_solusi',
        'id_rootcause',
        'id_permintaan',
        'id_rating',
        'id_eskalasi_pihak_ketiga',
        'created_on',
        'created_by',
        'last_updated_on',
        'last_updated_by',
        'escalation_date',
        'escalation_to',
        'ticket_closed_by',
        'ticket_status',
        'assigned_status',
        'assigned_date',
        'progress_date',
        'closed_date',
        'due_date',
        'id_ticket_type',
        'ticket_type',
        'ticket_title',
        'ticket_description',
        'resolusi_description',
        'rootcause_awal',
        'solusi_awal',
        'teks_pendukung',
        'link_pendukung',
        'screenshot_pendukung',
        'teknisi_tambahan',
        'ticket_attachments',
    ];

    protected $casts = [
        'created_on' => 'datetime',
        'last_updated_on' => 'datetime',
        'escalation_date' => 'datetime',
        'assigned_date' => 'datetime',
        'progress_date' => 'datetime',
        'closed_date' => 'datetime',
        'due_date' => 'datetime',
        'tp_accepted_date' => 'datetime',
        'tp_closed_date' => 'datetime',
        'teknisi_tambahan' => 'array',
        'ticket_attachments' => 'array',
    ];

    // Relationships
    public function priority()
    {
        return $this->belongsTo(TicketPriority::class, 'id_ticket_priority');
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'id_pic_ticket');
    }

    public function endUser()
    {
        return $this->belongsTo(User::class, 'id_end_user');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'id_divisi');
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'id_layanan');
    }

    public function solusi()
    {
        return $this->belongsTo(Solusi::class, 'id_solusi');
    }

    public function rootcause()
    {
        return $this->belongsTo(Rootcause::class, 'id_rootcause');
    }
    
    public function request()
    {
        return $this->belongsTo(Permintaan::class, 'id_permintaan');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lastUpdatedBy()
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    public function escalationTo(){

        return $this->belongsTo(User::class, 'escalation_to');
        
    }

    public function closedBy(){

        return $this->belongsTo(User::class, 'ticket_closed_by');
        
    }

    public function ticketTrackings()
    {
        return $this->hasMany(TicketTracking::class, 'id_ticket');
    }

    public function ticketLogs()
    {
        return $this->hasMany(TicketLog::class, 'id_ticket');
    }

    public function rating()
    {
        return $this->belongsTo(Rating::class, 'id_rating', 'id_rating');
    }

    public function eskalasiPihakKetiga()
    {
        return $this->belongsTo(EskalasiPihakKetiga::class, 'id_eskalasi_pihak_ketiga', 'id_eskalasi_pihak_ketiga');
    }

}
