<?php

namespace App\Http\Controllers\Branch;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\AddOn;
use App\Model\Branch;
use App\Model\Category;
use App\Model\CustomerAddress;
use App\Model\Notification;
use App\Model\Product;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\ProductByBranch;
use App\Model\Table;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Models\DeliveryChargeByArea;
use function App\CentralLogics\translate;

class POSController extends Controller
{
    public function __construct(
        private Category        $category,
        private Order           $order,
        private User            $user,
        private Table           $table,
        private Product         $product,
        private Branch          $branch,
        private ProductByBranch $product_by_Branch,
    )
    {}

    /**
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request): Renderable
    {
        $category = $request->query('category_id', 0);
        $categories = $this->category->where(['position' => 0])->active()->get();
        $keyword = $request->keyword;
        $key = explode(' ', $keyword);
        $selectedCustomer = $this->user->where('id', session('customer_id'))->first();
        $selectedTable = $this->table->where('id', session('table_id'))->first();

        $products = $this->product
            ->with('product_by_branch')
            ->with(['product_by_branch' => function ($q) {
                $q->where(['is_available' => 1, 'branch_id' => auth('branch')->id()]);
            }])
            ->whereHas('product_by_branch', function ($q) {
                $q->where(['is_available' => 1, 'branch_id' => auth('branch')->id()]);
            })
            ->when($request->has('category_id') && $request['category_id'] != 0, function ($query) use ($request) {
                $query->whereJsonContains('category_ids', [['id' => (string)$request['category_id']]]);
            })
            ->when($keyword, function ($query) use ($key) {
                return $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                });
            })
            ->active()
            ->latest()
            ->paginate(Helpers::getPagination());

        $branch = $this->branch->find(auth('branch')->id());
        $tables = $this->table->where(['branch_id' => auth('branch')->id()])->get();

        return view('branch-views.pos.index', compact('categories', 'products', 'category', 'keyword', 'branch', 'tables', 'selectedTable', 'selectedCustomer'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function quickView(Request $request): JsonResponse
    {
        $product = $this->product->with('product_by_branch')->findOrFail($request->product_id);

        return response()->json([
            'success' => 1,
            'view' => view('branch-views.pos._quick-view-data', compact('product'))->render(),
        ]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function variantPrice(Request $request): array
    {
        $product = $this->product->find($request->id);
        $price = $product->price;
        $addonPrice = 0;

        if ($request['addon_id']) {
            foreach ($request['addon_id'] as $id) {
                $addonPrice += $request['addon-price' . $id] * $request['addon-quantity' . $id];
            }
        }

        $branchProduct = $this->product_by_Branch->where(['product_id' => $request->id, 'branch_id' => auth('branch')->id()])->first();

        if (isset($branchProduct)) {
            $branchProductVariations = $branchProduct->variations;
            $discountData = [
                'discount_type' => $branchProduct['discount_type'],
                'discount' => $branchProduct['discount']
            ];

            if ($request->variations && count($branchProductVariations)) {
                $priceTotal = $branchProduct['price'] + Helpers::new_variation_price($branchProductVariations, $request->variations);
                $price = $priceTotal - Helpers::discount_calculate($discountData, $priceTotal);
            } else {
                $price = $branchProduct['price'] - Helpers::discount_calculate($discountData, $branchProduct['price']);
            }
        }
        return array('price' => Helpers::set_symbol(($price * $request->quantity) + $addonPrice));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCustomers(Request $request): JsonResponse
    {
        $key = explode(' ', $request['q']);
        $data = $this->user
            ->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%");
                }
            })
            ->whereNotNull(['f_name', 'l_name', 'phone'])
            ->limit(8)
            ->get([DB::raw('id, CONCAT(f_name, " ", l_name, " (", phone ,")") as text')]);

        $data[] = (object)['id' => false, 'text' => translate('walk_in_customer')];

        return response()->json($data);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateTax(Request $request): RedirectResponse
    {
        if ($request->tax < 0) {
            Toastr::error(translate('Tax_can_not_be_less_than_0_percent'));
            return back();
        } elseif ($request->tax > 100) {
            Toastr::error(translate('Tax_can_not_be_more_than_100_percent'));
            return back();
        }

        $cart = $request->session()->get('cart', collect([]));
        $cart['tax'] = $request->tax;
        $request->session()->put('cart', $cart);

        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateDiscount(Request $request): RedirectResponse
    {
        if (session()->has('cart')) {
            if (count(session()->get('cart')) < 1) {
                Toastr::error(translate('cart_empty_warning'));
                return back();
            }
        } else {
            Toastr::error(translate('cart_empty_warning'));
            return back();
        }

        if ($request->type == 'percent' && $request->discount < 0) {
            Toastr::error(translate('Extra_discount_can_not_be_less_than_0_percent'));
            return back();
        } elseif ($request->type == 'percent' && $request->discount > 100) {
            Toastr::error(translate('Extra_discount_can_not_be_more_than_100_percent'));
            return back();
        }

        $cart = $request->session()->get('cart', collect([]));
        $cart['extra_discount_type'] = $request->type;
        $cart['extra_discount'] = $request->discount;

        $request->session()->put('cart', $cart);
        return back();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateQuantity(Request $request): JsonResponse
    {
        $cart = $request->session()->get('cart', collect([]));
        $cart = $cart->map(function ($object, $key) use ($request) {
            if ($key == $request->key) {
                $object['quantity'] = $request->quantity;
            }
            return $object;
        });
        $request->session()->put('cart', $cart);

        return response()->json([], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addToCart(Request $request): JsonResponse
    {
        $product = $this->product->find($request->id);

        $data = array();
        $data['id'] = $product->id;
        $str = '';
        $variations = [];
        $price = 0;
        $addonPrice = 0;
        $variationPrice = 0;
        $addonTotalTax = 0;

        $branchProduct = $this->product_by_Branch->where(['product_id' => $request->id, 'branch_id' => auth('branch')->id()])->first();
        $branchProductPrice = 0;
        $discountData = [];

        if (isset($branchProduct)) {
            $branchProductVariations = $branchProduct->variations;

            if ($request->variations && count($branchProductVariations)) {
                foreach ($request->variations as $key => $value) {

                    if ($value['required'] == 'on' && !isset($value['values'])) {
                        return response()->json([
                            'data' => 'variation_error',
                            'message' => translate('Please select items from') . ' ' . $value['name'],
                        ]);
                    }
                    if (isset($value['values']) && $value['min'] != 0 && $value['min'] > count($value['values']['label'])) {
                        return response()->json([
                            'data' => 'variation_error',
                            'message' => translate('Please select minimum ') . $value['min'] . translate(' For ') . $value['name'] . '.',
                        ]);
                    }
                    if (isset($value['values']) && $value['max'] != 0 && $value['max'] < count($value['values']['label'])) {
                        return response()->json([
                            'data' => 'variation_error',
                            'message' => translate('Please select maximum ') . $value['max'] . translate(' For ') . $value['name'] . '.',
                        ]);
                    }
                }
                $variationData = Helpers::get_varient($branchProductVariations, $request->variations);
                $variationPrice = $variationData['price'];
                $variations = $request->variations;

            }

            $branchProductPrice = $branchProduct['price'];
            $discountData = [
                'discount_type' => $branchProduct['discount_type'],
                'discount' => $branchProduct['discount']
            ];

        }
        $price = $branchProductPrice + $variationPrice;
        $data['variation_price'] = $variationPrice;

        $discountOnProduct = Helpers::discount_calculate($discountData, $price);

        $data['variations'] = $variations;
        $data['variant'] = $str;

        $data['quantity'] = $request['quantity'];
        $data['price'] = $price;
        $data['name'] = $product->name;
        $data['discount'] = $discountOnProduct;
        $data['image'] = $product->image;
        $data['add_ons'] = [];
        $data['add_on_qtys'] = [];
        $data['add_on_prices'] = [];
        $data['add_on_tax'] = [];

        if ($request['addon_id']) {
            foreach ($request['addon_id'] as $id) {
                $addonPrice += $request['addon-price' . $id] * $request['addon-quantity' . $id];
                $data['add_on_qtys'][] = $request['addon-quantity' . $id];

                $add_on = AddOn::find($id);
                $data['add_on_prices'][] = $add_on['price'];
                $addonTax = ($add_on['price'] * $add_on['tax']/100);
                $addonTotalTax += (($add_on['price'] * $add_on['tax']/100) * $request['addon-quantity' . $id]);
                $data['add_on_tax'][] = $addonTax;
            }
            $data['add_ons'] = $request['addon_id'];
        }

        $data['addon_price'] = $addonPrice;
        $data['addon_total_tax'] = $addonTotalTax;

        if ($request->session()->has('cart')) {
            $cart = $request->session()->get('cart', collect([]));
            $cart->push($data);
        } else {
            $cart = collect([$data]);
            $request->session()->put('cart', $cart);
        }

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function placeOrder(Request $request): RedirectResponse
    {
        if ($request->session()->has('cart')) {
            if (count($request->session()->get('cart')) < 1) {
                Toastr::error(translate('cart_empty_warning'));
                return back();
            }
        } else {
            Toastr::error(translate('cart_empty_warning'));
            return back();
        }

        $orderType = session()->has('order_type') ? session()->get('order_type') : 'take_away';

        if ($orderType == 'dine_in'){
            if (!session()->has('table_id')){
                Toastr::error(translate('please select a table number'));
                return back();
            }
            if (!session()->has('people_number')){
                Toastr::error(translate('please enter people number'));
                return back();
            }

            $table = Table::find(session('table_id'));
            if (isset($table) && session('people_number') > $table->capacity  || session('people_number') < 1 ) {
                Toastr::error(translate('enter valid people number between 1 to '. $table->capacity));
                return back();
            }
        }

        $deliveryCharge = 0;
        // store customer address for home delivery
        if ($orderType == 'home_delivery'){
            if (!session()->has('customer_id')){
                Toastr::error(translate('please select a customer'));
                return back();
            }

            if (!session()->has('address')){
                Toastr::error(translate('please select a delivery address'));
                return back();
            }

            $addressData = session()->get('address');
            $distance = $addressData['distance'] ?? 0;
            $areaId = $addressData['area_id'];

            $deliveryCharge = Helpers::get_delivery_charge(branchId: auth('branch')->id() ?? 1, distance:  $distance, selectedDeliveryArea: $areaId);

            $address = [
                'address_type' => 'Home',
                'contact_person_name' => $addressData['contact_person_name'],
                'contact_person_number' => $addressData['contact_person_number'],
                'address' => $addressData['address'],
                'floor' => $addressData['floor'],
                'road' => $addressData['road'],
                'house' => $addressData['house'],
                'longitude' => (string)$addressData['longitude'],
                'latitude' => (string)$addressData['latitude'],
                'user_id' => session()->get('customer_id'),
                'is_guest' => 0,
            ];
            $customerAddress = CustomerAddress::create($address);
        }

        $cart = $request->session()->get('cart');
        $totalTaxAmount = 0;
        $totalAddonPrice = 0;
        $totalAddonTax = 0;
        $productPrice = 0;
        $orderDetails = [];

        $orderId = 100000 + $this->order->all()->count() + 1;
        if ($this->order->find($orderId)) {
            $orderId = $this->order->orderBy('id', 'DESC')->first()->id + 1;
        }

        $order = $this->order;
        $order->id = $orderId;

        $order->user_id = session()->get('customer_id') ?? null;
        $order->coupon_discount_title = $request->coupon_discount_title == 0 ? null : 'coupon_discount_title';
        $order->payment_status = ($orderType == 'take_away') ? 'paid' : (($orderType == 'dine_in' && $request->type != 'pay_after_eating') ? 'paid' : 'unpaid');
        $order->order_status = $orderType == 'take_away' ? 'delivered' : 'confirmed' ;
        $order->order_type = ($orderType == 'take_away') ? 'pos' : (($orderType == 'dine_in') ? 'dine_in' : (($orderType == 'home_delivery') ? 'delivery' : null));
        $order->coupon_code = $request->coupon_code ?? null;
        $order->payment_method = $request->type;
        $order->transaction_reference = $request->transaction_reference ?? null;
        $order->delivery_charge = $deliveryCharge;
        $order->delivery_address_id = $orderType == 'home_delivery' ? $customerAddress->id : null;
        $order->delivery_date = Carbon::now()->format('Y-m-d');
        $order->delivery_time = Carbon::now()->format('H:i:s');
        $order->order_note = null;
        $order->checked = 1;
        $order->created_at = now();
        $order->updated_at = now();

        $totalProductMainPrice = 0;
        $totalPriceForDiscountValidation = 0;

        foreach ($cart as $c) {
            if (is_array($c)) {
                $discountOnProduct = 0;
                $discount = 0;
                $productSubtotal = ($c['price']) * $c['quantity'];
                $discountOnProduct += ($c['discount'] * $c['quantity']);

                $totalPriceForDiscountValidation += $c['price'];

                $product = $this->product->find($c['id']);
                if ($product) {
                    $price = $c['price'];

                    $product = Helpers::product_data_formatting($product);
                    $addonData = Helpers::calculate_addon_price(AddOn::whereIn('id', $c['add_ons'])->get(), $c['add_on_qtys']);

                    //*** addon quantity integer casting ***
                    array_walk($c['add_on_qtys'], function (&$add_on_qtys) {
                        $add_on_qtys = (int)$add_on_qtys;
                    });
                    //***end***

                    $branchProduct = $this->product_by_Branch->where(['product_id' => $c['id'], 'branch_id' => auth('branch')->id()])->first();

                    $discountData = [];
                    if (isset($branchProduct)) {
                        $variationData = Helpers::get_varient($branchProduct->variations, $c['variations']);
                        $discountData = [
                            'discount_type' => $branchProduct['discount_type'],
                            'discount' => $branchProduct['discount']
                        ];
                    }

                    $discount = Helpers::discount_calculate($discountData, $price);
                    $variations = $variationData['variations'];

                    $orderData = [
                        'product_id' => $c['id'],
                        'product_details' => $product,
                        'quantity' => $c['quantity'],
                        'price' => $price,
                        'tax_amount' => Helpers::tax_calculate($product, $price),
                        'discount_on_product' => $discount,
                        'discount_type' => 'discount_on_product',
                        'variation' => json_encode($variations),
                        'add_on_ids' => json_encode($addonData['addons']),
                        'add_on_qtys' => json_encode($c['add_on_qtys']),
                        'add_on_prices' => json_encode($c['add_on_prices']),
                        'add_on_taxes' => json_encode($c['add_on_tax']),
                        'add_on_tax_amount' => $c['addon_total_tax'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    $totalTaxAmount += $orderData['tax_amount'] * $c['quantity'];
                    $totalAddonPrice += $addonData['total_add_on_price'];

                    $totalAddonTax += $c['addon_total_tax'];

                    $productPrice += $productSubtotal - $discountOnProduct;
                    $totalProductMainPrice += $productSubtotal;
                    $orderDetails[] = $orderData;
                }
            }
        }

        $totalPrice = $productPrice + $totalAddonPrice;
        if (isset($cart['extra_discount'])) {
            $extraDiscount = $cart['extra_discount_type'] == 'percent' && $cart['extra_discount'] > 0 ? (($totalProductMainPrice * $cart['extra_discount']) / 100) : $cart['extra_discount'];
            $totalPrice -= $extraDiscount;
        }
        if (isset($cart['extra_discount']) && $cart['extra_discount_type'] == 'amount') {
            if ($cart['extra_discount'] > $totalPriceForDiscountValidation) {
                Toastr::error(translate('discount_can_not_be_more_total_product_price'));
                return back();
            }
        }
        $tax = isset($cart['tax']) ? $cart['tax'] : 0;
        $totalTaxAmount = ($tax > 0) ? (($totalPrice * $tax) / 100) : $totalTaxAmount;
        try {
            $order->extra_discount = $extraDiscount ?? 0;
            $order->total_tax_amount = $totalTaxAmount;
            $order->order_amount = $totalPrice + $totalTaxAmount + $order->delivery_charge+$totalAddonTax;

            $order->coupon_discount_amount = 0.00;
            $order->branch_id = auth('branch')->id();
            $order->table_id = session()->get('table_id');
            $order->number_of_people = session()->get('people_number');

            $order->save();

            foreach ($orderDetails as $key => $item) {
                $orderDetails[$key]['order_id'] = $order->id;
            }
            OrderDetail::insert($orderDetails);

            session()->forget('cart');
            session(['last_order' => $order->id]);

            session()->forget('customer_id');
            session()->forget('branch_id');
            session()->forget('table_id');
            session()->forget('people_number');
            session()->forget('address');
            session()->forget('order_type');

            Toastr::success(translate('order_placed_successfully'));

            //send notification to kitchen
            if ($order->order_type == 'dine_in') {
                $notification = new Notification;
                $notification->title = "You have a new order from POS - (Order Confirmed). ";
                $notification->description = $order->id;
                $notification->status = 1;
                $notification->order_id =  $order->id;
                $notification->order_status = $order->order_status;

                try {
                    Helpers::send_push_notif_to_topic(data: $notification, topic: "kitchen-{$order->branch_id}", type: 'general', isNotificationPayloadRemove: true);
                    Toastr::success(translate('Notification sent successfully!'));
                } catch (\Exception $e) {
                    Toastr::warning(translate('Push notification failed!'));
                }
            }

            //send notification to customer for home delivery
            if ($order->order_type == 'delivery'){
                $message = Helpers::order_status_update_message('confirmed');
                $customer = $this->user->find($order->user_id);
                $customerFcmToken = $customer?->cm_firebase_token;
                $local = $customer?->language_code ?? 'en';
                $customerName = $customer?->f_name . ' '. $customer?->l_name ?? '';

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
                $value = Helpers::text_variable_data_format(value:$message, user_name: $customerName, restaurant_name: $restaurantName,  order_id: $orderId);


                if ($value && isset($customerFcmToken)) {
                    $data = [
                        'title' => translate('Order'),
                        'description' => $value,
                        'order_id' => $orderId,
                        'image' => '',
                        'type' => 'order_status',
                    ];
                    Helpers::send_push_notif_to_device($customerFcmToken, $data);
                }

                try {
                    $emailServices = Helpers::get_business_settings('mail_config');
                    $orderMailStatus = Helpers::get_business_settings('place_order_mail_status_user');
                    if (isset($emailServices['status']) && $emailServices['status'] == 1 && $orderMailStatus == 1 && isset($customer)) {
                        Mail::to($customer->email)->send(new \App\Mail\OrderPlaced($orderId));
                    }
                }catch (\Exception $e) {
                    //
                }
            }

            return back();
        } catch (\Exception $e) {
            info($e);
        }

        //Toastr::warning(translate('failed_to_place_order'));
        return back();
    }

    /**
     * @return Renderable
     */
    public function cartItems(): Renderable
    {
        return view('branch-views.pos._cart');
    }

    /**
     * @return JsonResponse
     */
    public function emptyCart(): JsonResponse
    {
        session()->forget('cart');
        Session::forget('table_id');
        Session::forget('customer_id');
        Session::forget('people_number');
        session()->forget('address');
        session()->forget('order_type');

        return response()->json([], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeFromCart(Request $request): JsonResponse
    {
        if ($request->session()->has('cart')) {
            $cart = $request->session()->get('cart', collect([]));
            $cart->forget($request->key);
            $request->session()->put('cart', $cart);
        }

        return response()->json([], 200);
    }

    /**
     * @param Request $request
     * @return Renderable
     */
    public function orderList(Request $request): Renderable
    {
        $from = $request->from;
        $to = $request->to;
        $queryParam = [];
        $search = $request['search'];

        $this->order->where(['checked' => 0])->update(['checked' => 1]);
        $query = $this->order->pos()->with(['customer', 'branch'])->where('branch_id', auth('branch')->id());

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $query = $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                        ->orWhere('order_status', 'like', "%{$value}%")
                        ->orWhere('transaction_reference', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        }

        $orders = $query->latest()->paginate(Helpers::getPagination())->appends($queryParam);

        return view('branch-views.pos.order.list', compact('orders', 'search', 'from', 'to'));
    }

    /**
     * @param $id
     * @return Renderable|RedirectResponse
     */
    public function orderDetails($id): Renderable|RedirectResponse
    {
        $order = $this->order->with('details')->where(['id' => $id, 'branch_id' => auth('branch')->id()])->first();
        if (isset($order)) {
            return view('branch-views.pos.order.order-view', compact('order'));
        } else {
            Toastr::info('No more orders!');
            return back();
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function generateInvoice($id): JsonResponse
    {
        $order = $this->order->where('id', $id)->first();

        return response()->json([
            'success' => 1,
            'view' => view('branch-views.pos.order.invoice', compact('order'))->render(),
        ]);
    }

    /**
     * @return RedirectResponse
     */
    public function clearSessionData(): RedirectResponse
    {
        session()->forget('customer_id');
        session()->forget('branch_id');
        session()->forget('table_id');
        session()->forget('people_number');
        session()->forget('address');
        session()->forget('order_type');
        Toastr::success(translate('clear data successfully'));

        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function customerStore(Request $request): RedirectResponse
    {
        $request->validate([
            'f_name' => 'required',
            'l_name' => 'required',
            'phone' => 'required',
            'email' => 'required|email',
        ]);

        $user_phone = $this->user->where('phone', $request->phone)->first();
        if (isset($user_phone)){
            Toastr::error(translate('The phone is already taken'));
            return back();
        }

        $user_email = $this->user->where('email', $request->email)->first();
        if (isset($user_email)){
            Toastr::error(translate('The email is already taken'));
            return back();
        }

        $this->user->create([
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt('password'),
        ]);

        Toastr::success(translate('customer added successfully'));
        return back();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store_keys(Request $request): JsonResponse
    {
        session()->put($request['key'], $request['value']);
        return response()->json($request['key'], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function sessionDestroy(Request $request): JsonResponse
    {
        Session::forget('cart');
        Session::forget('table_id');
        Session::forget('customer_id');
        Session::forget('people_number');
        session()->forget('address');
        session()->forget('order_type');

        return response()->json();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addDeliveryInfo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'contact_person_name' => 'required',
            'contact_person_number' => 'required',
            'address' => 'required',
//            'latitude' => 'required',
//            'longitude' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 200);
        }

        $branchId = auth('branch')->id();
        $branch = $this->branch->find($branchId);
        $originLat = $branch['latitude'];
        $originLng = $branch['longitude'];
        $destinationLat = $request['latitude'];
        $destinationLng = $request['longitude'];

        if ($request->has('latitude') && $request->has('longitude')){
            $apiKey = Helpers::get_business_settings('map_api_server_key');
            $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $originLat . ',' . $originLng . '&destinations=' . $destinationLat . ',' . $destinationLng . '&key=' . $apiKey);

            $data = json_decode($response, true);
            $distanceValue = $data['rows'][0]['elements'][0]['distance']['value'];
            $distance = $distanceValue/1000;
        }

        if ($request['selected_area_id']){
            $area = DeliveryChargeByArea::find($request['selected_area_id']);
        }

        $address = [
            'contact_person_name' => $request->contact_person_name,
            'contact_person_number' => $request->contact_person_number,
            'address_type' => 'Home',
            'address' => $request->address,
            'floor' => $request->floor,
            'road' => $request->road,
            'house' => $request->house,
            'distance' => $distance ?? 0,
            'longitude' => (string)$request->longitude,
            'latitude' => (string)$request->latitude,
            'area_id' => $request['selected_area_id'],
            'area_name' => $area->area_name ?? null
        ];

        $request->session()->put('address', $address);

        return response()->json([
            'data' => $address,
            'view' => view('admin-views.pos._address', compact('address'))->render(),
        ]);
    }

    public function getDistance(Request $request): mixed
    {
        $request->validate([
            'origin_lat' => 'required',
            'origin_lng' => 'required',
            'destination_lat' => 'required',
            'destination_lng' => 'required',
        ]);

        $apiKey = Helpers::get_business_settings('map_api_server_key');
        $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $request['origin_lat'] . ',' . $request['origin_lng'] . '&destinations=' . $request['destination_lat'] . ',' . $request['destination_lng'] . '&key=' . $apiKey);

        return $response->json();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function orderTypeStore(Request $request): JsonResponse
    {
        session()->put('order_type', $request['order_type']);
        return response()->json($request['order_type'], 200);
    }



}
