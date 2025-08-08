<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketTrackingCommentLog extends Model
{
    protected $table = 'tblt_ticket_tracking_comment_log';
    protected $primaryKey = 'id_tracking_comment';

    protected $fillable = [
        'id_ticket_tracking',
        'comment_type',
        'comment_text',
        'created_by',
        'comment_created_on',
    ];

    protected $casts = [
        'comment_created_on' => 'datetime',
    ];

    public function tracking()
    {
        return $this->belongsTo(TicketTracking::class, 'id_ticket_tracking');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

