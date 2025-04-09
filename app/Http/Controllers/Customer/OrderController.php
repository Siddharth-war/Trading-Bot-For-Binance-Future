<?php

namespace App\Http\Controllers\Customer;

use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;
use App\CentralLogics\CustomerLogic;
use App\Http\Controllers\Controller;
use App\Model\Branch;
use App\Model\BusinessSetting;
use App\Model\CustomerAddress;
use App\Model\DeliveryMan;
use App\Model\Order;
use App\Model\TableOrder;
use App\Models\DeliveryChargeByArea;
use App\Models\GuestUser;
use App\Models\OfflinePayment;
use App\Models\OrderArea;
use App\Models\OrderPartialPayment;
use App\User;
use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Brian2694\Toastr\Facades\Toastr;
use DateTime;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Support\Renderable;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function App\CentralLogics\translate;
use function Symfony\Component\String\u;
use Auth;

class OrderController extends Controller
{
    public function __construct(
        private Order           $order,
        private TableOrder      $table_order,
        private CustomerAddress $customer_address,
        private OrderLogic      $order_logic,
        private User            $user,
        private BusinessSetting $business_setting,
        private DeliveryMan     $delivery_man,
        private OrderArea     $orderArea
    )
    {}

    /**
     * @param Request $request
     * @param $status
     * @return Renderable
     */
    public function list(Request $request, $status): Renderable
    {
        Helpers::update_daily_product_stock();
        $this->order->where(['checked' => 0])->update(['checked' => 1]);

        $queryParam = [];
        $search = $request['search'];
        $from = $request['from'];
        $to = $request['to'];
        $branchId = $request['branch_id'];

        $query = $this->order->newQuery();

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                        ->orWhere('order_status', 'like', "%{$value}%")
                        ->orWhere('transaction_reference', 'like', "%{$value}%");
                }
            });
            $queryParam['search'] = $search;
        }

        if ($branchId && $branchId != 0) {
            $query->where('branch_id', $branchId);
            $queryParam['branch_id'] = $branchId;
        }

        if ($from && $to) {
            $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
            $queryParam['from'] = $from;
            $queryParam['to'] = $to;
        }

        if ($status == 'schedule') {
            $query->with(['customer', 'branch'])->schedule();
        } elseif ($status != 'all') {
            $query->with(['customer', 'branch'])->where('order_status', $status)->notSchedule();
        } else {
            $query->with(['customer', 'branch']);
        }

        $key = explode(' ', $request['search']);

        $orderCount = [
            'pending' => $this->order
            ->where("user_id",Auth::guard('customer')->user()->id)
                ->notPos()
                ->notDineIn()
                ->notSchedule()
                ->where(['order_status' => 'pending'])
                ->when($branchId && $branchId != 0, function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                })
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })
                ->when($request->has('search'), function ($query) use ($key) {
                    $query->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('id', 'like', "%{$value}%")
                                ->orWhere('order_status', 'like', "%{$value}%")
                                ->orWhere('transaction_reference', 'like', "%{$value}%");
                        }
                    });
                })
                ->count(),

            'confirmed' => $this->order
            ->where("user_id",Auth::guard('customer')->user()->id)
                ->notPos()
                ->notDineIn()
                ->notSchedule()
                ->where(['order_status' => 'confirmed'])
                ->when($branchId && $branchId != 0, function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                })
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })
                ->when($request->has('search'), function ($query) use ($key) {
                    $query->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('id', 'like', "%{$value}%")
                                ->orWhere('order_status', 'like', "%{$value}%")
                                ->orWhere('transaction_reference', 'like', "%{$value}%");
                        }
                    });
                })
                ->count(),

            'processing' => $this->order
            ->where("user_id",Auth::guard('customer')->user()->id)
                ->notPos()
                ->notDineIn()
                ->notSchedule()
                ->where(['order_status' => 'processing'])
                ->when($branchId && $branchId != 0, function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                })
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })
                ->when($request->has('search'), function ($query) use ($key) {
                    $query->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('id', 'like', "%{$value}%")
                                ->orWhere('order_status', 'like', "%{$value}%")
                                ->orWhere('transaction_reference', 'like', "%{$value}%");
                        }
                    });
                })
                ->count(),

            'out_for_delivery' => $this->order
            ->where("user_id",Auth::guard('customer')->user()->id)
                ->notPos()
                ->notDineIn()
                ->notSchedule()
                ->where(['order_status' => 'out_for_delivery'])
                ->when($branchId && $branchId != 0, function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                })
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })
                ->when($request->has('search'), function ($query) use ($key) {
                    $query->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('id', 'like', "%{$value}%")
                                ->orWhere('order_status', 'like', "%{$value}%")
                                ->orWhere('transaction_reference', 'like', "%{$value}%");
                        }
                    });
                })
                ->count(),

            'delivered' => $this->order
            ->where("user_id",Auth::guard('customer')->user()->id)
                ->notPos()
                ->notDineIn()
                ->where(['order_status' => 'delivered'])
                ->when($branchId && $branchId != 0, function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                })
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })
                ->when($request->has('search'), function ($query) use ($key) {
                    $query->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('id', 'like', "%{$value}%")
                                ->orWhere('order_status', 'like', "%{$value}%")
                                ->orWhere('transaction_reference', 'like', "%{$value}%");
                        }
                    });
                })
                ->count(),

            'canceled' => $this->order
            ->where("user_id",Auth::guard('customer')->user()->id)
                ->notPos()
                ->notDineIn()
                ->notSchedule()
                ->where(['order_status' => 'canceled'])
                ->when($branchId && $branchId != 0, function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                })
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })
                ->when($request->has('search'), function ($query) use ($key) {
                    $query->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('id', 'like', "%{$value}%")
                                ->orWhere('order_status', 'like', "%{$value}%")
                                ->orWhere('transaction_reference', 'like', "%{$value}%");
                        }
                    });
                })
                ->count(),

            'returned' => $this->order
                ->where("user_id",Auth::guard('customer')->user()->id)
                ->notPos()
                ->notDineIn()
                ->notSchedule()
                ->where(['order_status' => 'returned'])
                ->when($branchId && $branchId != 0, function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                })
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })
                ->when($request->has('search'), function ($query) use ($key) {
                    $query->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('id', 'like', "%{$value}%")
                                ->orWhere('order_status', 'like', "%{$value}%")
                                ->orWhere('transaction_reference', 'like', "%{$value}%");
                        }
                    });
                })
                ->count(),

                'hold' => $this->order
                    ->where("user_id",Auth::guard('customer')->user()->id)
                    ->notPos()
                    ->notDineIn()
                    ->notSchedule()
                    ->where(['order_status' => 'hold'])
                    ->when($branchId && $branchId != 0, function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId);
                    })
                    ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                        $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                    })
                    ->when($request->has('search'), function ($query) use ($key) {
                        $query->where(function ($q) use ($key) {
                            foreach ($key as $value) {
                                $q->orWhere('id', 'like', "%{$value}%")
                                    ->orWhere('order_status', 'like', "%{$value}%")
                                    ->orWhere('transaction_reference', 'like', "%{$value}%");
                            }
                        });
                    })
                    ->count(),

            'failed' => $this->order
            ->where("user_id",Auth::guard('customer')->user()->id)
                ->notPos()
                ->notDineIn()
                ->notSchedule()
                ->where(['order_status' => 'failed'])
                ->when($branchId && $branchId != 0, function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                })
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })->count(),
        ];

        $orders = $query->where("user_id",Auth::guard('customer')->user()->id)->notPos()->notDineIn()->latest()->paginate(Helpers::getPagination())->appends($queryParam);
        return view('customer-views.order.list', compact('orders', 'status', 'search', 'from', 'to', 'orderCount', 'branchId'));
    }

    /**
     * @param $id
     * @return Renderable|RedirectResponse
     */
    public function details($id): Renderable|RedirectResponse
    {
        $order = $this->order->with(['details', 'customer', 'delivery_address', 'branch', 'delivery_man', 'order_partial_payments'])
            ->where(['id' => $id])
            ->first();

        if (!isset($order)) {
            Toastr::info(translate('No order found!'));
            return back();
        }

        $deliverymen = $this->delivery_man->where(['is_active'=>1])
            ->where(function($query) use ($order) {
                $query->where('branch_id', $order->branch_id)
                    ->orWhere('branch_id', 0);
            })
            ->get();

        $deliveryDateTime = $order['delivery_date'] . ' ' . $order['delivery_time'];
        $orderedTime = Carbon::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s", strtotime($deliveryDateTime)));
        $remainingTime = $orderedTime->add($order['preparation_time'], 'minute')->format('Y-m-d H:i:s');
        $order['remaining_time'] = $remainingTime;

        return view('admin-views.order.order-view', compact('order', 'deliverymen'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
    {
        $order = $this->order->find($request->id);

        if (in_array($order->order_status, ['delivered', 'failed'])) {
            Toastr::warning(translate('you_can_not_change_the_status_of '. $order->order_status .' order'));
            return back();
        }

        if ($request->order_status == 'delivered' && $order['transaction_reference'] == null && !in_array($order['payment_method'], ['cash_on_delivery', 'wallet_payment', 'offline_payment'])) {
            Toastr::warning(translate('add_your_payment_reference_first'));
            return back();
        }

        if (($request->order_status == 'delivered' || $request->order_status == 'out_for_delivery') && $order['delivery_man_id'] == null && $order['order_type'] != 'take_away') {
            Toastr::warning(translate('Please assign delivery man first!'));
            return back();
        }
        if ($request->order_status == 'completed' && $order->payment_status != 'paid') {
            Toastr::warning(translate('Please update payment status first!'));
            return back();
        }

        if ($request->order_status == 'delivered') {
            if ($order->is_guest == 0){
                if ($order->user_id) CustomerLogic::create_loyalty_point_transaction($order->user_id, $order->id, $order->order_amount, 'order_place');

                if ($order->transaction == null) {
                    $ol = $this->order_logic->create_transaction($order, 'admin');
                }

                $user = $this->user->find($order->user_id);
                if (isset($user)){
                    $isFirstOrder = $this->order->where(['user_id' => $user->id, 'order_status' => 'delivered'])->count('id');
                    $referredByUser = $this->user->find($user->refer_by);

                    if ($isFirstOrder < 2 && isset($user->refer_by) && isset($referredByUser)) {
                        if ($this->business_setting->where('key', 'ref_earning_status')->first()->value == 1) {
                            CustomerLogic::referral_earning_wallet_transaction($order->user_id, 'referral_order_place', $referredByUser->id);
                        }
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
        }

        $order->order_status = $request->order_status;
        if ($request->order_status == 'delivered') {
            $order->payment_status = 'paid';
        }
        $order->save();

        $message = Helpers::order_status_update_message($request->order_status);

        $restaurantName = Helpers::get_business_settings('restaurant_name');
        $deliverymanName = $order->delivery_man ? $order->delivery_man->f_name. ' '. $order->delivery_man->l_name : '';
        $customerName = $order->is_guest == 0 ? ($order->customer ? $order->customer->f_name. ' '. $order->customer->l_name : '') : 'Guest User';
        $local = $order->is_guest == 0 ? ($order->customer ? $order->customer->language_code : 'en') : 'en';

        if ($local != 'en'){
            $statusKey = Helpers::order_status_message_key($request->order_status);
            $translatedMessage = $this->business_setting->with('translations')->where(['key' => $statusKey])->first();
            if (isset($translatedMessage->translations)){
                foreach ($translatedMessage->translations as $translation){
                    if ($local == $translation->locale){
                        $message = $translation->value;
                    }
                }
            }
        }

        $value = Helpers::text_variable_data_format(value:$message, user_name: $customerName, restaurant_name: $restaurantName, delivery_man_name: $deliverymanName, order_id: $order->id);

        $customerFcmToken = null;
        if($order->is_guest == 0){
            $customerFcmToken = $order->customer ? $order->customer->cm_firebase_token : null;
        }elseif($order->is_guest == 1){
            $customerFcmToken = $order->guest ? $order->guest->fcm_token : null;
        }

        try {
            if ($value) {
                $data = [
                    'title' => translate('Order'),
                    'description' => $value,
                    'order_id' => $order['id'],
                    'image' => '',
                    'type' => 'order_status',
                ];
                if (isset($customerFcmToken)) {
                    Helpers::send_push_notif_to_device($customerFcmToken, $data);
                }

            }
        } catch (\Exception $e) {
            Toastr::warning(translate('Push notification send failed for Customer!'));
        }

        //delivery man notification
        if ($request->order_status == 'processing' || $request->order_status == 'out_for_delivery') {
            if (isset($order->delivery_man)) {
                $deliverymanFcmToken = $order->delivery_man->fcm_token;
            }

            $value = translate('One of your order is on processing');
            $outForDeliveryValue = translate('One of your order is out for delivery');
            try {
                if ($value) {
                    $data = [
                        'title' => translate('Order'),
                        'description' => $request->order_status == 'processing' ? $value : $outForDeliveryValue,
                        'order_id' => $order['id'],
                        'image' => '',
                        'type' => 'order_status',
                    ];
                    if (isset($deliverymanFcmToken)) {
                        Helpers::send_push_notif_to_device(fcm_token: $deliverymanFcmToken, data: $data);
                    }
                }
            } catch (\Exception $e) {
                Toastr::warning(translate('Push notification failed for DeliveryMan!'));
            }
        }

        //kitchen order notification
        if ($request->order_status == 'confirmed') {
            $data = [
                'title' => translate('You have a new order - (Order Confirmed).'),
                'description' => $order->id,
                'order_id' => $order->id,
                'order_status' => $order->order_status,
                'image' => '',
            ];

            try {
                Helpers::send_push_notif_to_topic(data: $data, topic: "kitchen-{$order->branch_id}", type: 'general', isNotificationPayloadRemove: true);

            } catch (\Exception $e) {
                Toastr::warning(translate('Push notification failed!'));
            }
        }
        $table_order = $this->table_order->where(['id' => $order->table_order_id])->first();

        if ($request->order_status == 'completed' && $order->payment_status == 'paid') {
            if (isset($table_order->id)) {
                $orders = $this->order->where(['table_order_id' => $table_order->id])->get();
                $status = 1;
                foreach ($orders as $order) {
                    if ($order->order_status != 'completed') {
                        $status = 0;
                        break;
                    }
                }

                if ($status == 1) {
                    $table_order->branch_table_token_is_expired = 1;
                    $table_order->save();
                }
            }
        }

        if ($request->order_status == 'canceled') {

            if (isset($table_order->id)) {
                $orders = $this->order->where(['table_order_id' => $table_order->id])->get();
                $status = 1;
                foreach ($orders as $order) {
                    if ($order->order_status != 'canceled') {
                        $status = 0;
                        break;
                    }
                }

                if ($status == 1) {
                    $table_order->branch_table_token_is_expired = 1;
                    $table_order->save();
                }
            }
        }

        Toastr::success(translate('Order status updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     * @throws \Exception
     */
    public function preparationTime(Request $request, $id): RedirectResponse
    {
        $order = $this->order->with(['customer'])->find($id);
        $deliveryDateTime = $order['delivery_date'] . ' ' . $order['delivery_time'];

        $orderedTime = Carbon::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s", strtotime($deliveryDateTime)));
        $remainingTime = $orderedTime->add($order['preparation_time'], 'minute')->format('Y-m-d H:i:s');

        //if delivery time is not over
        if (strtotime(date('Y-m-d H:i:s')) < strtotime($remainingTime)) {
            $delivery_time = new DateTime($remainingTime); //time when preparation will be over
            $current_time = new DateTime(); // time now
            $interval = $delivery_time->diff($current_time);
            $remainingMinutes = $interval->i;
            $remainingMinutes += $interval->days * 24 * 60;
            $remainingMinutes += $interval->h * 60;
            $order->preparation_time = 0;
        } else {
            //if delivery time is over
            $delivery_time = new DateTime($remainingTime);
            $current_time = new DateTime();
            $interval = $delivery_time->diff($current_time);
            $diffInMinutes = $interval->i;
            $diffInMinutes += $interval->days * 24 * 60;
            $diffInMinutes += $interval->h * 60;
            $order->preparation_time = 0;
        }

        $newDeliveryDateTime = Carbon::now()->addMinutes($request->extra_minute);
        $order->delivery_date = $newDeliveryDateTime->format('Y-m-d');
        $order->delivery_time = $newDeliveryDateTime->format('H:i:s');

        $order->save();

        if ($order->is_guest == 0){
            $customer = $order->customer;

            $message = Helpers::order_status_update_message('customer_notify_message_for_time_change');
            $local = $order->customer ? $order->customer->language_code : 'en';

            if ($local != 'en'){
                $translatedMessage = $this->business_setting->with('translations')->where(['key' => 'customer_notify_message_for_time_change'])->first();
                if (isset($translatedMessage->translations)){
                    foreach ($translatedMessage->translations as $translation){
                        if ($local == $translation->locale){
                            $message = $translation->value;
                        }
                    }
                }
            }
            $restaurantName = Helpers::get_business_settings('restaurant_name');
            $deliverymanName = $order->delivery_man ? $order->delivery_man->f_name. ' '. $order->delivery_man->l_name : '';
            $customerName = $order->customer ? $order->customer->f_name. ' '. $order->customer->l_name : '';

            $value = Helpers::text_variable_data_format(value:$message, user_name: $customerName, restaurant_name: $restaurantName, delivery_man_name: $deliverymanName, order_id: $order->id);

            try {
                if ($value) {
                    $customerFcmToken = null;
                    $customerFcmToken = $customer?->cm_firebase_token;

                    $data = [
                        'title' => translate('Order'),
                        'description' => $value,
                        'order_id' => $order['id'],
                        'image' => '',
                        'type' => 'order_status',
                    ];
                    Helpers::send_push_notif_to_device($customerFcmToken, $data);
                } else {
                    throw new \Exception(translate('failed'));
                }

            } catch (\Exception $e) {
                Toastr::warning(translate('Push notification send failed for Customer!'));
            }
        }

        Toastr::success(translate('Order preparation time updated'));
        return back();
    }


    /**
     * @param $order_id
     * @param $delivery_man_id
     * @return JsonResponse
     */
    public function addDeliveryman($order_id, $delivery_man_id): JsonResponse
    {
        if ($delivery_man_id == 0) {
            return response()->json([], 401);
        }

        $order = $this->order->find($order_id);
        if ($order->order_status == 'pending' ||$order->order_status == 'delivered' || $order->order_status == 'returned' || $order->order_status == 'failed' || $order->order_status == 'canceled' || $order->order_status == 'scheduled') {
            return response()->json(['status' => false], 200);
        }
        $order->delivery_man_id = $delivery_man_id;
        $order->save();

        $deliverymanFcmToken = $order->delivery_man->fcm_token;
        $customerFcmToken = null;
        if (isset($order->customer)) {
            $customerFcmToken = $order->customer->cm_firebase_token;
        }

        $message = Helpers::order_status_update_message('del_assign');
        $local = $order->delivery_man ? $order->delivery_man->language_code : 'en';

        if ($local != 'en'){
            $translatedMessage = $this->business_setting->with('translations')->where(['key' => 'delivery_boy_assign_message'])->first();
            if (isset($translatedMessage->translations)){
                foreach ($translatedMessage->translations as $translation){
                    if ($local == $translation->locale){
                        $message = $translation->value;
                    }
                }
            }
        }
        $restaurantName = Helpers::get_business_settings('restaurant_name');
        $deliverymanName = $order->delivery_man ? $order->delivery_man->f_name. ' '. $order->delivery_man->l_name : '';
        $customerName = $order->customer ? $order->customer->f_name. ' '. $order->customer->l_name : '';

        $value = Helpers::text_variable_data_format(value:$message, user_name: $customerName, restaurant_name: $restaurantName, delivery_man_name: $deliverymanName, order_id: $order->id);

        try {
            if ($value) {
                $data = [
                    'title' => translate('Order'),
                    'description' => $value,
                    'order_id' => $order_id,
                    'image' => '',
                    'type' => 'order_status',
                ];
                Helpers::send_push_notif_to_device(fcm_token: $deliverymanFcmToken, data: $data, isDeliverymanAssigned: true );

                //send notification to customer
                if (isset($order->customer) && $customerFcmToken) {
                    $local = $order->customer->language_code ?? 'en';
                    $notifyMessage = Helpers::order_status_update_message('customer_notify_message');
                    if ($local != 'en'){
                        $translatedMessage = $this->business_setting->with('translations')->where(['key' => 'customer_notify_message'])->first();
                        if (isset($translatedMessage->translations)){
                            foreach ($translatedMessage->translations as $translation){
                                if ($local == $translation->locale){
                                    $notifyMessage = $translation->value;
                                }
                            }
                        }
                    }

                    $data['description'] = Helpers::text_variable_data_format(value:$notifyMessage, user_name: $customerName, restaurant_name: $restaurantName, delivery_man_name: $deliverymanName, order_id: $order->id);
                    Helpers::send_push_notif_to_device($customerFcmToken, $data);
                }
            }
        } catch (\Exception $e) {
            Toastr::warning(translate('Push notification failed for DeliveryMan!'));
        }

        return response()->json(['status' => true], 200);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function paymentStatus(Request $request): RedirectResponse
    {
        $order = $this->order->find($request->id);
        if ($request->payment_status == 'paid' && $order['transaction_reference'] == null &&  $order['order_type'] != 'dine_in' && !in_array($order['payment_method'], ['cash_on_delivery', 'wallet_payment', 'offline_payment', 'cash'])) {
            Toastr::warning(translate('Add your payment reference code first!'));
            return back();
        }
        $order->payment_status = $request->payment_status;
        $order->save();

        Toastr::success(translate('Payment status updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function updateShipping(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'contact_person_name' => 'required',
            'address_type' => 'required',
            'contact_person_number' => 'required|min:5|max:20',
            'address' => 'required'
        ]);

        $address = [
            'contact_person_name' => $request->contact_person_name,
            'contact_person_number' => $request->contact_person_number,
            'address_type' => $request->address_type,
            'road' => $request->road,
            'house' => $request->house,
            'floor' => $request->floor,
            'address' => $request->address,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'created_at' => now(),
            'updated_at' => now()
        ];

        if ($id) {
            $this->customer_address->where('id', $id)->update($address);
            Toastr::success(translate('Address updated!'));

        } else {
            $address = $this->customer_address;
            $address->contact_person_name = $request->input('contact_person_name');
            $address->contact_person_number = $request->input('contact_person_number');
            $address->address_type = $request->input('address_type');
            $address->address = $request->input('address');
            $address->longitude = $request->input('longitude');
            $address->latitude = $request->input('latitude');
            $address->user_id = $request->input('user_id');
            $address->house = $request->house;
            $address->floor = $request->floor;
            $address->address = $request->address;
            $address->save();
            $this->order->where('id', $request->input('order_id'))->update(['delivery_address_id' => $address->id]);
            Toastr::success(translate('Address added!'));
        }

        return back();
    }

    /**
     * @param $id
     * @return Renderable
     */
    public function generateInvoice($id): Renderable
    {
        $order = $this->order->with(['order_partial_payments'])->where('id', $id)->first();
        return view('admin-views.order.invoice', compact('order'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function addPaymentReferenceCode(Request $request, $id): RedirectResponse
    {
        $this->order->where(['id' => $id])->update([
            'transaction_reference' => $request['transaction_reference']
        ]);

        Toastr::success(translate('Payment reference code is added!'));
        return back();
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function branchFilter($id): RedirectResponse
    {
        session()->put('branch_filter', $id);
        return back();
    }


    /**
     * @return string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function exportData(): StreamedResponse|string
    {
        $orders = $this->order->all();
        return (new FastExcel($orders))->download('orders.xlsx');
    }

    /**
     * @param Request $request
     * @return RedirectResponse|string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function exportExcel(Request $request): StreamedResponse|string|RedirectResponse
    {
        $status = $request->status;
        $queryParam = [];
        $search = $request['search'];
        $from = $request['from'];
        $to = $request['to'];
        $branchId = $request['branch_id'];


        $query = $this->order->newQuery();

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                        ->orWhere('order_status', 'like', "%{$value}%")
                        ->orWhere('transaction_reference', 'like', "%{$value}%");
                }
            });
            $queryParam['search'] = $search;
        }

        if ($branchId && $branchId != 0) {
            $query->where('branch_id', $branchId);
            $queryParam['branch_id'] = $branchId;
        }

        if ($from && $to) {
            $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
            $queryParam['from'] = $from;
            $queryParam['to'] = $to;
        }

        if ($status == 'schedule') {
            $query->with(['customer', 'branch'])->schedule();
        } elseif ($status != 'all') {
            $query->with(['customer', 'branch'])->where('order_status', $status)->notSchedule();
        } else {
            $query->with(['customer', 'branch']);
        }

        $orders = $query->notPos()->notDineIn()->latest()->get();
        if ($orders->count() < 1) {
            Toastr::warning('No Data Available');
            return back();
        }

        $data = array();
        foreach ($orders as $key => $order) {
            $data[] = array(
                'SL' => ++$key,
                'Order ID' => $order->id,
                'Order Date' => date('d M Y h:m A', strtotime($order['created_at'])),
                'Customer Info' => $order['user_id'] == null ? 'Walk in Customer' : ($order->customer == null ? 'Customer Unavailable' : $order->customer['f_name'] . ' ' . $order->customer['l_name']),
                'Branch' => $order->branch ? $order->branch->name : 'Branch Deleted',
                'Total Amount' => Helpers::set_symbol($order['order_amount']),
                'Payment Status' => $order->payment_status == 'paid' ? 'Paid' : 'Unpaid',
                'Order Status' => $order['order_status'] == 'pending' ? 'Pending' : ($order['order_status'] == 'confirmed' ? 'Confirmed' : ($order['order_status'] == 'processing' ? 'Processing' : ($order['order_status'] == 'delivered' ? 'Delivered' : ($order['order_status'] == 'picked_up' ? 'Out For Delivery' : str_replace('_', ' ', $order['order_status']))))),
            );
        }

        return (new FastExcel($data))->download('Order_List.xlsx');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxChangeDeliveryTimeAndDate(Request $request): JsonResponse
    {
        $order = $this->order->where('id', $request->order_id)->first();
        if (!$order) {
            return response()->json(['status' => false]);
        }
        $order->delivery_date = $request->input('delivery_date') ?? $order->delivery_date;
        $order->delivery_time = $request->input('delivery_time') ?? $order->delivery_time;
        $order->save();

        return response()->json(['status' => true]);
    }

    /**
     * @param $order_id
     * @param $status
     * @return JsonResponse
     */
    public function verifyOfflinePayment($order_id, $status): JsonResponse
    {
        $offlineData = OfflinePayment::where(['order_id' => $order_id])->first();
        if (!isset($offlineData)){
            return response()->json(['status' => false], 200);
        }
        $offlineData->status = $status;
        $offlineData->save();

        $order = Order::find($order_id);
        if (!isset($order)){
            return response()->json(['status' => false], 200);
        }

        if ($offlineData->status == 1){
            $order->order_status = 'confirmed';
            $order->payment_status = 'paid';
            $order->save();

            $message = Helpers::order_status_update_message('confirmed');
            $local = $order->is_guest == 0 ? ($order->customer ? $order->customer->language_code : 'en') : 'en';;

            if ($local != 'en'){
                $statusKey = Helpers::order_status_message_key('confirmed');
                $translatedMessage = $this->business_setting->with('translations')->where(['key' => $statusKey])->first();
                if (isset($translatedMessage->translations)){
                    foreach ($translatedMessage->translations as $translation){
                        if ($local == $translation->locale){
                            $message = $translation->value;
                        }
                    }
                }
            }
            $restaurantName = Helpers::get_business_settings('restaurant_name');
            $deliverymanName = $order->delivery_man ? $order->delivery_man->f_name. ' '. $order->delivery_man->l_name : '';
            $customerName = $order->is_guest == 0 ? ($order->customer ? $order->customer->f_name. ' '. $order->customer->l_name : '') : '';

            $value = Helpers::text_variable_data_format(value:$message, user_name: $customerName, restaurant_name: $restaurantName, delivery_man_name: $deliverymanName, order_id: $order->id);

            $customerFcmToken = null;
            if($order->is_guest == 0){
                $customerFcmToken = $order->customer ? $order->customer->cm_firebase_token : null;
            }elseif($order->is_guest == 1){
                $customerFcmToken = $order->guest ? $order->guest->fcm_token : null;
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
                //
            }

        }elseif ($offlineData->status == 2){
            $customerFcmToken = null;
            if($order->is_guest == 0){
                $customerFcmToken = $order->customer ? $order->customer->cm_firebase_token : null;
            }elseif($order->is_guest == 1){
                $customerFcmToken = $order->guest ? $order->guest->fcm_token : null;
            }
            if ($customerFcmToken != null) {
                try {
                    $data = [
                        'title' => translate('Order'),
                        'description' => translate('Offline payment is not verified'),
                        'order_id' => $order->id,
                        'image' => '',
                        'type' => 'order',
                    ];
                    Helpers::send_push_notif_to_device($customerFcmToken, $data);
                } catch (\Exception $e) {
                }
            }

        }
        return response()->json(['status' => true], 200);
    }

    public function updateOrderDeliveryArea(Request $request, $order_id)
    {
        $request->validate([
            'selected_area_id' => 'required'
        ]);

        $order = $this->order->find($order_id);
        if (!$order){
            Toastr::warning(translate('order not found'));
            return back();
        }

        if ($order->order_status == 'delivered') {
            Toastr::warning(translate('you_can_not_change_the_area_once_the_order_status_is_delivered'));
            return back();
        }

        $branch = Branch::with(['delivery_charge_setup', 'delivery_charge_by_area'])
            ->where(['id' => $order['branch_id']])
            ->first(['id', 'name', 'status']);

        if ($branch->delivery_charge_setup->delivery_charge_type != 'area') {
            Toastr::warning(translate('this branch selected delivery type is not area'));
            return back();
        }

        $area = DeliveryChargeByArea::where(['id' => $request['selected_area_id'], 'branch_id' => $order->branch_id])->first();
        if (!$area){
            Toastr::warning(translate('Area not found'));
            return back();
        }

        $order->delivery_charge = $area->delivery_charge;
        $order->save();

        $orderArea = $this->orderArea->firstOrNew(['order_id' => $order_id]);
        $orderArea->area_id = $request->selected_area_id;
        $orderArea->save();

        $customerFcmToken = null;
        if($order->is_guest == 0){
            $customerFcmToken = $order->customer ? $order->customer->cm_firebase_token : null;
        }elseif($order->is_guest == 1){
            $customerFcmToken = $order->guest ? $order->guest->fcm_token : null;
        }

        try {
            if ($customerFcmToken != null) {
                $data = [
                    'title' => translate('Order'),
                    'description' => translate('order delivery area updated'),
                    'order_id' => $order['id'],
                    'image' => '',
                    'type' => 'order_status',
                ];
                Helpers::send_push_notif_to_device($customerFcmToken, $data);
            }
        } catch (\Exception $e) {
            //
        }

        Toastr::success(translate('Order delivery area updated successfully.'));
        return back();
    }

}
