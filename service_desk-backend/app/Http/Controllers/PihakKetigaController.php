<?php

namespace App\Http\Controllers;

use App\Models\PihakKetiga;
use Illuminate\Http\Request;

class PihakKetigaController extends Controller
{
    public function index(Request $request)
    {
        $query = PihakKetiga::query();

        // ✅ Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_perusahaan', 'ilike', '%' . $request->search . '%')
                  ->orWhere('perusahaan_description', 'ilike', '%' . $request->search . '%');
            });
        }

        // ✅ Sorting
        $sortBy = $request->input('sort_by');
        $sortDir = strtolower($request->input('sort_dir', 'asc'));
        $allowedSorts = ['id_pihak_ketiga', 'nama_perusahaan', 'perusahaan_description'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('id_pihak_ketiga', 'asc'); // default sort
        }

        // ✅ Pagination
        if ($request->has('page') || $request->has('per_page') || $request->has('search')) {
            $perPage = $request->input('per_page', 10);
            $data = $query->paginate($perPage);

            return response()->json([
                'data' => $data->items(),
                'total' => $data->total(),
            ]);
        }

        return response()->json($query->get());
    }

    public function show($id)
    {
        return response()->json(
            PihakKetiga::findOrFail($id)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_perusahaan'          => 'required|string|max:255',
            'perusahaan_description'   => 'nullable|string',
        ]);

        $data = PihakKetiga::create($validated);

        return response()->json($data, 201);
    }

    public function update(Request $request, $id)
    {
        $data = PihakKetiga::findOrFail($id);

        $validated = $request->validate([
            'nama_perusahaan'          => 'required|string|max:255',
            'perusahaan_description'   => 'nullable|string',
        ]);

        $data->update($validated);

        return response()->json($data);
    }

    public function destroy($id)
    {
        $data = PihakKetiga::findOrFail($id);
        $data->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}