<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\AdminContactController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminSaleController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\MidtransNotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/', [BookController::class, 'index'])->name('api.root');
Route::post('/midtrans/notification', MidtransNotificationController::class)->name('midtrans.notification');

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

Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('api.login.store');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1')->name('api.register');
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth:sanctum')->name('api.logout');

Route::middleware(['auth:sanctum', 'can:access-customer'])->group(function () {
    Route::get('/my/orders', [SaleController::class, 'myIndex'])->name('sales.mine');
    Route::post('/sales', [SaleController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('sales.store');
    Route::post('/checkout', [SaleController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('sales.checkout');
    Route::get('/sales/{reference}', [SaleController::class, 'show'])->name('sales.resource.show');
    Route::match(['put', 'patch'], '/sales/{reference}', [SaleController::class, 'update'])
        ->name('sales.update');

    Route::match(['put', 'patch'], '/orders/{reference}', [SaleController::class, 'update'])
        ->name('sales.order.update');
});

Route::post('/contact', [ContactController::class, 'apiStore'])
    ->middleware(['auth:sanctum', 'can:access-customer', 'throttle:5,1'])
    ->name('contact.store');

Route::get('/orders/{reference}', [SaleController::class, 'show'])
    ->middleware(['auth:sanctum', 'can:access-customer'])
    ->name('sales.show');

Route::middleware(['auth:sanctum', 'can:access-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'apiIndex'])->name('dashboard');
    Route::apiResource('books', BookController::class)->except('show');
    Route::get('/users', [AdminUserController::class, 'apiIndex'])->name('users.index');
    Route::get('/messages', [AdminContactController::class, 'apiIndex'])->name('messages.index');
    Route::patch('/messages/{contactMessage}', [AdminContactController::class, 'apiUpdate'])->name('messages.update');
    Route::get('/sales', [AdminSaleController::class, 'apiIndex'])->name('sales.index');
    Route::patch('/sales/{sale}', [AdminSaleController::class, 'apiUpdate'])->name('sales.update');
    Route::delete('/sales/{id}', [SaleController::class, 'destroy'])->name('sales.destroy');
    Route::post('/sales/{sale}/paid', [SaleController::class, 'markPaid'])->name('sales.paid');
    Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});
