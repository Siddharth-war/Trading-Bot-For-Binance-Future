<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\CustomerLogic;
use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;
use App\Http\Controllers\Controller;
use App\Model\BusinessSetting;
use App\Model\DeliveryHistory;
use App\Model\DeliveryMan;
use App\Model\Order;
use App\Models\OrderPartialPayment;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DeliverymanController extends Controller
{
    public function __construct(
        private DeliveryMan     $deliveryman,
        private Order           $order,
        private DeliveryHistory $deliveryHistory,
        private User            $user,
        private BusinessSetting $businessSetting

    )
    {}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $deliveryman = $this->deliveryman->where(['auth_token' => $request['token']])
            ->withCount('orders')
            ->withCount([
                'orders as delivered_orders_count' => function ($query) {
                    $query->where('order_status', 'delivered');
                }
            ])
            ->withSum('orders as total_order_amount', 'order_amount')
            ->first();

        if (!$deliveryman) {
            return response()->json(['errors' => [['code' => 'delivery-man', 'message' => translate('Invalid token!')]]], 401);
        }

        return response()->json($deliveryman, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $deliveryman = $this->deliveryman->where(['auth_token' => $request['token']])->first();

        if (!$deliveryman) {
            return response()->json(['errors' => [['code' => 'delivery-man', 'message' => translate('Invalid token!')]]], 401);
        }

        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'f_name' => 'required|string|max:255',
            'l_name' => 'nullable|string|max:255',
            'phone' => [
                'required',
                Rule::unique('delivery_men')->ignore($deliveryman->id),
            ],
            'password' => 'nullable|string|min:6',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }



        // Update password only if provided and valid
        if (!empty($request->password)) {
            $deliveryman->password = bcrypt($request->password);
        }

        $deliveryman->f_name = $request->f_name;
        $deliveryman->l_name = $request->l_name;
        $deliveryman->phone = $request->phone;
        $deliveryman->image = $request->has('image') ? Helpers::update('delivery-man/', $deliveryman->image, 'png', $request->file('image')) : $deliveryman->image;
        $deliveryman->save();
        $deliveryman->refresh(); // This will reload the model from the database with all attributes

        return response()->json([
            'deliveryman' => $deliveryman,
            'message' => translate('Profile updated successfully')
        ], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCurrentOrders(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 1);

        $deliveryman = $this->deliveryman->where(['auth_token' => $request['token']])->first();
        if (!isset($deliveryman)) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => translate('Invalid token!')]
                ]
            ], 401);
        }

        $orders = $this->order
            ->with(['customer', 'order_partial_payments', 'delivery_address'])
            ->whereIn('order_status', ['pending', 'processing', 'out_for_delivery', 'confirmed', 'done', 'cooking'])
            ->where(['delivery_man_id' => $deliveryman['id']])
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        $ordersArray = [
            'total_size' => $orders->total(),
            'limit' => $limit,
            'offset' => $offset,
            'orders' => $orders->items(),
        ];

        return response()->json($ordersArray, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function recordLocationData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $deliveryman = $this->deliveryman->where(['auth_token' => $request['token']])->first();
        if (!isset($deliveryman)) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => translate('Invalid token!')]
                ]
            ], 401);
        }

        $this->deliveryHistory->insert([
            'order_id' => $request['order_id'],
            'deliveryman_id' => $deliveryman['id'],
            'longitude' => $request['longitude'],
            'latitude' => $request['latitude'],
            'time' => now(),
            'location' => $request['location'],
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => translate('location recorded')], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderHistory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'order_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $deliveryman = $this->deliveryman->where(['auth_token' => $request['token']])->first();
        if (!isset($deliveryman)) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => translate('Invalid token!')]
                ]
            ], 401);
        }

        $history = $this->deliveryHistory->where(['order_id' => $request['order_id'], 'deliveryman_id' => $deliveryman['id']])->get();

        return response()->json($history, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateOrderStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'order_id' => 'required',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $deliveryman = $this->deliveryman->where(['auth_token' => $request['token']])->first();
        if (!isset($deliveryman)) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => translate('Invalid token!')]
                ]
            ], 401);
        }

        $this->order->where(['id' => $request['order_id'], 'delivery_man_id' => $deliveryman['id']])->update([
            'order_status' => $request['status']
        ]);

        $order = $this->order->find($request['order_id']);

        $customerFcmToken = null;
        $value = null;
        if($order->is_guest == 0){
            $customerFcmToken = $order->customer ? $order->customer->cm_firebase_token : null;
        }elseif($order->is_guest == 1){
            $customerFcmToken = $order->guest ? $order->guest->fcm_token : null;
        }

        $restaurantName = Helpers::get_business_settings('restaurant_name');
        $deliverymanName = $order->delivery_man ? $order->delivery_man->f_name. ' '. $order->delivery_man->l_name : '';
        $customerName = $order->is_guest == 0 ? ($order->customer ? $order->customer->f_name. ' '. $order->customer->l_name : '') : '';
        $local = $order->is_guest == 0 ? ($order->customer ? $order->customer->language_code : 'en') : 'en';;

        if ($request['status'] == 'out_for_delivery') {
            $message = Helpers::order_status_update_message('ord_start');

            if ($local != 'en'){
                $statusKey = Helpers::order_status_message_key('ord_start');
                $translatedMessage = $this->businessSetting->with('translations')->where(['key' => $statusKey])->first();
                if (isset($translatedMessage->translations)){
                    foreach ($translatedMessage->translations as $translation){
                        if ($local == $translation->locale){
                            $message = $translation->value;
                        }
                    }
                }
            }

            $value = Helpers::text_variable_data_format(value:$message, user_name: $customerName, restaurant_name: $restaurantName, delivery_man_name: $deliverymanName, order_id: $order->id);

        } elseif ($request['status'] == 'delivered') {
            if ($order->is_guest == 0){
                if ($order->user_id) CustomerLogic::create_loyalty_point_transaction($order->user_id, $order->id, $order->order_amount, 'order_place');

                if ($order->transaction == null) {
                    $ol = OrderLogic::create_transaction($order, 'admin');
                }

                $user = $this->user->find($order->user_id);
                $isFirstOrder = $this->order->where(['user_id' => $user->id, 'order_status' => 'delivered'])->count('id');
                $referredByUser = $this->user->find($user->refer_by);

                if ($isFirstOrder < 2 && isset($user->refer_by) && isset($referredByUser)) {
                    if ($this->businessSetting->where('key', 'ref_earning_status')->first()->value == 1) {
                        CustomerLogic::referral_earning_wallet_transaction($order->user_id, 'referral_order_place', $referredByUser->id);
                    }
                }
            }

            if ($order['payment_method'] == 'cash_on_delivery'){
                $partialData = OrderPartialPayment::where(['order_id' => $order->id])->first();
                if ($partialData){
                    $partial = new OrderPartialPayment;
                    $partial->order_id = $order['id'];
                    $partial->paid_with = 'cash_on_delivery';
                    $partial->paid_amount = $partialData->due_amount;
                    $partial->due_amount = 0;
                    $partial->save();
                }
            }

            $message = Helpers::order_status_update_message('delivery_boy_delivered');
            if ($local != 'en'){
                $statusKey = Helpers::order_status_message_key('delivery_boy_delivered');
                $translatedMessage = $this->businessSetting->with('translations')->where(['key' => $statusKey])->first();
                if (isset($translatedMessage->translations)){
                    foreach ($translatedMessage->translations as $translation){
                        if ($local == $translation->locale){
                            $message = $translation->value;
                        }
                    }
                }
            }

            $value = Helpers::text_variable_data_format(value:$message, user_name: $customerName, restaurant_name: $restaurantName, delivery_man_name: $deliverymanName, order_id: $order->id);
        }

        try {
            if ($value && $customerFcmToken != null) {
                $data = [
                    'title' => translate('Order'),
                    'description' => $value,
                    'order_id' => $order['id'],
                    'image' => '',
                    'type' => 'order_status',
                ];
                Helpers::send_push_notif_to_device($customerFcmToken, $data);
            }

        } catch (\Exception $e) {

        }

        return response()->json(['message' => translate('Status updated')], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderDetails(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $deliveryman = $this->deliveryman->where(['auth_token' => $request['token']])->first();
        if (!isset($deliveryman)) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => translate('Invalid token!')]
                ]
            ], 401);
        }

        $order = $this->order->with(['details'])->where(['delivery_man_id' => $deliveryman['id'], 'id' => $request['order_id']])->first();
        $details = isset($order->details) ? Helpers::order_details_formatter($order->details) : null;
        foreach ($details as $det) {
            $det['delivery_time'] = $order->delivery_time;
            $det['delivery_date'] = $order->delivery_date;
            $det['preparation_time'] = $order->preparation_time;
        }

        return response()->json($details, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllOrders(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'status' => 'in:all,confirmed,processing,out_for_delivery,delivered,done,cooking,canceled,returned,failed,completed'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 1);
        $status = $request->status;

        $deliveryman = $this->deliveryman->where(['auth_token' => $request['token']])->first();
        if (!isset($deliveryman)) {
            return response()->json([
                'errors' => [['code' => 'delivery-man', 'message' => translate('Invalid token!')]]], 401);
        }

        $orders = $this->order
            ->with(['delivery_address', 'customer'])
            ->where(['delivery_man_id' => $deliveryman['id']])
            ->when(($request->has('status') && $status != 'all'),function ($query) use ($status){
                $query->where(['order_status' => $status]);
            })
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        $ordersArray = [
            'total_size' => $orders->total(),
            'limit' => $limit,
            'offset' => $offset,
            'orders' => $orders->items(),
        ];

        return response()->json($ordersArray, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getLastLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $lastData = $this->deliveryHistory
            ->where(['order_id' => $request['order_id']])
            ->latest()
            ->first();

        return response()->json($lastData, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function orderPaymentStatusUpdate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $deliveryman = $this->deliveryman->where(['auth_token' => $request['token']])->first();
        if (!isset($deliveryman)) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => translate('Invalid token!')]
                ]
            ], 401);
        }

        if ($this->order->where(['delivery_man_id' => $deliveryman['id'], 'id' => $request['order_id']])->first()) {
            $this->order->where(['delivery_man_id' => $deliveryman['id'], 'id' => $request['order_id']])->update([
                'payment_status' => $request['status']
            ]);
            return response()->json(['message' => translate('Payment status updated')], 200);
        }

        return response()->json([
            'errors' => [
                ['code' => 'order', 'message' => translate('not found!')]
            ]
        ], 404);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $deliveryman = $this->deliveryman->where(['auth_token' => $request['token']])->first();
        if (!isset($deliveryman)) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => translate('Invalid token!')]
                ]
            ], 401);
        }

        $this->deliveryman->where(['id' => $deliveryman['id']])->update([
            'fcm_token' => $request['fcm_token'],
            'language_code' => $request->header('X-localization') ?? $deliveryman->language_code
        ]);

        return response()->json(['message' => translate('successfully updated!')], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function orderModel(Request $request): JsonResponse
    {
        $deliveryman = $this->deliveryman->where(['auth_token' => $request['token']])->first();

        if (!isset($deliveryman)) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery-man', 'message' => translate('Invalid token!')]
                ]
            ], 401);
        }

        $order = $this->order
            ->with(['customer', 'order_partial_payments'])
            ->whereIn('order_status', ['pending', 'processing', 'out_for_delivery', 'confirmed', 'done', 'cooking'])
            ->where(['delivery_man_id' => $deliveryman['id'], 'id' => $request->id])
            ->first();

        return response()->json($order, 200);
    }


    public function getOrderStatistics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'filter' => 'nullable|in:today,this_week,this_month,this_year,all_time'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $deliveryman = $this->deliveryman->where('auth_token', $request['token'])->first();

        if (!$deliveryman) {
            return response()->json(['errors' => [['code' => 'delivery-man', 'message' => translate('Invalid token!')]]], 401);
        }

        // Default the filter to 'all' if it’s not provided in the request
        $filter = $request->input('filter', 'all_time');

        $startDate = null;
        $endDate = Carbon::now()->endOfDay();

        switch ($filter) {
            case 'today':
                $startDate = Carbon::now()->startOfDay();
                break;
            case 'this_week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'this_year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'all_time':
                $startDate = null; // No date filtering for "all"
                break;
        }

        // Base query for all orders assigned to the delivery man, applying date filter if set
        $orders = $this->order->where('delivery_man_id', $deliveryman->id);

        if ($startDate) {
            $orders->whereBetween('created_at', [$startDate, $endDate]);
        }

        $orderStatisticsData = [
            'ongoing_assigned_orders' => (clone $orders)->whereNotIn('order_status', ['delivered', 'canceled', 'returned', 'failed'])->count(),
            'confirmed_orders' => (clone $orders)->where('order_status', 'confirmed')->count(),
            'processing_orders' => (clone $orders)->where('order_status', 'processing')->count(),
            'out_for_delivery_orders' => (clone $orders)->where('order_status', 'out_for_delivery')->count(),

            'delivered_orders' => (clone $orders)->where('order_status', 'delivered')->count(),
            'canceled_orders' => (clone $orders)->where('order_status', 'canceled')->count(),
            'returned_orders' => (clone $orders)->where('order_status', 'returned')->count(),
            'failed_orders' => (clone $orders)->where('order_status', 'failed')->count(),
        ];

        return response()->json($orderStatisticsData, 200);
    }
}
