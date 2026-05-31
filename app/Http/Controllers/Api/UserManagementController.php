<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * GET /api/admin/users
     * List users with search, role & status filters.
     * Excludes soft-deleted users (is_deleted = 1).
     */
    public function index(Request $request)
    {
        $query = User::where('is_deleted', 0);

        // Search by name or email
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($role = $request->query('role')) {
            $query->where('role', $role);
        }

        // Filter by status (is_active: 1 = Active, 0 = Inactive)
        if ($request->has('status')) {
            $status = $request->query('status');
            if ($status === 'active') {
                $query->where('is_active', 1);
            } elseif ($status === 'inactive') {
                $query->where('is_active', 0);
            }
        }

        $perPage = $request->query('per_page', 50);
        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * GET /api/admin/users/{id}
     * Show a single user.
     */
    public function show($id)
    {
        $user = User::where('is_deleted', 0)->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * POST /api/admin/users
     * Create a new user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:100',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:8',
            'phone'        => ['nullable', 'regex:/^(08|\+628)[0-9]{8,13}$/'],
            'role'         => ['required', Rule::in(['ADMIN', 'MEMBER'])],
            'company_code' => 'nullable|string|max:32',
            'is_active'    => 'boolean',
        ]);

        $user = User::create([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'password'     => Hash::make($validated['password']),
            'phone'        => $validated['phone'] ?? null,
            'role'         => $validated['role'],
            'company_code' => $validated['company_code'] ?? 'Synthera',
            'is_active'    => $validated['is_active'] ?? true,
            'status'       => 1,
            'is_deleted'   => 0,
            'created_by'   => $request->user()->name ?? 'Synthera',
            'created_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dibuat.',
            'data'    => $user,
        ], 201);
    }

    /**
     * PUT /api/admin/users/{id}
     * Update user fields (name, email, role, phone, company_code, is_active).
     */
    public function update(Request $request, $id)
    {
        $user = User::where('is_deleted', 0)->findOrFail($id);

        $validated = $request->validate([
            'name'         => 'sometimes|string|max:100',
            'email'        => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'phone'        => ['nullable', 'regex:/^(08|\+628)[0-9]{8,13}$/'],
            'role'         => ['sometimes', Rule::in(['ADMIN', 'MEMBER'])],
            'company_code' => 'nullable|string|max:32',
            'is_active'    => 'sometimes|boolean',
        ]);

        // Map is_active to audit fields
        $updateData = array_merge($validated, [
            'last_updated_by'   => $request->user()->name ?? 'Synthera',
            'last_updated_date' => now(),
        ]);

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diperbarui.',
            'data'    => $user->fresh(),
        ]);
    }

    /**
     * DELETE /api/admin/users/{id}
     * Soft-delete user by setting is_deleted = 1.
     * Prevents admin from deleting themselves.
     */
    public function destroy(Request $request, $id)
    {
        $user = User::where('is_deleted', 0)->findOrFail($id);

        // Prevent self-deletion
        if ($request->user()->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus akun sendiri.',
            ], 403);
        }

        $user->update([
            'is_deleted'        => 1,
            'last_updated_by'   => $request->user()->name ?? 'Synthera',
            'last_updated_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus.',
        ]);
    }
}
