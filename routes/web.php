<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CitizenReportController;
use App\Http\Controllers\Dashboard\PermissionController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HealthAssessmentController;
use App\Http\Controllers\MaintenanceEventsController;
use App\Http\Controllers\MaintenanceTypesController;
use App\Http\Controllers\MeTimezoneController;
use App\Http\Controllers\NeigborhoodController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\PlantingEventController;
use App\Http\Controllers\PlantingEventTreeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportTypeController;
use App\Http\Controllers\SpeciesController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\UserAddressController;
use App\Http\Controllers\UserEventsController;
use App\Http\Controllers\UserReportsController;
use App\Models\Neighborhood;
use App\Models\ReportType;
use App\Models\Species;
use App\Models\Tag;
use App\Models\Tree;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function (Request $request) {

    $initialTreeId = $request->filled('tree_id') ? (int) $request->input('tree_id') : null;
    $requestedLat = $request->filled('lat') ? (float) $request->input('lat') : null;
    $requestedLon = $request->filled('lon') ? (float) $request->input('lon') : null;

    $mode = $request->string('mode')->toString() ?: 'default';
    $eventId = $request->filled('event_id') ? (int) $request->input('event_id') : null;

    $initialLocation = [
        'lat' => $requestedLat,
        'lon' => $requestedLon,
    ];

    return Inertia::render('Map/MapView', [
        'reportTypes' => ReportType::all(),
        'speciesData' => Species::orderBy('common_name')
            ->get(['id', 'latin_name', 'common_name']),
        'neighborhoodData' => Neighborhood::orderBy('name')->get(['id', 'name', 'city']),
        'tagData' => Tag::all(),
        'treeSex' => Tree::getTreeSexOptions(),
        'healthStatus' => Tree::getHealthStatusOptions(),
        'treeStatus' => Tree::getTreeStatusOptions(),
        'ownerType' => Tree::getOwnerTypeOptions(),
        'initialTreeId' => $initialTreeId,
        'initialLocation' => $initialLocation,
        'mode' => $mode,
        'eventId' => $eventId,
    ]);
})->name('/');

Route::get('/map2', function (Request $request) {

    $initialTreeId = $request->filled('tree_id') ? (int) $request->input('tree_id') : null;
    $requestedLat = $request->filled('lat') ? (float) $request->input('lat') : null;
    $requestedLon = $request->filled('lon') ? (float) $request->input('lon') : null;

    $mode = $request->string('mode')->toString() ?: 'default';
    $eventId = $request->filled('event_id') ? (int) $request->input('event_id') : null;

    $initialLocation = [
        'lat' => $requestedLat,
        'lon' => $requestedLon,
    ];

    return Inertia::render('Map/MapView', [
        'reportTypes' => ReportType::all(),
        'speciesData' => Species::orderBy('common_name')
            ->get(['id', 'latin_name', 'common_name']),
        'neighborhoodData' => Neighborhood::orderBy('name')->get(['id', 'name', 'city']),
        'tagData' => Tag::all(),
        'treeSex' => Tree::getTreeSexOptions(),
        'healthStatus' => Tree::getHealthStatusOptions(),
        'treeStatus' => Tree::getTreeStatusOptions(),
        'ownerType' => Tree::getOwnerTypeOptions(),
        'initialTreeId' => $initialTreeId,
        'initialLocation' => $initialLocation,
        'mode' => $mode,
        'eventId' => $eventId,
    ]);
})->name('/map2');



Route::middleware(['auth'])->post('/me/timezone', [MeTimezoneController::class, 'store'])->name('me.timezone.store');



Route::middleware('auth', '2fa')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['verified'])->name('dashboard');
    Route::get('/dashboard/methodology', [DashboardController::class, 'methodology'])->name('dashboard.methodology');


    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])
        ->name('notifications.read');

    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])
        ->name('notifications.readAll');


    Route::resource('users', UserController::class)->except(['create', 'edit']);
    Route::post('/users/mass-destroy', [UserController::class, 'massDestroy'])->name('users.massDestroy');

    Route::resource('roles', RoleController::class)->except(['create', 'edit']);
    Route::post('/roles/mass-destroy', [RoleController::class, 'massDestroy'])->name('roles.massDestroy');

    Route::resource('permissions', PermissionController::class)->except(['create', 'edit']);
    Route::post('/permissions/store-group', [PermissionController::class, 'storeGroup'])->name('permissions.storeGroup');
    Route::patch('/permissions/{permission}/update-group', [PermissionController::class, 'updateGroup'])->name('permissions.updateGroup');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('/user/reports', UserReportsController::class)->only('index', 'update', 'destroy');
    Route::resource('/user/events', UserEventsController::class)->only('index', 'update', 'destroy');

    Route::patch('/address', [UserAddressController::class, 'update'])->name('address.update');

    Route::resource('trees', TreeController::class)->except(['create', 'edit']);
    Route::post('/trees/mass-destroy', [TreeController::class, 'massDestroy'])->name('trees.massDestroy');

    Route::resource('tags', TagController::class)->except(['create', 'edit']);
    Route::post('/tags/mass-destroy', [TagController::class, 'massDestroy'])->name('tags.massDestroy');

    Route::resource('species', SpeciesController::class)->except(['create', 'edit']);
    Route::post('/species/mass-destroy', [SpeciesController::class, 'massDestroy'])->name('species.massDestroy');

    Route::resource('neighborhoods', NeigborhoodController::class)->except(['create', 'edit']);
    Route::post('/neighborhoods/mass-destroy', [NeigborhoodController::class, 'massDestroy'])->name('neighborhoods.massDestroy');
    Route::post('/neighborhoods/remove-file', [NeigborhoodController::class, 'removeFile'])->name('neighborhoods.removeFile');
    Route::post('/neighborhoods/upload-file', [NeigborhoodController::class, 'uploadFile'])->name('neighborhoods.uploadFile');

    Route::resource('photos', PhotoController::class)->except(['create', 'edit']);
    Route::post('/photos/mass-destroy', [PhotoController::class, 'massDestroy'])->name('photos.massDestroy');

    Route::resource('campaigns', CampaignController::class)->except(['create', 'edit']);
    Route::post('/campaigns/mass-destroy', [CampaignController::class, 'massDestroy'])->name('campaigns.massDestroy');

    Route::resource('plantingEvents', PlantingEventController::class)->except(['create', 'edit']);
    Route::post('/plantingEvents/mass-destroy', [PlantingEventController::class, 'massDestroy'])->name('plantingEvents.massDestroy');

    Route::prefix('plantingEvents/{plantingEvent}')
        ->group(function () {
            Route::post('/trees', [PlantingEventTreeController::class, 'store'])->name('plantingEventTrees.store');
            Route::post('/photos', [PlantingEventController::class, 'storePhoto'])->name('plantingEvents.photos.store');
            Route::post('/complete', [PlantingEventController::class, 'complete'])->name('plantingEvents.complete');
            Route::post('/start', [PlantingEventController::class, 'start'])->name('plantingEvents.start');
        });
    Route::patch('/plantingEventTrees/{plantingEventTree}', [PlantingEventTreeController::class, 'update'])->name('plantingEventTrees.update');
    Route::delete('/plantingEventTrees/{plantingEventTree}', [PlantingEventTreeController::class, 'destroy'])->name('plantingEventTrees.destroy');

    Route::resource('maintenanceEvents', MaintenanceEventsController::class)->except(['create', 'edit']);
    Route::post('/maintenanceEvents/mass-destroy', [MaintenanceEventsController::class, 'massDestroy'])->name('maintenanceEvents.massDestroy');
    Route::post('/maintenanceEvents/{maintenanceEvent}/complete', [MaintenanceEventsController::class, 'complete'])->name('maintenanceEvents.complete');

    Route::resource('maintenanceTypes', MaintenanceTypesController::class)->except(['create', 'edit']);
    Route::post('/maintenanceTypes/mass-destroy', [MaintenanceTypesController::class, 'massDestroy'])->name('maintenanceTypes.massDestroy');

    Route::resource('healthAssessments', HealthAssessmentController::class)->except(['create', 'edit']);
    Route::post('/healthAssessments/mass-destroy', [HealthAssessmentController::class, 'massDestroy'])->name('healthAssessments.massDestroy');

    Route::resource('reportTypes', ReportTypeController::class)->except(['create', 'edit']);
    Route::post('/reportTypes/mass-destroy', [ReportTypeController::class, 'massDestroy'])->name('reportTypes.massDestroy');

    Route::resource('citizenReports', CitizenReportController::class)->except(['create', 'edit']);
    Route::post('/citizenReports/mass-destroy', [HealthAssessmentController::class, 'massDestroy'])->name('citizenReports.massDestroy');

    Route::resource('calendar', CalendarController::class)->except(['create', 'edit', 'show']);
    Route::get('/calendar/events', [CalendarController::class, 'getEvents'])
        ->name('calendar.events');
});

require __DIR__ . '/auth.php';
