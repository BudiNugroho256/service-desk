<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function index(Request $request)
    {
        $query = Rating::query();

        // ✅ Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_rating', 'ilike', '%' . $request->search . '%')
                  ->orWhere('rating_description', 'ilike', '%' . $request->search . '%');
            });
        }

        // ✅ Sorting
        $sortBy = $request->input('sort_by');
        $sortDir = strtolower($request->input('sort_dir', 'asc'));
        $allowedSorts = ['id_rating', 'nama_rating', 'rating_description', 'nilai_rating']; // ⭐ added

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('id_rating', 'asc'); // default sort
        }

        // ✅ Pagination
        if ($request->has('page') || $request->has('per_page') || $request->has('search')) {
            $perPage = $request->input('per_page', 10);
            $ratings = $query->paginate($perPage);

            return response()->json([
                'data'  => $ratings->items(),
                'total' => $ratings->total(),
            ]);
        }

        return response()->json($query->get());
    }

    public function show($id)
    {
        return response()->json(Rating::findOrFail($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_rating'         => 'required|string|max:100',
            'nilai_rating'        => 'required|integer|min:1|max:5',  // ⭐ added
            'rating_description'  => 'nullable|string',
        ]);

        $rating = Rating::create($validated);

        return response()->json($rating, 201);
    }

    public function update(Request $request, $id)
    {
        $rating = Rating::findOrFail($id);

        $validated = $request->validate([
            'nama_rating'         => 'required|string|max:100',
            'nilai_rating'        => 'required|integer|min:1|max:5',  // ⭐ added
            'rating_description'  => 'nullable|string',
        ]);

        $rating->update($validated);

        return response()->json($rating);
    }

    public function destroy($id)
    {
        $rating = Rating::findOrFail($id);
        $rating->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}