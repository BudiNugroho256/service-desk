<?php

namespace App\Http\Controllers;

use App\Models\Solusi;
use Illuminate\Http\Request;

class SolusiController extends Controller
{
    public function index(Request $request)
    {
        $query = Solusi::with('layanan');

        // ✅ Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_solusi', 'ilike', '%' . $request->search . '%')
                  ->orWhere('solusi_description', 'ilike', '%' . $request->search . '%');
            });
        }

        // ✅ Sorting
        $sortBy = $request->input('sort_by');
        $sortDir = strtolower($request->input('sort_dir', 'asc'));
        $allowedSorts = ['id_solusi', 'nama_solusi', 'solusi_description'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('id_solusi', 'asc'); // default sort
        }

        // ✅ Pagination
        if ($request->has('page') || $request->has('per_page') || $request->has('search')) {
            $perPage = $request->input('per_page', 10);
            $solusi = $query->paginate($perPage);

            return response()->json([
                'data' => $solusi->items(),
                'total' => $solusi->total(),
            ]);
        }

        return response()->json($query->get());
    }

    public function show($id)
    {
        return response()->json(Solusi::with('layanan')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_layanan' => 'nullable|exists:tblm_layanan,id_layanan',
            'nama_solusi' => 'required|string|max:100',
            'solusi_description' => 'nullable|string',
        ]);

        $solusi = Solusi::create($validated);
        return response()->json($solusi, 201);
    }

    public function update(Request $request, $id)
    {
        $solusi = Solusi::findOrFail($id);

        $validated = $request->validate([
            'id_layanan' => 'nullable|exists:tblm_layanan,id_layanan',
            'nama_solusi' => 'required|string|max:100',
            'solusi_description' => 'nullable|string',
        ]);

        $solusi->update($validated);
        return response()->json($solusi);
    }

    public function destroy($id)
    {
        $solusi = Solusi::findOrFail($id);
        $solusi->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
