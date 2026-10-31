<?php

use App\Http\Controllers\Tenant\ContractController;
use App\Http\Controllers\Tenant\CustomerController;
use App\Http\Controllers\Tenant\FeeController;
use App\Http\Controllers\Tenant\UnitController;
use Illuminate\Support\Facades\Route;

/*
| Tenant routes, prefixed with the tenant slug: /{tenant}/...
| {tenant} is resolved by slug via Route::bind in AppServiceProvider.
| Keep this file required last from web.php so fixed paths win.
| Child ids ({unit}, {customer}, {contract}, {fee}) are plain numbers: controllers pass
| them to use cases together with the current tenant, so a foreign id is simply "not found".
*/

Route::pattern('unit', '[1-9][0-9]*');
Route::pattern('customer', '[1-9][0-9]*');
Route::pattern('contract', '[1-9][0-9]*');
Route::pattern('fee', '[1-9][0-9]*');

Route::prefix('{tenant}')->middleware('tenant')->name('tenant.')->group(function () {
    Route::middleware(['auth', 'verified', 'tenant.member'])->group(function () {
        Route::inertia('dashboard', 'tenant/dashboard')->name('dashboard');
        Route::resource('units', UnitController::class)->except(['show']);
        Route::resource('customers', CustomerController::class)->except(['show']);
        Route::resource('contracts', ContractController::class)->except(['show']);

        Route::controller(FeeController::class)->prefix('fees')->name('fees.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('generate', 'generate')->name('generate');
            Route::post('{fee}/payment', 'pay')->name('pay');
            Route::delete('{fee}/payment', 'unpay')->name('unpay');
        });
    });
});
