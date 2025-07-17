<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.manager.auth.login');
});

Route::get('/login', function () {
    return redirect()->route('filament.manager.auth.login');
})->name('login');

Route::get('report/employee-performance', [\App\Http\Controllers\pdf\EmployeePerformanceReportController::class, 'export'])->name('report.employee-performance');
Route::get('report/item-purchase-transaction', [\App\Http\Controllers\pdf\ItemPurchaseTransactionController::class, 'export'])->name('report.item-purchase-transaction');
Route::get('report/cleaning-report', [\App\Http\Controllers\pdf\CleaningReportController::class, 'export'])->name('report.cleaning-report');
