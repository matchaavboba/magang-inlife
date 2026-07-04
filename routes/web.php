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

    // ===== Products (Admin & Staff) =====
    Route::middleware(['role:admin|staff'])->group(function () {
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);
    });

    // Products read-only for managers
    Route::middleware(['role:admin|staff|manager'])->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    });

    // ===== Borrowings (Admin & Staff) =====
    Route::middleware(['role:admin|staff'])->group(function () {
        Route::resource('borrowings', BorrowingController::class)->except(['edit', 'update', 'destroy']);
        Route::post('/borrowings/{borrowing}/return', [BorrowingController::class, 'returnItems'])->name('borrowings.return');
    });

    // Borrowings read-only for managers
    Route::middleware(['role:admin|staff|manager'])->group(function () {
        Route::get('/borrowings', [BorrowingController::class, 'index'])->name('borrowings.index');
        Route::get('/borrowings/{borrowing}', [BorrowingController::class, 'show'])->name('borrowings.show');
    });

    // ===== Big Data Dashboard (All roles) =====
    Route::prefix('big-data')->name('bigdata.')->group(function () {
        Route::get('/', [BigDataController::class, 'index'])->name('index');
        Route::post('/spark/analyze', [BigDataController::class, 'runSparkAnalysis'])->name('spark.analyze');
        Route::post('/hadoop/run', [BigDataController::class, 'runHadoopJob'])->name('hadoop.run');
        Route::post('/kafka/produce', [BigDataController::class, 'produceEvent'])->name('kafka.produce');
        Route::post('/kafka/consume', [BigDataController::class, 'consumeEvents'])->name('kafka.consume');
        Route::get('/events/stream', [BigDataController::class, 'eventStream'])->name('events.stream');
        Route::get('/stats', [BigDataController::class, 'getStats'])->name('stats');
    });

    // ===== Reports (Admin & Manager) =====
    Route::middleware(['role:admin|manager'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/products/pdf', [ReportController::class, 'exportProductsPdf'])->name('products.pdf');
        Route::get('/products/excel', [ReportController::class, 'exportProductsExcel'])->name('products.excel');
        Route::get('/borrowings/pdf', [ReportController::class, 'exportBorrowingsPdf'])->name('borrowings.pdf');
        Route::get('/borrowings/excel', [ReportController::class, 'exportBorrowingsExcel'])->name('borrowings.excel');
    });
});
