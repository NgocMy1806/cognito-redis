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
     $token = file_get_contents('http://169.254.169.254/latest/api/token', false, stream_context_create([
    'http' => [
        'method' => 'PUT',
        'header' => "X-aws-ec2-metadata-token-ttl-seconds: 300",
    ],
]));

$instanceId = file_get_contents('http://169.254.169.254/latest/meta-data/instance-id', false, stream_context_create([
    'http' => [
        'header' => "X-aws-ec2-metadata-token: $token",
    ],
]));
    return view('index',
     [
        'instanceId' => $instanceId
      ]);
})->name('index');

Route::get('/index', function () {
    $token = file_get_contents('http://169.254.169.254/latest/api/token', false, stream_context_create([
    'http' => [
        'method' => 'PUT',
        'header' => "X-aws-ec2-metadata-token-ttl-seconds: 300",
    ],
]));

$instanceId = file_get_contents('http://169.254.169.254/latest/meta-data/instance-id', false, stream_context_create([
    'http' => [
        'header' => "X-aws-ec2-metadata-token: $token",
    ],
]));
    return view('index',
     [
        'instanceId' => $instanceId
      ]);
});

Route::get('/dashboard', [AuthController::class, 'handleALBCallback'])->name('handleALBCallback');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
