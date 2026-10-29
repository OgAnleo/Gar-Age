<?php

use App\Http\Controllers\Tenant\UnitController;
use Illuminate\Support\Facades\Route;

/*
| Tenant routes, prefixed with the tenant slug: /{tenant}/...
| {tenant} is resolved by slug via Route::bind in AppServiceProvider.
| Keep this file required last from web.php so fixed paths win.
*/

Route::prefix('{tenant}')->middleware('tenant')->name('tenant.')->group(function () {
    Route::middleware(['auth', 'verified', 'tenant.member'])->group(function () {
        Route::inertia('dashboard', 'tenant/dashboard')->name('dashboard');
        Route::resource('units', UnitController::class)->only(['index', 'store']);
    });
});
