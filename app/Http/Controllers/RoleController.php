<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('role_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $roles = Role::with(['permissions:id,name'])->get();              
        return view('admin.role.index', compact('roles'));
    }

    public function create()
    {
        abort_if(Gate::denies('role_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $permissions = Permission::all();
        return view('admin.role.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('role_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'name' => 'bail|required|unique:roles|max:255',
        ]);
        $role = Role::create(['name' => $request->name, 'guard_name' => $request->guard_name ?? 'web']);
        if ($request->permissions != null) {
            $per = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($per);
        }
        return redirect()->route('roles.index')->withStatus(__('Role is added successfully.'));
    }

    public function show(Role $role)
    {
        abort_if(Gate::denies('role_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return view('admin.role.show', compact('role'));
    }

    public function edit(Role $role)
    {
        if ($role->name == 'admin') {
            return redirect()->route('roles.index')->withStatus(__('You can not edit admin.'));
        }
        abort_if(Gate::denies('role_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $permissions = Permission::all();
        return view('admin.role.edit', compact('permissions', 'role'));
    }

    public function update(Request $request, Role $role)
    {
        abort_if(Gate::denies('role_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if ($role->name == 'admin') {
            return redirect()->route('roles.index')->withStatus(__('You can not edit admin.'));
        }

        $request->validate([
            'name' => 'bail|required|unique:roles,name,' . $role->id . ',id',
        ]);
        $role->update(['name' => $request->name]);
        if ($request->permissions != null) {
            $per = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($per);
        } else {
            $role->syncPermissions([]);
        }
        return redirect()->route('roles.index')->withStatus(__('Role is updated successfully.'));
    }

    public function destroy(Role $role)
    {
       
    }
}
