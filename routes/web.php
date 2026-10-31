<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookController::class, 'catalog'])->name('home');
Route::get('/books', [BookController::class, 'catalog'])->name('books.index');
Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');
Route::get('/authors', [AuthorController::class, 'index'])->name('authors.index');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');
Route::post('/checkout', [SaleController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('sales.store');
Route::get('/orders/{sale:reference}', [SaleController::class, 'show'])->name('sales.show');

Route::middleware(['auth', 'can:access-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('books', BookController::class)->except('show');
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::post('/sales/{sale}/paid', [SaleController::class, 'markPaid'])->name('sales.paid');
    Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});
