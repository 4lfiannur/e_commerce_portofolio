<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\OrdersController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\User\UserLandingController;
use App\Http\Controllers\User\UserCheckoutController;
use App\Http\Controllers\Admin\OrderHistoryController;

//Landing Page Routes
Route::get('/', [UserLandingController::class, 'index'])->name('home');

Route::get('/cart', function () {
    return view('landing.shopping-cart');
})->name('cart');

Route::get('/auth/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');
Route::get('/auth/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout')->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::post('/checkout/process', [UserCheckoutController::class, 'process'])->name('checkout.process');

});

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])
        ->name('admin.categories.index');
    Route::post('/', [CategoryController::class, 'store'])
        ->name('admin.categories.store');
    Route::put('/{category}', [CategoryController::class, 'update'])
        ->name('admin.categories.update');
    Route::delete('/{category}', [CategoryController::class, 'destroy'])
        ->name('admin.categories.destroy');
});

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index'])
        ->name('admin.products.index');
    Route::post('/', [ProductController::class, 'store'])
        ->name('admin.products.store');
    Route::put('/{product}', [ProductController::class, 'update'])
        ->name('admin.products.update');
    Route::delete('/{product}', [ProductController::class, 'destroy'])
        ->name('admin.products.destroy');
});

// Users Management
Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index'])
        ->name('admin.users.index');
    Route::delete('/{user}', [UserController::class, 'destroy'])
        ->name('admin.users.destroy');
});
// Order History
Route::get('/history-order', [OrderHistoryController::class, 'index'])
    ->name('admin.history.index');


// Order Routes
Route::prefix('orders')->group(function () {
    Route::get('/', [OrdersController::class, 'index'])
        ->name('admin.orders.index');
    Route::patch('/{order}/status', [OrdersController::class, 'updateStatus'])
        ->name('admin.orders.update-status');
});

Route::get('/search', function () {
    return view('search');
});
// Admin Routes
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

Route::get('/admin/products', [ProductController::class, "index"])->name('admin.products');

Route::post('checkout/process', [UserCheckoutController::class, 'process'])->name('checkout.process'); 
Route::post('payment/update-status', [UserCheckoutController::class, 'updateStatus'])->name('payment.update-status');
