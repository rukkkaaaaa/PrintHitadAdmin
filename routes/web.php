<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\GeneralController;



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

Route::match(['get', 'post'], '/users', [AuthController::class, 'manageUsers'])->middleware('auth.session.custom');

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

Route::middleware(['auth.session.custom'])->group(function () {
    Route::get('/categories', [GeneralController::class, 'getCategories']);
    Route::post('/add-category', [GeneralController::class, 'addCategory']);
    Route::post('/update-category/{id}', [GeneralController::class, 'updateCategory']);
});

Route::middleware(['auth.session.custom'])->group(function () {
    Route::get('/adtypes', [GeneralController::class, 'getAdTypes']);
    Route::post('/add-adtype', [GeneralController::class, 'addAdType']);
    Route::post('/update-adtype/{id}', [GeneralController::class, 'updateAdType']);
});

Route::middleware(['auth.session.custom'])->group(function () {
    Route::get('/adsizes', [GeneralController::class, 'getAdSizes']);
    Route::post('/add-adsize', [GeneralController::class, 'addAdSize']);
    Route::post('/update-adsize/{id}', [GeneralController::class, 'updateAdSize']);
});

Route::middleware(['auth.session.custom'])->group(function () {
    Route::get('/adcriterias', [GeneralController::class, 'getAdCriterias']);
    Route::post('/add-adcriteria', [GeneralController::class, 'addAdCriteria']);
    Route::post('/update-adcriteria/{id}', [GeneralController::class, 'updateAdCriteria']);
});

Route::middleware(['auth.session.custom'])->group(function () {
    Route::get('/adcriteria-options', [GeneralController::class, 'getAdCriteriaOptions']);
    Route::post('/add-adcriteria-option', [GeneralController::class, 'addAdCriteriaOption']);
    Route::post('/update-adcriteria-option/{id}', [GeneralController::class, 'updateAdCriteriaOption']);
});

Route::middleware(['auth.session.custom'])->group(function () {
    Route::get('/districts', [GeneralController::class, 'getDistricts']);
    Route::post('/add-district', [GeneralController::class, 'addDistrict']);
    Route::post('/update-district/{id}', [GeneralController::class, 'updateDistrict']);
});

Route::middleware(['auth.session.custom'])->group(function () {
    Route::get('/cities', [GeneralController::class, 'getCities']);
    Route::post('/add-city', [GeneralController::class, 'addCity']);
    Route::post('/update-city/{id}', [GeneralController::class, 'updateCity']);
});
