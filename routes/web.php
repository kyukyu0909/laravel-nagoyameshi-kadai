<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\TermController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/user', [UserController::class, 'index']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth:admin')->group(function () {
    Route::get('admin/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::get('admin/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('admin.users.show');
});

require __DIR__.'/auth.php';

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth:admin'], function () {
    Route::get('home', [Admin\HomeController::class, 'index'])->name('home');
});


Route::get('admin/restaurants/index', [Admin\RestaurantController::class, 'index'])->name('admin.restaurants.index');

Route::get('admin/restaurants/create', [Admin\RestaurantController::class, 'create'])->name('admin.restaurants.create');

Route::get('admin/restaurants/show={restaurant}', [Admin\RestaurantController::class, 'show'])->name('admin.restaurants.show');

Route::resource('restaurants', RestaurantController::class )->only('store', 'update', 'destroy');

Route::get('admin/restaurants/edit', [Admin\RestaurantController::class, 'edit'])->name('admin.restaurants.edit');

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth:admin'], function () {
    Route::resource('restaurants', RestaurantController::class);
});

Route::resource('admin/categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy'])->names('admin.categories');

Route::prefix('admin/company')->group(function () {
    Route::get('/index', [CompanyController::class, 'index'])->name('admin.company.index');
    Route::get('/edit/{company}', [CompanyController::class, 'edit'])->name('admin.company.edit');
    Route::patch('/edit/{company}', [CompanyController::class, 'update'])->name('admin.company.update');
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth:admin'], function () {
    Route::resource('terms', TermController::class);
});
