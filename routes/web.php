<?php
use App\Models\Sale;
use App\Http\Controllers\PortfolioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Portfolio Routes (Public)
Route::prefix('portfolio')->name('portfolio.')->group(function () {
    Route::get('/', [PortfolioController::class, 'index'])->name('index');
    Route::get('/category/{category}', [PortfolioController::class, 'category'])->name('category');
    Route::get('/product/{product}', [PortfolioController::class, 'product'])->name('product');
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
