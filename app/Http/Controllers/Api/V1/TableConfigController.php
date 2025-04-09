<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Branch;
use App\Model\Currency;
use App\Traits\HelperTrait;
use Illuminate\Http\JsonResponse;

class TableConfigController extends Controller
{
    use HelperTrait;
    public function __construct(
        private Branch          $branch,
        private Currency $currency,
    )
    {}

    /**
     * @return JsonResponse
     */
    public function configuration(): JsonResponse
    {
        $currencySymbol = $this->currency->where(['currency_code' => Helpers::currency_code()])->first()->currency_symbol;

        $branchTable = $this->branch
            ->with(['table' => function ($q) {
                $q->where('is_active', 1);
            }])
            ->where('status', 1)
            ->get();

        $branchPromotion = $this->branch
            ->with('branch_promotion')
            ->where(['branch_promotion_status' => 1])
            ->get();

        $maintenanceMode = $this->checkMaintenanceMode();

        return response()->json([
            'currency_symbol' => $currencySymbol,
            'time_format' => (string)(Helpers::get_business_settings('time_format') ?? '12'),
            'decimal_point_settings' => (int)(Helpers::get_business_settings('decimal_point_settings') ?? 2),
            'currency_symbol_position' => Helpers::get_business_settings('currency_symbol_position') ?? 'right',
            'base_urls' => [
                'product_image_url' => asset('storage/app/public/product'),
                'customer_image_url' => asset('storage/app/public/profile'),
                'banner_image_url' => asset('storage/app/public/banner'),
                'category_image_url' => asset('storage/app/public/category'),
                'category_banner_image_url' => asset('storage/app/public/category/banner'),
                'review_image_url' => asset('storage/app/public/review'),
                'notification_image_url' => asset('storage/app/public/notification'),
                'restaurant_image_url' => asset('storage/app/public/restaurant'),
                'delivery_man_image_url' => asset('storage/app/public/delivery-man'),
                'chat_image_url' => asset('storage/app/public/conversation'),
                'promotional_url' => asset('storage/app/public/promotion'),
            ],
            'branch' => $branchTable,
            'promotion_campaign' => $branchPromotion,
            'is_veg_non_veg_active' => (integer)(Helpers::get_business_settings('toggle_veg_non_veg') ?? 0) ,
            'maintenance_mode' => $maintenanceMode,
            'restaurant_phone' => Helpers::get_business_settings('phone'),
            'restaurant_email' => Helpers::get_business_settings('email_address')
        ]);
    }
}
