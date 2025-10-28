<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\GeneralController;
use Illuminate\Support\Facades\DB;




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

        // ✅ Fetch dashboard counts
        $customerCount = DB::table('customers')->count();
        $adminCount = DB::table('users')->count();
        $adCount = DB::table('advertisements')->count();

        // ✅ Pass them to the dashboard view
        return view('dashboard', compact('user', 'customerCount', 'adminCount', 'adCount'));
    });

    // ✅ Categories
    Route::get('/categories', [GeneralController::class, 'getCategories']);
    Route::post('/add-category', [GeneralController::class, 'addCategory']);
    Route::post('/update-category/{id}', [GeneralController::class, 'updateCategory']);

    // ✅ Ad Types
    Route::get('/adtypes', [GeneralController::class, 'getAdTypes']);
    Route::post('/add-adtype', [GeneralController::class, 'addAdType']);
    Route::post('/update-adtype/{id}', [GeneralController::class, 'updateAdType']);

    // ✅ Ad Sizes
    Route::get('/adsizes', [GeneralController::class, 'getAdSizes']);
    Route::post('/add-adsize', [GeneralController::class, 'addAdSize']);
    Route::post('/update-adsize/{id}', [GeneralController::class, 'updateAdSize']);

    // ✅ Ad Criterias
    Route::get('/adcriterias', [GeneralController::class, 'getAdCriterias']);
    Route::post('/add-adcriteria', [GeneralController::class, 'addAdCriteria']);
    Route::post('/update-adcriteria/{id}', [GeneralController::class, 'updateAdCriteria']);

    // ✅ Ad Criteria Options
    Route::get('/adcriteria-options', [GeneralController::class, 'getAdCriteriaOptions']);
    Route::post('/add-adcriteria-option', [GeneralController::class, 'addAdCriteriaOption']);
    Route::post('/update-adcriteria-option/{id}', [GeneralController::class, 'updateAdCriteriaOption']);

    // ✅ Districts
    Route::get('/districts', [GeneralController::class, 'getDistricts']);
    Route::post('/add-district', [GeneralController::class, 'addDistrict']);
    Route::post('/update-district/{id}', [GeneralController::class, 'updateDistrict']);

    // ✅ Cities
    Route::get('/cities', [GeneralController::class, 'getCities']);
    Route::post('/add-city', [GeneralController::class, 'addCity']);
    Route::post('/update-city/{id}', [GeneralController::class, 'updateCity']);

    // ✅ Advertisements
    Route::get('/advertisements', [GeneralController::class, 'getAdvertisements']);
    Route::get('/advertisements/{id}/view', [GeneralController::class, 'viewAdvertisement']);
});