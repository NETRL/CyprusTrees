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
        $admin = Role::findOrCreate('admin', $guard);
        // Role::findOrCreate('staff', $guard);
        // Role::findOrCreate('citizen', $guard);

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

        // trees permissions
        $trees = Permission::firstOrCreate([
            'name'       => 'trees',
            'group_name' => 'trees'
        ]);

        Permission::firstOrCreate([
            'name'        => 'trees.view',
            'group_name'  => 'trees',
            'description' => 'Can view trees.',
            'parent_id'   => $trees->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'trees.edit',
            'group_name'  => 'trees',
            'description' => 'Can edit existing trees.',
            'parent_id'   => $trees->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'trees.create',
            'group_name'  => 'trees',
            'description' => 'Can create new trees.',
            'parent_id'   => $trees->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'trees.delete',
            'group_name'  => 'trees',
            'description' => 'Can delete trees.',
            'parent_id'   => $trees->id
        ]);

        // treeTags permissions
        $treeTags = Permission::firstOrCreate([
            'name'       => 'treeTags',
            'group_name' => 'treeTags'
        ]);

        Permission::firstOrCreate([
            'name'        => 'treeTags.view',
            'group_name'  => 'treeTags',
            'description' => 'Can view treeTags.',
            'parent_id'   => $treeTags->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'treeTags.edit',
            'group_name'  => 'treeTags',
            'description' => 'Can edit existing treeTags.',
            'parent_id'   => $treeTags->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'treeTags.create',
            'group_name'  => 'treeTags',
            'description' => 'Can create new treeTags.',
            'parent_id'   => $treeTags->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'treeTags.delete',
            'group_name'  => 'treeTags',
            'description' => 'Can delete treeTags.',
            'parent_id'   => $treeTags->id
        ]);

        // tags permissions
        $tags = Permission::firstOrCreate([
            'name'       => 'tags',
            'group_name' => 'tags'
        ]);

        Permission::firstOrCreate([
            'name'        => 'tags.view',
            'group_name'  => 'tags',
            'description' => 'Can view tags.',
            'parent_id'   => $tags->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'tags.edit',
            'group_name'  => 'tags',
            'description' => 'Can edit existing tags.',
            'parent_id'   => $tags->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'tags.create',
            'group_name'  => 'tags',
            'description' => 'Can create new tags.',
            'parent_id'   => $tags->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'tags.delete',
            'group_name'  => 'tags',
            'description' => 'Can delete tags.',
            'parent_id'   => $tags->id
        ]);

        // species permissions
        $species = Permission::firstOrCreate([
            'name'       => 'species',
            'group_name' => 'species'
        ]);

        Permission::firstOrCreate([
            'name'        => 'species.view',
            'group_name'  => 'species',
            'description' => 'Can view species.',
            'parent_id'   => $species->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'species.edit',
            'group_name'  => 'species',
            'description' => 'Can edit existing species.',
            'parent_id'   => $species->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'species.create',
            'group_name'  => 'species',
            'description' => 'Can create new species.',
            'parent_id'   => $species->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'species.delete',
            'group_name'  => 'species',
            'description' => 'Can delete species.',
            'parent_id'   => $species->id
        ]);

        // photos permissions
        $photos = Permission::firstOrCreate([
            'name'       => 'photos',
            'group_name' => 'photos'
        ]);

        Permission::firstOrCreate([
            'name'        => 'photos.view',
            'group_name'  => 'photos',
            'description' => 'Can view photos.',
            'parent_id'   => $photos->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'photos.edit',
            'group_name'  => 'photos',
            'description' => 'Can edit existing photos.',
            'parent_id'   => $photos->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'photos.create',
            'group_name'  => 'photos',
            'description' => 'Can create new photos.',
            'parent_id'   => $photos->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'photos.delete',
            'group_name'  => 'photos',
            'description' => 'Can delete photos.',
            'parent_id'   => $photos->id
        ]);

        // neighborhoods permissions
        $neighborhoods = Permission::firstOrCreate([
            'name'       => 'neighborhoods',
            'group_name' => 'neighborhoods'
        ]);

        Permission::firstOrCreate([
            'name'        => 'neighborhoods.view',
            'group_name'  => 'neighborhoods',
            'description' => 'Can view neighborhoods.',
            'parent_id'   => $neighborhoods->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'neighborhoods.edit',
            'group_name'  => 'neighborhoods',
            'description' => 'Can edit existing neighborhoods.',
            'parent_id'   => $neighborhoods->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'neighborhoods.create',
            'group_name'  => 'neighborhoods',
            'description' => 'Can create new neighborhoods.',
            'parent_id'   => $neighborhoods->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'neighborhoods.delete',
            'group_name'  => 'neighborhoods',
            'description' => 'Can delete neighborhoods.',
            'parent_id'   => $neighborhoods->id
        ]);

        // events permissions
        $events = Permission::firstOrCreate([
            'name'       => 'events',
            'group_name' => 'events'
        ]);

        Permission::firstOrCreate([
            'name'        => 'events.view',
            'group_name'  => 'events',
            'description' => 'Can view events.',
            'parent_id'   => $events->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'events.edit',
            'group_name'  => 'events',
            'description' => 'Can edit existing events.',
            'parent_id'   => $events->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'events.create',
            'group_name'  => 'events',
            'description' => 'Can create new events.',
            'parent_id'   => $events->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'events.delete',
            'group_name'  => 'events',
            'description' => 'Can delete events.',
            'parent_id'   => $events->id
        ]);

        // plantingEvents permissions
        $plantingEvents = Permission::firstOrCreate([
            'name'       => 'plantingEvents',
            'group_name' => 'plantingEvents'
        ]);

        Permission::firstOrCreate([
            'name'        => 'plantingEvents.view',
            'group_name'  => 'plantingEvents',
            'description' => 'Can view plantingEvents.',
            'parent_id'   => $plantingEvents->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'plantingEvents.edit',
            'group_name'  => 'plantingEvents',
            'description' => 'Can edit existing plantingEvents.',
            'parent_id'   => $plantingEvents->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'plantingEvents.create',
            'group_name'  => 'plantingEvents',
            'description' => 'Can create new plantingEvents.',
            'parent_id'   => $plantingEvents->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'plantingEvents.delete',
            'group_name'  => 'plantingEvents',
            'description' => 'Can delete plantingEvents.',
            'parent_id'   => $plantingEvents->id
        ]);

        // campaigns permissions
        $campaigns = Permission::firstOrCreate([
            'name'       => 'campaigns',
            'group_name' => 'campaigns'
        ]);

        Permission::firstOrCreate([
            'name'        => 'campaigns.view',
            'group_name'  => 'campaigns',
            'description' => 'Can view campaigns.',
            'parent_id'   => $campaigns->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'campaigns.edit',
            'group_name'  => 'campaigns',
            'description' => 'Can edit existing campaigns.',
            'parent_id'   => $campaigns->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'campaigns.create',
            'group_name'  => 'campaigns',
            'description' => 'Can create new campaigns.',
            'parent_id'   => $campaigns->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'campaigns.delete',
            'group_name'  => 'campaigns',
            'description' => 'Can delete campaigns.',
            'parent_id'   => $campaigns->id
        ]);

        // maintenance permissions
        $maintenance = Permission::firstOrCreate([
            'name'       => 'maintenance',
            'group_name' => 'maintenance'
        ]);

        Permission::firstOrCreate([
            'name'        => 'maintenance.view',
            'group_name'  => 'maintenance',
            'description' => 'Can view maintenance.',
            'parent_id'   => $maintenance->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'maintenance.edit',
            'group_name'  => 'maintenance',
            'description' => 'Can edit existing maintenance.',
            'parent_id'   => $maintenance->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'maintenance.create',
            'group_name'  => 'maintenance',
            'description' => 'Can create new maintenance.',
            'parent_id'   => $maintenance->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'maintenance.delete',
            'group_name'  => 'maintenance',
            'description' => 'Can delete maintenance.',
            'parent_id'   => $maintenance->id
        ]);


        // maintenanceEvents permissions
        $maintenanceEvents = Permission::firstOrCreate([
            'name'       => 'maintenanceEvents',
            'group_name' => 'maintenanceEvents'
        ]);

        Permission::firstOrCreate([
            'name'        => 'maintenanceEvents.view',
            'group_name'  => 'maintenanceEvents',
            'description' => 'Can view maintenanceEvents.',
            'parent_id'   => $maintenanceEvents->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'maintenanceEvents.edit',
            'group_name'  => 'maintenanceEvents',
            'description' => 'Can edit existing maintenanceEvents.',
            'parent_id'   => $maintenanceEvents->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'maintenanceEvents.create',
            'group_name'  => 'maintenanceEvents',
            'description' => 'Can create new maintenanceEvents.',
            'parent_id'   => $maintenanceEvents->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'maintenanceEvents.delete',
            'group_name'  => 'maintenanceEvents',
            'description' => 'Can delete maintenanceEvents.',
            'parent_id'   => $maintenanceEvents->id
        ]);

        // maintenanceTypes permissions
        $maintenanceTypes = Permission::firstOrCreate([
            'name'       => 'maintenanceTypes',
            'group_name' => 'maintenanceTypes'
        ]);

        Permission::firstOrCreate([
            'name'        => 'maintenanceTypes.view',
            'group_name'  => 'maintenanceTypes',
            'description' => 'Can view maintenanceTypes.',
            'parent_id'   => $maintenanceTypes->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'maintenanceTypes.edit',
            'group_name'  => 'maintenanceTypes',
            'description' => 'Can edit existing maintenanceTypes.',
            'parent_id'   => $maintenanceTypes->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'maintenanceTypes.create',
            'group_name'  => 'maintenanceTypes',
            'description' => 'Can create new maintenanceTypes.',
            'parent_id'   => $maintenanceTypes->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'maintenanceTypes.delete',
            'group_name'  => 'maintenanceTypes',
            'description' => 'Can delete maintenanceTypes.',
            'parent_id'   => $maintenanceTypes->id
        ]);

        // calendar permissions
        $calendar = Permission::firstOrCreate([
            'name'       => 'calendar',
            'group_name' => 'calendar'
        ]);

        Permission::firstOrCreate([
            'name'        => 'calendar.view',
            'group_name'  => 'calendar',
            'description' => 'Can view calendar.',
            'parent_id'   => $calendar->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'calendar.edit',
            'group_name'  => 'calendar',
            'description' => 'Can edit existing calendar.',
            'parent_id'   => $calendar->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'calendar.create',
            'group_name'  => 'calendar',
            'description' => 'Can create new calendar.',
            'parent_id'   => $calendar->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'calendar.delete',
            'group_name'  => 'calendar',
            'description' => 'Can delete calendar.',
            'parent_id'   => $calendar->id
        ]);

        // health permissions
        $health = Permission::firstOrCreate([
            'name'       => 'health',
            'group_name' => 'health'
        ]);

        Permission::firstOrCreate([
            'name'        => 'health.view',
            'group_name'  => 'health',
            'description' => 'Can view health.',
            'parent_id'   => $health->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'health.edit',
            'group_name'  => 'health',
            'description' => 'Can edit existing health.',
            'parent_id'   => $health->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'health.create',
            'group_name'  => 'health',
            'description' => 'Can create new health.',
            'parent_id'   => $health->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'health.delete',
            'group_name'  => 'health',
            'description' => 'Can delete health.',
            'parent_id'   => $health->id
        ]);

        // healthAssessments permissions
        $healthAssessments = Permission::firstOrCreate([
            'name'       => 'healthAssessments',
            'group_name' => 'healthAssessments'
        ]);

        Permission::firstOrCreate([
            'name'        => 'healthAssessments.view',
            'group_name'  => 'healthAssessments',
            'description' => 'Can view healthAssessments.',
            'parent_id'   => $healthAssessments->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'healthAssessments.edit',
            'group_name'  => 'healthAssessments',
            'description' => 'Can edit existing healthAssessments.',
            'parent_id'   => $healthAssessments->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'healthAssessments.create',
            'group_name'  => 'healthAssessments',
            'description' => 'Can create new healthAssessments.',
            'parent_id'   => $healthAssessments->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'healthAssessments.delete',
            'group_name'  => 'healthAssessments',
            'description' => 'Can delete healthAssessments.',
            'parent_id'   => $healthAssessments->id
        ]);

        // reports permissions
        $reports = Permission::firstOrCreate([
            'name'       => 'reports',
            'group_name' => 'reports'
        ]);

        Permission::firstOrCreate([
            'name'        => 'reports.view',
            'group_name'  => 'reports',
            'description' => 'Can view reports.',
            'parent_id'   => $reports->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'reports.edit',
            'group_name'  => 'reports',
            'description' => 'Can edit existing reports.',
            'parent_id'   => $reports->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'reports.create',
            'group_name'  => 'reports',
            'description' => 'Can create new reports.',
            'parent_id'   => $reports->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'reports.delete',
            'group_name'  => 'reports',
            'description' => 'Can delete reports.',
            'parent_id'   => $reports->id
        ]);

        // citizenReports permissions
        $citizenReports = Permission::firstOrCreate([
            'name'       => 'citizenReports',
            'group_name' => 'citizenReports'
        ]);

        Permission::firstOrCreate([
            'name'        => 'citizenReports.view',
            'group_name'  => 'citizenReports',
            'description' => 'Can view citizenReports.',
            'parent_id'   => $citizenReports->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'citizenReports.edit',
            'group_name'  => 'citizenReports',
            'description' => 'Can edit existing citizenReports.',
            'parent_id'   => $citizenReports->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'citizenReports.create',
            'group_name'  => 'citizenReports',
            'description' => 'Can create new citizenReports.',
            'parent_id'   => $citizenReports->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'citizenReports.delete',
            'group_name'  => 'citizenReports',
            'description' => 'Can delete citizenReports.',
            'parent_id'   => $citizenReports->id
        ]);

        // reportTypes permissions
        $reportTypes = Permission::firstOrCreate([
            'name'       => 'reportTypes',
            'group_name' => 'reportTypes'
        ]);

        Permission::firstOrCreate([
            'name'        => 'reportTypes.view',
            'group_name'  => 'reportTypes',
            'description' => 'Can view reportTypes.',
            'parent_id'   => $reportTypes->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'reportTypes.edit',
            'group_name'  => 'reportTypes',
            'description' => 'Can edit existing reportTypes.',
            'parent_id'   => $reportTypes->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'reportTypes.create',
            'group_name'  => 'reportTypes',
            'description' => 'Can create new reportTypes.',
            'parent_id'   => $reportTypes->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'reportTypes.delete',
            'group_name'  => 'reportTypes',
            'description' => 'Can delete reportTypes.',
            'parent_id'   => $reportTypes->id
        ]);
        
        $gisLayers = Permission::firstOrCreate([
            'name'       => 'gisLayers',
            'group_name' => 'gisLayers'
        ]);

        Permission::firstOrCreate([
            'name'        => 'gisLayers.view',
            'group_name'  => 'gisLayers',
            'description' => 'Can view gisLayers.',
            'parent_id'   => $gisLayers->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'gisLayers.edit',
            'group_name'  => 'gisLayers',
            'description' => 'Can edit existing gisLayers.',
            'parent_id'   => $gisLayers->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'gisLayers.create',
            'group_name'  => 'gisLayers',
            'description' => 'Can create new gisLayers.',
            'parent_id'   => $gisLayers->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'gisLayers.delete',
            'group_name'  => 'gisLayers',
            'description' => 'Can delete gisLayers.',
            'parent_id'   => $gisLayers->id
        ]);
        Permission::firstOrCreate([
            'name'        => 'gisLayers.import',
            'group_name'  => 'gisLayers',
            'description' => 'Can import gisLayers.',
            'parent_id'   => $gisLayers->id
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
        $admin->givePermissionTo([$trees->children]);
        $admin->givePermissionTo([$treeTags->children]);
        $admin->givePermissionTo([$tags->children]);
        $admin->givePermissionTo([$species->children]);
        $admin->givePermissionTo([$photos->children]);
        $admin->givePermissionTo([$neighborhoods->children]);
        $admin->givePermissionTo([$events->children]);
        $admin->givePermissionTo([$plantingEvents->children]);
        $admin->givePermissionTo([$campaigns->children]);
        $admin->givePermissionTo([$maintenance->children]);
        $admin->givePermissionTo([$maintenanceEvents->children]);
        $admin->givePermissionTo([$maintenanceTypes->children]);
        $admin->givePermissionTo([$calendar->children]);
        $admin->givePermissionTo([$administration->children]);
        $admin->givePermissionTo([$gisLayers->children]);


        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
