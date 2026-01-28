<?php

use App\Http\Controllers\Api\NeighborhoodGeoController;
use App\Http\Controllers\Api\TreesGeoController;
use App\Http\Controllers\GeocodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/test', function () {
    return 'test ok';
});

Route::get('/neighborhoods', [NeighborhoodGeoController::class, 'index']);
Route::get('/neighborhoods/{id}/stats', [NeighborhoodGeoController::class, 'showStats']);

Route::get('/trees', [TreesGeoController::class, 'index']);
Route::get('/trees/{treeId}', [TreesGeoController::class, 'show']);

Route::middleware('throttle:60,1')->get('/geocode/reverse', [GeocodeController::class, 'reverse'])->name('api.geocode.reverse');
Route::get('/geocode/search', [GeocodeController::class, 'search'])->middleware('throttle:30,1')->name('api.geocode.search');
