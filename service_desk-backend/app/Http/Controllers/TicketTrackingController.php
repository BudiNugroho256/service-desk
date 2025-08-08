<?php

namespace App\Http\Controllers;

use App\Models\TicketTracking;
use Illuminate\Http\Request;

class TicketTrackingController extends Controller
{
    public function index()
    {
        $ticketTrackings = TicketTracking::with(['trackingPoint', 'ticket'])->get()->map(function ($tracking) {
            return [
                'id_ticket_tracking' => $tracking->id_ticket_tracking,
                'id_ticket' => $tracking->id_ticket,
                'tracking_status' => $tracking->tracking_status,
                'id_tracking_point' => $tracking->id_tracking_point,
                'tracking_point_name' => $tracking->trackingPoint?->tracking_point,
                'comment' => $tracking->comment,
            ];
        });

        return response()->json($ticketTrackings);
    }
}
