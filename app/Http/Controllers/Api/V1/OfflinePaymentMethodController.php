<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OfflinePaymentMethod;
use Illuminate\Http\JsonResponse;

class OfflinePaymentMethodController extends Controller
{
    public function __construct(
        private OfflinePaymentMethod $offlinePaymentMethod
    ){}

    /**
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $methods = $this->offlinePaymentMethod->latest()->active()->get();
        return response()->json($methods, 200);
    }
}
