<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\StoreGroupRequest;
use App\Http\Requests\Permission\UpdateGroupRequest;
use App\Http\Requests\Permission\StorePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Permission::class, 'permission');
    }

    public function index(): \Inertia\Response
    {
        return Inertia::render('Permission/Index', [
            'permissions' => Permission::whereNull('parent_id')->get()
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function storeGroup(StoreGroupRequest $request): RedirectResponse
    {
        $this->authorize('create', Permission::class);

        $permission             = new Permission();
        $permission->name       = $request->input('name');
        $permission->group_name = $request->input('name');
        $permission->guard_name = $request->input('guard_name');

        $permission->save();

        if ($request->input('is_crud')) {
            $permission->makeCRUDPermissions($request);
        }

        $request->session()->flash('message', [
            'type'    => 'success', // error, success, info
            'message' => __('Group has been created.')
        ]);

        return redirect()->route('permissions.index');
    }

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $permission = new Permission();
        $group      = Permission::findOrFail($request->input('group'));

        $permission->name        = $group->name . '.' . $request->input('type');
        $permission->group_name  = $group->group_name;
        $permission->guard_name  = $group->guard_name;
        $permission->parent_id   = $group->id;
        $permission->description = $request->input('description');

        $permission->save();

        $request->session()->flash('message', [
            'type'    => 'success', // error, success, info
            'message' => __('Permission has been created.')
        ]);

        return redirect()->route('permissions.index');
    }

    /**
     * @throws AuthorizationException
     */
    public function updateGroup(UpdateGroupRequest $request, Permission $permission): RedirectResponse
    {
        $this->authorize('update', [Permission::class, $permission]);

        $permission->name       = $request->input('name');
        $permission->group_name = $request->input('name');
        $permission->guard_name = $request->input('guard_name');
        $permission->save();

        $permission->children->map(function ($child) use ($request) {
            $child->name       = $request->input('name') . '.' . $child->type;
            $child->group_name = $request->input('name');
            $child->guard_name = $request->input('guard_name');
            $child->save();
        });

        $request->session()->flash('message', [
            'type'    => 'success', // error, success, info
            'message' => __('Group has been updated.')
        ]);

        return redirect()->route('permissions.index');
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        $permission->name        = $permission->group_name . '.' . $request->input('type');
        $permission->description = $request->input('description');

        $permission->save();

        $request->session()->flash('message', [
            'type'    => 'success', // error, success, info
            'message' => __('Permission has been created.')
        ]);

        return redirect()->route('permissions.index');
    }

    public function destroy(Request $request, Permission $permission): RedirectResponse
    {
        $permission->delete();

        $request->session()->flash('message', [
            'type'    => 'success', // error, success, info
            'message' => __('Group/Permission has been deleted.')
        ]);

        return redirect()->route('permissions.index');
    }
}
