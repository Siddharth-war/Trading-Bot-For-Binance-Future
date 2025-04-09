<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\Auth\LoginController;
use App\Http\Controllers\Customer\POSController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\BucketController;
use App\Http\Controllers\Customer\SystemController;


Route::group(['namespace' => 'Customer', 'as' => 'customer.'], function () {

Route::group(['namespace' => 'Auth', 'prefix' => 'auth', 'as' => 'auth.'], function () {
    Route::get('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/login', [LoginController::class, 'submit'])->middleware('actch');
    Route::get('logout', [LoginController::class, 'logout'])->name('logout');
});

Route::group(['prefix' => 'pos', 'as' => 'pos.'], function () {
    Route::get('/', 'POSController@index')->name('index');
    Route::get('quick-view', 'POSController@quick_view')->name('quick-view');
    Route::post('variant_price', 'POSController@variant_price')->name('variant_price');
    Route::post('add-to-cart', 'POSController@addToCart')->name('add-to-cart');
    Route::post('remove-from-cart', 'POSController@removeFromCart')->name('remove-from-cart');
    Route::post('cart-items', 'POSController@cart_items')->name('cart_items');
    Route::post('update-quantity', 'POSController@updateQuantity')->name('updateQuantity');
    Route::post('empty-cart', 'POSController@emptyCart')->name('emptyCart');
    Route::post('tax', 'POSController@update_tax')->name('tax');
    Route::post('discount', 'POSController@update_discount')->name('discount');
    Route::get('customers', 'POSController@get_customers')->name('customers');
    Route::post('order', 'POSController@place_order')->name('order');
    Route::get('orders', 'POSController@order_list')->name('orders');
    Route::get('export-excel', 'POSController@export_excel')->name('export-excel');
    Route::get('order-details/{id}', 'POSController@order_details')->name('order-details');
    Route::get('invoice/{id}', 'POSController@generate_invoice');
    Route::any('store-keys', 'POSController@store_keys')->name('store-keys');
    Route::post('table', 'POSController@getTableListByBranch')->name('table');
    Route::get('clear', 'POSController@clear_session_data')->name('clear');
    Route::post('customer-store', 'POSController@customer_store')->name('customer-store');
    Route::post('session-destroy', 'POSController@session_destroy')->name('session-destroy');
    Route::post('add-delivery-address', 'POSController@addDeliveryInfo')->name('add-delivery-address');
    Route::get('get-distance', 'POSController@get_distance')->name('get-distance');
    Route::post('order_type/store', 'POSController@order_type_store')->name('order_type.store');
});

Route::get('settings', [SystemController::class, 'settings'])->name('settings');
Route::post('settings', [SystemController::class, 'settingsUpdate']);
Route::post('settings-password', [SystemController::class, 'settingsPasswordUpdate'])->name('settings-password');

Route::group(['prefix' => 'orders', 'as' => 'orders.'], function () {
    Route::get('list/{status}', [OrderController::class, 'list'])->name('list');
    Route::get('export-excel', [OrderController::class, 'exportExcel'])->name('export-excel');
    Route::get('details/{id}', [OrderController::class, 'details'])->name('details');
    Route::post('increase-preparation-time/{id}', [OrderController::class, 'preparationTime'])->name('increase-preparation-time');
    Route::get('status', [OrderController::class, 'status'])->name('status');
    Route::get('add-delivery-man/{order_id}/{delivery_man_id}', [OrderController::class, 'addDeliveryman'])->name('add-delivery-man');
    Route::get('payment-status', [OrderController::class, 'paymentStatus'])->name('payment-status');
    Route::get('generate-invoice/{id}', [OrderController::class, 'generateInvoice'])->name('generate-invoice')->withoutMiddleware(['module:order_management']);
    Route::post('add-payment-ref-code/{id}', [OrderController::class, 'addPaymentReferenceCode'])->name('add-payment-ref-code');
    Route::get('branch-filter/{branch_id}', [OrderController::class, 'branchFilter'])->name('branch-filter');
    Route::post('update-shipping/{id}', [OrderController::class, 'updateShipping'])->name('update-shipping');
    Route::delete('delete/{id}', [OrderController::class, 'delete'])->name('delete');
    Route::get('export', [OrderController::class, 'exportData'])->name('export');
    Route::get('ajax-change-delivery-time-date/{order_id}', [OrderController::class, 'ajaxChangeDeliveryTimeAndDate'])->name('ajax-change-delivery-time-date');
    Route::get('verify-offline-payment/{order_id}/{status}', [OrderController::class, 'verifyOfflinePayment']);
    Route::post('update-order-delivery-area/{order_id}', [OrderController::class, 'updateOrderDeliveryArea'])->name('update-order-delivery-area');
});

Route::group(['prefix' => 'custom-bucket', 'as' => 'custom-bucket.',"controller"=>BucketController::class],function(){
    Route::get('list','index')->name('bucket_list');
    Route::get('create','create')->name('bucket_create');
    Route::post('bucket-store','store')->name('bucket_store');
    Route::get('edit/{id}','edit')->name('bucket_edit');
    Route::delete('delete/{id}','destroy')->name('bucket_delete');
    Route::get('place-order/{id}','placeOrder')->name('bucket_placeOrder');




});

});
