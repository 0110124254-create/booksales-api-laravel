<?php

use App\Http\Controllers\AdminBookController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminContactController;
use App\Http\Controllers\AdminGenreController;
use App\Http\Controllers\AdminSaleController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('/books', [CatalogController::class, 'index'])->name('books.catalog');
Route::get('/book/{book}', [CatalogController::class, 'show'])->name('catalog.books.show');
Route::get('/genres', [GenreController::class, 'webIndex'])->name('catalog.genres.index');
Route::get('/authors', [AuthorController::class, 'webIndex'])->name('catalog.authors.index');
Route::view('/about', 'about')->name('about');
Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'webCreate'])->name('login');
    Route::post('/login', [AuthController::class, 'webStore'])
        ->middleware('throttle:5,1')
        ->name('login.store');
    Route::get('/register', [AuthController::class, 'webRegisterCreate'])->name('register');
    Route::post('/register', [AuthController::class, 'webRegisterStore'])
        ->middleware('throttle:10,1')
        ->name('register.store');
});

Route::post('/logout', [AuthController::class, 'webDestroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'can:access-customer'])->group(function (): void {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{book}', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{book}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{book}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:5,1')->name('checkout.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{reference}', [OrderController::class, 'show'])->name('orders.show');
});

Route::middleware(['auth', 'can:access-admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::resource('books', AdminBookController::class)
            ->except('show')
            ->names('admin.web-books');
        Route::resource('genres', AdminGenreController::class)
            ->except('show')
            ->names('admin.web-genres');
        Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('/orders', [AdminSaleController::class, 'index'])->name('admin.sales.index');
        Route::get('/orders/{sale}', [AdminSaleController::class, 'show'])->name('admin.sales.show');
        Route::patch('/orders/{sale}', [AdminSaleController::class, 'update'])->name('admin.sales.update');
        Route::get('/messages', [AdminContactController::class, 'index'])->name('admin.contacts.index');
        Route::patch('/messages/{contactMessage}', [AdminContactController::class, 'update'])->name('admin.contacts.update');
    });
