<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\User;
use Illuminate\Http\Request;

class LayananController extends Controller
{
    public function index(Request $request)
    {
        $query = Layanan::query();

        // ✅ Filtering
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('group_layanan', 'like', '%' . $request->search . '%')
                  ->orWhere('nama_layanan', 'like', '%' . $request->search . '%')
                  ->orWhere('status_layanan', 'like', '%' . $request->search . '%');
            });
        }

        // ✅ Sorting
        $sortBy = $request->input('sort_by');
        $sortDir = strtolower($request->input('sort_dir', 'asc'));

        $allowedSorts = ['id_layanan', 'group_layanan', 'nama_layanan', 'status_layanan'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('id_layanan', 'asc'); // Default sort
        }

        // ✅ Pagination
        if ($request->has('page') || $request->has('per_page') || $request->has('search')) {
            $perPage = $request->input('per_page', 10);
            $layanans = $query->paginate($perPage);

            return response()->json([
                'data' => $layanans->items(),
                'total' => $layanans->total(),
            ]);
        }

        return response()->json($query->get());
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_layanan' => 'required|string',
            'nama_layanan' => 'required|string',
            'status_layanan' => 'required|string',
        ]);

        $layanan = Layanan::create($validated);

        return response()->json([
            'message' => 'Layanan created successfully.',
            'data' => $layanan,
        ]);
    }

    public function show($id)
    {
        $layanan = Layanan::with('assignedUser')->findOrFail($id);

        return response()->json([
            'data' => $layanan,
        ]);
    }

    public function update(Request $request, $id)
    {
        $layanan = Layanan::findOrFail($id);

        $validated = $request->validate([
            'group_layanan' => 'required|string',
            'nama_layanan' => 'required|string',
            'status_layanan' => 'required|string',
        ]);

        $layanan->update($validated);

        return response()->json([
            'message' => 'Layanan updated successfully.',
            'data' => $layanan,
        ]);
    }

    public function destroy($id)
    {
        $layanan = Layanan::findOrFail($id);
        $layanan->delete();

        return response()->json([
            'message' => 'Layanan deleted successfully.'
        ]);
    }
}
