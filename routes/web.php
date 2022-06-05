<?php

use App\Http\Controllers\BroadcastController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', fn () => inertia('Dashboard', [
        'users' => [
            ['user_id' => 'xxxxx-1', 'user_info' => ['id' => 'xxxxx-1', 'name' => 'Alice']],
            ['user_id' => 'xxxxx-2', 'user_info' => ['id' => 'xxxxx-2', 'name' => 'Bob']],
            ['user_id' => 'xxxxx-3', 'user_info' => ['id' => 'xxxxx-3', 'name' => 'Charlie']],
        ],
    ]))->name('dashboard');

    Route::post('/authorize-channel', [BroadcastController::class, 'authorizeChannel'])->name('auth-channel');
    Route::post('/authorize-connection', [BroadcastController::class, 'authorizeConnection'])->name('auth-connection');
    Route::post('/broadcast', [BroadcastController::class, 'broadcast'])->name('broadcast');
    Route::post('/proxy-http-request', [BroadcastController::class, 'proxyHttpRequest'])->name('proxy-http-request');
});
