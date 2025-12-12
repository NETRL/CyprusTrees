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
                'name'        => 'Tree Registry',
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
                'name'        => 'Species Catalogue',
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

        $events = $this->createOrUpdateNavlink(
            ['key' => 'events'],
            [
                'name'        => 'Events',
                'permissions' => 'events.view',
                'icon'        => 'EventIcon',
            ]
        );

        $this->createOrUpdateNavlink(
            ['key' => 'events-planting'],
            [
                'name'        => 'Planting Events',
                'permissions' => 'plantingEvents.view',
                'icon'        => 'PlantIcon',
                'parent_id'   => $events->id,
                'route_name'  => 'plantingEvents.index'
            ]
        );

        $this->createOrUpdateNavlink(
            ['key' => 'events-campaign'],
            [
                'name'        => 'Campaigns',
                'permissions' => 'campaigns.view',
                'icon'        => 'CampaignIcon',
                'parent_id'   => $events->id,
                'route_name'  => 'campaigns.index'
            ]
        );

        $maintenance = $this->createOrUpdateNavlink(
            ['key' => 'maintenance'],
            [
                'name'        => 'Maintenance',
                'permissions' => 'maintenance.view',
                'icon'        => 'pi pi-wrench',
            ]
        );

        $this->createOrUpdateNavlink(
            ['key' => 'maintenance-events'],
            [
                'name'        => 'Maintenance Events',
                'permissions' => 'maintenanceEvents.view',
                'icon'        => 'MaintenanceEventIcon',
                'parent_id'   => $maintenance->id,
                'route_name'  => 'maintenanceEvents.index'
            ]
        );

        $this->createOrUpdateNavlink(
            ['key' => 'maintenance-types'],
            [
                'name'        => 'Maintenance Types',
                'permissions' => 'maintenanceTypes.view',
                'icon'        => 'MaintenanceTypeIcon',
                'parent_id'   => $maintenance->id,
                'route_name'  => 'maintenanceTypes.index'
            ]
        );

        $health = $this->createOrUpdateNavlink(
            ['key' => 'health'],
            [
                'name'        => 'Health & Assessments',
                'permissions' => 'health.view',
                'icon'        => 'HealthIcon',
            ]
        );

        $this->createOrUpdateNavlink(
            ['key' => 'health-healthAssessments'],
            [
                'name'        => 'Health Assessments',
                'permissions' => 'healthAssessments.view',
                'icon'        => 'HealthAssessmentIcon',
                'parent_id'   => $health->id,
                'route_name'  => 'healthAssessments.index'
            ]
        );

        $calendar = $this->createOrUpdateNavlink(
            ['key' => 'calendar'],
            [
                'name'        => 'Calendar',
                'permissions' => 'calendar.view',
                'icon'        => 'pi pi-calendar',
                'route_name'  => 'calendar.index'
            ]
        );

        $reports = $this->createOrUpdateNavlink(
            ['key' => 'reports'],
            [
                'name'        => 'Citizen Reports',
                'permissions' => 'reports.view',
                'icon'        => 'ReportsIcon',
            ]
        );
        $this->createOrUpdateNavlink(
            ['key' => 'reports-citizenReports'],
            [
                'name'        => 'Citizen Reports',
                'permissions' => 'citizenReports.view',
                'icon'        => 'CitizenReportIcon',
                'parent_id'   => $reports->id,
                'route_name'  => 'citizenReports.index'
            ]
        );
        $this->createOrUpdateNavlink(
            ['key' => 'reports-reportTypes'],
            [
                'name'        => 'Report Types',
                'permissions' => 'reportTypes.view',
                'icon'        => 'ReportTypeIcon',
                'parent_id'   => $reports->id,
                'route_name'  => 'reportTypes.index'
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
