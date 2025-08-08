<?php

namespace App\Http\Controllers;

use App\Models\Permintaan;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class PermintaanController extends Controller
{
    public function index(Request $request)
    {
        $query = Permintaan::with('layanan');

        // ✅ Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_permintaan', 'like', '%' . $request->search . '%')
                ->orWhere('permintaan_description', 'like', '%' . $request->search . '%');
            });
        }

        // ✅ Filter by id_layanan
        if ($request->filled('id_layanan')) {
            $query->where('id_layanan', $request->id_layanan);
        }

        $permintaans = $query->get();

        // ✅ Transform
        $data = $permintaans->map(function ($item) {
            return [
                'id_permintaan' => $item->id_permintaan,
                'nama_permintaan' => $item->nama_permintaan,
                'permintaan_description' => $item->permintaan_description,
                'id_layanan' => $item->id_layanan,
                'group_layanan' => $item->layanan?->group_layanan ?? '',
                'nama_layanan' => $item->layanan?->nama_layanan ?? '',
            ];
        });

        // ✅ Sorting (manual, like in TicketController)
        $sortBy = $request->input('sort_by');
        $sortDir = strtolower($request->input('sort_dir', 'asc'));

        if ($sortBy) {
            $manualSortFields = ['group_layanan', 'nama_layanan', 'nama_permintaan', 'permintaan_description'];

            if (in_array($sortBy, $manualSortFields)) {
                $data = $data->sortBy(fn($item) => $item[$sortBy] ?? null, SORT_REGULAR, $sortDir === 'desc')->values();
            } else {
                // fallback sort by id_permintaan if unknown
                $data = $data->sortBy('id_permintaan')->values();
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
        return response()->json(Permintaan::with('layanan')->findOrFail($id));
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
            'nama_permintaan' => 'required|string|max:100',
            'permintaan_description' => 'nullable|string',
        ]);

        $permintaan = Permintaan::create($validated);
        $permintaan->load('layanan');

        return response()->json([
            'id_permintaan' => $permintaan->id_permintaan,
            'id_layanan' => $permintaan->id_layanan,
            'nama_permintaan' => $permintaan->nama_permintaan,
            'permintaan_description' => $permintaan->permintaan_description,
            'group_layanan' => $permintaan->layanan?->group_layanan,
            'nama_layanan' => $permintaan->layanan?->nama_layanan,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $permintaan = Permintaan::findOrFail($id);

        // ✅ Sanitize if needed
        if (is_array($request->id_layanan)) {
            $request->merge([
                'id_layanan' => $request->id_layanan['id_layanan'] ?? null
            ]);
        }

        $validated = $request->validate([
            'id_layanan' => 'nullable|exists:tblm_layanan,id_layanan',
            'nama_permintaan' => 'required|string|max:100',
            'permintaan_description' => 'nullable|string',
        ]);

        $permintaan->update($validated);
        $permintaan->load('layanan');

        return response()->json([
            'id_permintaan' => $permintaan->id_permintaan,
            'id_layanan' => $permintaan->id_layanan,
            'nama_permintaan' => $permintaan->nama_permintaan,
            'permintaan_description' => $permintaan->permintaan_description,
            'group_layanan' => $permintaan->layanan?->group_layanan,
            'nama_layanan' => $permintaan->layanan?->nama_layanan,
        ]);
    }

    public function destroy($id)
    {
        $permintaan = Permintaan::findOrFail($id);
        $permintaan->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}