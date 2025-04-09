<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Branch\Auth\LoginController;
use App\Http\Controllers\Branch\BusinessSettingsController;
use App\Http\Controllers\Branch\BranchPromotionController;
use App\Http\Controllers\Branch\DashboardController;
use App\Http\Controllers\Branch\KitchenController;
use App\Http\Controllers\Branch\OrderController;
use App\Http\Controllers\Branch\POSController;
use App\Http\Controllers\Branch\ProductController;
use App\Http\Controllers\Branch\SystemController;
use App\Http\Controllers\Branch\TableController;
use App\Http\Controllers\Branch\TableOrderController;

Route::group(['namespace' => 'Branch', 'as' => 'branch.', 'middleware' => 'maintenance_mode'], function () {
    Route::group(['namespace' => 'Auth', 'prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::get('/code/captcha/{tmp}', [LoginController::class, 'captcha'])->name('default-captcha');
        Route::get('login', [LoginController::class, 'login'])->name('login');
        Route::post('login', [LoginController::class, 'submit']);
        Route::get('logout', [LoginController::class, 'logout'])->name('logout');
    });

    Route::group(['middleware' => ['branch', 'branch_status']], function () {
        Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
        Route::post('order-stats', [DashboardController::class, 'orderStats'])->name('order-stats');
        Route::get('order-statistics', [DashboardController::class, 'orderStatistics'])->name('order-statistics');
        Route::get('earning-statistics', [DashboardController::class, 'earningStatistics'])->name('earning-statistics');
        Route::get('settings', [DashboardController::class, 'settings'])->name('settings');
        Route::post('settings', [DashboardController::class, 'settingsUpdate']);
        Route::post('settings-password', [DashboardController::class, 'settingsPasswordUpdate'])->name('settings-password');

        Route::get('/get-restaurant-data', [SystemController::class, 'restaurantData'])->name('get-restaurant-data');

        Route::group(['prefix' => 'pos', 'as' => 'pos.'], function () {
            Route::get('/', [POSController::class, 'index'])->name('index');
            Route::get('quick-view', [POSController::class, 'quickView'])->name('quick-view');
            Route::post('variant_price', [POSController::class, 'variantPrice'])->name('variant_price');
            Route::post('add-to-cart', [POSController::class, 'addToCart'])->name('add-to-cart');
            Route::post('remove-from-cart', [POSController::class, 'removeFromCart'])->name('remove-from-cart');
            Route::post('cart-items', [POSController::class, 'cartItems'])->name('cart_items');
            Route::post('update-quantity', [POSController::class, 'updateQuantity'])->name('updateQuantity');
            Route::post('empty-cart', [POSController::class, 'emptyCart'])->name('emptyCart');
            Route::post('tax', [POSController::class, 'updateTax'])->name('tax');
            Route::post('discount', [POSController::class, 'updateDiscount'])->name('discount');
            Route::get('customers', [POSController::class, 'getCustomers'])->name('customers');
            Route::post('order', [POSController::class, 'placeOrder'])->name('order');
            Route::get('orders', [POSController::class, 'orderList'])->name('orders');
            Route::get('order-details/{id}', [POSController::class, 'orderDetails'])->name('order-details');
            Route::get('invoice/{id}', [POSController::class, 'generateInvoice']);
            Route::get('clear', [POSController::class, 'clearSessionData'])->name('clear');
            Route::post('customer-store', [POSController::class, 'customerStore'])->name('customer-store');
            Route::any('store-keys', [POSController::class, 'store_keys'])->name('store-keys');
            Route::post('session-destroy', [POSController::class, 'sessionDestroy'])->name('session-destroy');
            Route::post('add-delivery-address', [POSController::class, 'addDeliveryInfo'])->name('add-delivery-address');
            Route::get('get-distance', [POSController::class, 'getDistance'])->name('get-distance');
            Route::post('order_type/store', [POSController::class, 'orderTypeStore'])->name('order_type.store');

        });

        Route::group(['prefix' => 'orders', 'as' => 'orders.'], function () {
            Route::get('list/{status}', [OrderController::class, 'list'])->name('list');
            Route::get('details/{id}', [OrderController::class, 'details'])->name('details');
            Route::post('increase-preparation-time/{id}', [OrderController::class, 'preparationTime'])->name('increase-preparation-time');
            Route::get('status', [OrderController::class, 'status'])->name('status');
            Route::get('add-delivery-man/{order_id}/{delivery_man_id}', [OrderController::class, 'addDeliveryman'])->name('add-delivery-man');
            Route::get('payment-status', [OrderController::class, 'paymentStatus'])->name('payment-status');
            Route::get('generate-invoice/{id}', [OrderController::class, 'generateInvoice'])->name('generate-invoice');
            Route::post('add-payment-ref-code/{id}', [OrderController::class, 'addPaymentReferenceCode'])->name('add-payment-ref-code');
            Route::get('export-excel', [OrderController::class, 'exportExcel'])->name('export-excel');
            Route::get('ajax-change-delivery-time-date/{order_id}', [OrderController::class, 'changeDeliveryTimeDate'])->name('ajax-change-delivery-time-date');
            Route::get('verify-offline-payment/{order_id}/{status}', [OrderController::class, 'verifyOfflinePayment']);
            Route::post('update-order-delivery-area/{order_id}', [OrderController::class, 'updateOrderDeliveryArea'])->name('update-order-delivery-area');
        });

        Route::group(['prefix' => 'table/order', 'as' => 'table.order.', 'middleware' => ['app_activate:' . APPS['table_app']['software_id']]], function () {
            Route::get('list/{status}', [TableOrderController::class, 'orderList'])->name('list');
            Route::get('details/{id}', [TableOrderController::class, 'orderDetails'])->name('details');
            Route::get('running', [TableOrderController::class, 'tableRunningOrder'])->name('running');
            Route::get('running/invoice', [TableOrderController::class, 'runningOrderInvoice'])->name('running.invoice');
            Route::get('export-excel', [TableOrderController::class, 'export_excel'])->name('export-excel');
        });

        Route::group(['prefix' => 'order', 'as' => 'order.'], function () {
            Route::get('list/{status}', [OrderController::class, 'list'])->name('list');
            Route::put('status-update/{id}', [OrderController::class, 'status'])->name('status-update');
            Route::post('update-shipping/{id}', [OrderController::class, 'updateShipping'])->name('update-shipping');
        });

        Route::group(['prefix' => 'table', 'as' => 'table.','middleware'=>[ 'app_activate:' . APPS['table_app']['software_id']]], function () {
            Route::get('index', [TableController::class, 'index'])->name('index');
            Route::get('list', [TableController::class, 'list'])->name('list');
            Route::post('store', [TableController::class, 'store'])->name('store');
            Route::get('edit/{id}', [TableController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [TableController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [TableController::class, 'delete'])->name('delete');
            Route::get('status/{id}/{status}', [TableController::class, 'status'])->name('status');
        });

        Route::group(['prefix' => 'kitchen', 'as' => 'kitchen.','middleware'=>[ 'app_activate:' . APPS['kitchen_app']['software_id']]], function () {
            Route::get('list', [KitchenController::class, 'list'])->name('list');
            Route::get('add-new', [KitchenController::class, 'index'])->name('add-new');
            Route::post('add-new', [KitchenController::class, 'store']);
            Route::get('edit/{id}', [KitchenController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [KitchenController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [KitchenController::class, 'delete'])->name('delete');
            Route::get('status/{id}/{status}', [KitchenController::class, 'status'])->name('status');
        });

        Route::group(['prefix' => 'promotion', 'as' => 'promotion.','middleware'=>[ 'app_activate:' . APPS['table_app']['software_id']]], function () {
            Route::get('create', [BranchPromotionController::class, 'create'])->name('create');
            Route::post('store', [BranchPromotionController::class, 'store'])->name('store');
            Route::get('edit/{id}', [BranchPromotionController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [BranchPromotionController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [BranchPromotionController::class, 'delete'])->name('delete');
            Route::get('status/{id}/{status}', [BranchPromotionController::class, 'status'])->name('status');
        });

        Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
            Route::get('list', [ProductController::class, 'list'])->name('list');
            Route::get('set-price/{id}', [ProductController::class, 'setPriceIndex'])->name('set-price');
            Route::post('set-price-update/{id}', [ProductController::class, 'setPriceUpdate'])->name('set-price-update');
            Route::get('status/{id}/{status}', [ProductController::class, 'status'])->name('status');
        });

        Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.'], function () {
            Route::get('index', [BusinessSettingsController::class, 'branchIndex'])->name('index');
            Route::post('update', [BusinessSettingsController::class, 'settingsUpdate'])->name('update');

        });

        Route::get('verify-offline-payment/quick-view-details', [OrderController::class, 'offlineViewDetails'])->name('offline-modal-view');
        Route::get('verify-offline-payment/{status}', [OrderController::class, 'offlineOrderList'])->name('verify-offline-payment');

    });
});


