<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\MidtransNotificationController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman publik (Home adalah halaman pertama saat website dibuka)
|--------------------------------------------------------------------------
*/
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/product', [PageController::class, 'product'])->name('product');
Route::get('/portfolio', [PageController::class, 'portfolio'])->name('portfolio');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

/*
|--------------------------------------------------------------------------
| Login / Sign Up user + Google
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/signup', [RegisterController::class, 'show'])->name('signup');
    Route::post('/signup', [RegisterController::class, 'store'])->middleware('throttle:10,1')->name('signup.store');

    Route::get('/admin/login', [LoginController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/admin/login', [LoginController::class, 'adminLogin'])->middleware('throttle:10,1');
});

Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Pembayaran Midtrans
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::post('/checkout/{order}/sync', [CheckoutController::class, 'sync'])->name('checkout.sync');
});
Route::post('/midtrans/notification', [MidtransNotificationController::class, 'handle'])->name('midtrans.notification');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/products', [Admin\ProductController::class, 'index'])->name('products');
    Route::patch('/products/{product}', [Admin\ProductController::class, 'update'])->name('products.update');

    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders');
    Route::patch('/orders/{order}/confirm', [Admin\OrderController::class, 'confirm'])->name('orders.confirm');
    Route::patch('/orders/{order}/cancel', [Admin\OrderController::class, 'cancel'])->name('orders.cancel');

    Route::post('/withdrawals', [Admin\WithdrawalController::class, 'store'])->name('withdrawals.store');
    Route::patch('/withdrawals/{withdrawal}/complete', [Admin\WithdrawalController::class, 'complete'])->name('withdrawals.complete');
    Route::delete('/withdrawals/{withdrawal}', [Admin\WithdrawalController::class, 'destroy'])->name('withdrawals.destroy');
});
