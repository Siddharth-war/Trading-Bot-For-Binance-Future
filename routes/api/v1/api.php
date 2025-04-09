<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\CustomerAuthController;
use App\Http\Controllers\Api\V1\Auth\DeliveryManLoginController;
use App\Http\Controllers\Api\V1\Auth\KitchenLoginController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\BranchController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ConfigController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\CouponController;
use App\Http\Controllers\Api\V1\CuisineController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\CustomerWalletController;
use App\Http\Controllers\Api\V1\DeliverymanController;
use App\Http\Controllers\Api\V1\DeliveryManReviewController;
use App\Http\Controllers\Api\V1\GuestUserController;
use App\Http\Controllers\Api\V1\KitchenController;
use App\Http\Controllers\Api\V1\LoyaltyPointController;
use App\Http\Controllers\Api\V1\MapApiController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OfflinePaymentMethodController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\TableConfigController;
use App\Http\Controllers\Api\V1\TableController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\WishlistController;

Route::group(['namespace' => 'Api\V1', 'middleware' => 'localization'], function () {

    Route::post('fcm-subscribe-to-topic', [CustomerController::class, 'fcmSubscribeToTopic']);

    Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {
        Route::post('registration', [CustomerAuthController::class, 'registration']);
        Route::post('login', [CustomerAuthController::class, 'login']);
        Route::post('social-customer-login', [CustomerAuthController::class, 'customerSocialLogin']);
        Route::post('check-phone', [CustomerAuthController::class, 'checkPhone']);
        Route::post('verify-phone', [CustomerAuthController::class, 'verifyPhone']);
        Route::post('check-email', [CustomerAuthController::class, 'checkEmail']);
        Route::post('verify-email', [CustomerAuthController::class, 'verifyEmail']);
        Route::post('firebase-auth-verify', [CustomerAuthController::class, 'firebaseAuthVerify']);
        Route::post('verify-otp', [CustomerAuthController::class, 'verifyOTP']);
        Route::post('registration-with-otp', [CustomerAuthController::class, 'registrationWithOTP']);
        Route::post('existing-account-check', [CustomerAuthController::class, 'existingAccountCheck']);
        Route::post('registration-with-social-media', [CustomerAuthController::class, 'registrationWithSocialMedia']);

        Route::post('forgot-password', [PasswordResetController::class, 'passwordResetRequest']);
        Route::post('verify-token', [PasswordResetController::class, 'verifyToken']);
        Route::put('reset-password', [PasswordResetController::class, 'resetPasswordSubmit']);

        Route::group(['prefix' => 'delivery-man'], function () {
            Route::post('register', [DeliveryManLoginController::class, 'registration']);
            Route::post('login', [DeliveryManLoginController::class, 'login']);
        });

        Route::group(['prefix' => 'kitchen', 'middleware' => 'app_activate:' . APPS['kitchen_app']['software_id']], function () {
            Route::post('login', [KitchenLoginController::class, 'login']);
            Route::post('logout', [KitchenLoginController::class, 'logout'])->middleware('auth:kitchen_api');
        });
    });

    Route::group(['prefix' => 'delivery-man', 'middleware' => 'deliveryman_is_active'], function () {
        Route::get('profile', [DeliverymanController::class, 'getProfile']);
        Route::put('update-profile', [DeliverymanController::class, 'updateProfile']);
        Route::get('current-orders', [DeliverymanController::class, 'getCurrentOrders']);
        Route::get('all-orders', [DeliverymanController::class, 'getAllOrders']);
        Route::post('record-location-data', [DeliverymanController::class, 'recordLocationData']);
        Route::get('order-delivery-history', [DeliverymanController::class, 'getOrderHistory']); // not used
        Route::put('update-order-status', [DeliverymanController::class, 'updateOrderStatus']);
        Route::put('update-payment-status', [DeliverymanController::class, 'orderPaymentStatusUpdate']);
        Route::get('order-details', [DeliverymanController::class, 'getOrderDetails']);
        Route::put('update-fcm-token', [DeliverymanController::class, 'updateFcmToken']);
        Route::get('order-model', [DeliverymanController::class, 'orderModel']);
        Route::get('order-statistics', [DeliverymanController::class, 'getOrderStatistics']);

        //delivery-man message
        Route::group(['prefix' => 'message'], function () {
            Route::post('get-message', [ConversationController::class, 'getOrderMessageForDm']);
            Route::post('send/{sender_type}', [ConversationController::class, 'storeMessageByOrder']);
        });

        Route::group(['prefix' => 'reviews', 'middleware' => ['auth:api']], function () {
            Route::get('/{delivery_man_id}', [DeliveryManReviewController::class, 'getReviews']); //not used
            Route::get('rating/{delivery_man_id}', [DeliveryManReviewController::class, 'getRating']); //not used
        });
    });

    Route::middleware('auth:api')->post('delivery-man/reviews/submit', [DeliveryManReviewController::class, 'submitReview']);
    Route::middleware('auth:api')->get('delivery-man/last-location', [DeliverymanController::class, 'getLastLocation']);

    Route::group(['prefix' => 'config'], function () {
        Route::get('/', [ConfigController::class, 'configuration']);
        Route::get('table', [TableConfigController::class, 'configuration']);
        Route::get('get-direction-api', [ConfigController::class, 'direction_api']);
        Route::get('delivery-fee', [ConfigController::class, 'deliveryFree']);
    });

    Route::group(['prefix' => 'products', 'middleware' => 'branch_adder'], function () {
        Route::get('latest', [ProductController::class, 'latestProducts']);
        Route::get('popular', [ProductController::class, 'popularProducts']);
        Route::get('set-menu', [ProductController::class, 'setMenus']);
        Route::post('search', [ProductController::class, 'searchedProducts']);
        Route::get('details/{id}', [ProductController::class, 'getProduct']);
        Route::get('related-products/{product_id}', [ProductController::class, 'relatedProducts']);
        Route::get('reviews/{product_id}', [ProductController::class, 'productReviews']);
        Route::get('rating/{product_id}', [ProductController::class, 'productRating']);
        Route::post('reviews/submit', [ProductController::class, 'submitProductReview'])->middleware('auth:api');
        Route::get('recommended', [ProductController::class, 'recommendedProducts']);
        Route::get('frequently-bought', [ProductController::class, 'frequentlyBoughtProducts']);
        Route::get('search-suggestion', [ProductController::class, 'searchSuggestion']);
        Route::post('change-branch', [ProductController::class, 'changeBranchProductUpdate']);
        Route::post('re-order', [ProductController::class, 'reOrderProducts']);
        Route::get('search-recommended', [ProductController::class, 'searchRecommendedData']);
    });

    Route::group(['prefix' => 'banners', 'middleware' => 'branch_adder'], function () {
        Route::get('/', [BannerController::class, 'getBanners']);
    });

    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', [NotificationController::class, 'getNotifications']);
    });

    Route::group(['prefix' => 'categories'], function () {
        Route::get('/', [CategoryController::class, 'getCategories']);
        Route::get('childes/{category_id}', [CategoryController::class, 'getChildes']);
        Route::get('products/{category_id}', [CategoryController::class, 'getProducts'])->middleware('branch_adder');
        Route::get('products/{category_id}/all', [CategoryController::class, 'getAllProducts'])->middleware('branch_adder');
    });

    Route::group(['prefix' => 'cuisine'], function () {
        Route::get('list', [CuisineController::class, 'getCuisines']);
    });

    Route::group(['prefix' => 'tag'], function () {
        Route::get('popular', [TagController::class, 'getPopularTags']);
    });

    Route::group(['prefix' => 'customer', 'middleware' => ['auth:api', 'is_active']], function () {
        Route::get('info', [CustomerController::class, 'info']);
        Route::put('update-profile', [CustomerController::class, 'updateProfile']);
        Route::post('verify-profile-info', [CustomerController::class, 'verifyProfileInfo']);
        Route::put('cm-firebase-token', [CustomerController::class, 'updateFirebaseToken'])->withoutMiddleware(['auth:api', 'is_active']);
        Route::get('transaction-history', [CustomerController::class, 'getTransactionHistory']);

        Route::group(['prefix' => 'address'], function () {
            Route::get('list', [CustomerController::class, 'addressList'])->withoutMiddleware(['auth:api', 'is_active']);
            Route::post('add', [CustomerController::class, 'addAddress'])->withoutMiddleware(['auth:api', 'is_active']);
            Route::put('update/{id}', [CustomerController::class, 'updateAddress'])->withoutMiddleware(['auth:api', 'is_active']);
            Route::delete('delete', [CustomerController::class, 'deleteAddress'])->withoutMiddleware(['auth:api', 'is_active']);
        });
        Route::get('last-ordered-address', [CustomerController::class, 'lastOrderedAddress']);

        Route::namespace('Auth')->group(function () {
            Route::delete('remove-account', [CustomerAuthController::class, 'remove_account']);
        });

        Route::group(['prefix' => 'order'], function () {
            Route::get('track', [OrderController::class, 'trackOrder'])->withoutMiddleware(['auth:api', 'is_active']);
            Route::post('place', [OrderController::class, 'placeOrder'])->withoutMiddleware(['auth:api', 'is_active']);
            Route::get('list', [OrderController::class, 'getOrderList'])->withoutMiddleware(['auth:api', 'is_active']);
            Route::get('details', [OrderController::class, 'getOrderDetails'])->withoutMiddleware(['auth:api', 'is_active']);
            Route::put('cancel', [OrderController::class, 'cancelOrder'])->withoutMiddleware(['auth:api', 'is_active']);
            Route::put('payment-method', [OrderController::class, 'updatePaymentMethod'])->withoutMiddleware(['auth:api', 'is_active']);
            Route::post('guest-track', [OrderController::class, 'guestTrackOrder'])->withoutMiddleware(['auth:api', 'is_active']);
            Route::post('details-guest', [OrderController::class, 'getGuestOrderDetails'])->withoutMiddleware(['auth:api', 'is_active']);
        });
        // Chatting
        Route::group(['prefix' => 'message'], function () {
            //customer-admin
            Route::get('list', [ConversationController::class, 'messageList']);
            Route::get('get-admin-message', [ConversationController::class, 'getAdminMessage']);
            Route::post('send-admin-message', [ConversationController::class, 'storeAdminMessage']);
            //customer-deliveryman
            Route::get('get-order-message', [ConversationController::class, 'getMessageByOrder']);
            Route::post('send/{sender_type}', [ConversationController::class, 'storeMessageByOrder']);
            Route::get('deliveryman-conversation-list', [ConversationController::class, 'getDeliverymanConversationList']);
        });

        Route::group(['prefix' => 'wish-list'], function () {
            Route::get('/', [WishlistController::class, 'wishlist'])->middleware('branch_adder');
            Route::post('add', [WishlistController::class, 'addToWishlist']);
            Route::delete('remove', [WishlistController::class, 'removeFromWishlist']);
        });

        Route::post('transfer-point-to-wallet', [CustomerWalletController::class, 'transferLoyaltyPointToWallet']);
        Route::get('wallet-transactions', [CustomerWalletController::class, 'walletTransactions']);
        Route::get('loyalty-point-transactions', [LoyaltyPointController::class, 'pointTransactions']);
        Route::get('bonus/list', [CustomerWalletController::class, 'walletBonusList']);

    });

    Route::group(['prefix' => 'coupon'], function () {
        Route::get('list', [CouponController::class, 'list']);
        Route::get('apply', [CouponController::class, 'apply']);
    });

    //map api
    Route::group(['prefix' => 'mapapi'], function () {
        Route::get('place-api-autocomplete', [MapApiController::class, 'placeApiAutoComplete']);
        Route::get('distance-api', [MapApiController::class, 'distanceApi']);
        Route::get('place-api-details', [MapApiController::class, 'placeApiDetails']);
        Route::get('geocode-api', [MapApiController::class, 'geocodeApi']);
    });

    Route::post('subscribe-newsletter', [CustomerController::class, 'subscribeNewsletter']);

    Route::get('pages', [PageController::class, 'index']);

    Route::group(['prefix' => 'table', 'middleware' => 'app_activate:' . APPS['table_app']['software_id']], function () {
        Route::get('list', [TableController::class, 'list']);
        Route::post('order/place', [TableController::class, 'placeOrder']);
        Route::get('order/details', [TableController::class, 'orderDetails']);
        Route::get('product/type', [TableController::class, 'filter_by_product_type']);
        Route::get('promotional/page', [TableController::class, 'get_promotional_page']);
        Route::get('order/list', [TableController::class, 'tableOrderList']);
    });

    Route::group(['prefix' => 'kitchen', 'middleware' => 'auth:kitchen_api', 'app_activate:' . APPS['kitchen_app']['software_id']], function () {
        Route::get('profile', [KitchenController::class, 'getProfile']);
        Route::get('order/list', [KitchenController::class, 'getOrderList']);
        Route::get('order/search', [KitchenController::class, 'search']);
        Route::get('order/filter', [KitchenController::class, 'filterByStatus']);
        Route::get('order/details', [KitchenController::class, 'getOrderDetails']);
        Route::put('order/status', [KitchenController::class, 'changeStatus']);
        Route::put('update-fcm-token', [KitchenController::class, 'updateFcmToken']);
    });

    Route::group(['prefix' => 'guest'], function () {
        Route::post('/add', [GuestUserController::class, 'guestStore']);
    });

    Route::group(['prefix' => 'offline-payment-method'], function () {
        Route::get('/list', [OfflinePaymentMethodController::class, 'list']);
    });

    Route::group(['prefix' => 'branch'], function () {
        Route::get('list', [BranchController::class, 'list']);
        Route::get('products', [BranchController::class, 'products']);
    });


});
