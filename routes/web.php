<?php
use App\Models\Sale;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});


Route::post('/sales/{id}/mark-printed', function ($id, Request $request) {
    $sale = Sale::findOrFail($id);
    $sale->print = true;
    $sale->save();

    return response()->json(['success' => true]);
})->name('sales.mark-printed');
