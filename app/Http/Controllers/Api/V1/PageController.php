<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Model\BusinessSetting;

class PageController extends Controller
{
    public function __construct(
        private BusinessSetting $businessSetting,
    )
    {}

    public function index(): JsonResponse
    {
        $returnPage = $this->businessSetting->where(['key' => 'return_page'])->first();
        $refundPage = $this->businessSetting->where(['key' => 'refund_page'])->first();
        $cancellationPage = $this->businessSetting->where(['key' => 'cancellation_page'])->first();
        $termsAndConditions = $this->businessSetting->where(['key' => 'terms_and_conditions'])->first()->value;
        $privacyPolicy = $this->businessSetting->where(['key' => 'privacy_policy'])->first()->value;
        $aboutUs = $this->businessSetting->where(['key' => 'about_us'])->first()->value;

        return response()->json([
            'return_page' => isset($returnPage) ? json_decode($returnPage->value, true) : null,
            'refund_page' => isset($refundPage) ? json_decode($refundPage->value, true) : null,
            'cancellation_page' => isset($cancellationPage) ? json_decode($cancellationPage->value, true) : null,
            'terms_and_conditions' => $termsAndConditions,
            'privacy_policy' => $privacyPolicy,
            'about_us' => $aboutUs
        ]);
    }

}
