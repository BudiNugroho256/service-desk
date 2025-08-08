<?php

namespace App\Http\Controllers;

use App\Models\TicketPriority;
use Illuminate\Http\Request;

class TicketPriorityController extends Controller
{
    public function index(Request $request)
    {
        $query = TicketPriority::query();

        // ✅ Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('tingkat_priority', 'like', '%' . $request->search . '%')
                ->orWhere('tingkat_dampak', 'like', '%' . $request->search . '%')
                ->orWhere('tingkat_urgensi', 'like', '%' . $request->search . '%')
                ->orWhere('ticket_priority_description', 'like', '%' . $request->search . '%');
            });
        }

        // ✅ Sorting
        $sortBy = $request->input('sort_by');
        $sortDir = strtolower($request->input('sort_dir', 'asc'));
        $allowedSorts = [
            'id_ticket_priority',
            'tingkat_priority',
            'tingkat_dampak',
            'tingkat_urgensi',
            'sla_duration_normal',
            'sla_duration_escalation',
            'sla_duration_thirdparty',
            'ticket_priority_description',
        ];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('id_ticket_priority', 'asc'); // default sort
        }

        // ✅ Pagination
        if ($request->has('page') || $request->has('per_page') || $request->has('search')) {
            $perPage = $request->input('per_page', 10);
            $priorities = $query->paginate($perPage);

            return response()->json([
                'data' => $priorities->items(),
                'total' => $priorities->total(),
            ]);
        }

        return response()->json($query->get());
    }


    public function show($id)
    {
        return response()->json(TicketPriority::findOrFail($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tingkat_priority' => 'required|string|max:255',
            'tingkat_dampak' => 'required|string|max:255',
            'tingkat_urgensi' => 'required|string|max:255',
            'sla_duration_normal' => 'required|integer',
            'sla_duration_escalation' => 'required|integer',
            'sla_duration_thirdparty' => 'required|integer',
            'ticket_priority_description' => 'nullable|string',
        ]);

        $priority = TicketPriority::create($validated);
        return response()->json($priority, 201);
    }

    public function update(Request $request, $id)
    {
        $priority = TicketPriority::findOrFail($id);

        $validated = $request->validate([
            'tingkat_priority' => 'required|string|max:255',
            'tingkat_dampak' => 'required|string|max:255',
            'tingkat_urgensi' => 'required|string|max:255',
            'sla_duration_normal' => 'required|integer',
            'sla_duration_escalation' => 'required|integer',
            'sla_duration_thirdparty' => 'required|integer',
            'ticket_priority_description' => 'nullable|string',
        ]);

        $priority->update($validated);
        return response()->json($priority);
    }

    public function destroy($id)
    {
        $priority = TicketPriority::findOrFail($id);
        $priority->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}