<?php

namespace App\Http\Controllers;

use App\Models\Rootcause;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class RootcauseController extends Controller
{

    public function index(Request $request)
    {
        $query = Rootcause::with('layanan');

        // ✅ Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_rootcause', 'ilike', '%' . $request->search . '%')
                ->orWhere('rootcause_description', 'ilike', '%' . $request->search . '%');
            });
        }

        $rootcauses = $query->get();

        // ✅ Transform
        $data = $rootcauses->map(function ($item) {
            return [
                'id_rootcause' => $item->id_rootcause,
                'nama_rootcause' => $item->nama_rootcause,
                'rootcause_description' => $item->rootcause_description,
                'id_layanan' => $item->id_layanan,
                'group_layanan' => $item->layanan?->group_layanan ?? '',
                'nama_layanan' => $item->layanan?->nama_layanan ?? '',
            ];
        });

        // ✅ Sorting (manual, like in TicketController)
        $sortBy = $request->input('sort_by');
        $sortDir = strtolower($request->input('sort_dir', 'asc'));

        if ($sortBy) {
            $manualSortFields = ['group_layanan', 'nama_layanan', 'nama_rootcause', 'rootcause_description'];

            if (in_array($sortBy, $manualSortFields)) {
                $data = $data->sortBy(fn($item) => $item[$sortBy] ?? null, SORT_REGULAR, $sortDir === 'desc')->values();
            } else {
                // fallback sort by id_rootcause if unknown
                $data = $data->sortBy('id_rootcause')->values();
            }
        }

        // ✅ Pagination (manual, like in TicketController)
        $perPage = (int) $request->input('per_page', 10);
        $currentPage = Paginator::resolveCurrentPage();
        $currentItems = $data->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginated = new LengthAwarePaginator(
            $currentItems,
            $data->count(),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );

        return response()->json([
            'data' => $paginated->items(),
            'total' => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
        ]);
    }


    public function show($id)
    {
        return response()->json(Rootcause::with('layanan')->findOrFail($id));
    }

    public function store(Request $request)
    {
        // ✅ Sanitize if Vue sent an object
        if (is_array($request->id_layanan)) {
            $request->merge([
                'id_layanan' => $request->id_layanan['id_layanan'] ?? null
            ]);
        }

        $validated = $request->validate([
            'id_layanan' => 'nullable|exists:tblm_layanan,id_layanan',
            'nama_rootcause' => 'required|string|max:100',
            'rootcause_description' => 'nullable|string',
        ]);

        $rootcause = Rootcause::create($validated);
        $rootcause->load('layanan');

        return response()->json([
            'id_rootcause' => $rootcause->id_rootcause,
            'id_layanan' => $rootcause->id_layanan,
            'nama_rootcause' => $rootcause->nama_rootcause,
            'rootcause_description' => $rootcause->rootcause_description,
            'group_layanan' => $rootcause->layanan?->group_layanan,
            'nama_layanan' => $rootcause->layanan?->nama_layanan,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $rootcause = Rootcause::findOrFail($id);

        // ✅ Sanitize if needed
        if (is_array($request->id_layanan)) {
            $request->merge([
                'id_layanan' => $request->id_layanan['id_layanan'] ?? null
            ]);
        }

        $validated = $request->validate([
            'id_layanan' => 'nullable|exists:tblm_layanan,id_layanan',
            'nama_rootcause' => 'required|string|max:100',
            'rootcause_description' => 'nullable|string',
        ]);

        $rootcause->update($validated);
        $rootcause->load('layanan');

        return response()->json([
            'id_rootcause' => $rootcause->id_rootcause,
            'id_layanan' => $rootcause->id_layanan,
            'nama_rootcause' => $rootcause->nama_rootcause,
            'rootcause_description' => $rootcause->rootcause_description,
            'group_layanan' => $rootcause->layanan?->group_layanan,
            'nama_layanan' => $rootcause->layanan?->nama_layanan,
        ]);
    }

    public function destroy($id)
    {
        $rootcause = Rootcause::findOrFail($id);
        $rootcause->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
