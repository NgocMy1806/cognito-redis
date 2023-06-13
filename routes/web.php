<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('index');
})->name('index');
Route::get('/index', function () {
    return view('index');
});

Route::get('/dashboard', [AuthController::class, 'handleALBCallback'])->name('handleALBCallback');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
