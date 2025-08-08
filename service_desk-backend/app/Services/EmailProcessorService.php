<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketTracking;
use App\Models\User;
use App\Models\TicketTrackingCommentLog;
use App\Notifications\TicketNotification;
use App\Mail\TicketCreatedNotification;
use App\Mail\InvalidSubjectNotification;
use EmailReplyParser\EmailReplyParser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class EmailProcessorService
{
    protected $ticketPurifier;

    public function logTrackingComment($trackingId, $type, $text, $userId = null, $createdOn = null)
    {
        return TicketTrackingCommentLog::create([
            'id_ticket_tracking' => $trackingId,
            'comment_type' => $type, // 'user', 'pic', or 'system'
            'comment_text' => $text,
            'created_by' => $userId,
            'comment_created_on' => $createdOn ?? now(),
        ]);
    }

    public function __construct()
    {
        // Purifier for ticket creation
        $ticketConfig = HTMLPurifier_Config::createDefault();
        $ticketConfig->set('HTML.SafeIframe', true);
        $ticketConfig->set('Attr.AllowedFrameTargets', ['_blank']);
        $ticketConfig->set('HTML.Allowed', 'p,br,b,strong,i,em,u,a[href|target],ul,ol,li,table,thead,tbody,tr,td,th,img[src|alt|width|height],span,div');
        $this->ticketPurifier = new HTMLPurifier($ticketConfig);
    }

    public function process($message)
    {
        $emailFrom = $message->getFrom()[0]->mail;
        $subject = $message->getSubject();
        $body = $message->getHTMLBody() ?? $message->getTextBody();
        $supportUsers = Role::findByName('Admin')->users;
        $body = preg_replace('/<(script|style)\b[^>]*>(.*?)<\/\1>/is', '', $body);

        // Allow only specific domain
        if (!str_ends_with($emailFrom, '@binus.ac.id')) {
            return "⛔ Unauthorized sender: $emailFrom";
        }

        // Normalize subject (handle encoding safely)
        $normalizedSubject = preg_replace('/[^\x20-\x7E]/u', '', $subject);

        // Handle reply based on ticket ID and tracking point
        if (preg_match('/^(?:Re:\s*)*\[Ticket\s*#(\d+)\]/i', $normalizedSubject, $matches)) {
            $idTicket = (int) $matches[1];
            $ticket = Ticket::with('escalationTo', 'endUser')->find($idTicket);


            if ($ticket) {
                // ✅ Check if status is Closed or Cancelled
                // if (in_array($ticket->ticket_status, ['Closed', 'Cancelled'])) {
                //     return "⚠ Ticket #{$ticket->id_ticket} is '{$ticket->ticket_status}' — reply ignored.";
                // }

                // Extract the DateTime object from the Attribute
                $emailDateAttribute = $message->getDate();
                $sentAt = $emailDateAttribute ? \Carbon\Carbon::parse($emailDateAttribute->get())->timezone(config('app.timezone')) : now();


                // Add this check
                $closedTracking = TicketTracking::where('id_ticket', $ticket->id_ticket)
                    ->whereIn('tracking_status', ['Closed', 'Cancelled'])
                    ->latest('tracking_created_on')
                    ->first();

                if ($closedTracking && $sentAt->greaterThan($closedTracking->tracking_created_on)) {
                    Log::warning('Reply ignored because ticket was closed/cancelled before email was sent.', [
                        'ticket_id' => $ticket->id_ticket,
                        'ticket_status' => $ticket->ticket_status,
                        'email_sent_at' => $sentAt,
                        'closed_at' => $closedTracking->tracking_created_on,
                    ]);

                    return "⚠ Ticket #{$ticket->id_ticket} was '{$closedTracking->tracking_status}' at {$closedTracking->tracking_created_on->format('Y-m-d H:i:s')} — reply ignored.";
                }


                // Find the latest tracking whose tracking_created_on is BEFORE or EQUAL to the sentAt, excluding Closed/Cancelled
                $tracking = TicketTracking::where('id_ticket', $ticket->id_ticket)
                    ->where('tracking_created_on', '<=', $sentAt)
                    ->whereNotIn('tracking_status', ['Closed', 'Cancelled'])
                    ->latest('tracking_created_on')
                    ->first();

                // Fallback: if nothing found, get the latest available tracking that is not Closed/Cancelled
                if (!$tracking) {
                    $tracking = TicketTracking::where('id_ticket', $ticket->id_ticket)
                        ->whereNotIn('tracking_status', ['Closed', 'Cancelled'])
                        ->latest('tracking_created_on')
                        ->first();
                }

                // Final fallback: no tracking found at all
                if (!$tracking) {
                    Log::warning('Reply received but no tracking entry found.', [
                        'ticket_id' => $idTicket,
                        'sender' => $emailFrom,
                        'subject' => $normalizedSubject,
                    ]);
                    return "⚠ No tracking entry found for ticket {$idTicket}";
                }


                // ✅ THIS IS MISSING IN YOUR CODE
                $plainTextBody = $message->getTextBody() ?: strip_tags($message->getHTMLBody());
                $replyOnlyText = EmailReplyParser::parseReply($plainTextBody);
                $cleanedReplyBody = $this->ticketPurifier->purify($replyOnlyText);

                $tracking->update([
                    'user_comment' => $cleanedReplyBody,
                    'comment_created_on' => $sentAt,
                ]);

                $this->logTrackingComment(
                    $tracking->id_ticket_tracking,
                    'user',
                    $cleanedReplyBody,
                    $ticket->id_end_user,
                    $sentAt
                );

                $ticket->load('endUser');

                $namaUser = $ticket->endUser?->nama_user ?? '-';
                Log::info('Sending ticket_update notification', [
                    'ticket_id' => $ticket->id_ticket,
                    'tracking_id' => $tracking->id_ticket_tracking,
                    'nama_user' => $namaUser,
                ]);

                // Always notify all Admins
                foreach ($supportUsers as $adminUser) {
                    $adminUser->notify(new TicketNotification(
                        $ticket->id_ticket,
                        $tracking->id_ticket_tracking,
                        'ticket_update',
                        $namaUser,
                        $ticket->id_ticket_type
                    ));
                }

                // If there is an escalation PIC, notify them too
                if ($ticket->escalationTo) {
                    $escalationUser = $ticket->escalationTo;
                    $escalationUser->notify(new TicketNotification(
                        $ticket->id_ticket,
                        $tracking->id_ticket_tracking,
                        'ticket_update',
                        $namaUser,
                        $ticket->id_ticket_type
                    ));
                }

                return "✅ Reply recorded for Ticket #{$ticket->id_ticket} (latest tracking updated)";
            }


            return "⚠ Ticket ID not found in database: {$idTicket}";
        }



        // Check subject starts with 'Open Ticket - '
        if (!Str::startsWith($subject, 'Open Ticket - ')) {
            // Extract name for greeting
            $rawName = explode('@', $emailFrom)[0];
            $formattedName = collect(explode('.', $rawName))->map(fn($part) => Str::ucfirst($part))->join(' ');

            // Send informative reply
            Mail::to($emailFrom)->send(new InvalidSubjectNotification($formattedName, $subject));
            
            return "⛔ Invalid subject: $subject — Auto-reply sent.";
        }

        // Remove 'Open Ticket - ' prefix
        $cleanTitle = preg_replace('/^Open Ticket\s*-\s*/i', '', $subject);

        // Create user if not exists
        $rawName = explode('@', $emailFrom)[0];
        $formattedName = collect(explode('.', $rawName))->map(fn($part) => Str::ucfirst($part))->join(' ');

        $user = User::firstOrCreate(
            ['email' => $emailFrom],
            [
                'nama_user' => $formattedName,
                'id_divisi' => null,
                'nik_user' => $emailFrom,  // Set nik_user to email
                'password' => bcrypt('default123')
            ]
        );

        $user->syncRoles('End User');

        // Create ticket
        $ticket = Ticket::create([
            'id_end_user' => $user->id_user,
            'id_divisi' => $user->id_divisi,
            'created_by' => $user->id_user,
            'last_updated_by' => $user->id_user,
            'ticket_title' => trim($cleanTitle),
            'ticket_description' => 'TEMP_PLACEHOLDER',
            'ticket_status' => 'Open',
            'assigned_status' => 'Unassigned',
            'created_on' => now(),
            'last_updated_on' => now(),
        ]);

        $systemComment = 'Tiket dibuat oleh ' . $user->nama_user;

        $tracking = TicketTracking::create([
            'id_ticket' => $ticket->id_ticket,
            'id_ticket_type' => $ticket->id_ticket_type,
            'ticket_type' => $ticket->ticket_type,
            'tracking_status' => 'Created',
            'ticket_comment' => $systemComment,
            'comment_created_on' => now(),
            'tracking_created_on' => now(),
        ]);

        $idTicketTracking = $tracking->id_ticket_tracking;

        // Process both inline and standalone attachments
        $attachments = [];
        $attachmentIndex = 0;
        $attachmentNotes = '';

        // 🔍 Extract all href links from HTML body
        preg_match_all('/<a\s+(?:[^>]*?\s+)?href=["\'](https?:\/\/[^"\']+)["\']/i', $body, $matches);
        $hrefLinks = $matches[1] ?? [];

        // 🧼 Optional: restrict to certain file types (PDF, DOCX, etc.)
        foreach ($hrefLinks as $link) {
            $attachments[] = [
                'name' => $link,
                'url' => $link,
            ];
            $attachmentIndex++;
        }

        foreach ($message->getAttachments() as $attachment) {
            $cid = $attachment->getContentId();
            $contentType = $attachment->getContentType();
            $isImage = str_starts_with($contentType, 'image/');
            $isInline = $cid && $isImage;

            $extension = pathinfo($attachment->getName() ?: 'file.bin', PATHINFO_EXTENSION) ?: 'bin';
            $timestamp = now()->format('Ymd_His');
            $filename = "ticket_{$ticket->id_ticket}_{$timestamp}_{$attachmentIndex}.{$extension}";

            $dir = storage_path('app/public/email_attachments');
            if (!file_exists($dir)) mkdir($dir, 0755, true);
            file_put_contents("{$dir}/{$filename}", $attachment->getContent());

            $url = asset("storage/email_attachments/{$filename}");

            $originalName = $attachment->getName() ?: "Attachment_{$attachmentIndex}.{$extension}";

            $attachments[] = [
                'name' => $originalName,
                'url' => $url
            ];

            $attachmentIndex++;
        }

        // Strip links before plain text conversion
        $body = preg_replace('/<a\s+[^>]*?href=["\']https?:\/\/[^"\']+["\'][^>]*>(.*?)<\/a>/is', '$1', $body);
        $body = preg_replace('/https?:\/\/[^\s<>"\']+/i', '', $body);

        // Get plain text body (from either HTML or plain text)
        $body = preg_replace('/<img[^>]+src=["\']cid:[^"\']+["\'][^>]*>/i', '', $body);
        $body = preg_replace('/<br\s*\/?>/i', "\n", $body);
        $body = preg_replace('/<p[^>]*>/i', "\n", $body);
        $body = preg_replace('/<\/p>/i', "\n\n", $body);


        // Strip tags *after* converting to plain text structure
        $plainText = strip_tags($body);


        // Normalize line breaks and trim
        $cleanedBody = trim(preg_replace('/\r\n|\r|\n/', "\n", $plainText));

        // Delete [cid:xxxxx] from body
        $cleanedBody = preg_replace('/\[cid:[^\]]+\]/i', '', $cleanedBody);

        // Save description and attachments (no HTML anymore)
        $ticket->update([
            'ticket_description' => $cleanedBody,
            'ticket_attachments' => $attachments,
        ]);


        Mail::to($user->email)->send(new TicketCreatedNotification($ticket, $user, $systemComment, $idTicketTracking));

        Log::info('Sending ticket_created notification', [
            'ticket_id' => $ticket->id_ticket,
            'tracking_id' => $tracking->id_ticket_tracking,
            'nama_user' => $user->nama_user,
        ]);

        foreach ($supportUsers as $adminUser) {
            $adminUser->notify(new TicketNotification(
                $ticket->id_ticket,
                $tracking->id_ticket_tracking,
                'ticket_created',
                $user->nama_user
            ));
        }

        return "✅ Ticket created ID: {$ticket->id_ticket}";
    }
}
