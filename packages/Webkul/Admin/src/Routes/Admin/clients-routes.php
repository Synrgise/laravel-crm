<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\Client\ClientController;
use Webkul\Admin\Http\Controllers\Client\ClientContractController;
use Webkul\Admin\Http\Controllers\Client\ClientSlaController;

Route::prefix('clients')->group(function () {
    Route::controller(ClientController::class)->group(function () {
        Route::get('', 'index')->name('admin.clients.index');
        Route::get('view/{id}', 'view')->name('admin.clients.view');
        Route::get('edit/{id}', 'edit')->name('admin.clients.edit');
        Route::put('edit/{id}', 'update')->name('admin.clients.update');
        Route::get('{id}/leads', 'leads')->name('admin.clients.leads.index');
    });

    Route::controller(ClientContractController::class)->prefix('{clientId}/contracts')->group(function () {
        Route::post('', 'store')->name('admin.clients.contracts.store');
        Route::put('{id}', 'update')->name('admin.clients.contracts.update');
        Route::delete('{id}', 'destroy')->name('admin.clients.contracts.destroy');
    });

    Route::controller(ClientSlaController::class)->prefix('{clientId}/slas')->group(function () {
        Route::post('', 'store')->name('admin.clients.slas.store');
        Route::put('{id}', 'update')->name('admin.clients.slas.update');
        Route::delete('{id}', 'destroy')->name('admin.clients.slas.destroy');
    });
});
