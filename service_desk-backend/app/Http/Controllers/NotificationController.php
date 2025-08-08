<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the logged-in user.
     */

    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->get()->map(function ($notification) {
            $ticketId = $notification->data['id_ticket'] ?? null;

            // Fetch the latest ticket type from the Ticket model
            $ticketType = $ticketId ? Ticket::find($ticketId)?->id_ticket_type : null;

            return [
                'id' => $notification->id,
                'notification_type' => $notification->data['notification_type'] ?? '-',
                'notification_message' => $notification->data['message'] ?? '-',
                'id_ticket' => $ticketId,
                'id_ticket_tracking' => $notification->data['id_ticket_tracking'] ?? null,
                'id_ticket_type' => $ticketType ?: ($notification->data['id_ticket_type'] ?? null),
                'nama_user' => $notification->data['nama_user'] ?? '-',
                'read_at' => optional($notification->read_at)?->toDateTimeString(),
                // 'created_at' => optional($notification->created_at)?->toDateTimeString(),
            ];
        });

        Log::info('Fetched notifications:', ['notifications' => $notifications->toArray()]);

        return response()->json($notifications);
    }




    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, $notificationId)
    {
        $notification = $request->user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    /**
     * Delete a single notification.
     */
    public function delete(Request $request, $notificationId)
    {
        $notification = $request->user()->notifications()->findOrFail($notificationId);
        $notification->delete();

        return response()->json(['message' => 'Notification deleted']);
    }

    public function clearAll(Request $request)
    {
        $request->user()->notifications()->delete();
        return response()->json(['message' => 'All notifications cleared']);
    }

    public function markAsReadByTicket(Request $request)
    {
        $ticketId = $request->input('id_ticket');

        $request->user()->notifications()
            ->whereRaw("data::jsonb ->> 'id_ticket' = ?", [$ticketId])
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Notifications for ticket marked as read']);
    }

}
