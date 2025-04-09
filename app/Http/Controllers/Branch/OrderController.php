<?php

namespace App\Http\Controllers\Branch;

use App\CentralLogics\CustomerLogic;
use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;
use App\Http\Controllers\Controller;
use App\Model\Branch;
use App\Model\BusinessSetting;
use App\Model\CustomerAddress;
use App\Model\Order;
use App\Models\DeliveryChargeByArea;
use App\Models\OfflinePayment;
use App\Models\OrderArea;
use App\Models\OrderPartialPayment;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use DateTime;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Support\Renderable;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function App\CentralLogics\translate;

class OrderController extends Controller
{
    public function __construct(
        private Order           $order,
        private User            $user,
        private BusinessSetting $business_setting,
        private CustomerAddress $customer_addresses,
        private OrderArea $orderArea,
    ){}

    /**
     * @param $status
     * @param Request $request
     * @return Renderable
     */
    public function list($status, Request $request): Renderable
    {
        Helpers::update_daily_product_stock();

        $from = $request['from'];
        $to = $request['to'];

        $this->order->where(['checked' => 0, 'branch_id' => auth('branch')->id()])->update(['checked' => 1]);

        if ($status == 'all') {
            $orders = $this->order
                ->with(['customer'])
                ->where(['branch_id' => auth('branch')->id()]);

        } elseif ($status == 'schedule') {
            $orders = $this->order
                ->whereDate('delivery_date', '>', \Carbon\Carbon::now()->format('Y-m-d'))
                ->where(['branch_id' => auth('branch')->id()]);

        } else {
            $orders = $this->order
                ->with(['customer'])
                ->where(['order_status' => $status, 'branch_id' => auth('branch')->id()])
                ->whereDate('delivery_date', '<=', \Carbon\Carbon::now()->format('Y-m-d'));
        }

        $queryParam = [];
        $search = $request['search'];

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $orders = $this->order
                ->where(['branch_id' => auth('branch')->id()])
                ->whereDate('delivery_date', '<=', Carbon::now()->format('Y-m-d'))
                ->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('id', 'like', "%{$value}%")
                            ->orWhere('order_status', 'like', "%{$value}%")
                            ->orWhere('transaction_reference', 'like', "%{$value}%");
                    }
                });
            $queryParam = ['search' => $request['search']];
        }

        if ($from && $to) {
            $orders = $this->order->whereBetween('created_at', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()]);
            $queryParam = ['from' => $from, 'to' => $to];
        }

        $orderCount = [
            'pending' => $this->order
                ->notPos()
                ->notSchedule()
                ->where(['order_status' => 'pending', 'branch_id' => auth('branch')->id()])
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })->count(),

            'confirmed' => $this->order
                ->notPos()
                ->notSchedule()
                ->where(['order_status' => 'confirmed', 'branch_id' => auth('branch')->id()])
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })->count(),

            'processing' => $this->order
                ->notPos()
                ->notSchedule()
                ->where(['order_status' => 'processing', 'branch_id' => auth('branch')->id()])
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })->count(),

            'out_for_delivery' => $this->order
                ->notPos()
                ->notSchedule()
                ->where(['order_status' => 'out_for_delivery', 'branch_id' => auth('branch')->id()])
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })->count(),

            'delivered' => $this->order
                ->notPos()
                ->notSchedule()
                ->where(['order_status' => 'delivered', 'branch_id' => auth('branch')->id()])
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })->count(),

            'canceled' => $this->order
                ->notPos()
                ->notSchedule()
                ->where(['order_status' => 'canceled', 'branch_id' => auth('branch')->id()])
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })->count(),

            'returned' => $this->order
                ->notPos()
                ->notSchedule()
                ->where(['order_status' => 'returned', 'branch_id' => auth('branch')->id()])
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })->count(),

            'failed' => $this->order
                ->notPos()
                ->notSchedule()
                ->where(['order_status' => 'failed', 'branch_id' => auth('branch')->id()])
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })->count(),
        ];

        $orders = $orders->notPos()->notDineIn()->latest()->paginate(Helpers::getPagination())->appends($queryParam);
        session()->put('order_data_export', $orders);

        return view('branch-views.order.list', compact('orders', 'status', 'search', 'from', 'to', 'orderCount'));
    }

    /**
     * @param $id
     * @return Renderable|RedirectResponse
     */
    public function details($id): Renderable|RedirectResponse
    {
        $order = $this->order
            ->with(['details','order_partial_payments'])
            ->where(['id' => $id, 'branch_id' => auth('branch')->id()])
            ->first();

        if (!isset($order)) {
            Toastr::info(translate('Order not found!'));
            return back();
        }

        //remaining delivery time
        $deliveryDateTime = $order['delivery_date'] . ' ' . $order['delivery_time'];
        $orderedTime = Carbon::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s", strtotime($deliveryDateTime)));
        $remainingTime = $orderedTime->add($order['preparation_time'], 'minute')->format('Y-m-d H:i:s');
        $order['remaining_time'] = $remainingTime;

        return view('branch-views.order.order-view', compact('order'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
    {
        $order = $this->order
            ->where(['id' => $request->id, 'branch_id' => auth('branch')->id()])
            ->first();

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
                    $ol = OrderLogic::create_transaction($order, 'admin');
                }

                $user = $this->user->find($order->user_id);
                if(isset($user)){
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
            Toastr::warning(translate('Push notification failed for Customer!'));
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
            $deliveryTime = new DateTime($remainingTime); //time when preparation will be over
            $currentTime = new DateTime(); // time now
            $interval = $deliveryTime->diff($currentTime);
            $remainingMinutes = $interval->i;
            $remainingMinutes += $interval->days * 24 * 60;
            $remainingMinutes += $interval->h * 60;
            $order->preparation_time = 0;
        } else {
            //if delivery time is over
            $deliveryTime = new DateTime($remainingTime);
            $currentTime = new DateTime();
            $interval = $deliveryTime->diff($currentTime);
            $diffInMinutes = $interval->i;
            $diffInMinutes += $interval->days * 24 * 60;
            $diffInMinutes += $interval->h * 60;
            $order->preparation_time = 0;
        }

        $newDeliveryDateTime = Carbon::now()->addMinutes($request->extra_minute);
        $order->delivery_date = $newDeliveryDateTime->format('Y-m-d');
        $order->delivery_time = $newDeliveryDateTime->format('H:i:s');
        $order->save();

        $customer = $order->customer;
        $customerFcmToken = null;
        $customerFcmToken = $customer?->cm_firebase_token;
        $value = Helpers::order_status_update_message('customer_notify_message_for_time_change');

        try {
            if ($value) {
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

        Toastr::success(translate('Order preparation time increased'));
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
        $order = $this->order->where(['id' => $order_id, 'branch_id' => auth('branch')->id()])->first();
        if ($order->order_status == 'pending' || $order->order_status == 'delivered' || $order->order_status == 'returned' || $order->order_status == 'failed' || $order->order_status == 'canceled' || $order->order_status == 'scheduled') {
            return response()->json(['status' => false], 200);
        }
        $order->delivery_man_id = $delivery_man_id;
        $order->save();

        $deliverymanFcmToken = $order->delivery_man->fcm_token;
        $customerFcmToken = null;
        if (isset($order->customer)) {
            $customerFcmToken = $order->customer->cm_firebase_token;
        }
        $value = Helpers::order_status_update_message('del_assign');
        try {
            if ($value) {
                $data = [
                    'title' => translate('Order'),
                    'description' => $value,
                    'order_id' => $order['id'],
                    'image' => '',
                    'type' => 'order_status',
                ];
                Helpers::send_push_notif_to_device(fcm_token: $deliverymanFcmToken, data: $data, isDeliverymanAssigned: true);
            }
        } catch (\Exception $e) {
            Toastr::warning(translate('Push notification failed for DeliveryMan!'));
        }

        Toastr::success(translate('Order deliveryman added!'));
        return response()->json(['status' => true], 200);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function paymentStatus(Request $request): RedirectResponse
    {
        $order = $this->order->where(['id' => $request->id, 'branch_id' => auth('branch')->id()])->first();
        if ($request->payment_status == 'paid' && $order['transaction_reference'] == null && $order['payment_method'] != 'cash_on_delivery' && $order['order_type'] != 'dine_in' && !in_array($order['payment_method'], ['cash_on_delivery', 'wallet_payment', 'offline_payment', 'cash'])) {
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
            'contact_person_number' => 'required',
            'address' => 'required'
        ]);

        $this->customer_addresses->where('id', $id)->update([
            'contact_person_name' => $request->contact_person_name,
            'contact_person_number' => $request->contact_person_number,
            'address_type' => $request->address_type,
            'floor' => $request->floor,
            'house' => $request->house,
            'road' => $request->road,
            'address' => $request->address,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Toastr::success(translate('Address updated!'));
        return back();
    }

    /**
     * @param $id
     * @return Renderable
     */
    public function generateInvoice($id): Renderable
    {
        $order = $this->order->with(['order_partial_payments'])->where(['id' => $id, 'branch_id' => auth('branch')->id()])->first();
        return view('branch-views.order.invoice', compact('order'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function addPaymentReferenceCode(Request $request, $id): RedirectResponse
    {
        $this->order->where(['id' => $id, 'branch_id' => auth('branch')->id()])->update([
            'transaction_reference' => $request['transaction_reference']
        ]);

        Toastr::success(translate('Payment reference code is added!'));
        return back();
    }

    /**
     * @return StreamedResponse|string
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\InvalidArgumentException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException
     */
    public function exportExcel(): StreamedResponse|string
    {
        $data = [];
        $orders = session('order_data_export');
        foreach ($orders as $key => $order) {
            $data[$key]['SL'] = ++$key;
            $data[$key]['Order ID'] = $order->id;
            $data[$key]['Order Date'] = date('d M Y h:m A', strtotime($order['created_at']));
            $data[$key]['Customer Info'] = $order['user_id'] == null ? 'Walk in Customer' : ($order->customer == null ? 'Customer Unavailable' : $order->customer['f_name'] . ' ' . $order->customer['l_name']);
            $data[$key]['Branch'] = $order->branch ? $order->branch->name : 'Branch Deleted';
            $data[$key]['Total Amount'] = Helpers::set_symbol($order['order_amount']);
            $data[$key]['Payment Status'] = $order->payment_status == 'paid' ? 'Paid' : 'Unpaid';
            $data[$key]['Order Status'] = $order['order_status'] == 'pending' ? 'Pending' : ($order['order_status'] == 'confirmed' ? 'Confirmed' : ($order['order_status'] == 'processing' ? 'Processing' : ($order['order_status'] == 'delivered' ? 'Delivered' : ($order['order_status'] == 'picked_up' ? 'Out For Delivery' : str_replace('_', ' ', $order['order_status'])))));
        };
        return (new FastExcel($data))->download('orders.xlsx');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function changeDeliveryTimeDate(Request $request): JsonResponse
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
     * @param Request $request
     * @param $status
     * @return Application|Factory|View
     */
    public function offlineOrderList(Request $request, $status): Factory|View|Application
    {
        $search = $request['search'];
        $statusMapping = [
            'pending' => 0,
            'denied' => 2,
        ];

        $status = $statusMapping[$status];

        $orders = $this->order->with(['offline_payment'])
            ->where(['branch_id' => auth('branch')->id(), 'payment_method' => 'offline_payment'])
            ->whereHas('offline_payment', function ($query) use($status){
                $query->where('status', $status);
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('id', 'LIKE', '%' . $key . '%')
                            ->orWhere('order_status', 'LIKE', "%{$key}%")
                            ->orWhere('payment_status', 'LIKE', "{$key}%");
                    }
                });
            })
            ->latest()
            ->paginate(Helpers::getPagination());

        return view('branch-views.order.offline-payment.list', compact('orders', 'search'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function offlineViewDetails(Request $request): JsonResponse
    {
        $order = $this->order->find($request->id);

        return response()->json([
            'view' => view('branch-views.order.offline-payment.details-quick-view', compact('order'))->render(),
        ]);
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
