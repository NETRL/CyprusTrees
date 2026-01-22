<?php

use App\Http\Controllers\Api\NeighborhoodGeoController;
use App\Http\Controllers\Api\TreesGeoController;
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