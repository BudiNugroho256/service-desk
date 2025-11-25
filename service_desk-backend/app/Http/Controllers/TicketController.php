<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Layanan;
use App\Models\Solusi;
use App\Models\Rootcause;
use App\Models\Rating;
use App\Models\TicketPriority;
use App\Models\TicketLog;
use App\Models\TicketTracking;
use App\Models\TicketTrackingCommentLog;
use App\Models\PihakKetiga;
use App\Models\EskalasiPihakKetiga;
use App\Mail\TicketTrackingStatusUpdated;
use App\Mail\TicketClosedStatus;
use App\Mail\TicketCancelledStatus;
use App\Mail\TicketAssignedStatus;
use App\Mail\TicketOnProgressStatus;
use App\Mail\TicketEskalasiUpdate;
use App\Mail\TicketPicCommentMail;
use App\Notifications\TicketNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class TicketController extends Controller
{
    private function generateCustomTicketTypeId(string $type): string
    {
        $year = now()->year;
        $prefix = $type === 'Request' ? 'REQ' : 'INC';

        $lastNumber = Ticket::where(function ($query) use ($year) {
                $query->where('id_ticket_type', 'ilike', "REQ{$year}%")
                    ->orWhere('id_ticket_type', 'ilike', "INC{$year}%");
            })
            ->get()
            ->map(function ($ticket) {
                return (int) substr($ticket->id_ticket_type, 7);
            })
            ->max();

        $newNumber = $lastNumber + 1;

        return $prefix . $year . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    public function calculateDueDateSkippingHolidays(string $startDate, int $workingDays): ?\Carbon\Carbon
    {
        $path = storage_path('app/calendar.min.json');


        if (!file_exists($path)) {
            return null;
        }

        $json = file_get_contents($path);
        $holidays = json_decode($json, true);

        $date = \Carbon\Carbon::parse($startDate);
        $addedDays = 0;

        while ($addedDays < $workingDays) {
            $date->addDay();

            $isWeekend = $date->isWeekend(); // Saturday or Sunday
            $isHoliday = isset($holidays[$date->format('Y-m-d')]) && ($holidays[$date->format('Y-m-d')]['holiday'] ?? false);

            if (!$isWeekend && !$isHoliday) {
                $addedDays++;
            }
        }

        return $date;
    }

    public function getEffectiveSlaDays($ticket)
    {
        $isEscalated = !empty($ticket->escalation_to);
        return $ticket->priority
            ? ($isEscalated ? $ticket->priority->sla_duration_escalation : $ticket->priority->sla_duration_normal)
            : null;
    }


    public function getTicketLogs($id)
    {
        $ticket = Ticket::with([
            'ticketLogs' => function ($query) {
                $query->with(['picUser', 'escalationUser', 'lastUpdatedBy', 'ticket.layanan', 'ticket.priority']);
            }
        ])->findOrFail($id);


        $logs = $ticket->ticketLogs
            ->sortByDesc('last_updated_on')
            ->map(function ($log) {
                $ticket = $log->ticket; // assuming you have ticket relationship loaded
                return [
                    'id_log' => $log->id_ticket_log,
                    'last_updated_on' => optional($log->last_updated_on)->toDateTimeString(),
                    'last_updated_by' => optional($log->lastUpdatedBy)?->nama_user ?? '-',
                    'pic_user' => optional($log->picUser)?->nama_user ?? '-',
                    'escalation_user' => optional($log->escalationUser)?->nama_user ?? '-',
                    'layanan_label' => ($ticket->layanan?->group_layanan ?? '') . ' - ' . ($ticket->layanan?->nama_layanan ?? ''),
                    'priority_label' => ($ticket->priority?->tingkat_priority ?? '') . ' - ' . ($ticket->priority?->tingkat_dampak ?? '') . ' - ' . ($ticket->priority?->tingkat_urgensi ?? ''),
                    'value' => $this->formatLogContent($log),
                ];
            })
            ->values();

        return response()->json($logs);
    }


    private function formatLogContent($log): string
    {
        return $log->change_summary ?: 'No update details.';
    }

    public function getTrackingPoints($id)
    {
        $trackings = TicketTracking::with('commentLogs.author')
            ->where('id_ticket', $id)
            ->orderBy('tracking_created_on', 'asc')
            ->get()
            ->map(function ($track) {
                return [
                    'id_ticket_tracking' => $track->id_ticket_tracking,
                    'tracking_status' => $track->tracking_status,
                    'ticket_comment' => $track->ticket_comment,
                    'comment_created_on' => optional($track->comment_created_on)->toDateTimeString(),
                    'tracking_created_on' => optional($track->tracking_created_on)->toDateTimeString(),
                    'user_comment' => $track->user_comment,
                    'pic_comment' => $track->pic_comment,
                    'comment_logs' => $track->commentLogs->map(function ($comment) {
                        return [
                            'id_tracking_comment' => $comment->id_tracking_comment,
                            'comment_text' => $comment->comment_text,
                            'comment_type' => $comment->comment_type,
                            'created_by' => $comment->author?->nama_user?? '-',
                            'comment_created_on' => optional($comment->comment_created_on)->toDateTimeString(),
                            'tracking_created_on' => optional($comment->tracking_created_on)->toDateTimeString(),
                        ];
                    }),
                ];
            });


        return response()->json($trackings);
    }

    public function submitPicComment(Request $request, $ticketId, $trackingId)
    {
        $request->validate([
            'pic_comment' => 'nullable|string|max:2000',
        ]);

        $tracking = TicketTracking::where('id_ticket', $ticketId)
            ->where('id_ticket_tracking', $trackingId)
            ->first();

        if (!$tracking) {
            return response()->json(['message' => 'Tracking point not found.'], 404);
        }

        $tracking->pic_comment = $request->pic_comment;
        $tracking->comment_created_on = now();
        $tracking->save();

        // ✅ Send email after successful comment save
        $ticket = Ticket::with(['endUser', 'pic', 'escalationTo'])->findOrFail($ticketId);
        $recipients = array_filter([
            $ticket->endUser?->email,
            $ticket->pic?->email,
            $ticket->escalationTo?->email,
        ]);

        foreach ($recipients as $email) {
            if ($email && str_contains($email, '@') && $tracking->pic_comment) {
                Mail::to($email)->send(new TicketPicCommentMail($ticket, $tracking->pic_comment, $tracking->id_ticket_tracking));
            }
        }

        $newComment = $this->logTrackingComment(
            $tracking->id_ticket_tracking,
            'pic',
            $tracking->pic_comment,
            auth()->id()
        )->load('author');

        return response()->json([
            'message' => 'PIC comment saved successfully.',
            'data' => $tracking,
            'new_comment' => [
                'id_tracking_comment' => $newComment->id_tracking_comment,
                'comment_text'        => $newComment->comment_text,
                'comment_type'        => $newComment->comment_type,
                'created_by'          => $newComment->author?->nama_user ?? '-',
                'comment_created_on'  => optional($newComment->comment_created_on)->toDateTimeString(),
            ],
        ]);

    }
    
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

    public function getPihakKetiga()
    {
        return response()->json([
            'data' => PihakKetiga::all()
        ]);
    }

    public function assignPihakKetiga(Request $request, $ticketId)
    {
        $request->validate([
            'id_pihak_ketiga' => 'required|exists:tblm_pihak_ketiga,id_pihak_ketiga',
            'tp_pic_ticket' => 'required|string|max:255',
            'tp_problem_description' => 'required|string',
            'tp_sla_duration' => 'required|integer|min:0',
        ]);

        $ticket = Ticket::findOrFail($ticketId);

        // Create vendor escalation entry
        $eskalasi = EskalasiPihakKetiga::create([
            'id_pihak_ketiga'      => $request->id_pihak_ketiga,
            'tp_pic_ticket'        => $request->tp_pic_ticket,
            'tp_problem_description' => $request->tp_problem_description,
            'tp_sla_duration'      => $request->tp_sla_duration,
        ]);

        // Update ticket
        $ticket->update([
            'id_eskalasi_pihak_ketiga' => $eskalasi->id_eskalasi_pihak_ketiga,
        ]);

        // -------------------------
        // ⭐ CREATE TRACKING POINT
        // -------------------------
        $vendor = $eskalasi;
        $company = $vendor->pihakKetiga->nama_perusahaan;
        $pic     = $vendor->tp_pic_ticket;

        $assignedTo = $pic . ' (' . $company . ')';

        $tracking = TicketTracking::create([
            'id_ticket'           => $ticket->id_ticket,
            'id_ticket_type'      => $ticket->id_ticket_type,
            'id_pic_ticket'       => $ticket->id_pic_ticket,
            'ticket_type'         => $ticket->ticket_type,
            'tracking_status'     => 'On Progress',
            'ticket_comment'      => 'Pengerjaan dialihkan ke ' . $assignedTo,
            'comment_created_on'  => now(),
            'tracking_created_on' => now(),
        ]);


        // ============================================================
        // ⭐ FIXED NOTIFICATION SECTION (Admin + escalation PIC only)
        // ============================================================

        $namaUser = $ticket->endUser?->nama_user ?? '-';

        // Collect all notifiable users
        $notifiables = collect();

        // Add Admins
        $adminUsers = Role::findByName('Admin')->users;
        $notifiables = $notifiables->merge($adminUsers);

        // Add escalation PIC if exists
        if ($ticket->escalationTo) {
            $notifiables->push($ticket->escalationTo);
        }

        // Remove duplicates based on user id
        $notifiables = $notifiables->unique('id_user');

        // Send notifications once only
        foreach ($notifiables as $user) {
            $user->notify(new TicketNotification(
                $ticket->id_ticket,
                $tracking->id_ticket_tracking,
                'ticket_escalation',
                $namaUser,
                $ticket->id_ticket_type,
                $assignedTo
            ));
        }


        // ============================================================
        // ⭐ FIXED EMAIL SECTION (User + PIC_Eskalasi + Admin)
        // ============================================================

        $emailRecipients = collect();

        // Add end user email
        if ($ticket->endUser?->email) {
            $emailRecipients->push($ticket->endUser->email);
        }

        // Add escalation PIC email
        if ($ticket->escalationTo?->email) {
            $emailRecipients->push($ticket->escalationTo->email);
        }

        // Add admin emails
        foreach ($adminUsers as $admin) {
            if ($admin->email) {
                $emailRecipients->push($admin->email);
            }
        }

        // Remove duplicates
        $emailRecipients = $emailRecipients->unique()->filter();


        foreach ($emailRecipients as $email) {
            Mail::to($email)->send(
                new TicketEskalasiUpdate(
                    $ticket,
                    $tracking->ticket_comment,
                    null,
                    $tracking->id_ticket_tracking
                )
            );
        }

        // -------------------------
        // ⭐ FINAL RESPONSE
        // -------------------------
        return response()->json([
            'message' => 'Pihak ketiga berhasil ditambahkan.',
            'data'    => $eskalasi
        ]);
    }


    public function index(Request $request)
    {
        $query = Ticket::with([
            'priority', 'pic', 'endUser', 'solusi', 'rootcause', 'divisi', 'layanan', 'createdBy', 'lastUpdatedBy', 'escalationTo', 'rating', 'closedBy', 'eskalasiPihakKetiga', 'eskalasiPihakKetiga.pihakKetiga'
        ]);

        $user = Auth::user();

        if ($user->hasRole('Petugas IT')) {
            $query->where(function ($q) use ($user) {
                $q->where('escalation_to', $user->id_user)
                ->orWhere('id_end_user', $user->id_user);
            });
        }

        if ($user->hasRole('End User')) {
            $query->where(function ($q) use ($user) {
                $q->where('id_end_user', $user->id_user);
            });
        }

        if (!$request->has('filter_status')) {
            $query->where('ticket_status', '!=', 'Cancelled');
        }

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_title', 'ilike', "%{$search}%")
                ->orWhere('id_ticket', 'ilike', "%{$search}%")
                ->orWhere('id_ticket_type', 'ilike', "%{$search}%")
                ->orWhereHas('endUser', fn($q) => $q->where('nama_user', 'ilike', "%{$search}%"))
                ->orWhereHas('pic', fn($q) => $q->where('nama_user', 'ilike', "%{$search}%"))
                ->orWhereHas('escalationTo', fn($q) => $q->where('nama_user', 'ilike', "%{$search}%"));
            });
        }

        if ($request->has('filter_status') && $request->filter_status !== '') {
            $statuses = explode(',', $request->filter_status);
            $query->whereIn('ticket_status', $statuses);
        }

        $tickets = $query->get();

        $data = $tickets->map(function ($ticket) {
            $normalSla = $ticket->priority?->sla_duration_normal ?? 0;
            $internalEscalationSla = $ticket->escalation_to ? ($ticket->priority?->sla_duration_escalation ?? 0) : 0;
            $thirdPartySla = $ticket->id_eskalasi_pihak_ketiga ? ($ticket->eskalasiPihakKetiga?->tp_sla_duration ?? 0) : 0;

            $totalSla = $normalSla + $internalEscalationSla + $thirdPartySla;


            $dueDate = $totalSla && $ticket->progress_date
                ? $this->calculateDueDateSkippingHolidays($ticket->progress_date, $totalSla)
                : null;

            if ($dueDate) {
                $ticket->due_date = $dueDate;
                $ticket->save(); // persists to DB
            }

            $isOverdue = $dueDate && optional($ticket->closed_date ?? now())->gt($dueDate);


            return [
                'id_ticket' => $ticket->id_ticket,
                'id_ticket_type' => $ticket->id_ticket_type,
                'id_divisi' => $ticket->id_divisi,
                'id_layanan' => $ticket->id_layanan,
                'id_ticket_priority' => $ticket->id_ticket_priority,
                'id_rating' => $ticket->id_rating,
                'id_solusi' => $ticket->id_solusi,
                'id_rootcause' => $ticket->id_rootcause,
                'id_user' => $ticket->id_end_user,
                'ticket_type' => $ticket->ticket_type,
                'status_overdue' => $isOverdue ? 'Overdue' : 'On Time',
                'due_date' => $dueDate ? $dueDate->toDateTimeString() : null,
                'ticket_title' => $ticket->ticket_title,
                'group_layanan' => $ticket->layanan?->group_layanan,
                'ticket_status' => $ticket->ticket_status,
                'tingkat_priority' => $ticket->priority?->tingkat_priority,
                'id_pic_ticket' => $ticket->id_pic_ticket,
                'pic_tiket' => $ticket->pic?->nama_user,
                'nama_user' => $ticket->endUser?->nama_user,
                'nama_divisi' => $ticket->divisi?->nama_divisi,
                'assigned_status' => $ticket->assigned_status,
                'assigned_date' => optional($ticket->assigned_date)->toDateTimeString(),
                'escalation_to' => $ticket->escalation_to,
                'pic_eskalasi' => $ticket->escalationTo?->nama_user,
                'ticket_closed_by' => $ticket->ticket_closed_by,
                'pic_closed' => $ticket->closedBy?->nama_user,
                'tanggal_eskalasi' => optional($ticket->escalation_date)->toDateTimeString(),
                'created_on' => optional($ticket->created_on)->toDateTimeString(),
                'created_by' => $ticket->createdBy?->nama_user,
                'last_updated_on' => optional($ticket->last_updated_on)->toDateTimeString(),
                'last_updated_by' => $ticket->lastUpdatedBy?->nama_user,
                'closed_date' => optional($ticket->closed_date)->toDateTimeString(),
                'nilai_rating' => $ticket->rating?->nilai_rating,
                'nama_rootcause' => $ticket->rootcause?->nama_rootcause,
                'nama_solusi' => $ticket->solusi?->nama_solusi,
                'teks_pendukung' => $ticket->teks_pendukung,
            ];
        });

        $sortBy = $request->input('sort_by');
        $sortDir = strtolower($request->input('sort_direction', 'desc'));

        if ($sortBy) {
            $manualSortFields = [
                'status_overdue', 'due_date', 'group_layanan', 'tingkat_priority',
                'pic_tiket', 'nama_user', 'nama_divisi', 'pic_eskalasi'
            ];

            if (in_array($sortBy, $manualSortFields)) {
                $data = $data->sortBy(function ($item) use ($sortBy) {
                    return match ($sortBy) {
                        'status_overdue' => $item['status_overdue'] === 'Overdue' ? 0 : 1,
                        'due_date' => $item['due_date'],
                        default => $item[$sortBy] ?? null,
                    };
                }, SORT_REGULAR, $sortDir === 'desc')->values();
            } else {
                $data = $data->sortBy($sortBy, SORT_REGULAR, $sortDir === 'desc')->values();
            }
        } else {
            $data = $data->sortBy(function ($item) {
                return [
                    empty($item['id_ticket_type']) ? 0 : 1,
                    empty($item['id_ticket_type']) 
                        ? -((int) $item['id_ticket']) 
                        : -((int) substr($item['id_ticket_type'], 3)),
                ];
            })->values();
        }


        $perPage = $request->input('per_page', 10);
        $currentPage = Paginator::resolveCurrentPage();
        $currentItems = $data->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedData = new LengthAwarePaginator($currentItems, $data->count(), $perPage, $currentPage, [
            'path' => Paginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);

        return response()->json([
            'data' => $paginatedData->items(),
            'current_page' => $paginatedData->currentPage(),
            'last_page' => $paginatedData->lastPage(),
            'per_page' => $paginatedData->perPage(),
            'total' => $paginatedData->total(),
        ]);
    }



    public function show($id)
    {   
        $ticket = is_numeric($id)
            ? $ticket = Ticket::with([
                'priority', 'pic', 'endUser', 'divisi', 'layanan', 'solusi', 'rootcause', 'request', 'createdBy', 'lastUpdatedBy', 'escalationTo', 'eskalasiPihakKetiga', 'eskalasiPihakKetiga.pihakKetiga'
            ])->findOrFail($id)
            : $ticket = Ticket::with([
                'priority', 'pic', 'endUser', 'divisi', 'layanan', 'solusi', 'rootcause', 'request', 'createdBy', 'lastUpdatedBy', 'escalationTo', 'eskalasiPihakKetiga', 'eskalasiPihakKetiga.pihakKetiga'
            ])->where('id_ticket_type', $id)->firstOrFail();

        $normalSla = $ticket->priority?->sla_duration_normal ?? 0;
        $internalEscalationSla = $ticket->escalation_to ? ($ticket->priority?->sla_duration_escalation ?? 0) : 0;
        $thirdPartySla = $ticket->id_eskalasi_pihak_ketiga ? ($ticket->eskalasiPihakKetiga?->tp_sla_duration ?? 0) : 0;

        $totalSla = $normalSla + $internalEscalationSla + $thirdPartySla;


        $dueDate = $totalSla && $ticket->progress_date
            ? $this->calculateDueDateSkippingHolidays($ticket->progress_date, $totalSla)
            : null;

        if ($dueDate) {
            $ticket->due_date = $dueDate;
            $ticket->save(); // persists to DB
        }

        $isOverdue = $dueDate && optional($ticket->closed_date ?? now())->gt($dueDate);


        return response()->json([
            'id_ticket' => $ticket->id_ticket,
            'status_overdue' => $isOverdue ? 'Overdue' : 'On Time',
            'due_date' => $dueDate ? $dueDate->toDateTimeString() : null,
            'ticket_title' => $ticket->ticket_title,
            'ticket_description' => $ticket->ticket_description,
            'group_layanan' => $ticket->layanan?->group_layanan,
            'nama_layanan' => $ticket->layanan?->nama_layanan,
            'ticket_status' => $ticket->ticket_status,
            'tingkat_priority' => $ticket->priority?->tingkat_priority,
            'tingkat_dampak' => $ticket->priority?->tingkat_dampak,
            'tingkat_urgensi' => $ticket->priority?->tingkat_urgensi,
            'sla_duration_normal' => $ticket->priority?->sla_duration_normal,
            'sla_duration_escalation' => $ticket->priority?->sla_duration_escalation,
            'pic_tiket' => $ticket->pic?->nama_user,
            'nama_user' => $ticket->endUser?->nama_user,
            'divisi_user' => $ticket->divisi?->nama_divisi,
            'nama_permintaan' => $ticket->request?->nama_permintaan,
            'id_ticket_priority' => $ticket->id_ticket_priority,
            'id_pic_ticket' => $ticket->id_pic_ticket,
            'id_user' => $ticket->id_end_user,
            'id_divisi' => $ticket->id_divisi,
            'id_ticket_type' => $ticket->id_ticket_type,
            'id_layanan' => $ticket->id_layanan,
            'id_rootcause' => $ticket->id_rootcause,
            'id_solusi' => $ticket->id_solusi,
            'id_permintaan' => $ticket->id_permintaan,
            'id_eskalasi_pihak_ketiga' => $ticket->id_eskalasi_pihak_ketiga,
            'escalation_to' => $ticket->escalation_to,
            'assigned_status' => $ticket->assigned_status,
            'assigned_date' => optional($ticket->assigned_date)->toDateTimeString(),
            'progress_date' => optional($ticket->progress_date)->toDateTimeString(),
            'pic_eskalasi' => $ticket->escalationTo?->nama_user,
            'tanggal_eskalasi' => optional($ticket->escalation_date)->toDateTimeString(),
            'created_on' => optional($ticket->created_on)->toDateTimeString(),
            'created_by' => $ticket->createdBy?->nama_user,
            'last_updated_on' => optional($ticket->last_updated_on)->toDateTimeString(),
            'last_updated_by' => $ticket->lastUpdatedBy?->nama_user,
            'tanggal_close' => optional($ticket->closed_date)->toDateTimeString(),
            'ticket_type' => $ticket->ticket_type,
            'analisis_awal' => $ticket->rootcause_awal,
            'nama_rootcause' => $ticket->rootcause?->nama_rootcause ?? '-',
            'rootcause_description' => $ticket->rootcause?->rootcause_description ?? '-',
            'nama_solusi' => $ticket->solusi?->nama_solusi ?? '-',
            'solusi_description' => $ticket->solusi?->solusi_description ?? '-',
            'solusi_awal' => $ticket->solusi_awal,
            'tp_pic_ticket' => $ticket->tp_pic_ticket,
            'tp_pic_company' => $ticket->tp_pic_company,
            'tp_accepted_date' => optional($ticket->tp_accepted_date)->toDateTimeString(),
            'tp_sla_duration' => $ticket->tp_sla_duration,
            'tp_closed_date' => optional($ticket->tp_closed_date)->toDateTimeString(),
            'link_pendukung' => $ticket->link_pendukung,
            'screenshot_pendukung' => $ticket->screenshot_pendukung,
            'teknisi_tambahan' => $ticket->teknisi_tambahan,
            'ticket_attachments'   => $ticket->ticket_attachments,
        ]);
    }

    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        
        $originalTicket = clone $ticket;
        
        $validated = $request->validate([
            'ticket_type' => 'nullable|string|in:Request,Incident',
            'ticket_title' => 'sometimes|string|max:255',
            'ticket_description' => 'sometimes|string|nullable',
            'assigned_status' => 'sometimes|string|max:50|nullable',
            'escalation_to' => 'nullable|exists:tblm_user,id_user',
            'escalation_date' => 'nullable|date',
            'assigned_date' => 'nullable|date',
            'progress_date' => 'nullable|date',
            'closed_date' => 'nullable|date',
            'last_updated_on' => 'nullable|date',
            'ticket_status' => 'nullable|string|in:Open,On Progress,Closed,Cancelled',
            'id_ticket_priority' => 'nullable|exists:tblm_ticket_priority,id_ticket_priority',
            'id_pic_ticket' => 'nullable|exists:tblm_user,id_user',
            'id_divisi' => 'nullable|exists:tblm_divisi,id_divisi',
            'id_layanan' => 'nullable|exists:tblm_layanan,id_layanan',
            'id_solusi' => 'nullable|exists:tblm_solusi,id_solusi',
            'id_end_user' => 'nullable|exists:tblm_user,id_user',
            'ticket_closed_by' => 'nullable|exists:tblm_user,id_user',
            'id_rootcause' => 'nullable|exists:tblm_rootcause,id_rootcause',
            'id_permintaan' => 'nullable|exists:tblm_permintaan,id_permintaan',
            'id_rating' => 'nullable|exists:tblm_rating,id_rating',
            'rootcause_awal' => 'nullable|string',
            'solusi_awal' => 'nullable|string',
            'teks_pendukung'=> 'nullable|string',
            'link_pendukung' => 'nullable|string',
            'screenshot_pendukung' => 'nullable|string',
            'teknisi_tambahan' => 'nullable|array',

        ]);

        $validated['last_updated_by'] = auth()->id();
        $validated['last_updated_on'] = now();

        if (!empty($validated['screenshot_pendukung'])) {
            // Check if it's actually a base64 string
            if (preg_match('/^data:image\/(\w+);base64,/', $validated['screenshot_pendukung'], $type)) {
                $data = substr($validated['screenshot_pendukung'], strpos($validated['screenshot_pendukung'], ',') + 1);
                $data = base64_decode($data);
                $extension = strtolower($type[1]); // jpg, png, gif, etc.
                
                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    return response()->json(['message' => 'Invalid image format'], 422);
                }

                $fileName = 'screenshot_' . uniqid() . '.' . $extension;
                $filePath = 'screenshots/' . $fileName;

                \Storage::disk('public')->put($filePath, $data);

                // Update the validated field to store only the file path
                $validated['screenshot_pendukung'] = $filePath;
            }
        }

        $isComplete =
            ($ticket->ticket_type ?? $validated['ticket_type'] ?? null) &&
            ($ticket->id_layanan ?? $validated['id_layanan'] ?? null) &&
            ($ticket->id_ticket_priority ?? $validated['id_ticket_priority'] ?? null) &&
            ($ticket->id_divisi ?? $validated['id_divisi'] ?? null);

        $picIsMissing = empty($ticket->id_pic_ticket) && empty($validated['id_pic_ticket']);

        if ($request->inline_update && $isComplete && $picIsMissing) {
            $validated['id_pic_ticket'] = Auth::id(); // ✅ assign current user
            $validated['assigned_status'] = 'Assigned';
            $validated['tracking_status'] = 'Assigned';
            $validated['assigned_date'] = now();
        }

        // ✅ Ensure ticket_type exists if provided
        if ($request->has('inline_update') && $request->inline_update && isset($validated['ticket_type'])) {
            $validated['ticket_type'] = $request->ticket_type;

            // If id_ticket_type is missing, generate a new one
            if (empty($ticket->id_ticket_type)) {
                $validated['id_ticket_type'] = $this->generateCustomTicketTypeId($validated['ticket_type']);
            } else {
                // Otherwise, update only the prefix if type has changed
                $oldPrefix = strtoupper(substr($ticket->id_ticket_type, 0, 3));
                $suffix = substr($ticket->id_ticket_type, 3);

                if (in_array($oldPrefix, ['REQ', 'INC'])) {
                    $newPrefix = $validated['ticket_type'] === 'Request' ? 'REQ' : 'INC';
                    $validated['id_ticket_type'] = $newPrefix . $suffix;
                }
            }
        }

        if ($request->has('inline_update') && $request->inline_update && $request->has('escalation_to')) {
            if (!empty($validated['escalation_to'])) {
                $validated['escalation_date'] = now();
            } else {
                $validated['escalation_to'] = null;
                $validated['escalation_date'] = null;
            }
        }

        // ✅ Auto update assigned date
        if ($request->has('inline_update') && $request->inline_update && $request->has('id_pic_ticket')) {
            if ($request->id_pic_ticket) {
                $validated['assigned_status'] = 'Assigned';
                $validated['assigned_date'] = now();
            } else {
                $validated['assigned_status'] = 'Unassigned';
                $validated['assigned_date'] = null;
            }
        }

        // ✅ Auto update progress date
        if ($request->has('inline_update') && $request->inline_update && isset($validated['ticket_status'])) {
            if ($validated['ticket_status'] === 'On Progress') {
                $validated['progress_date'] = now();
                $validated['tracking_status'] = 'On Progress';
            } elseif ($validated['ticket_status'] === 'Closed') {
                $validated['tracking_status'] = 'Closed';
            }
        }

        
        // ✅ Auto update closed date
        if ($request->has('inline_update') && $request->inline_update && isset($validated['ticket_status'])) {
            if ($validated['ticket_status'] === 'Closed') {
                $validated['closed_date'] = now();
                $validated['tracking_status'] = 'Closed';
                $validated['ticket_closed_by'] = Auth::id();
            } else {
                $validated['closed_date'] = null;
            }
        }

        // ✅ Auto update cancelled tracking status
        if ($request->has('inline_update') && $request->inline_update && isset($validated['ticket_status'])) {
            if ($validated['ticket_status'] === 'Cancelled') {
                $validated['tracking_status'] = 'Cancelled';
            }
        }

        // ✅ Add nilai rating
        if ($request->inline_update && $request->has('nilai_rating')) {
            $rating = Rating::updateOrCreate(
                ['nilai_rating' => $request->nilai_rating]
            );

            $validated['id_rating'] = $rating->id_rating;
        }


        $effectiveProgressDate = $validated['progress_date'] ?? $ticket->progress_date;
        $effectiveEscalationTo = $validated['escalation_to'] ?? $ticket->escalation_to;

        $normalSla = $ticket->priority?->sla_duration_normal ?? 0;
        $internalEscalationSla = $ticket->escalation_to ? ($ticket->priority?->sla_duration_escalation ?? 0) : 0;
        $thirdPartySla = $ticket->id_eskalasi_pihak_ketiga ? ($ticket->eskalasiPihakKetiga?->tp_sla_duration ?? 0) : 0;

        $totalSla = $normalSla + $internalEscalationSla + $thirdPartySla;


        $dueDate = $totalSla && $effectiveProgressDate
            ? $this->calculateDueDateSkippingHolidays($effectiveProgressDate, $totalSla)
            : null;

        if ($dueDate) {
            $validated['due_date'] = $dueDate; // put it in validated so it's saved with other changes
        }

        $isOverdue = $dueDate && optional($ticket->closed_date ?? now())->gt($dueDate);

        // Capture old escalation before saving
        $oldEscalation = $ticket->escalation_to;
        $newEscalation = $validated['escalation_to'] ?? $ticket->escalation_to;

        $oldVendor = $ticket->id_eskalasi_pihak_ketiga;
        $newVendor = $validated['id_eskalasi_pihak_ketiga'] ?? $ticket->id_eskalasi_pihak_ketiga;

        $isVendorEscalated = !empty($newVendor) && $newVendor !== $oldVendor;


        $isEscalated = !empty($newEscalation) && $newEscalation !== $oldEscalation;

        // Evaluate against status possibly changed in this request
        $currentStatus = $validated['ticket_status'] ?? $ticket->ticket_status;

        // -------------------------------------------------------
        // 🚫 BLOCK INTERNAL ESCALATION IF THIRD-PARTY ESCALATION EXISTS
        // -------------------------------------------------------
        if ($ticket->id_eskalasi_pihak_ketiga) {

            // Completely ignore incoming escalation_to from request
            unset($validated['escalation_to']);
            unset($validated['escalation_date']);

            // DO NOT run internal escalation logic
        }

        // -------------------------------------------------------
        // ✅ INTERNAL ESCALATION (when NO third-party escalation)
        // -------------------------------------------------------
        elseif ($isEscalated) {

            $newEscalationUser = User::find($newEscalation);
            $namaUser = $ticket->endUser?->nama_user ?? '-';
            $adminUsers = Role::findByName('Admin')->users;

            // 🟦 Build escalation target display (internal OR external)
            if ($ticket->eskalasiPihakKetiga) {
                $assignedTo =
                    $ticket->eskalasiPihakKetiga->tp_pic_ticket .
                    ' (' . ($ticket->eskalasiPihakKetiga->pihakKetiga->nama_perusahaan ?? '-') . ')';
            } else {
                $assignedTo = $newEscalationUser?->nama_user ?? '-';
            }

            // --- Open: notifications only ---
            if ($currentStatus === 'Open') {
                foreach ($adminUsers as $adminUser) {
                    $adminUser->notify(new TicketNotification(
                        $ticket->id_ticket,
                        null,
                        'ticket_escalation',
                        $namaUser,
                        $ticket->id_ticket_type,
                        $assignedTo
                    ));
                }

                if ($newEscalationUser) {
                    $newEscalationUser->notify(new TicketNotification(
                        $ticket->id_ticket,
                        null,
                        'ticket_escalation',
                        $namaUser,
                        $ticket->id_ticket_type,
                        $assignedTo
                    ));
                }
            }

            // --- On Progress: tracking + notifications + emails ---
            if ($currentStatus === 'On Progress') {
                $tracking = TicketTracking::create([
                    'id_ticket'           => $ticket->id_ticket,
                    'id_ticket_type'      => $ticket->id_ticket_type,
                    'id_pic_ticket'       => $ticket->id_pic_ticket,
                    'ticket_type'         => $ticket->ticket_type,
                    'tracking_status'     => 'On Progress',
                    'ticket_comment'      => 'Pengerjaan dialihkan ke ' . $assignedTo,
                    'comment_created_on'  => now(),
                    'tracking_created_on' => now(),
                ]);

                foreach ($adminUsers as $adminUser) {
                    $adminUser->notify(new TicketNotification(
                        $ticket->id_ticket,
                        $tracking->id_ticket_tracking,
                        'ticket_escalation',
                        $namaUser,
                        $ticket->id_ticket_type,
                        $assignedTo
                    ));
                }

                if ($newEscalationUser) {
                    $newEscalationUser->notify(new TicketNotification(
                        $ticket->id_ticket,
                        $tracking->id_ticket_tracking,
                        'ticket_escalation',
                        $namaUser,
                        $ticket->id_ticket_type,
                        $assignedTo
                    ));
                }

                $recipients = array_filter([
                    $ticket->endUser?->email,
                    $ticket->pic?->email,
                    $newEscalationUser?->email,
                ]);

                foreach ($recipients as $email) {
                    if (str_contains($email, '@')) {
                        $ticket->escalation_to = $newEscalation;

                        if ($newEscalationUser) {
                            $ticket->setRelation('escalationTo', $newEscalationUser);
                        }

                        $ticket->due_date = $validated['due_date'] ?? $ticket->due_date;

                        Mail::to($email)->send(
                            new TicketEskalasiUpdate($ticket, $tracking->ticket_comment, null, $tracking->id_ticket_tracking)
                        );
                    }
                }
            }

        } // <-- closes the elseif


        // ✅ Save once
        $ticket->update($validated);
        $ticket->refresh()->load(['endUser', 'escalationTo']);

        $fieldLabels = [
            'ticket_status' => 'Status Tiket',
            'ticket_type' => 'Jenis Tiket',
            'id_pic_ticket' => 'PIC Tiket',
            'assigned_status' => 'Status Penugasan',
            'escalation_to' => 'PIC Eskalasi',
            'ticket_title' => 'Judul',
            'id_ticket_priority' => 'Prioritas',
            'assigned_date' => 'Tanggal Penugasan',
            'closed_date' => 'Tanggal Close',
            'escalation_date' => 'Tanggal Eskalasi',
            'rootcause_awal' => 'Analisis Awal',
            'solusi_awal' => 'Solusi Awal',
            'ticket_closed_by' => 'Ditutup Oleh',
        ];

        $changes = [];

        foreach ($validated as $field => $newValue) {
            if (in_array($field, ['tracking_status'])) {
                continue;
            }

            $oldValue = $originalTicket->{$field};

            if ($oldValue instanceof \Carbon\Carbon) {
                $oldValue = $oldValue->toDateTimeString();
            }
            if ($newValue instanceof \Carbon\Carbon) {
                $newValue = $newValue->toDateTimeString();
            }

            if ($oldValue != $newValue) {
                $label = $fieldLabels[$field] ?? ucwords(str_replace('_', ' ', $field));

                if (in_array($field, ['id_pic_ticket', 'escalation_to', 'last_updated_by', 'ticket_closed_by'])) {
                    $oldUser = $oldValue ? User::find($oldValue)?->nama_user : '-';
                    $newUser = $newValue ? User::find($newValue)?->nama_user : '-';
                    $changes[] = "$label: " . ($oldUser ?? '-') . " ➜ " . ($newUser ?? '-');

                } elseif ($field == 'id_layanan') {
                    $oldLayanan = $oldValue ? Layanan::find($oldValue) : null;
                    $newLayanan = $newValue ? Layanan::find($newValue) : null;
                    $oldLabel = $oldLayanan ? "{$oldLayanan->group_layanan} - {$oldLayanan->nama_layanan}" : '-';
                    $newLabel = $newLayanan ? "{$newLayanan->group_layanan} - {$newLayanan->nama_layanan}" : '-';
                    $changes[] = "$label: $oldLabel ➜ $newLabel";

                } elseif ($field == 'id_ticket_priority') {
                    $oldPriority = $oldValue ? TicketPriority::find($oldValue) : null;
                    $newPriority = $newValue ? TicketPriority::find($newValue) : null;
                    $oldLabel = $oldPriority ? "{$oldPriority->tingkat_priority} - {$oldPriority->tingkat_dampak} - {$oldPriority->tingkat_urgensi}" : '-';
                    $newLabel = $newPriority ? "{$newPriority->tingkat_priority} - {$newPriority->tingkat_dampak} - {$newPriority->tingkat_urgensi}" : '-';
                    $changes[] = "$label: $oldLabel ➜ $newLabel";

                } elseif ($field == 'id_solusi') {
                    $oldSolusi = $oldValue ? Solusi::find($oldValue)?->nama_solusi : '-';
                    $newSolusi = $newValue ? Solusi::find($newValue)?->nama_solusi : '-';
                    $changes[] = "Solusi: $oldSolusi ➜ $newSolusi";

                } elseif ($field == 'id_rootcause') {
                    $oldRoot = $oldValue ? Rootcause::find($oldValue)?->nama_rootcause : '-';
                    $newRoot = $newValue ? Rootcause::find($newValue)?->nama_rootcause : '-';
                    $changes[] = "Rootcause: $oldRoot ➜ $newRoot";

                } elseif (is_array($oldValue) || is_array($newValue)) {
                    $oldString = is_array($oldValue) ? implode(", ", $oldValue) : ($oldValue ?? '-');
                    $newString = is_array($newValue) ? implode(", ", $newValue) : ($newValue ?? '-');
                    $changes[] = "$label: $oldString ➜ $newString";

                } else {
                    $changes[] = "$label: " . ($oldValue ?? '-') . " ➜ " . ($newValue ?? '-');
                }
            }
        }


        $logSummary = implode(", \n", $changes) ?: 'No changes detected.';

        $oldEscalation = $ticket->escalation_to; // ✅ capture BEFORE update

        $ticket->update($validated);
        $ticket->load('priority', 'pic', 'solusi', 'rootcause', 'escalationTo', 'endUser'); // 🔁 reload the relation after update

        TicketLog::create([
            'id_ticket' => $ticket->id_ticket,
            'id_ticket_type' => $ticket->id_ticket_type,
            'ticket_title' => $ticket->ticket_title,
            'ticket_type' => $ticket->ticket_type,
            'ticket_status' => $ticket->ticket_status,
            'assigned_status' => $ticket->assigned_status,
            'assigned_date' => $ticket->assigned_date,
            'id_pic_ticket' => $ticket->id_pic_ticket,
            'escalation_to' => $ticket->escalation_to,
            'escalation_date' => $ticket->escalation_date,
            'rootcause_awal' => $ticket->rootcause_awal,
            'last_updated_by' => $validated['last_updated_by'],
            'last_updated_on' => $validated['last_updated_on'],
            'ticket_closed_by' => $ticket->ticket_closed_by,
            'change_summary' => $logSummary, // ✅ here it goes
        ]);

        if ($request->has('inline_update') && isset($validated['tracking_status'])) {
            $trackingStatus = $validated['tracking_status'];

            $systemComment = match ($trackingStatus) {
                'Created' => 'Tiket telah dibuat oleh ' . ($ticket->endUser?->nama_user ?? '-'),
                'Assigned' => 'Tiket diassign ke ' . ($ticket->pic?->nama_user ?? '-'),
                'On Progress' => 'Tiket sedang dikerjakan oleh ' . 
                    ($ticket->escalationTo?->nama_user ?? $ticket->pic?->nama_user ?? '-'),
                'Closed' => 'Tiket telah selesai',
                'Cancelled' => 'Tiket dibatalkan',
                default => null,
            };

            if ($systemComment) {
                $tracking = TicketTracking::create([
                    'id_ticket' => $ticket->id_ticket,
                    'id_ticket_type' => $ticket->id_ticket_type,
                    'id_pic_ticket' => $ticket->id_pic_ticket,
                    'ticket_type' => $ticket->ticket_type,
                    'tracking_status' => $trackingStatus,
                    'ticket_comment' => $systemComment,
                    'comment_created_on' => now(),
                    'tracking_created_on' => now(),
                    'pic_comment' => null,
                    'user_comment' => null,
                    'cancel_comment' => $trackingStatus === 'Cancelled' ? ($request->cancel_comment ?? null) : null,
                ]);

                $idTicketTracking = $tracking->id_ticket_tracking;

                // 📨 Send email to relevant users
                $recipients = array_filter([
                    $ticket->endUser?->email,
                    $ticket->pic?->email,
                    $ticket->escalationTo?->email,
                ]);
                foreach ($recipients as $email) {
                    if ($email && str_contains($email, '@')) {
                        $trackingStatus = $tracking->tracking_status;
                        $systemComment = $tracking->ticket_comment;
                        $picComment = $tracking->pic_comment;
                        $cancelComment = $tracking->cancel_comment;

                        switch ($trackingStatus) {
                            case 'Closed':
                                Mail::to($email)->send(new TicketClosedStatus($ticket, $systemComment, $picComment, $idTicketTracking));
                                break;
                            case 'Cancelled':
                                Mail::to($email)->send(new TicketCancelledStatus($ticket, $systemComment, $picComment, $cancelComment, $idTicketTracking));
                                break;
                            case 'Assigned':
                                Mail::to($email)->send(new TicketAssignedStatus($ticket, $systemComment, $picComment, $idTicketTracking));
                                break;
                            case 'On Progress':
                                Mail::to($email)->send(new TicketOnProgressStatus($ticket, $systemComment, $picComment, $idTicketTracking));
                                break;
                        }
                    }
                }

            }
        }

        return response()->json([
            'message' => 'Ticket updated successfully',
            'data' => [
                'id_ticket' => $ticket->id_ticket,
                'id_ticket_type' => $ticket->id_ticket_type,
                'ticket_type' => $ticket->ticket_type,
                'status_overdue' => $isOverdue ? 'Overdue' : 'On Time',
                'due_date' => $dueDate ? $dueDate->toDateTimeString() : null,
                'ticket_title' => $ticket->ticket_title,
                'ticket_description' => $ticket->ticket_description,
                'ticket_status' => $ticket->ticket_status,
                'tingkat_priority' => $ticket->priority?->tingkat_priority,
                'pic_tiket' => $ticket->pic?->nama_user,
                'nama_user' => $ticket->endUser?->nama_user,
                'divisi_user' => $ticket->divisi?->nama_divisi,
                'id_ticket_priority' => $ticket->id_ticket_priority,
                'id_pic_ticket' => $ticket->id_pic_ticket,
                'id_user' => $ticket->id_end_user,
                'id_divisi' => $ticket->id_divisi,
                'id_layanan' => $ticket->id_layanan,
                'id_rootcause' => $ticket->id_rootcause,
                'id_solusi' => $ticket->id_solusi,
                'id_permintaan' => $ticket->id_permintaan,
                'id_rating' => $ticket->id_rating,
                'nama_rootcause' => $ticket->rootcause?->nama_rootcause ?? '-',
                'rootcause_description' => $ticket->rootcause?->rootcause_description ?? '-',
                'nama_solusi' => $ticket->solusi?->nama_solusi ?? '-',
                'solusi_description' => $ticket->solusi?->solusi_description ?? '-',
                'assigned_status' => $ticket->assigned_status,
                'assigned_date' => optional($ticket->assigned_date)->toDateTimeString(),
                'progress_date' => optional($ticket->progress_date)->toDateTimeString(),
                'pic_eskalasi' => $ticket->escalationTo?->nama_user ?? 'Belum Ada',
                'tanggal_eskalasi' => optional($ticket->escalation_date)->toDateTimeString(),
                'tanggal_close' => optional($ticket->closed_date)->toDateTimeString(),
                'analisis_awal' => $ticket->rootcause_awal,
                'teks_pendukung' => $ticket->teks_pendukung,
                'link_pendukung' => $ticket->link_pendukung,
                'screenshot_pendukung' => $ticket->screenshot_pendukung,
                'teknisi_tambahan' => $ticket->teknisi_tambahan,
                'ticket_closed_by' => $ticket->ticket_closed_by,
                'pic_closed' => $ticket->closedBy?->nama_user,
                'nilai_rating' => $ticket->rating?->nilai_rating,
            ]
        ]);
    }


    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();

        return response()->json([
            'message' => 'Ticket deleted successfully',
        ]);
    }
}