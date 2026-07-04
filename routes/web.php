<?php

use App\Http\Controllers\BigDataController;
use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to dashboard
Route::redirect('/', '/dashboard');

// Auth routes (provided by Breeze)
require __DIR__ . '/auth.php';

// Protected routes — require authentication
Route::middleware(['auth', 'verified'])->group(function () {

    // ===== Dashboard =====
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ===== Profile =====
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ===== Products WRITE — Admin & Staff only =====
    // (Must be defined BEFORE the parameterized read routes to avoid /create being matched as {product})
    Route::middleware(['role:admin|staff'])->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::patch('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::patch('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/borrowings/create', [BorrowingController::class, 'create'])->name('borrowings.create');
        Route::post('/borrowings', [BorrowingController::class, 'store'])->name('borrowings.store');
        Route::post('/borrowings/{borrowing}/return', [BorrowingController::class, 'returnItems'])->name('borrowings.return');
    });

    // ===== Products READ — All roles =====
    Route::middleware(['role:admin|staff|manager'])->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

        Route::get('/borrowings', [BorrowingController::class, 'index'])->name('borrowings.index');
        Route::get('/borrowings/{borrowing}', [BorrowingController::class, 'show'])->name('borrowings.show');
    });

    // ===== Big Data Dashboard — All roles =====
    Route::prefix('big-data')->name('bigdata.')->group(function () {
        Route::get('/', [BigDataController::class, 'index'])->name('index');
        Route::post('/spark/analyze', [BigDataController::class, 'runSparkAnalysis'])->name('spark.analyze');
        Route::post('/hadoop/run', [BigDataController::class, 'runHadoopJob'])->name('hadoop.run');
        Route::post('/kafka/produce', [BigDataController::class, 'produceEvent'])->name('kafka.produce');
        Route::post('/kafka/consume', [BigDataController::class, 'consumeEvents'])->name('kafka.consume');
        Route::get('/events/stream', [BigDataController::class, 'eventStream'])->name('events.stream');
        Route::get('/stats', [BigDataController::class, 'getStats'])->name('stats');
    });

    // ===== Reports — Admin & Manager =====
    Route::middleware(['role:admin|manager'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/products/pdf', [ReportController::class, 'exportProductsPdf'])->name('products.pdf');
        Route::get('/products/excel', [ReportController::class, 'exportProductsExcel'])->name('products.excel');
        Route::get('/borrowings/pdf', [ReportController::class, 'exportBorrowingsPdf'])->name('borrowings.pdf');
        Route::get('/borrowings/excel', [ReportController::class, 'exportBorrowingsExcel'])->name('borrowings.excel');
    });
});
