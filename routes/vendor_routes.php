<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Vendor\Auth\AuthenticateController;
use App\Http\Controllers\Vendor\InvoiceController;
use App\Http\Controllers\Vendor\SystemController;

use App\Http\Controllers\Vendor\DahboardController;
Route::group(['namespace' => 'Vendor', 'as' => 'vendor.'], function () {
    Route::group(['namespace' => 'Auth', 'prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::get('/login', [AuthenticateController::class, 'login'])->name('login');
        Route::get('/sign-up', [AuthenticateController::class, 'signup'])->name('signup');
        Route::post('/registration', [AuthenticateController::class, 'registration'])->name('registration');
        Route::post('/login-user', [AuthenticateController::class, 'submit'])->name('submit');
        Route::get('logout', [AuthenticateController::class, 'logout'])->name('logout');

    });

    Route::controller(DahboardController::class)->group(function(){
        Route::get('/invoice', 'invoice')->name('invoice');
        Route::post('/save-vendor', 'store')->name('store');
    });
    Route::get('settings', [SystemController::class, 'settings'])->name('settings');
    Route::post('settings', [SystemController::class, 'settingsUpdate']);
    Route::post('settings-password', [SystemController::class, 'settingsPasswordUpdate'])->name('settings-password');

    Route::controller(InvoiceController::class)->group(function(){
        Route::get('/invoice-create', 'create')->name('invoice_create');
        Route::get('/invoice-send/{id}', 'sendPdf')->name('invoice_sendPdf');
        Route::post('/save-invoice', 'save')->name('save');
        Route::get('/invoice-edit/{id}', 'editInvoice')->name('editInvoice');



    });

});

?>
