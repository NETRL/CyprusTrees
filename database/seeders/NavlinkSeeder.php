<?php

namespace Database\Seeders;

use App\Models\Navlink;
use Illuminate\Database\Seeder;

class NavlinkSeeder extends Seeder
{
    public function run(): void
    {
        Navlink::create([
            'name'       => 'Map',
            'icon'       => 'pi pi-map',
            'route_name' => '/'
        ]);

        $user_management = Navlink::create([
            'name'        => 'User Management',
            'permissions' => 'users.view|permissions.view|roles.view', // or
            'icon'        => 'pi pi-users',
        ]);

        Navlink::create([
            'name'        => 'Users',
            'icon'        => 'pi pi-user',
            'permissions' => 'users.view',
            'parent_id'   => $user_management->id,
            'route_name'  => 'users.index'
        ]);

        Navlink::create([
            'name'        => 'Roles',
            'icon'        => 'pi pi-user-plus',
            'permissions' => 'roles.view',
            'parent_id'   => $user_management->id,
            'route_name'  => 'roles.index'
        ]);

        Navlink::create([
            'name'        => 'Permissions',
            'icon'        => 'pi pi-user-plus',
            'permissions' => 'permissions.view',
            'parent_id'   => $user_management->id,
            'route_name'  => 'permissions.index'
        ]);

        // Navlink::create([
        //     'name'        => 'Log Viewer',
        //     'icon'        => 'pi pi-book',
        //     'external'    => true,
        //     'permissions' => 'logs.view',
        //     'route_name'  => 'log-viewer.index'
        // ]);
    }
}
