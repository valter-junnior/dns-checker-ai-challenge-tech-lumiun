<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DnsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UsersController;
use App\Models\Dns;
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
});


Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('users', UsersController::class)
        ->parameters(['users' => 'user'])
        ->names([
            'index'   => 'users.index',
            'store'   => 'users.store',
            'update'  => 'users.update',
            'destroy' => 'users.destroy',
        ]);

    Route::resource('dns', DnsController::class)
        ->parameters(['dns' => 'dns'])
        ->names([
            'index'   => 'dns.index',
        ]);

    Route::resource('upload', UploadController::class)
        ->parameters(['upload' => 'upload'])
        ->names([
            'store'   => 'upload.store',
        ]);

    Route::resource('dashboard', DashboardController::class)
        ->parameters(['dashboard' => 'dashboard'])
        ->names([
            'index'   => 'dashboard.index',
        ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/broadcast-test', function () {
    $user = auth()->user();

    $dns = Dns::forUser($user->id)->first();
    $dns->queried_at = now();

    event(new \App\Events\DnsEvent($dns));

    return ['ok' => true];
})->middleware(['web','auth']); 


require __DIR__ . '/auth.php';
