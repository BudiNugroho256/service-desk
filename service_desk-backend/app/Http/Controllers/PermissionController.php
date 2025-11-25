<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Permission::query();

        // ✅ Search
        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        // ✅ Sort
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = strtolower($request->input('sort_direction', 'asc')); // Make sure your frontend sends 'sort_direction'

        if (in_array($sortBy, ['id', 'name', 'guard_name'])) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('id', 'asc');
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

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:permissions,name']);
        return Permission::create(['name' => $request->name]);
    }

    public function show(Permission $permission)
    {
        return $permission;
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate(['name' => 'required|unique:permissions,name,' . $permission->id]);
        $permission->update(['name' => $request->name]);
        return $permission;
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return response()->noContent();
    }
}
