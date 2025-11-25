<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::with('permissions');

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        // Sort (match the parameter names from your frontend)
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = strtolower($request->input('sort_direction', 'asc'));  // ✅ FIXED from sort_dir to sort_direction

        if (in_array($sortBy, ['id', 'name'])) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('id', 'asc'); // Default
        }

        // Pagination
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
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create(['name' => $request->name]);
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return $role->load('permissions');
    }

    public function show(Role $role)
    {
        return $role->load('permissions');
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'array',
        ]);

        $role->update(['name' => $request->name]);
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return $role->load('permissions');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->noContent();
    }
}
