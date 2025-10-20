<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        $admin = Role::findOrCreate('admin');

        // user permissions
        $users = Permission::firstOrCreate([
            'name'       => 'users',
            'group_name' => 'users'
        ]);

        Permission::firstOrCreate([
            'name'        => 'users.view',
            'group_name'  => 'users',
            'description' => 'Can view users.',
            'parent_id'   => $users->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'users.edit',
            'group_name'  => 'users',
            'description' => 'Can edit existing users.',
            'parent_id'   => $users->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'users.create',
            'group_name'  => 'users',
            'description' => 'Can create new users.',
            'parent_id'   => $users->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'users.delete',
            'group_name'  => 'users',
            'description' => 'Can delete users.',
            'parent_id'   => $users->id
        ]);

        // roles permissions
        $roles = Permission::firstOrCreate([
            'name'       => 'roles',
            'group_name' => 'roles'
        ]);

        Permission::firstOrCreate([
            'name'        => 'roles.view',
            'group_name'  => 'roles',
            'description' => 'Can view roles.',
            'parent_id'   => $roles->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'roles.edit',
            'group_name'  => 'roles',
            'description' => 'Can edit existing roles.',
            'parent_id'   => $roles->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'roles.create',
            'group_name'  => 'roles',
            'description' => 'Can create new roles.',
            'parent_id'   => $roles->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'roles.delete',
            'group_name'  => 'roles',
            'description' => 'Can delete roles.',
            'parent_id'   => $roles->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'roles.assign',
            'group_name'  => 'roles',
            'description' => 'Can assign roles to models (typically to users).',
            'parent_id'   => $roles->id
        ]);

        // permissions permissions
        $permissions = Permission::firstOrCreate([
            'name'       => 'permissions',
            'group_name' => 'permissions'
        ]);

        Permission::firstOrCreate([
            'name'        => 'permissions.view',
            'group_name'  => 'permissions',
            'description' => 'Can view permissions.',
            'parent_id'   => $permissions->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'permissions.edit',
            'group_name'  => 'permissions',
            'description' => 'Can edit existing permissions.',
            'parent_id'   => $permissions->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'permissions.create',
            'group_name'  => 'permissions',
            'description' => 'Can create new permissions.',
            'parent_id'   => $permissions->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'permissions.delete',
            'group_name'  => 'permissions',
            'description' => 'Can delete permissions.',
            'parent_id'   => $permissions->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'permissions.assign',
            'group_name'  => 'permissions',
            'description' => 'Can assign permissions to models (typically to roles).',
            'parent_id'   => $permissions->id
        ]);

        // // log permissions
        // $logs = Permission::firstOrCreate([
        //     'name'       => 'logs',
        //     'group_name' => 'logs'
        // ]);

        // Permission::firstOrCreate([
        //     'name'        => 'logs.view',
        //     'group_name'  => 'logs',
        //     'description' => 'Can view websites logs.',
        //     'parent_id'   => $logs->id
        // ]);

        $admin->syncPermissions(Permission::where('guard_name', $guard)->pluck('name')->all());


        // assign permissions to admin
        $admin->givePermissionTo([$users->children]);
        $admin->givePermissionTo([$permissions->children]);
        $admin->givePermissionTo([$roles->children]);


        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
