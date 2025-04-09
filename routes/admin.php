<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\AddonController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BusinessSettingsController;
use App\Http\Controllers\Admin\BranchPromotionController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ConversationController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CuisineController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\VendorController;

use App\Http\Controllers\Admin\CustomerWalletController;
use App\Http\Controllers\Admin\CustomRoleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DatabaseSettingsController;
use App\Http\Controllers\Admin\DeliveryManController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\KitchenController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\LocationSettingsController;
use App\Http\Controllers\Admin\LoyaltyPointController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OfflinePaymentMethodController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\POSController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\QRCodeController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewsController;
use App\Http\Controllers\Admin\SMSModuleController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\TableOrderController;
use App\Http\Controllers\Admin\TimeScheduleController;
use App\Http\Controllers\Admin\WalletBonusController;
use App\Http\Controllers\Admin\LoginSetupController;
use App\Http\Controllers\Admin\DeliveryChargeSetupController;
use App\Http\Controllers\Admin\ManageGroupController;
use App\Http\Controllers\Admin\ManageDayDelivery;
use App\Http\Controllers\Admin\PlaceOrderController;
use App\Http\Controllers\Admin\InvoiceController;


Route::group(['namespace' => 'Admin', 'as' => 'admin.'], function () {

    Route::resource('groups', ManageGroupController::class);
    Route::controller(ManageDayDelivery::class)->group(function(){
        Route::get('/list','index')->name('day_delivery_list');
        Route::get('/create','create')->name('day_delivery_create');
        Route::post('/day-delivery-save','store')->name('day_delivery_store');
        Route::get('/day-delivery-edit/{id}','edit')->name('day_delivery_edit');
        Route::post('/day-delivery-delete','destroy')->name('day_delivery_destroy');

    });

    Route::get("/next-day-orders",[PlaceOrderController::class,'nextDayOrders'])->name("nextDayOrders");
    Route::get("/invoices",[InvoiceController::class,'index'])->name("invoice_index");
    Route::get("/invoice-view",[InvoiceController::class,'viewInvoice'])->name("invoice_viewInvoice");




    Route::get('lang/{locale}', [LanguageController::class, 'lang'])->name('lang');

    Route::group(['middleware' => ['app_activate:get_from_route']], function () {
        Route::get('app-activate/{app_id}', [SystemController::class, 'appActivate'])->name('app-activate');
        Route::post('app-activate/{app_id}', [SystemController::class, 'activationSubmit']);
    });

    Route::group(['namespace' => 'Auth', 'prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::get('/code/captcha/{tmp}', [LoginController::class, 'captcha'])->name('default-captcha');
        Route::get('login', [LoginController::class, 'login'])->name('login');
        Route::post('login', [LoginController::class, 'submit'])->middleware('actch');
        Route::get('logout', [LoginController::class, 'logout'])->name('logout');
    });

    Route::group(['middleware' => ['admin']], function () {
        Route::get('/fcm/{id}', [DashboardController::class, 'fcm'])->name('dashboard');     //test route
        Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
        Route::post('order-stats', [DashboardController::class, 'orderStats'])->name('order-stats');
        Route::get('settings', [SystemController::class, 'settings'])->name('settings');
        Route::post('settings', [SystemController::class, 'settingsUpdate']);
        Route::post('settings-password', [SystemController::class, 'settingsPasswordUpdate'])->name('settings-password');
        Route::get('/get-restaurant-data', [SystemController::class, 'restaurantData'])->name('get-restaurant-data');
        Route::get('order-statistics', [DashboardController::class, 'orderStatistics'])->name('order-statistics');
        Route::get('earning-statistics', [DashboardController::class, 'earningStatistics'])->name('earning-statistics');

        Route::group(['prefix' => 'custom-role', 'as' => 'custom-role.', 'middleware' => ['module:user_management']], function () {
            Route::get('create', [CustomRoleController::class, 'create'])->name('create');
            Route::post('create',  [CustomRoleController::class, 'store'])->name('store');
            Route::get('update/{id}',  [CustomRoleController::class, 'edit'])->name('update');
            Route::post('update/{id}',  [CustomRoleController::class, 'update']);
            Route::delete('delete',  [CustomRoleController::class, 'delete'])->name('delete');
            Route::get('excel-export',  [CustomRoleController::class, 'excelExport'])->name('excel-export');
            Route::get('change-status/{id}',  [CustomRoleController::class, 'changeStatus'])->name('change-status');
        });

        Route::group(['prefix' => 'employee', 'as' => 'employee.', 'middleware' => ['module:user_management']], function () {
            Route::get('add-new', [EmployeeController::class, 'index'])->name('add-new');
            Route::post('add-new', [EmployeeController::class, 'store']);
            Route::get('list', [EmployeeController::class, 'list'])->name('list');
            Route::get('update/{id}', [EmployeeController::class, 'edit'])->name('update');
            Route::post('update/{id}', [EmployeeController::class, 'update']);
            Route::get('status/{id}/{status}', [EmployeeController::class, 'status'])->name('status');
            Route::delete('delete', [EmployeeController::class, 'delete'])->name('delete');
            Route::get('excel-export', [EmployeeController::class, 'exportExcel'])->name('excel-export');
        });

        Route::group(['prefix' => 'pos', 'as' => 'pos.', 'middleware' => ['module:pos_management']], function () {
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

        Route::group(['prefix' => 'table/order', 'as' => 'table.order.', 'middleware' => ['module:order_management', 'app_activate:' . APPS['table_app']['software_id']]], function () {
            Route::get('list/{status}', [TableOrderController::class, 'orderList'])->name('list');
            Route::get('details/{id}', [TableOrderController::class, 'orderDetails'])->name('details');
            Route::get('running', [TableOrderController::class, 'tableRunningOrder'])->name('running');
            Route::get('branch/table/{id}', [TableOrderController::class, 'branch_table_list'])->name('branch.table');
            Route::get('running/list', [TableOrderController::class, 'table_running_order_list'])->name('running.list');
            Route::get('running/invoice', [TableOrderController::class, 'runningOrderInvoice'])->name('running.invoice');
            Route::get('branch-filter/{branch_id}', [TableOrderController::class, 'branchFilter'])->name('branch-filter');
            Route::get('export-excel', [TableOrderController::class, 'exportExcel'])->name('export-excel');
            Route::get('tables-by-branch/{branchId}', [TableOrderController::class, 'getTablesByBranch'])->name('tables-by-branch');
        });

        Route::group(['prefix' => 'banner', 'as' => 'banner.', 'middleware' => ['module:promotion_management']], function () {
            Route::get('add-new', [BannerController::class, 'index'])->name('add-new');
            Route::post('store', [BannerController::class, 'store'])->name('store');
            Route::get('edit/{id}', [BannerController::class, 'edit'])->name('edit');
            Route::put('update/{id}', [BannerController::class, 'update'])->name('update');
            Route::get('list', [BannerController::class, 'list'])->name('list');
            Route::get('status/{id}/{status}', [BannerController::class, 'status'])->name('status');
            Route::delete('delete/{id}', [BannerController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'attribute', 'as' => 'attribute.', 'middleware' => ['module:product_management']], function () {
            Route::get('add-new', 'AttributeController@index')->name('add-new');
            Route::post('store', 'AttributeController@store')->name('store');
            Route::get('edit/{id}', 'AttributeController@edit')->name('edit');
            Route::post('update/{id}', 'AttributeController@update')->name('update');
            Route::delete('delete/{id}', 'AttributeController@delete')->name('delete');
        });

        Route::group(['prefix' => 'branch', 'as' => 'branch.', 'middleware' => ['module:system_management']], function () {
            Route::get('add-new', [BranchController::class, 'index'])->name('add-new');
            Route::post('store', [BranchController::class, 'store'])->name('store');
            Route::get('edit/{id}', [BranchController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [BranchController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [BranchController::class, 'delete'])->name('delete');
            Route::get('status/{id}/{status}', [BranchController::class, 'status'])->name('status');
            Route::get('list', [BranchController::class, 'list'])->name('list');
        });

        Route::group(['prefix' => 'addon', 'as' => 'addon.', 'middleware' => ['module:product_management']], function () {
            Route::get('add-new', [AddonController::class, 'index'])->name('add-new');
            Route::post('store', [AddonController::class, 'store'])->name('store');
            Route::get('edit/{id}', [AddonController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [AddonController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [AddonController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'delivery-man', 'as' => 'delivery-man.', 'middleware' => ['module:user_management']], function () {
            Route::get('add', [DeliveryManController::class, 'index'])->name('add');
            Route::post('store', [DeliveryManController::class, 'store'])->name('store');
            Route::get('list', [DeliveryManController::class, 'list'])->name('list');
            Route::get('preview/{id}', [DeliveryManController::class, 'preview'])->name('preview');
            Route::get('edit/{id}', [DeliveryManController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [DeliveryManController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [DeliveryManController::class, 'delete'])->name('delete');
            Route::get('ajax-is-active', [DeliveryManController::class, 'ajaxIsActive'])->name('ajax-is-active');
            Route::get('excel-export', [DeliveryManController::class, 'excelExport'])->name('excel-export');
            Route::get('pending/list', [DeliveryManController::class, 'pendingList'])->name('pending');
            Route::get('denied/list', [DeliveryManController::class, 'deniedList'])->name('denied');
            Route::get('update-application/{id}/{status}', [DeliveryManController::class, 'update_application'])->name('application');
            Route::get('details/{id}', [DeliveryManController::class, 'details'])->name('details');
            Route::get('order-excel-export', [DeliveryManController::class, 'orderExcelExport'])->name('order-excel-export');


            Route::group(['prefix' => 'reviews', 'as' => 'reviews.'], function () {
                Route::get('list', [DeliveryManController::class, 'reviewsList'])->name('list');
            });
        });

        Route::group(['prefix' => 'notification', 'as' => 'notification.', 'middleware' => ['module:promotion_management']], function () {
            Route::get('add-new', [NotificationController::class, 'index'])->name('add-new');
            Route::post('store', [NotificationController::class, 'store'])->name('store');
            Route::get('edit/{id}', [NotificationController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [NotificationController::class, 'update'])->name('update');
            Route::get('status/{id}/{status}', [NotificationController::class, 'status'])->name('status');
            Route::delete('delete/{id}', [NotificationController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'product', 'as' => 'product.', 'middleware' => ['module:product_management']], function () {
            Route::get('add-new', [ProductController::class, 'index'])->name('add-new');
            Route::post('variant-combination', [ProductController::class, 'variantCombination'])->name('variant-combination');
            Route::post('store', [ProductController::class, 'store'])->name('store');
            Route::get('edit/{id}', [ProductController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [ProductController::class, 'update'])->name('update');
            Route::get('list', [ProductController::class, 'list'])->name('list');
            Route::get('excel-import', [ProductController::class, 'excelImport'])->name('excel-import');
            Route::delete('delete/{id}', [ProductController::class, 'delete'])->name('delete');
            Route::get('status/{id}/{status}', [ProductController::class, 'status'])->name('status');
            Route::post('search', [ProductController::class, 'search'])->name('search');
            Route::get('bulk-import', [ProductController::class, 'bulkImportIndex'])->name('bulk-import');
            Route::post('bulk-import', [ProductController::class, 'bulkImportData']);
            Route::get('bulk-export', [ProductController::class, 'bulkExportIndex'])->name('bulk-export');
            Route::post('bulk-export', [ProductController::class, 'bulkExportData']);
            Route::get('view/{id}', [ProductController::class, 'view'])->name('view');
            Route::get('get-categories', [ProductController::class, 'getCategories'])->name('get-categories');
            Route::get('recommended/{id}/{status}', [ProductController::class, 'recommended'])->name('recommended');
        });

        Route::group(['prefix' => 'orders', 'as' => 'orders.', 'middleware' => ['module:order_management']], function () {
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

        Route::group(['prefix' => 'category', 'as' => 'category.', 'middleware' => ['module:product_management']], function () {
            Route::get('add', [CategoryController::class, 'index'])->name('add');
            Route::get('add-sub-category', [CategoryController::class, 'subIndex'])->name('add-sub-category');
            Route::post('store', [CategoryController::class, 'store'])->name('store');
            Route::get('edit/{id}', [CategoryController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [CategoryController::class, 'update'])->name('update');
            Route::post('store', [CategoryController::class, 'store'])->name('store');
            Route::get('status/{id}/{status}', [CategoryController::class, 'status'])->name('status');
            Route::delete('delete/{id}', [CategoryController::class, 'delete'])->name('delete');
            Route::post('search', [CategoryController::class, 'search'])->name('search');
            Route::get('priority', [CategoryController::class, 'priority'])->name('priority');
        });

        Route::group(['prefix' => 'cuisine', 'as' => 'cuisine.', 'middleware' => ['module:product_management']], function () {
            Route::get('add', [CuisineController::class, 'index'])->name('add');
            Route::post('store', [CuisineController::class, 'store'])->name('store');
            Route::get('edit/{id}', [CuisineController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [CuisineController::class, 'update'])->name('update');
            Route::get('status/{id}/{status}', [CuisineController::class, 'status'])->name('status');
            Route::get('feature/{id}/{status}', [CuisineController::class, 'featureStatus'])->name('feature');
            Route::delete('delete/{id}', [CuisineController::class, 'delete'])->name('delete');
            Route::get('priority', [CuisineController::class, 'priority'])->name('priority');
        });

        Route::group(['prefix' => 'message', 'as' => 'message.', 'middleware' => ['module:help_and_support_management']], function () {
            Route::get('list',  [ConversationController::class, 'list'])->name('list');
            Route::post('update-fcm-token',  [ConversationController::class, 'updateFcmToken'])->name('update_fcm_token');
            Route::get('get-firebase-config',  [ConversationController::class, 'getFirebaseConfig'])->name('get_firebase_config');
            Route::get('get-conversations',  [ConversationController::class, 'getConversations'])->name('get_conversations');
            Route::post('store/{user_id}',  [ConversationController::class, 'store'])->name('store');
            Route::get('view/{user_id}',  [ConversationController::class, 'view'])->name('view');
        });

        Route::group(['prefix' => 'reviews', 'as' => 'reviews.', 'middleware' => ['module:product_management']], function () {
            Route::get('list', [ReviewsController::class, 'list'])->name('list');
        });

        Route::group(['prefix' => 'coupon', 'as' => 'coupon.', 'middleware' => ['module:promotion_management']], function () {
            Route::get('add-new', [CouponController::class, 'index'])->name('add-new');
            Route::post('store', [CouponController::class, 'store'])->name('store');
            Route::get('update/{id}', [CouponController::class, 'edit'])->name('update');
            Route::post('update/{id}', [CouponController::class, 'update']);
            Route::get('status/{id}/{status}', [CouponController::class, 'status'])->name('status');
            Route::delete('delete/{id}', [CouponController::class, 'delete'])->name('delete');
            Route::get('generate-coupon-code', [CouponController::class, 'generateCouponCode'])->name('generate-coupon-code');
            Route::get('coupon-details', [CouponController::class, 'couponDetails'])->name('coupon-details');
        });

        Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.', 'middleware' => ['module:system_management']], function () {

            Route::group(['prefix' => 'email-setup'], function () {
                Route::get('{type}/{tab?}', [EmailTemplateController::class, 'emailIndex'])->name('email-setup');
                Route::POST('update/{type}/{tab?}', [EmailTemplateController::class, 'updateEmailIndex'])->name('email-setup.update');
                Route::get('{type}/{tab}/{status}', [EmailTemplateController::class, 'updateEmailStatus'])->name('email-status');
            });

            Route::group(['prefix' => 'restaurant', 'as' => 'restaurant.'], function () {
                Route::get('restaurant-setup', [BusinessSettingsController::class, 'restaurantIndex'])->name('restaurant-setup')->middleware('actch');
                Route::post('update-setup', [BusinessSettingsController::class, 'restaurantSetup'])->name('update-setup')->middleware('actch');
                Route::post('delivery-fee-setup', [BusinessSettingsController::class, 'updateDeliveryFee'])->name('update-delivery-fee')->middleware('actch');
                Route::get('main-branch-setup', [BusinessSettingsController::class, 'mainBranchSetup'])->name('main-branch-setup')->middleware('actch');

                Route::get('delivery-fee-setup', [DeliveryChargeSetupController::class, 'deliveryFeeSetup'])->name('delivery-fee-setup')->middleware('actch');
                Route::post('store-kilometer-wise-delivery-charge', [DeliveryChargeSetupController::class, 'storeKilometerWiseDeliveryCharge'])->name('store-kilometer-wise-delivery-charge')->middleware('actch');
                Route::post('store-delivery-wise-delivery-charge', [DeliveryChargeSetupController::class, 'StoreAreaWiseDeliveryCharge'])->name('store-delivery-wise-delivery-charge')->middleware('actch');
                Route::post('store-fixed-delivery-charge', [DeliveryChargeSetupController::class, 'storeFixedDeliveryCharge'])->name('store-fixed-delivery-charge')->middleware('actch');
                Route::post('change-delivery-charge-type', [DeliveryChargeSetupController::class, 'changeDeliveryChargeType'])->name('change-delivery-charge-type')->middleware('actch');
                Route::delete('delete-area-delivery-charge/{id}', [DeliveryChargeSetupController::class, 'deleteAreaDeliveryCharge'])->name('delete-area-delivery-charge');
                Route::get('edit-area-delivery-charge/{id}', [DeliveryChargeSetupController::class, 'editAreaDeliveryCharge'])->name('edit-area-delivery-charge');
                Route::post('update-area-delivery-charge/{id}', [DeliveryChargeSetupController::class, 'updateAreaDeliveryCharge'])->name('update-area-delivery-charge');
                Route::get('export-area-delivery-charge/{id}', [DeliveryChargeSetupController::class, 'exportAreaDeliveryCharge'])->name('export-area-delivery-charge');
                Route::post('import-area-delivery-charge/{id}', [DeliveryChargeSetupController::class, 'importAreaDeliveryCharge'])->name('import-area-delivery-charge');
                Route::get('check-distance-based-delivery', [DeliveryChargeSetupController::class, 'checkDistanceBasedDelivery'])->name('check-distance-based-delivery');

                Route::get('time-schedule', [TimeScheduleController::class, 'timeScheduleIndex'])->name('time_schedule_index');
                Route::post('add-time-schedule', [TimeScheduleController::class, 'addSchedule'])->name('time_schedule_add');
                Route::get('time-schedule-remove', [TimeScheduleController::class, 'removeSchedule'])->name('time_schedule_remove');

                Route::get('location-setup', [LocationSettingsController::class, 'locationIndex'])->name('location-setup')->middleware('actch');
                Route::post('update-location', [LocationSettingsController::class,'locationSetup'])->name('update-location')->middleware('actch');

                Route::get('cookies-setup', [BusinessSettingsController::class, 'cookiesSetup'])->name('cookies-setup');
                Route::post('cookies-setup-update', [BusinessSettingsController::class, 'cookiesSetupUpdate'])->name('cookies-setup-update');

                Route::get('otp-setup', [BusinessSettingsController::class, 'OTPSetup'])->name('otp-setup');
                Route::post('otp-setup-update', [BusinessSettingsController::class, 'OTPSetupUpdate'])->name('otp-setup-update');

                Route::get('customer-settings', [BusinessSettingsController::class, 'customerSettings'])->name('customer.settings');
                Route::post('customer-settings-update', [BusinessSettingsController::class, 'customerSettingsUpdate'])->name('customer.settings.update');

                Route::get('order-index', [BusinessSettingsController::class, 'orderIndex'])->name('order-index');
                Route::post('order-update', [BusinessSettingsController::class, 'orderUpdate'])->name('order-update');

                Route::get('qrcode-index', [QRCodeController::class, 'index'])->name('qrcode-index');
                Route::post('qrcode/store', [QRCodeController::class, 'store'])->name('qrcode.store');
                Route::get('qrcode/download-pdf', [QRCodeController::class, 'downloadPdf'])->name('qrcode.download-pdf');
                Route::get('qrcode/print', [QRCodeController::class, 'printQRCode'])->name('qrcode.print');

                Route::get('product-index', [BusinessSettingsController::class, 'productIndex'])->name('product-index');
                Route::post('search-placeholder-store', [BusinessSettingsController::class, 'searchPlaceholderStore'])->name('search-placeholder-store');
                Route::get('search-placeholder-status/{id}', [BusinessSettingsController::class, 'searchPlaceholderStatus'])->name('search-placeholder-status');
                Route::delete('search-placeholder-delete/{id}', [BusinessSettingsController::class, 'searchPlaceholderDelete'])->name('search-placeholder-delete');

                Route::post('maintenance-mode-setup', [BusinessSettingsController::class, 'maintenanceModeSetup'])->name('maintenance-mode-setup')->middleware('actch');

                Route::get('login-setup', [LoginSetupController::class, 'loginSetup'])->name('login-setup');
                Route::post('login-setup-update', [LoginSetupController::class, 'loginSetupUpdate'])->name('login-setup-update');
                Route::get('check-active-sms-gateway', [LoginSetupController::class, 'checkActiveSMSGateway'])->name('check-active-sms-gateway');
                Route::get('check-active-social-media', [LoginSetupController::class, 'checkActiveSocialMedia'])->name('check-active-social-media');

            });

            Route::group(['prefix' => 'web-app', 'as' => 'web-app.', 'middleware' => ['module:system_management']], function () {
                Route::get('third-party/mail-config', [BusinessSettingsController::class, 'mailIndex'])->name('mail-config')->middleware('actch');
                Route::post('third-party/mail-config', [BusinessSettingsController::class, 'mailConfig'])->middleware('actch');
                Route::post('mail-send', [BusinessSettingsController::class, 'mailSend'])->name('mail-send');

                Route::get('third-party/sms-module', [SMSModuleController::class, 'smsIndex'])->name('sms-module');
                Route::post('sms-module-update/{sms_module}', [SMSModuleController::class, 'smsUpdate'])->name('sms-module-update');

                Route::get('third-party/payment-method', [BusinessSettingsController::class, 'paymentIndex'])->name('payment-method')->middleware('actch');
                Route::post('payment-method-update/{payment_method}', [BusinessSettingsController::class, 'payment_update'])->name('payment-method-update')->middleware('actch');
                Route::post('payment-config-update', [BusinessSettingsController::class, 'paymentConfigUpdate'])->name('payment-config-update')->middleware('actch');
                Route::post('payment-method-status', [BusinessSettingsController::class, 'paymentMethodStatus'])->name('payment-method-status')->middleware('actch');

                Route::group(['prefix' => 'system-setup', 'as' => 'system-setup.'], function () {
                    Route::get('app-setting', [BusinessSettingsController::class, 'appSettingIndex'])->name('app_setting');
                    Route::post('app-setting', [BusinessSettingsController::class, 'appSettingUpdate']);

                    Route::get('db-index', [DatabaseSettingsController::class, 'databaseIndex'])->name('db-index');
                    Route::post('db-clean', [DatabaseSettingsController::class, 'cleanDatabase'])->name('clean-db');

                    Route::get('firebase-message-config', [BusinessSettingsController::class, 'firebaseMessageConfigIndex'])->name('firebase_message_config_index');

                    Route::group(['prefix' => 'language', 'as' => 'language.', 'middleware' => []], function () {
                        Route::get('', [LanguageController::class, 'index'])->name('index');
                        Route::post('add-new', [LanguageController::class, 'store'])->name('add-new');
                        Route::get('update-status', [LanguageController::class, 'updateStatus'])->name('update-status');
                        Route::get('update-default-status', [LanguageController::class, 'updateDefaultStatus'])->name('update-default-status');
                        Route::post('update', [LanguageController::class, 'update'])->name('update');
                        Route::get('translate/{lang}', [LanguageController::class, 'translate'])->name('translate');
                        Route::post('translate-submit/{lang}', [LanguageController::class, 'translateSubmit'])->name('translate-submit');
                        Route::post('remove-key/{lang}', [LanguageController::class, 'translateKeyRemove'])->name('remove-key');
                        Route::get('delete/{lang}', [LanguageController::class, 'delete'])->name('delete');
                    });
                });

                Route::group(['prefix' => 'third-party', 'as' => 'third-party.', 'middleware' => ['module:system_management']], function () {
                    Route::get('map-api-settings', [BusinessSettingsController::class, 'mapApiSettings'])->name('map_api_settings');
                    Route::post('map-api-settings', [BusinessSettingsController::class, 'updateMapApi']);

                    Route::get('fetch', [BusinessSettingsController::class, 'fetch'])->name('fetch');
                    Route::post('social-media-store', [BusinessSettingsController::class, 'socialMediaStore'])->name('social-media-store');
                    Route::post('social-media-edit', [BusinessSettingsController::class, 'socialMediaEdit'])->name('social-media-edit');
                    Route::post('social-media-update', [BusinessSettingsController::class, 'socialMediaUpdate'])->name('social-media-update');
                    Route::post('social-media-delete', [BusinessSettingsController::class, 'socialMediaDelete'])->name('social-media-delete');
                    Route::get('social-media-status-update', [BusinessSettingsController::class, 'socialMediaStatusUpdate'])->name('social-media-status-update');

                    Route::get('recaptcha', [BusinessSettingsController::class, 'recaptchaIndex'])->name('recaptcha_index');
                    Route::post('recaptcha-update', [BusinessSettingsController::class, 'recaptchaUpdate'])->name('recaptcha_update');

                    Route::get('fcm-index', [BusinessSettingsController::class, 'fcmIndex'])->name('fcm-index')->middleware('actch');
                    Route::get('fcm-config', [BusinessSettingsController::class, 'fcmConfig'])->name('fcm-config')->middleware('actch');
                    Route::post('update-fcm', [BusinessSettingsController::class, 'updateFcm'])->name('update-fcm')->middleware('actch');

                    Route::get('social-login', [BusinessSettingsController::class, 'socialLogin'])->name('social-login');
                    Route::post('update-apple-login', [BusinessSettingsController::class, 'updateAppleLogin'])->name('update-apple-login');

                    Route::get('chat', [BusinessSettingsController::class, 'chatIndex'])->name('chat');
                    Route::post('chat-update/{name}', [BusinessSettingsController::class, 'chatUpdate'])->name('chat-update');

                    Route::group(['prefix' => 'offline-payment', 'as' => 'offline-payment.'], function(){
                        Route::get('list', [OfflinePaymentMethodController::class, 'list'])->name('list');
                        Route::get('add', [OfflinePaymentMethodController::class, 'add'])->name('add');
                        Route::post('store', [OfflinePaymentMethodController::class, 'store'])->name('store');
                        Route::get('edit/{id}', [OfflinePaymentMethodController::class, 'edit'])->name('edit');
                        Route::post('update/{id}', [OfflinePaymentMethodController::class, 'update'])->name('update');
                        Route::get('status/{id}/{status}', [OfflinePaymentMethodController::class, 'status'])->name('status');
                        Route::post('delete', [OfflinePaymentMethodController::class, 'delete'])->name('delete');
                    });

                    Route::post('firebase-otp-verification-update', [BusinessSettingsController::class, 'firebaseOTPVerificationUpdate'])->name('firebase-otp-verification-update');
                    Route::get('firebase-otp-verification', [BusinessSettingsController::class, 'firebaseOTPVerification'])->name('firebase-otp-verification');
                    Route::get('marketing-tools', [BusinessSettingsController::class, 'marketingTools'])->name('marketing-tools');
                    Route::post('update-marketing-tools/{type}', [BusinessSettingsController::class, 'updateMarketingTools'])->name('update-marketing-tools');

                });

                Route::group(['as' => 'third-party.', 'middleware' => ['module:system_management']], function () {
                    Route::get('social-media', [BusinessSettingsController::class, 'socialMedia'])->name('social-media');
                });

            });

            Route::post('update-fcm-messages', [BusinessSettingsController::class, 'updateFcmMessages'])->name('update-fcm-messages');

            Route::group(['prefix' => 'page-setup', 'as' => 'page-setup.', 'middleware' => ['module:system_management']], function () {
                Route::get('terms-and-conditions', [BusinessSettingsController::class, 'termsAndConditions'])->name('terms-and-conditions')->middleware('actch');
                Route::post('terms-and-conditions', [BusinessSettingsController::class, 'termsAndConditionsUpdate'])->middleware('actch');

                Route::get('privacy-policy', [BusinessSettingsController::class, 'privacyPolicy'])->name('privacy-policy')->middleware('actch');
                Route::post('privacy-policy', [BusinessSettingsController::class, 'privacyPolicyUpdate'])->middleware('actch');

                Route::get('about-us', [BusinessSettingsController::class, 'aboutUs'])->name('about-us')->middleware('actch');
                Route::post('about-us', [BusinessSettingsController::class, 'aboutUsUpdate'])->middleware('actch');

                Route::get('return-page', [BusinessSettingsController::class, 'returnPageIndex'])->name('return_page_index');
                Route::post('return-page-update', [BusinessSettingsController::class, 'returnPageUpdate'])->name('return_page_update');

                Route::get('refund-page', [BusinessSettingsController::class, 'refundPageIndex'])->name('refund_page_index');
                Route::post('refund-page-update', [BusinessSettingsController::class, 'refundPageUpdate'])->name('refund_page_update');

                Route::get('cancellation-page', [BusinessSettingsController::class, 'cancellationPageIndex'])->name('cancellation_page_index');
                Route::post('cancellation-page-update', [BusinessSettingsController::class, 'cancellationPageUpdate'])->name('cancellation_page_update');

                Route::get('faq-page', [BusinessSettingsController::class, 'faq_page_index'])->name('faq-page-index');
            });
            Route::get('currency-position/{position}', [BusinessSettingsController::class, 'currencySymbolPosition'])->name('currency-position');
            Route::get('maintenance-mode', [BusinessSettingsController::class, 'maintenanceMode'])->name('maintenance-mode');

        });

        Route::group(['prefix' => 'report', 'as' => 'report.', 'middleware' => ['module:report_and_analytics_management']], function () {
            Route::get('order', [ReportController::class, 'orderIndex'])->name('order');
            Route::get('earning', [ReportController::class, 'earningIndex'])->name('earning');
            Route::post('set-date', [ReportController::class, 'setDate'])->name('set-date');
            Route::get('deliveryman-report', [ReportController::class, 'deliverymanReport'])->name('deliveryman_report');
            Route::post('deliveryman-filter', [ReportController::class, 'deliverymanFilter'])->name('deliveryman_filter');
            Route::get('product-report', [ReportController::class, 'productReport'])->name('product-report');
            Route::post('product-report-filter', [ReportController::class, 'productReportFilter'])->name('product-report-filter');
            Route::get('export-product-report', [ReportController::class, 'exportProductReport'])->name('export-product-report');
            Route::get('sale-report', [ReportController::class, 'saleReport'])->name('sale-report');
            Route::post('sale-report-filter', [ReportController::class, 'saleFilter'])->name('sale-report-filter');
            Route::get('export-sale-report', [ReportController::class, 'exportSaleReport'])->name('export-sale-report');
        });


        Route::group(['prefix' => 'vendor', 'as' => 'vendor.', 'middleware' => ['actch', 'module:user_management']], function () {
            Route::get('list', [VendorController::class, 'vendorList'])->name('list');
            Route::get('view/{user_id}', [VendorController::class, 'view'])->name('view');
            Route::get('update-status/{id}', [VendorController::class, 'updateStatus'])->name('update_status');
            Route::delete('delete', [VendorController::class, 'destroy'])->name('destroy');

            Route::get("/vendor-add",[VendorController::class,"saveViewPage"])->name('vendor_add');
            Route::post("/store-vendor",[VendorController::class,"saveVendor"])->name('savevendor');
            Route::get("/vendor-edit/{id}",[VendorController::class,"editVendor"])->name('vendor_edit');



            Route::post('get-user-info', [VendorController::class, 'getUserInfo'])->name('get_user_info');
        });



        // -------------------------------------
        Route::group(['prefix' => 'customer', 'as' => 'customer.', 'middleware' => ['actch', 'module:user_management']], function () {
            Route::post('add-point/{id}', [CustomerController::class, 'addLoyaltyPoint'])->name('add-point');
            Route::get('set-point-modal-data/{id}', [CustomerController::class, 'setPointModalData'])->name('set-point-modal-data');
            Route::get('list', [CustomerController::class, 'customerList'])->name('list');
            Route::get('view/{user_id}', [CustomerController::class, 'view'])->name('view');
            Route::post('AddPoint/{id}', [CustomerController::class, 'AddPoint'])->name('AddPoint');
            Route::get('transaction', [CustomerController::class, 'transaction'])->name('transaction');
            Route::get('transaction/{id}', [CustomerController::class, 'customerTransaction'])->name('customer_transaction');
            Route::get('subscribed-emails', [CustomerController::class, 'subscribedEmails'])->name('subscribed_emails');
            Route::get('subscribed-emails-export', [CustomerController::class, 'subscribedEmailsExport'])->name('subscribed_emails_export');
            Route::get('update-status/{id}', [CustomerController::class, 'updateStatus'])->name('update_status');
            Route::delete('delete', [CustomerController::class, 'destroy'])->name('destroy');
            Route::get('excel-import', [CustomerController::class, 'excelImport'])->name('excel_import');

            Route::get("/customer-add",[CustomerController::class,"saveViewPage"])->name('customer_add');
            Route::post("/store-customer",[CustomerController::class,"saveCustomer"])->name('saveCustomer');
            Route::get("/customer-edit/{id}",[CustomerController::class,"editCustomer"])->name('customer_edit');




            Route::get('chat', [CustomerController::class, 'chat'])->name('chat');
            Route::post('get-user-info', [CustomerController::class, 'getUserInfo'])->name('get_user_info');
            Route::post('message-notification', [CustomerController::class, 'messageNotification'])->name('message_notification');
            Route::post('chat-image-upload', [CustomerController::class, 'chatImageUpload'])->name('chat_image_upload');

            Route::get('settings', [CustomerController::class, 'settings'])->name('settings');
            Route::post('update-settings', [CustomerController::class, 'updateSettings'])->name('update-settings');

            Route::get('select-list', [CustomerWalletController::class, 'getCustomers'])->name('select-list');

            Route::get('loyalty-point/report', [LoyaltyPointController::class, 'report'])->name('loyalty-point.report');

            Route::group(['prefix' => 'wallet', 'as' => 'wallet.'], function () {
                Route::get('add-fund', [CustomerWalletController::class, 'addFundView'])->name('add-fund');
                Route::post('add-fund', [CustomerWalletController::class, 'addFund'])->name('add-fund-store');
                Route::get('report', [CustomerWalletController::class, 'report'])->name('report');

                Route::group(['prefix' => 'bonus', 'as' => 'bonus.'], function () {
                    Route::get('index', [WalletBonusController::class, 'index'])->name('index');
                    Route::post('store', [WalletBonusController::class, 'store'])->name('store');
                    Route::get('edit/{id}', [WalletBonusController::class, 'edit'])->name('edit');
                    Route::post('update/{id}', [WalletBonusController::class, 'update'])->name('update');
                    Route::get('status/{id}/{status}', [WalletBonusController::class, 'status'])->name('status');
                    Route::delete('delete/{id}', [WalletBonusController::class, 'delete'])->name('delete');
                });
            });

        });

        Route::group(['prefix' => 'kitchen', 'as' => 'kitchen.', 'middleware' => ['module:user_management', 'app_activate:' . APPS['kitchen_app']['software_id']]], function () {
            Route::get('add-new', [KitchenController::class, 'index'])->name('add-new');
            Route::post('add-new', [KitchenController::class, 'store']);
            Route::get('list', [KitchenController::class, 'list'])->name('list');
            Route::get('update/{id}', [KitchenController::class, 'edit'])->name('update');
            Route::post('update/{id}', [KitchenController::class, 'update']);
            Route::delete('delete/{id}', [KitchenController::class, 'delete'])->name('delete');
            Route::get('status/{id}/{status}', [KitchenController::class, 'status'])->name('status');
        });

        Route::group(['prefix' => 'table', 'as' => 'table.', 'middleware' => ['module:table_management', 'app_activate:' . APPS['table_app']['software_id']]], function () {
            Route::get('list', [TableController::class, 'list'])->name('list');
            Route::post('store', [TableController::class, 'store'])->name('store');
            Route::get('update/{id}', [TableController::class, 'edit'])->name('update');
            Route::post('update/{id}', [TableController::class, 'update']);
            Route::delete('delete/{id}', [TableController::class, 'delete'])->name('delete');
            Route::get('status/{id}/{status}', [TableController::class, 'status'])->name('status');
            Route::get('index', [TableController::class, 'index'])->name('index');
            Route::post('branch-table', [TableController::class, 'getTableListByBranch'])->name('branch-table');
        });

        Route::group(['prefix' => 'promotion', 'as' => 'promotion.', 'middleware' => ['module:table_management', 'app_activate:' . APPS['table_app']['software_id']]], function () {
            Route::get('create', [BranchPromotionController::class, 'create'])->name('create');
            Route::post('store',  [BranchPromotionController::class, 'store'])->name('store');
            Route::get('edit/{id}',  [BranchPromotionController::class, 'edit'])->name('edit');
            Route::post('update/{id}',  [BranchPromotionController::class, 'update'])->name('update');
            Route::delete('delete/{id}',  [BranchPromotionController::class, 'delete'])->name('delete');
            Route::get('branch/{id}',  [BranchPromotionController::class, 'branchWiseList'])->name('branch');
            Route::get('status/{id}/{status}',  [BranchPromotionController::class, 'status'])->name('status');
        });

        Route::group(['namespace' => 'System','prefix' => 'system-addon', 'as' => 'system-addon.', 'middleware'=>['module:user_management']], function () {
            Route::get('/', 'AddonController@index')->name('index');
            Route::post('publish', 'AddonController@publish')->name('publish');
            Route::post('activation', 'AddonController@activation')->name('activation');
            Route::post('upload', 'AddonController@upload')->name('upload');
            Route::post('delete', 'AddonController@delete_theme')->name('delete');
        });

        Route::get('verify-offline-payment/quick-view-details', [OfflinePaymentMethodController::class, 'quickViewDetails'])->name('offline-modal-view');
        Route::get('verify-offline-payment/{status}', [OfflinePaymentMethodController::class, 'offlinePaymentList'])->name('verify-offline-payment');

    });
});

