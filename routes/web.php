<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Session;

/*
|--------------------------------------------------------------------------
| Base Path
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (Session::has('user')) {
        $user = session('user');
        return view('dashboard', compact('user'));
    }
    return redirect('/login');
});

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth.session.custom');

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth.session.custom'])->group(function () {
    Route::get('/dashboard', function () {
        $user = session('user');
        return view('dashboard', compact('user'));
    });
});
