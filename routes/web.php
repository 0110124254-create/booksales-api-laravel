<?php

use App\Http\Controllers\AdminBookController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\GenreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('/books', [CatalogController::class, 'index'])->name('books.catalog');
Route::get('/genres', [GenreController::class, 'webIndex'])->name('catalog.genres.index');
Route::get('/authors', [AuthorController::class, 'webIndex'])->name('catalog.authors.index');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'webCreate'])->name('login');
    Route::post('/login', [AuthController::class, 'webStore'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::post('/logout', [AuthController::class, 'webDestroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'can:access-admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::resource('books', AdminBookController::class)
            ->except('show')
            ->names('admin.web-books');
    });
