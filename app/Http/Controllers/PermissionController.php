<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Permission::with('roles')->orderBy('module')->orderBy('action');

        // Filter by module
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('module', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $permissions = $query->paginate(50);
        $roles = Role::orderBy('name')->get();

        // Get unique modules for filter
        $modules = Permission::select('module')->distinct()->orderBy('module')->pluck('module');

        // Stats
        $totalPermissions = Permission::count();
        $totalRoles = Role::count();
        $moduleCount = Permission::select('module')->distinct()->count();

        return view('permissions.index', compact(
            'permissions', 'roles', 'modules',
            'totalPermissions', 'totalRoles', 'moduleCount'
        ));
    }

    public function create()
    {
        $modules = Permission::select('module')->distinct()->orderBy('module')->pluck('module');
        $roles = Role::orderBy('name')->get();

        return view('permissions.create', compact('modules', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'module' => 'required|string|max:100',
            'action' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        // Check if permission already exists
        $exists = Permission::where('module', $validated['module'])
            ->where('action', $validated['action'])
            ->exists();

        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'สิทธิ์นี้มีอยู่แล้วในระบบ');
        }

        try {
            DB::beginTransaction();

            $permission = Permission::create([
                'module' => $validated['module'],
                'action' => $validated['action'],
                'description' => $validated['description'] ?? null,
            ]);

            // Assign to roles
            if (!empty($validated['roles'])) {
                $permission->roles()->sync($validated['roles']);
            }

            DB::commit();

            return redirect()
                ->route('permissions.index')
                ->with('success', 'เพิ่มสิทธิ์เรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $permission = Permission::with('roles')->findOrFail($id);

        return view('permissions.show', compact('permission'));
    }

    public function edit($id)
    {
        $permission = Permission::with('roles')->findOrFail($id);
        $modules = Permission::select('module')->distinct()->orderBy('module')->pluck('module');
        $roles = Role::orderBy('name')->get();

        return view('permissions.edit', compact('permission', 'modules', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $validated = $request->validate([
            'module' => 'required|string|max:100',
            'action' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        // Check if permission already exists (exclude current)
        $exists = Permission::where('module', $validated['module'])
            ->where('action', $validated['action'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'สิทธิ์นี้มีอยู่แล้วในระบบ');
        }

        try {
            DB::beginTransaction();

            $permission->update([
                'module' => $validated['module'],
                'action' => $validated['action'],
                'description' => $validated['description'] ?? null,
            ]);

            // Sync roles
            $permission->roles()->sync($validated['roles'] ?? []);

            DB::commit();

            return redirect()
                ->route('permissions.index')
                ->with('success', 'อัปเดตสิทธิ์เรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $permission = Permission::findOrFail($id);

            // Detach from all roles first
            $permission->roles()->detach();

            $permission->delete();

            return response()->json(['success' => true, 'message' => 'ลบสิทธิ์เรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // API: Toggle role permission
    public function toggleRolePermission(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        try {
            $role = Role::findOrFail($validated['role_id']);
            $permission = Permission::findOrFail($validated['permission_id']);

            if ($role->permissions()->where('permission_id', $permission->id)->exists()) {
                $role->revokePermissionTo($permission);
                $granted = false;
            } else {
                $role->givePermissionTo($permission);
                $granted = true;
            }

            return response()->json([
                'success' => true,
                'granted' => $granted,
                'message' => $granted ? 'เพิ่มสิทธิ์แล้ว' : 'ยกเลิกสิทธิ์แล้ว'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
