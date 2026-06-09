<?php
use App\Models\Sale;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Portfolio Routes (Public)
Route::prefix('portfolio')->name('portfolio.')->group(function () {
    Route::get('/', [PortfolioController::class, 'index'])->name('index');
    Route::get('/category/{category}', [PortfolioController::class, 'category'])->name('category');
    Route::get('/product/{product}', [PortfolioController::class, 'product'])->name('product');
});

// Cart Routes
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add/{product}', [CartController::class, 'add'])->name('add');
    Route::post('/remove/{id}', [CartController::class, 'remove'])->name('remove');
    Route::post('/update/{id}', [CartController::class, 'update'])->name('update');
});

// Checkout Routes
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CartController::class, 'checkout'])->name('index');
    Route::post('/', [CartController::class, 'processCheckout'])->name('process');
    Route::get('/success', [CartController::class, 'success'])->name('success');
});

// Admin routes are handled by Filament (see config/filament.php)
Route::get('/', function () {
    return redirect('/portfolio');
});

Route::post('/sales/{id}/mark-printed', function ($id, Request $request) {
    $sale = Sale::findOrFail($id);
    $sale->print = true;
    $sale->save();

    return response()->json(['success' => true]);
})->name('sales.mark-printed');
