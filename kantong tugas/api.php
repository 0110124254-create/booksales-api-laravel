<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/', [BookController::class, 'index'])->name('api.root');

Route::apiResource('books', BookController::class)->only(['index', 'show']);
Route::apiResource('books', BookController::class)
    ->only(['store', 'update', 'destroy'])
    ->middleware(['auth:sanctum', 'can:access-admin']);

Route::apiResource('genres', GenreController::class)->only(['index', 'show']);
Route::apiResource('genres', GenreController::class)
    ->only(['store', 'update', 'destroy'])
    ->middleware(['auth:sanctum', 'can:access-admin']);

Route::apiResource('authors', AuthorController::class)->only(['index', 'show']);
Route::apiResource('authors', AuthorController::class)
    ->only(['store', 'update', 'destroy'])
    ->middleware(['auth:sanctum', 'can:access-admin']);

Route::get('/login', [AuthController::class, 'create'])->name('api.login');
Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('api.login.store');
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth:sanctum')->name('api.logout');

Route::post('/checkout', [SaleController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('sales.store');
Route::get('/orders/{sale:reference}', [SaleController::class, 'show'])->name('sales.show');

Route::middleware(['auth:sanctum', 'can:access-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::apiResource('books', BookController::class)->except('show');
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::post('/sales/{sale}/paid', [SaleController::class, 'markPaid'])->name('sales.paid');
    Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});
