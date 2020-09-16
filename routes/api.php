<?php

use App\Http\Controllers\Api\AuthController;
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

Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/login/refresh', [AuthController::class, 'refresh'])->name('api.refresh-access-token');

Route::group(['middleware' => ['auth:api']], function () {
	Route::get('/user', [AuthController::class, 'user'])->name('api.user');
	Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
});
