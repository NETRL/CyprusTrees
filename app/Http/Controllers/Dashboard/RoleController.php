<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Role::class, 'role');
    }

    public function index(): \Inertia\Response
    {
        return Inertia::render('Role/Index', [
            'roles'       => Role::with('permissions')->get(),
            'permissions' => Permission::whereNull('parent_id')->get()
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role       = new Role();
        $role->name = $request->input('name');
        $role->save();

        // has permission to assign permissions
        if (auth()->user()->hasPermissionTo('permissions.assign')) {
            $role->syncPermissions($request->input('permissions'));
        }

        $request->session()->flash('message', [
            'type'    => 'success', // error, success, info
            'message' => __('Role has been created.')
        ]);

        return redirect()->route('roles.index');
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $role->name = $request->input('name');

        $role->save();

        // has permission to assign permissions
        if (auth()->user()->hasPermissionTo('permissions.assign')) {
            $role->syncPermissions($request->input('permissions'));
        }

        $request->session()->flash('message', [
            'type'    => 'success', // error, success, info
            'message' => __('Role has been updated.')
        ]);

        return redirect()->route('roles.index');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        $role->delete();

        $request->session()->flash('message', [
            'type'    => 'success', // error, success, info
            'message' => __('Role has been deleted.')
        ]);

        return redirect()->route('roles.index');
    }

    public function massDestroy(Request $request): RedirectResponse
    {
        $this->authorize('delete', Role::class);

        Role::whereIn('id', collect($request->input('roles'))->pluck('id'))->delete();

        $request->session()->flash('message', [
            'type'    => 'success', // error, success, info
            'message' => __('Role has been deleted.')
        ]);

        return redirect()->route('roles.index');
    }
}
