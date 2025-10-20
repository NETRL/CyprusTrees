<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Throwable;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(): \Inertia\Response
    {
        return Inertia::render('User/Index', [
            'users' => User::all(),
            'roles' => Role::all()
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = new User();

        $user->name     = $request->input('name');
        $user->email    = $request->input('email');
        $user->password = bcrypt($request->input('password'));

        // has permission to assign roles
        if (auth()->user()->hasPermissionTo('roles.assign')) {
            $user->assignRole($request->input('roles'));
        }

        $user->save();

        $request->session()->flash('message', [
            'type'    => 'success', // error, success, info
            'message' => __('User has been created.')
        ]);

        return redirect()->route('users.index');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->name  = $request->input('name');
        $user->email = $request->input('email');

        // has permission to assign roles
        if (auth()->user()->hasPermissionTo('roles.assign')) {
            $user->syncRoles($request->input('roles'));
        }

        if ($request->input('password')) {
            $user->password = bcrypt($request->input('password'));
        }

        $user->save();

        $request->session()->flash('message', [
            'type'    => 'success', // error, success, info
            'message' => __('User has been updated.')
        ]);

        return redirect()->route('users.index');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === auth()->user()->id) {
            $request->session()->flash('message', [
                'type'    => 'error', // error, success, info
                'message' => __('You cannot delete yourself.')
            ]);
        } else {
            $user->delete();

            $request->session()->flash('message', [
                'type'    => 'success', // error, success, info
                'message' => __('User has been deleted.')
            ]);

        }

        return redirect()->route('users.index');
    }

    /**
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function massDestroy(Request $request): RedirectResponse
    {
        $this->authorize('delete', User::class);

        DB::beginTransaction();

        foreach ($request->input('users') as $user) {
            try {
                if ($user['id'] === auth()->user()->id) {
                    $request->session()->flash('message', [
                        'type'    => 'error', // error, success, info
                        'message' => __('You cannot delete yourself.')
                    ]);
                    return redirect()->route('users.index');
                }
                User::find($user['id'])->delete();
            } catch (Throwable $e) {
                $request->session()->flash('message', [
                    'type'    => 'error', // error, success, info
                    'message' => __('Something went wrong try again later.')
                ]);
                return redirect()->route('users.index');
            }
        }

        DB::commit();

        $request->session()->flash('message', [
            'type'    => 'success', // error, success, info
            'message' => __('Users have been deleted.')
        ]);

        return redirect()->route('users.index');
    }


}
