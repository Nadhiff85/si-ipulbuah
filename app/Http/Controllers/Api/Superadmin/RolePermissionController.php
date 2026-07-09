<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    // Pengaturan Role & Permission (fitur C.11) - memakai Spatie Laravel Permission
    public function index()
    {
        return response()->json([
            'roles' => Role::with('permissions')->get(),
            'permissions' => Permission::all(),
        ]);
    }

    // Superadmin dapat menambahkan role baru untuk kebutuhan masa depan
    public function storeRole(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:roles,name']);
        $role = Role::create(['name' => $request->name]);

        return response()->json(['role' => $role], 201);
    }

    // Atur ulang permission untuk sebuah role
    public function syncPermissions(Request $request, Role $role)
    {
        $request->validate(['permissions' => 'required|array']);
        $role->syncPermissions($request->permissions);

        return response()->json(['role' => $role->load('permissions')]);
    }
}
