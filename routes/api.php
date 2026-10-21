<?php

use App\Http\Controllers\Api\NewsApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->controller(NewsApiController::class)->group(function () {
    Route::get('/events', 'events');
    Route::get('/events/{event}', 'event');
    Route::get('/situations', 'situations');
    Route::get('/situations/{situation}', 'situation');
    Route::get('/map', 'map');
    Route::get('/stats', 'stats');
    Route::get('/sources', 'sources');
});
