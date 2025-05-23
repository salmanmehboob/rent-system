<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\RoomShopController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\InvoiceController;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');


Route::middleware('auth')->group( function() {

    
    //  buildings Routes 
    Route::prefix('buildings')->name('buildings.')->group(function () {
        Route::get('/', [BuildingController::class, 'index'])->name('index');  
        Route::get('/{id}', [BuildingController::class, 'show'])->name('show');
        Route::post('/', [BuildingController::class, 'store'])->name('store'); 
        Route::put('/{id}', [BuildingController::class, 'update'])->name('update'); 
        Route::delete('/{id}', [BuildingController::class, 'destroy'])->name('destroy'); 
    });


    //  rooms/shops Routes 
    Route::prefix('roomshops')->name('roomshops.')->group(function () {
        Route::get('/', [RoomShopController::class, 'index'])->name('index');  
        Route::get('/{id}', [RoomShopController::class, 'show'])->name('show');
        Route::post('/', [RoomShopController::class, 'store'])->name('store'); 
        Route::put('/{id}', [RoomShopController::class, 'update'])->name('update'); 
        Route::delete('/{id}', [RoomShopController::class, 'destroy'])->name('destroy'); 
    });

       //  customers Routes 
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');  
        Route::get('/{id}', [CustomerController::class, 'show'])->name('show');
        Route::post('/', [CustomerController::class, 'store'])->name('store'); 
        Route::put('/{id}', [CustomerController::class, 'update'])->name('update'); 
        Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('destroy'); 
    });
    // picks rooms or shops by selected building
    Route::get('/roomshop-by-building', [CustomerController::class, 'getByBuilding']);


        //  transactions Routes 
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');  
        Route::get('/{id}', [TransactionController::class, 'show'])->name('show');
        Route::post('/', [TransactionController::class, 'store'])->name('store'); 
        Route::put('/{id}', [TransactionController::class, 'update'])->name('update'); 
        Route::delete('/{id}', [TransactionController::class, 'destroy'])->name('destroy'); 
    });
    // picks rooms or shops by selected building
    Route::get('/customer-by-building', [TransactionController::class, 'getByBuilding']);

    // route for print invoice
    Route::get('invoice/{id}/print',[InvoiceController::class, 'printInvoice'])->name('invoice');


});