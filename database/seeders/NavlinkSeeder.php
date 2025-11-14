<?php

namespace Database\Seeders;

use App\Models\Navlink;
use Illuminate\Database\Seeder;

class NavlinkSeeder extends Seeder
{
    public function run(): void
    {
        $this->createOrUpdateNavlink(
            ['key' => 'map'],
            [
                'name'       => 'Map',
                'icon'       => 'pi pi-map',
                'route_name' => '/',
            ]
        );

        $user_management = $this->createOrUpdateNavlink(
            ['key' => 'user_management'],
            [
                'name'        => 'User Management',
                'permissions' => 'users.view|permissions.view|roles.view',
                'icon'        => 'pi pi-users',
            ]
        );

        $this->createOrUpdateNavlink(
            ['key' => 'user_management-users'],
            [
                'name'        => 'Users',
                'icon'        => 'pi pi-user',
                'permissions' => 'users.view',
                'parent_id'   => $user_management->id,
                'route_name'  => 'users.index'
            ]
        );

        $this->createOrUpdateNavlink(
            ['key' => 'user_management-roles'],
            [
                'name'        => 'Roles',
                'icon'        => 'pi pi-user-plus',
                'permissions' => 'roles.view',
                'parent_id'   => $user_management->id,
                'route_name'  => 'roles.index',
            ]
        );

        $this->createOrUpdateNavlink(
            ['key' => 'user_management-permissions'],
            [
                'name'        => 'Permissions',
                'icon'        => 'pi pi-user-plus',
                'permissions' => 'permissions.view',
                'parent_id'   => $user_management->id,
                'route_name'  => 'permissions.index'
            ]
        );

        $tree_management = $this->createOrUpdateNavlink(
            ['key' => 'tree_management'],
            [
                'name'        => 'Tree Management',
                'permissions' => 'trees.view',
                'icon'        => 'TreeIcon',
            ]
        );

        $this->createOrUpdateNavlink(
            ['key' => 'tree_management-trees'],
            [
                'name'        => 'Trees',
                'permissions' => 'trees.view',
                'icon'        => 'TreeIcon',
                'parent_id'   => $tree_management->id,
                'route_name'  => 'trees.index'


            ]
        );

        $this->createOrUpdateNavlink(
            ['key' => 'tree_management-tags'],
            [
                'name'        => 'Tree Tags',
                'permissions' => 'tags.view',
                'icon'        => 'pi pi-tags',
                'parent_id'   => $tree_management->id,
                'route_name'  => 'tags.index'


            ]
        );
        $this->createOrUpdateNavlink(
            ['key' => 'tree_management-species'],
            [
                'name'        => 'Species',
                'permissions' => 'species.view',
                'icon'        => 'SpeciesIcon',
                'parent_id'   => $tree_management->id,
                'route_name'  => 'species.index'


            ]
        );

        $this->createOrUpdateNavlink(
            ['key' => 'tree_management-photos'],
            [
                'name'        => 'Photos',
                'permissions' => 'photos.view',
                'icon'        => 'pi pi-images',
                'parent_id'   => $tree_management->id,
                'route_name'  => 'photos.index'


            ]
        );

        $this->createOrUpdateNavlink(
            ['key' => 'neighborhood_management'],
            [
                'name'        => 'Neighborhoods',
                'permissions' => 'neighborhoods.view',
                'icon'        => 'NeigborhoodIcon',
                'route_name'  => 'neighborhoods.index'


            ]
        );

        // $this->createOrUpdateNavlink([
        //     'name'        => 'Log Viewer',
        //     'icon'        => 'pi pi-book',
        //     'external'    => true,
        //     'permissions' => 'logs.view',
        //     'route_name'  => 'log-viewer.index'
        // ]);
    }


    protected function createOrUpdateNavlink(array $match, array $values): Navlink
    {
        $values = array_merge([
            'enabled' => true, // default if not specified
            'parent_id' => null,
            'route_name'  => null,
        ], $values);

        return Navlink::updateOrCreate($match, $values);
    }
}
