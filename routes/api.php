<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Gaji Pokok
Route::get('/gaji-pokok', 'GajiPokokController@index')->name('api.gaji-pokok.index');

// Additional pada Slip Gaji
Route::get('/slip-gaji/additional', 'SlipGajiController@additional')->name('api.slip-gaji.additional');

\Ajifatur\Helpers\RouteExt::api();