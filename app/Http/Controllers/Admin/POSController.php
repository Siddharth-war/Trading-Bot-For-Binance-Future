<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\AddOn;
use App\Model\Admin;
use App\Model\Branch;
use App\Model\Category;
use App\Model\CustomerAddress;
use App\Model\DeliveryMan;
use App\Model\Notification;
use App\Model\Product;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\ProductByBranch;
use App\Model\Table;
use App\Models\DeliveryChargeByArea;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;
use PhpParser\Node\Stmt\DeclareDeclare;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Http;
use function App\CentralLogics\translate;
use App\Models\Group as ManageGroup;
class POSController extends Controller
{
    public function __construct(
        private User            $user,
        private Table           $table,
        private Admin           $admin,
        private Branch          $branch,
        private Product         $product,
        private Category        $category,
        private ProductByBranch $productByBranch,
        private Order           $order,
        private OrderDetail     $order_detail,
        private Notification    $notification,
        private DeliveryMan     $delivery_man
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function index(Request $request): Factory|View|Application
    {
        $selected_branch = session()->get('branch_id') ?? 1;
        session()->put('branch_id', $selected_branch);

        $category = $request->query('category_id', 0);
        $categories = $this->category->where(['position' => 0])->active()->get();
        $keyword = $request->keyword;
        $key = explode(' ', $keyword);

        $selected_customer = $this->user->where('id', session('customer_id'))->first();
        $selected_table = $this->table->where('id', session('table_id'))->first();
        $tables = $this->table->where(['is_active' => 1, 'branch_id' => $selected_branch])->get();

        $products = $this->product
            ->with(['branch_products' => function ($q) use ($selected_branch) {
                $q->where(['is_available' => 1, 'branch_id' => $selected_branch]);
            }])
            ->whereHas('branch_products', function ($q) use ($selected_branch) {
                $q->where(['is_available' => 1, 'branch_id' => $selected_branch]);
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

            $user = null;
            $group = null;

            if(session()->get('customer_id')){
                $user = User::find(session()->get('customer_id'));
                $group = ManageGroup::with('price')->find($user->group_id);

            }


        $current_branch = $this->admin->find(auth('admin')->id());
        $branches = $this->branch->select('id', 'name')->get();
        return view('admin-views.pos.index', compact('categories', 'products', 'category', 'keyword', 'current_branch', 'branches', 'selected_customer', 'selected_table', 'tables','group'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function quick_view(Request $request): JsonResponse
    {
        $product = $this->product->findOrFail($request->product_id);
        $data = json_decode($product->category_ids, true);
        $ids = array_column($data, 'id');
        $user = User::find(session()->get('customer_id'));
        $group = ManageGroup::with('price')->find($user->group_id);

        $prices = $group?->price->whereIn("category_id",$ids)->first();

        $price = $prices ? $prices->price ? $prices->price : 0 :0;

        return response()->json([
            'success' => 1,
            'view' => view('admin-views.pos._quick-view-data', compact('product','price'))->render(),
        ]);
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function variant_price(Request $request): array
    {
        $product = $this->product->find($request->id);

        $price = $product->price;
        $addon_price = 0;

        if ($request['addon_id']) {
            foreach ($request['addon_id'] as $id) {
                $addon_price += $request['addon-price' . $id] * $request['addon-quantity' . $id];
            }
        }

        $branch_product = $this->productByBranch->where(['product_id' => $request->id, 'branch_id' => session()->get('branch_id')])->first();

        if (isset($branch_product)) {
            $branch_product_variations = $branch_product->variations;

            $discount_data = [
                'discount_type' => $branch_product['discount_type'],
                'discount' => $branch_product['discount']
            ];

            if ($request->variations && count($branch_product_variations)) {
                $price_total = $branch_product['price'] + Helpers::new_variation_price($branch_product_variations, $request->variations);
                $price = $price_total - Helpers::discount_calculate($discount_data, $price_total);
            } else {
                $price = $branch_product['price'] - Helpers::discount_calculate($discount_data, $branch_product['price']);
            }
        }

        $data = json_decode($product->category_ids, true);
        $ids = array_column($data, 'id');
        $user = User::find(session()->get('customer_id'));
        $group = ManageGroup::with('price')->find($user->group_id);

        $prices = $group?->price->whereIn("category_id",$ids)->first();
        $priced = $prices ? $prices->price ? $prices->price : 0 :0;
        $price = $price + $priced;


        return array('price' => \App\CentralLogics\Helpers::set_symbol(($price * $request->quantity) + $addon_price));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get_customers(Request $request): JsonResponse
    {
        $key = explode(' ', $request['q']);
        $data = $this->user
            ->where(['user_type' => null])
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

    public function update_tax(Request $request): RedirectResponse
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
    public function update_discount(Request $request): RedirectResponse
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

        $total_price = 0;
        foreach (session('cart') as $cart) {
            if (isset($cart['price'])) {
                $total_price += ($cart['price'] - $cart['discount']);
            }
        }

        if ($request->type == 'amount' && $request->discount > $total_price) {
            Toastr::error(translate('Extra_discount_can_not_be_more_total_product_price'));
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
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function addToCart(Request $request): JsonResponse
    {
        $product = $this->product->find($request->id);

        $data = json_decode($product->category_ids, true);
        $ids = array_column($data, 'id');
        $user = User::find(session()->get('customer_id'));
        $group = ManageGroup::with('price')->find($user->group_id);

        $prices = $group?->price->whereIn("category_id",$ids)->first();
        $priced = $prices ? $prices->price ? $prices->price : 0 :0;


        $data = array();
        $data['id'] = $product->id;
        $str = '';
        $variations = [];
        $price = 0;
        $addon_price = 0;
        $addon_total_tax = 0;
        $variation_price = 0;

        $branch_product = $this->productByBranch->where(['product_id' => $request->id, 'branch_id' => session()->get('branch_id')])->first();

        $branch_product_price = 0;
        $discount_data = [];

        if (isset($branch_product)) {
            $branch_product_variations = $branch_product->variations;

            if ($request->variations && count($branch_product_variations)) {
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
                $variation_data = Helpers::get_varient($branch_product_variations, $request->variations);
                $variation_price = $variation_data['price'];
                $variations = $request->variations;

            }

            $branch_product_price = $branch_product['price'];
            $discount_data = [
                'discount_type' => $branch_product['discount_type'],
                'discount' => $branch_product['discount']
            ];
        }

        $price = $branch_product_price + $variation_price;
        $price = $price + $priced;

        $data['variation_price'] = $variation_price;

        $discount_on_product = Helpers::discount_calculate($discount_data, $price);

        $data['variations'] = $variations;
        $data['variant'] = $str;

        $data['quantity'] = $request['quantity'];
        $data['price'] = $price;
        $data['name'] = $product->name;
        $data['discount'] = $discount_on_product;
        $data['image'] = $product->image;
        $data['add_ons'] = [];
        $data['add_on_qtys'] = [];
        $data['add_on_prices'] = [];
        $data['add_on_tax'] = [];

        if ($request['addon_id']) {
            foreach ($request['addon_id'] as $id) {
                $addon_price += $request['addon-price' . $id] * $request['addon-quantity' . $id];
                $data['add_on_qtys'][] = $request['addon-quantity' . $id];

                $add_on = AddOn::find($id);
                $data['add_on_prices'][] = $add_on['price'];
                $add_on_tax = ($add_on['price'] * $add_on['tax']/100);
                $addon_total_tax += (($add_on['price'] * $add_on['tax']/100) * $request['addon-quantity' . $id]);
                $data['add_on_tax'][] = $add_on_tax;
            }
            $data['add_ons'] = $request['addon_id'];
        }

        $data['addon_price'] = $addon_price;
        $data['addon_total_tax'] = $addon_total_tax;

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
    public function place_order(Request $request): RedirectResponse
    {
        $customer_user = User::find(session()->get('customer_id'));


        if ($request->session()->has('cart')) {
            if (count($request->session()->get('cart')) < 1) {
                Toastr::error(translate('cart_empty_warning'));
                return back();
            }
        } else {
            Toastr::error(translate('cart_empty_warning'));
            return back();
        }

        $order_type = session()->has('order_type') ? session()->get('order_type') : 'take_away';

        // if ($order_type == 'dine_in'){
        //     if (!session()->has('table_id')){
        //         Toastr::error(translate('please select a table number'));
        //         return back();
        //     }
        //     if (!session()->has('people_number')){
        //         Toastr::error(translate('please enter people number'));
        //         return back();
        //     }

        //     $table = Table::find(session('table_id'));
        //     if (isset($table) && session('people_number') > $table->capacity  || session('people_number') < 1 ) {
        //         Toastr::error(translate('enter valid people number between 1 to '. $table->capacity));
        //         return back();
        //     }
        // }

        $delivery_charge = 0;
        // store customer address for home delivery
        // if ($order_type == 'home_delivery'){
        //    if (!session()->has('customer_id')){
        //        Toastr::error(translate('please select a customer'));
        //        return back();
        //    }

        //    if (!session()->has('address')){
        //        Toastr::error(translate('please select a delivery address'));
        //        return back();
        //    }

        //    $address_data = session()->get('address');
        //    $distance = $address_data['distance'] ?? 0;
        //    $areaId = $address_data['area_id'];

        //    $delivery_charge = Helpers::get_delivery_charge(branchId: session()->get('branch_id') ?? 1, distance:  $distance, selectedDeliveryArea: $areaId);

        //    $address = [
        //         'address_type' => 'Home',
        //         'contact_person_name' => $address_data['contact_person_name'],
        //         'contact_person_number' => $address_data['contact_person_number'],
        //         'address' => $address_data['address'],
        //         'floor' => $address_data['floor'],
        //         'road' => $address_data['road'],
        //         'house' => $address_data['house'],
        //         'longitude' => (string)$address_data['longitude'],
        //         'latitude' => (string)$address_data['latitude'],
        //         'user_id' => session()->get('customer_id'),
        //         'is_guest' => 0,
        //     ];
        //     $customer_address = CustomerAddress::create($address);
        // }

        $cart = $request->session()->get('cart');
        $total_tax_amount = 0;
        $total_addon_price = 0;
        $total_addon_tax = 0;
        $product_price = 0;
        $order_details = [];

        $order_id = 100000 + $this->order->all()->count() + 1;
        if ($this->order->find($order_id)) {
            $order_id = $this->order->orderBy('id', 'DESC')->first()->id + 1;
        }

        $order = $this->order;
        $order->id = $order_id;

        $order->user_id = session()->get('customer_id') ?? null;
        $order->coupon_discount_title = $request->coupon_discount_title == 0 ? null : 'coupon_discount_title';
        // $order->payment_status = ($order_type == 'take_away') ? 'paid' : (($order_type == 'dine_in' && $request->type != 'pay_after_eating') ? 'paid' : 'unpaid');
        // $order->order_status = $order_type == 'take_away' ? 'delivered' : 'confirmed' ;
        // $order->order_type = ($order_type == 'take_away') ? 'pos' : (($order_type == 'dine_in') ? 'dine_in' : (($order_type == 'home_delivery') ? 'delivery' : null));
        $order->payment_status = 'paid';
        $order->order_status =  'Pending' ;
        $order->order_type = 'delivery';
        $order->coupon_code = $request->coupon_code ?? null;
        $order->payment_method = $request->type;
        $order->transaction_reference = $request->transaction_reference ?? null;
        $order->delivery_charge = $delivery_charge;
        $order->delivery_address_id = $order_type == 'home_delivery' ? $customer_address->id : null;
        $order->delivery_date = Carbon::now()->format('Y-m-d');
        $order->delivery_time = Carbon::now()->format('H:i:s');
        $order->order_note = null;
        $order->checked = 1;
        $order->created_at = now();
        $order->updated_at = now();

        $total_product_main_price = 0;

        // check if discount is more than total price
        $total_price_for_discount_validation = 0;

        foreach ($cart as $c) {
            if (is_array($c)) {
                $discount_on_product = 0;
                $discount = 0;
                $product_subtotal = ($c['price']) * $c['quantity'];
                $discount_on_product += ($c['discount'] * $c['quantity']);

                $total_price_for_discount_validation += $c['price'];

                $product = $this->product->find($c['id']);
                if ($product) {
                    $price = $c['price'];

                    $product = Helpers::product_data_formatting($product);
                    $addon_data = Helpers::calculate_addon_price(AddOn::whereIn('id', $c['add_ons'])->get(), $c['add_on_qtys']);

                    //*** addon quantity integer casting ***
                    array_walk($c['add_on_qtys'], function (&$add_on_qtys) {
                        $add_on_qtys = (int)$add_on_qtys;
                    });
                    //***end***

                    $branch_product = $this->productByBranch->where(['product_id' => $c['id'], 'branch_id' => session()->get('branch_id')])->first();

                    $discount_data = [];
                    if (isset($branch_product)) {
                        $variation_data = Helpers::get_varient($branch_product->variations, $c['variations']);

                        $discount_data = [
                            'discount_type' => $branch_product['discount_type'],
                            'discount' => $branch_product['discount']
                        ];
                    }

                    $discount = Helpers::discount_calculate($discount_data, $price);


                    $variations = $variation_data['variations'];

                    $or_d = [
                        'product_id' => $c['id'],
                        'product_details' => $product,
                        'quantity' => $c['quantity'],
                        'price' => $price,
                        'tax_amount' => Helpers::tax_calculate($product, $price),
                        'discount_on_product' => $discount,
                        'discount_type' => 'discount_on_product',
                        //'variant' => json_encode($c['variant']),
                        'variation' => json_encode($variations),
                        'add_on_ids' => json_encode($addon_data['addons']),
                        'add_on_qtys' => json_encode($c['add_on_qtys']),
                        'add_on_prices' => json_encode($c['add_on_prices']),
                        'add_on_taxes' => json_encode($c['add_on_tax']),
                        'add_on_tax_amount' => $c['addon_total_tax'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    $total_tax_amount += $or_d['tax_amount'] * $c['quantity'];
                    $total_addon_price += $addon_data['total_add_on_price'];

                    $total_addon_tax += $c['addon_total_tax'];

                    $product_price += $product_subtotal - $discount_on_product;
                    $total_product_main_price += $product_subtotal;
                    $order_details[] = $or_d;
                }
            }
        }

        $total_price = $product_price + $total_addon_price;
        if (isset($cart['extra_discount'])) {
            $extra_discount = $cart['extra_discount_type'] == 'percent' && $cart['extra_discount'] > 0 ? (($total_product_main_price * $cart['extra_discount']) / 100) : $cart['extra_discount'];
            $total_price -= $extra_discount;
        }
        if (isset($cart['extra_discount']) && $cart['extra_discount_type'] == 'amount') {
            if ($cart['extra_discount'] > $total_price_for_discount_validation) {
                Toastr::error(translate('discount_can_not_be_more_total_product_price'));
                return back();
            }
        }
        $tax = isset($cart['tax']) ? $cart['tax'] : 0;
        $total_tax_amount = ($tax > 0) ? (($total_price * $tax) / 100) : $total_tax_amount;
        try {
            $order->extra_discount = $extra_discount ?? 0;
            $order->total_tax_amount = $total_tax_amount;
            $order->order_amount = $total_price + $total_tax_amount + $total_addon_tax;
            $order->coupon_discount_amount = 0.00;
            $order->branch_id = session()->get('branch_id');
            $order->table_id = session()->get('table_id');
            $order->number_of_people = session()->get('people_number');

            if (session('branch_id')) {


                if($request->type == 'credit_limit'){
                    $remaining_credit = (int)$customer_user->remaining_credit_limit - (int)$order->order_amount;
                    $customer_user->update(["remaining_credit_limit"=>$remaining_credit]);
                    $order->order_status =  (int)$order->order_amount > (int)$customer_user->remaining_credit_limit ? 'Hold' : 'Pending';
                    $order->payment_status =  (int)$order->order_amount > (int)$customer_user->remaining_credit_limit ? 'unpaid' : 'paid';
                }



                $order->save();

                foreach ($order_details as $key => $item) {
                    $order_details[$key]['order_id'] = $order->id;
                }
                $this->order_detail->insert($order_details);

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
                    $notification = $this->notification;
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
                    $value = Helpers::text_variable_data_format(value:$message, user_name: $customerName, restaurant_name: $restaurantName,  order_id: $order_id);

                    if ($value && isset($customerFcmToken)) {
                        $data = [
                            'title' => translate('Order'),
                            'description' => $value,
                            'order_id' => $order_id,
                            'image' => '',
                            'type' => 'order_status',
                        ];
                        Helpers::send_push_notif_to_device($customerFcmToken, $data);
                    }
                    //send email
                    try {
                        $emailServices = Helpers::get_business_settings('mail_config');
                        $order_mail_status = Helpers::get_business_settings('place_order_mail_status_user');
                        if (isset($emailServices['status']) && $emailServices['status'] == 1 && $order_mail_status == 1 && isset($customer)) {
                            Mail::to($customer->email)->send(new \App\Mail\OrderPlaced($order_id));
                        }
                    }catch (\Exception $e) {
                        //dd($e);
                    }
                }

                return back();
            } else {
                Toastr::warning(translate('Branch select is required'));
            }

        } catch (\Exception $e) {
            info($e);
        }
        //Toastr::warning(translate('failed_to_place_order'));
        return back();
    }

    /**
     * @return Renderable
     */
    public function cart_items(): Renderable
    {
        return view('admin-views.pos._cart');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function emptyCart(Request $request): JsonResponse
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
     * @return JsonResponse
     */
    public function store_keys(Request $request): JsonResponse
    {
        session()->put($request['key'], $request['value']);
        return response()->json($request['key'], 200);
    }

    //order

    /**
     * @param Request $request
     * @return Renderable
     */
    public function order_list(Request $request): Renderable
    {

        $query_param = [];
        $search = $request['search'];
        $branch_id = $request['branch_id'];
        $from = $request['from'];
        $to = $request['to'];

        $query = $this->order->pos()->with(['customer', 'branch']);
        $branches = $this->branch->all();

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $query = $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                        ->orWhere('order_status', 'like', "%{$value}%")
                        ->orWhere('transaction_reference', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        } elseif ($request->has('filter')) {

            $query->when($from && $to && $branch_id == 'all', function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
            })
                ->when($from && $to && $branch_id != 'all', function ($q) use ($from, $to, $branch_id) {
                    $q->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()])
                        ->whereHas('branch', function ($q) use ($branch_id) {
                            $q->where('id', $branch_id);
                        });
                })
                ->when($from == null && $to == null && $branch_id != 'all', function ($q) use ($from, $to, $branch_id) {
                    $q->whereHas('branch', function ($q) use ($branch_id) {
                        $q->where('id', $branch_id);
                    });
                })
                ->get();
            $query_param = ['filter' => '', 'branch_id' => $request['branch_id'] ?? '', 'from' => $request['from'] ?? '', 'to' => $request['to'] ?? ''];

        }

        $orders = $query->latest()->paginate(Helpers::getPagination())->appends($query_param);

        return view('admin-views.pos.order.list', compact('orders', 'search', 'branches', 'from', 'to', 'branch_id'));
    }

    /**
     * @param $id
     * @return Renderable|RedirectResponse
     */
    public function order_details($id): Renderable|RedirectResponse
    {
        $order = $this->order->with('details')->where(['id' => $id])->first();
        if (!isset($order)) {
            Toastr::info(translate('No more orders!'));
            return back();
        }

        $deliverymen = $this->delivery_man->where(['is_active'=>1])
            ->where(function($query) use ($order) {
                $query->where('branch_id', $order->branch_id)
                    ->orWhere('branch_id', 0);
            })
            ->get();

        return view('admin-views.order.order-view', compact('order', 'deliverymen'));
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function generate_invoice($id): JsonResponse
    {
        $order = $this->order->where('id', $id)->first();

        return response()->json([
            'success' => 1,
            'view' => view('admin-views.pos.order.invoice', compact('order'))->render(),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getTableListByBranch(Request $request): JsonResponse
    {
        session()->forget('cart');
        session()->forget('table_id');
        session()->forget('customer_id');
        session()->forget('people_number');
        session()->forget('address');
        session()->forget('order_type');

        return response()->json();
    }

    /**
     * @return RedirectResponse
     */
    public function clear_session_data(): RedirectResponse
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
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|string|RedirectResponse
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\InvalidArgumentException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException
     */
    public function export_excel(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|string|RedirectResponse
    {
        $query = $this->order->pos()->with(['customer', 'branch']);
        if ($request->search != null) {
            $key = explode(' ', $request['search']);
            $orders = $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                        ->orWhere('order_status', 'like', "%{$value}%")
                        ->orWhere('transaction_reference', 'like', "%{$value}%");
                }
            })->get();

        } else {
            $branch_id = $request->branch_id != null ? $request->branch_id : 'all';
            $to = $request->to;
            $from = $request->from;

            $orders = $query->when($from && $to && $branch_id == 'all', function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
            })
                ->when($from && $to && $branch_id != 'all', function ($q) use ($from, $to, $branch_id) {
                    $q->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()])
                        ->whereHas('branch', function ($q) use ($branch_id) {
                            $q->where('id', $branch_id);
                        });
                })
                ->when($from == null && $to == null && $branch_id != 'all', function ($q) use ($from, $to, $branch_id) {
                    $q->whereHas('branch', function ($q) use ($branch_id) {
                        $q->where('id', $branch_id);
                    });
                })->get();
        }

        if ($orders->count() < 1) {
            Toastr::warning(translate('No Data Found'));
            return back();
        }

        $data = array();
        foreach ($orders as $key => $order) {
            $data[] = array(
                'SL' => ++$key,
                'Order ID' => $order->id,
                'Order Date' => date('d M Y', strtotime($order['created_at'])) . ' ' . date("h:i A", strtotime($order['created_at'])),
                'Customer Info' => $order['user_id'] == null ? 'Walk in Customer' : $order->customer['f_name'] . ' ' . $order->customer['l_name'],
                'Branch' => $order->branch ? $order->branch->name : 'Branch Deleted',
                'Total Amount' => Helpers::set_symbol($order['order_amount']),
                'Payment Status' => $order->payment_status == 'paid' ? 'Paid' : 'Unpaid',
                'Order Status' => $order['order_status'] == 'pending' ? 'Pending' : ($order['order_status'] == 'confirmed' ? 'Confirmed' : ($order['order_status'] == 'processing' ? 'Processing' : ($order['order_status'] == 'delivered' ? 'Delivered' : ($order['order_status'] == 'picked_up' ? 'Out For Delivery' : str_replace('_', ' ', $order['order_status']))))),
                'Order Type' => $order['order_type'] == 'take_away' ? 'Take Away' : 'Delivery',
            );
        }

        return (new FastExcel($data))->download('Order_Details.xlsx');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function customer_store(Request $request): RedirectResponse
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
    public function session_destroy(Request $request): JsonResponse
    {
        Session::forget('cart');
        Session::forget('table_id');
        Session::forget('customer_id');
        Session::forget('people_number');
        session()->forget('address');
        session()->forget('order_type');

        return response()->json();
    }

    public function addDeliveryInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_person_name' => 'required',
            'contact_person_number' => 'required',
            'address' => 'required',
            //'latitude' => 'required',
            //'longitude' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 200);
        }

        $branch_id = session()->get('branch_id');
        $branch = $this->branch->find($branch_id);
        $origin_lat = $branch['latitude'];
        $origin_lng = $branch['longitude'];
        $destination_lat = $request['latitude'];
        $destination_lng = $request['longitude'];

        if ($request->has('latitude') && $request->has('longitude')){
            $api_key = Helpers::get_business_settings('map_api_server_key');
            $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $origin_lat . ',' . $origin_lng . '&destinations=' . $destination_lat . ',' . $destination_lng . '&key=' . $api_key);

            //return $response->json();
            $data = json_decode($response, true);
            $distance_value = $data['rows'][0]['elements'][0]['distance']['value'];
            $distance = $distance_value/1000;
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

    public function get_distance(Request $request): mixed
    {
        $request->validate([
            'origin_lat' => 'required',
            'origin_lng' => 'required',
            'destination_lat' => 'required',
            'destination_lng' => 'required',
        ]);

        $api_key = Helpers::get_business_settings('map_api_server_key');
        $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $request['origin_lat'] . ',' . $request['origin_lng'] . '&destinations=' . $request['destination_lat'] . ',' . $request['destination_lng'] . '&key=' . $api_key);

        return $response->json();
    }

    public function order_type_store(Request $request)
    {
        session()->put('order_type', $request['order_type']);
        return response()->json($request['order_type'], 200);
    }

}
