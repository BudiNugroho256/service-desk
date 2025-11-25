<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketTracking extends Model
{
    use HasFactory;

    protected $table = 'tblt_ticket_tracking';
    protected $primaryKey = 'id_ticket_tracking';

    protected $fillable = [
        'id_ticket',
        'id_ticket_type',
        'ticket_type',
        'tracking_status',
        'pic_comment',
        'ticket_comment',
        'id_pic_ticket',
        'tracking_created_on',
        'comment_created_on',
        'user_comment',
        'cancel_comment',
    ];

    protected $casts = [
        'comment_created_on' => 'datetime',
        'tracking_created_on' => 'datetime'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'id_ticket');
    }

    public function picUser()
    {
        return $this->belongsTo(User::class, 'id_pic_ticket');
    }

    public function commentLogs()
    {
        return $this->hasMany(TicketTrackingCommentLog::class, 'id_ticket_tracking');
    }

}
