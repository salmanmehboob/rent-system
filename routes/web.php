<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\RoomShopController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ExcelExportController;

Route::get('/', function () {
   return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');


Route::middleware('auth')->group(function () {


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
    Route::get('/agreements', [CustomerController::class, 'showAgreement'])->name('agreement.show');
    Route::post('/agreements', [CustomerController::class, 'setAgreement'])->name('agreement.set');
    // picks rooms or shops by selected building
    Route::get('/roomshop-by-building', [CustomerController::class, 'getByBuilding']);


    //  transactions Routes
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::put('/{id}', [InvoiceController::class, 'update'])->name('updateInvoice');
        Route::put('paid/{id}', [InvoiceController::class, 'paid'])->name('paid');
        Route::delete('/{id}', [InvoiceController::class, 'destroy'])->name('destroy');
        // generate all the bills by one click
        Route::post('/{id}/transactions', [InvoiceController::class, 'getTransactions'])->name('transactions');

    });
    // picks rooms or shops by selected building
    Route::get('/customer-by-building', [InvoiceController::class, 'getByBuilding']);
    Route::get('/total-bills', [InvoiceController::class, 'show'])->name('bills');
    Route::post('/generate-bills', [InvoiceController::class, 'combine'])->name('combine-bills');
    Route::post('invoices/pay-now-combine', [\App\Http\Controllers\InvoiceController::class, 'payCombineInvoice'])->name('invoices.pay-now-combine');


    // route for print invoice
    Route::get('invoice/{id}/print', [PrintController::class, 'printInvoice'])->name('print');

    Route::get('/print-latest-invoices', [PrintController::class, 'printLatest'])->name('invoices.print.latest');



    Route::prefix('reports')->name('reports.')->controller(ReportController::class)->group(function () {
        Route::get('customers', 'getCustomerReports')->name('customers');
        Route::get('buildings', 'getBuildingReports')->name('buildings');
        Route::get('dues', 'getDuesReports')->name('dues');
    });
    // get rooms and shops by selecting building
    Route::get('/depend', [ReportController::class, 'getByBuilding'])->name('depend');

    // Excel Sheet Generator
    Route::get('/generate-excel', [ExcelExportController::class, 'index'])->name('excel.index');

});
