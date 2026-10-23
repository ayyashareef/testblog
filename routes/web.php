<?php

use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

Route::controller(NewsController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/map/{hour}', 'mapHour')->where('hour', '\d{4}-\d{2}-\d{2}T\d{2}')->name('map.hour');
    Route::get('/events', 'events')->name('events.index');
    Route::get('/events/{event}', 'event')->name('events.show');
    Route::get('/situations', 'situations')->name('situations.index');
    Route::get('/situations/{situation}', 'situation')->name('situations.show');
    Route::get('/atolls/{code}', 'atoll')->name('atolls.show');
    Route::get('/observatory', 'observatory')->name('observatory');
    Route::get('/developers', 'developers')->name('developers');
});
