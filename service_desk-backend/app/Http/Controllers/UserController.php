<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['divisi', 'roles']);

        // ✅ Filtering
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_user', 'ilike', '%' . $request->search . '%')
                    ->orWhere('email', 'ilike', '%' . $request->search . '%');
            });
        }

        // ✅ Sorting
        $sortBy = $request->input('sort_by');
        $sortDir = strtolower($request->input('sort_direction', 'asc'));

        if (in_array($sortBy, ['id_user', 'nama_user', 'email'])) {
            $query->orderBy($sortBy, $sortDir);
        } elseif ($sortBy === 'divisi') {
            $query->join('tblm_divisi as d', 'tblm_user.id_divisi', '=', 'd.id_divisi')
                ->orderBy('d.nama_divisi', $sortDir)
                ->select('tblm_user.*'); // Prevent column conflict from join
        } elseif ($sortBy === 'roles') {
            $query->leftJoin('model_has_roles as mhr', function ($join) {
                    $join->on('tblm_user.id_user', '=', 'mhr.id_user')
                        ->where('mhr.model_type', '=', User::class);
                })
                ->leftJoin('roles as r', 'mhr.role_id', '=', 'r.id')
                ->orderBy('r.name', $sortDir)
                ->select('tblm_user.*'); // Prevent column conflict from join
        } else {
            $query->orderBy('id_user', 'asc'); // Default
        }

        // ✅ Pagination
        if ($request->has('page') || $request->has('per_page') || $request->has('search')) {
            $perPage = $request->input('per_page', 10);
            $users = $query->paginate($perPage);

            return response()->json([
                'data' => $users->items(),
                'total' => $users->total(),
            ]);
        }

        return response()->json($query->get());
    }


    public function show($id)
    {
        return response()->json(
            User::with(['divisi', 'roles'])->findOrFail($id)
        );
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_divisi' => 'nullable|exists:tblm_divisi,id_divisi',
            'nama_user' => 'required|string|max:255',
            'nik_user' => 'required|string|unique:tblm_user,nik_user',
            'email' => 'required|email|unique:tblm_user,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|exists:roles,name',  // ✅ validate the role
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        $user->syncRoles($request->role); // ✅ assign the role using Spatie

        return response()->json($user->load('roles'), 201); // Optional: return user with roles
    }


    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $oldDivisiId = $user->id_divisi;

        $validated = $request->validate([
            'id_divisi' => 'nullable|exists:tblm_divisi,id_divisi',
            'nama_user' => 'required|string|max:255',
            'nik_user' => ['nullable', Rule::unique('tblm_user', 'nik_user')->ignore($user->id_user, 'id_user')],
            'email' => ['required', 'email', Rule::unique('tblm_user', 'email')->ignore($user->id_user, 'id_user')],
            'password' => 'nullable|string|min:6',
            'role' => 'required|string|exists:roles,name',  // ✅ validate role input
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        $user->syncRoles([$request->role]); // ✅ sync role (remove old, assign new)

        // ✅ Update tickets if division has changed
        if (array_key_exists('id_divisi', $validated) && $validated['id_divisi'] !== $oldDivisiId) {
            \App\Models\Ticket::where('id_end_user', $user->id_user)
                ->update(['id_divisi' => $validated['id_divisi']]);
        }

        return response()->json($user->load('roles'));
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}