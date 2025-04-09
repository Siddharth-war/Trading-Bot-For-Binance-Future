<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Coupon;
use App\Model\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    public function __construct(
        private Coupon $coupon,
        private Order  $order
    ){}

    /**
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $couponQuery = $this->coupon->active();

        if (auth('api')->user()) {
            $coupon = $couponQuery->get();
        } else {
            $coupon = $couponQuery->default()->get();
        }

        return response()->json($coupon, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function apply(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'guest_id' => auth('api')->user() ? 'nullable' : 'required',
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        try {
            $coupon = $this->coupon->active()->where(['code' => $request['code']])->first();

            if (isset($coupon)) {

                //first order coupon type
                if ($coupon['coupon_type'] == 'first_order') {
                   if (!(bool)auth('api')->user()){
                       return response()->json([
                           'errors' => [
                               ['code' => 'coupon', 'message' => translate('This coupon in not valid for you!')]
                           ]
                       ], 401);
                   }

                    $total = $this->order->where(['user_id' => auth('api')->user()->id, 'is_guest' => 0])->count();
                    if ($total == 0) {
                        return response()->json($coupon, 200);
                    } else {
                        return response()->json([
                            'errors' => [
                                ['code' => 'coupon', 'message' => translate('This coupon in not valid for you!')]
                            ]
                        ], 401);
                    }
                }

                //default coupon type
                if ($coupon['limit'] == null) {
                    return response()->json($coupon, 200);
                } else {
                    $userId = (bool)auth('api')->user() ? auth('api')->user()->id : $request['guest_id'];
                    $userType = (bool)auth('api')->user() ? 0 : 1;

                    $total = $this->order->where(['user_id' => $userId, 'coupon_code' => $request['code'], 'is_guest' =>$userType])->count();
                    if ($total < $coupon['limit']) {
                        return response()->json($coupon, 200);
                    } else {
                        return response()->json([
                            'errors' => [
                                ['code' => 'coupon', 'message' => translate('coupon_limit_over')]
                            ]
                        ], 401);
                    }
                }

            } else {
                return response()->json([
                    'errors' => [
                        ['code' => 'coupon', 'message' => translate('coupon_not_found')]
                    ]
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['errors' => $e], 403);
        }
    }
}
