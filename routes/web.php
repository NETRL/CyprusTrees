<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\Dashboard\PermissionController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\MaintenanceEventsController;
use App\Http\Controllers\MaintenanceTypesController;
use App\Http\Controllers\NeigborhoodController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\PlantingEventController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SpeciesController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\UserAddressController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('/');



Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth', '2fa')->group(function () {


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

    Route::patch('/address', [UserAddressController::class, 'update'])->name('address.update');

    Route::resource('trees', TreeController::class);
    Route::post('/trees/mass-destroy', [TreeController::class, 'massDestroy'])->name('trees.massDestroy');

    Route::resource('tags', TagController::class);
    Route::post('/tags/mass-destroy', [TagController::class, 'massDestroy'])->name('tags.massDestroy');

    Route::resource('species', SpeciesController::class);
    Route::post('/species/mass-destroy', [SpeciesController::class, 'massDestroy'])->name('species.massDestroy');

    Route::resource('neighborhoods', NeigborhoodController::class);
    Route::post('/neighborhoods/mass-destroy', [NeigborhoodController::class, 'massDestroy'])->name('neighborhoods.massDestroy');

    Route::resource('photos', PhotoController::class);
    Route::post('/photos/mass-destroy', [PhotoController::class, 'massDestroy'])->name('photos.massDestroy');

    Route::resource('campaigns', CampaignController::class);
    Route::post('/campaigns/mass-destroy', [CampaignController::class, 'massDestroy'])->name('campaigns.massDestroy');

    Route::resource('plantingEvents', PlantingEventController::class);
    Route::post('/plantingEvents/mass-destroy', [PlantingEventController::class, 'massDestroy'])->name('plantingEvents.massDestroy');
   
    Route::resource('maintenanceEvents', MaintenanceEventsController::class);
    Route::post('/maintenanceEvents/mass-destroy', [MaintenanceEventsController::class, 'massDestroy'])->name('maintenanceEvents.massDestroy');
    
    Route::resource('maintenanceTypes', MaintenanceTypesController::class);
    Route::post('/maintenanceTypes/mass-destroy', [MaintenanceTypesController::class, 'massDestroy'])->name('maintenanceTypes.massDestroy');

});

require __DIR__ . '/auth.php';
