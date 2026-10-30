<?php

use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Application\FindHomeTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function (Request $request, FindHomeTenant $findHomeTenant) {
        $tenant = $findHomeTenant->handle(new UserId($request->user()->id));

        return $tenant !== null
            ? to_route('tenant.dashboard', ['tenant' => $tenant->slug->value])
            : Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/tenant.php';
