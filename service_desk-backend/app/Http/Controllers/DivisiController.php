<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use Illuminate\Http\Request;

class DivisiController extends Controller
{
    public function index(Request $request)
    {
        $query = Divisi::query();

        // ✅ Filtering
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_divisi', 'like', '%' . $request->search . '%')
                ->orWhere('kode_divisi', 'like', '%' . $request->search . '%')
                ->orWhere('divisi_alias', 'like', '%' . $request->search . '%');
            });
        }

        // ✅ Sorting
        $sortBy = $request->input('sort_by');
        $sortDir = strtolower($request->input('sort_dir', 'asc'));

        $allowedSorts = ['id_divisi', 'nama_divisi', 'kode_divisi', 'divisi_alias', 'lantai_divisi'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('id_divisi', 'asc'); // default sort
        }

        // ✅ Pagination
        if ($request->has('page') || $request->has('per_page') || $request->has('search')) {
            $perPage = $request->input('per_page', 10);
            $divisions = $query->paginate($perPage);

            return response()->json([
                'data' => $divisions->items(),
                'total' => $divisions->total(),
            ]);
        }

        return response()->json($query->get());
    }


    public function show($id)
    {
        return response()->json(Divisi::findOrFail($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_divisi' => 'required|string|max:255',
            'kode_divisi' => 'required|string|max:255|unique:tblm_divisi,kode_divisi',
            'divisi_alias' => 'required|string|max:255',
            'lantai_divisi' => 'required|integer',
        ]);

        $divisi = Divisi::create($validated);
        return response()->json($divisi, 201);
    }

    public function update(Request $request, $id)
    {
        $divisi = Divisi::findOrFail($id);

        $validated = $request->validate([
            'nama_divisi' => 'required|string|max:255',
            'kode_divisi' => 'required|string|max:255|unique:tblm_divisi,kode_divisi,' . $id . ',id_divisi',
            'divisi_alias' => 'required|string|max:255',
            'lantai_divisi' => 'required|integer',
        ]);

        $divisi->update($validated);
        return response()->json($divisi);
    }

    public function destroy($id)
    {
        $divisi = Divisi::findOrFail($id);
        $divisi->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
