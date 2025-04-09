<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\AddOn;
use App\Model\Notification;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\Product;
use App\Model\ProductByBranch;
use App\Model\Table;
use App\Model\TableOrder;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use function App\CentralLogics\translate;

class TableController extends Controller
{
    public function __construct(
        private Table           $table,
        private Order           $order,
        private OrderDetail     $order_detail,
        private TableOrder      $tableOrder,
        private Notification    $notification,
        private Product         $product,
        private ProductByBranch $productByBranch
    )
    {}

    /**
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        Helpers::update_daily_product_stock();

        $tables = $this->table->where('is_active', 1)->paginate(Helpers::getPagination());
        return response()->json($tables, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function placeOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_amount' => 'required',
            'table_id' => 'required',
            'branch_id' => 'required',
            'delivery_time' => 'required',
            'delivery_date' => 'required',
            'number_of_people' => 'required',
            'payment_status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        Helpers::update_daily_product_stock();

        try {
            $order = $this->order;
            $order->id = 100000 + $this->order->all()->count() + 1;
            $order->user_id = $request->id;
            $order->order_amount = Helpers::set_price($request['order_amount']);
            $order->coupon_discount_amount = Helpers::set_price($request->coupon_discount_amount);
            $order->coupon_discount_title = $request->coupon_discount_title == 0 ? null : 'coupon_discount_title';
            $order->payment_method = $request->payment_method;
            $order->payment_status = $request->payment_status;
            $order->order_status = 'confirmed';
            $order->coupon_code = $request['coupon_code'];
            $order->transaction_reference = $request->transaction_reference ?? null;
            $order->order_note = $request['order_note'];
            $order->order_type = 'dine_in';
            $order->branch_id = $request['branch_id'];
            $order->checked = 0;

            $order->delivery_date = Carbon::now()->format('Y-m-d');
            $order->delivery_time = Carbon::now()->format('H:i:s');

            $order->preparation_time = Helpers::get_business_settings('default_preparation_time') ?? 0;

            $order->table_id = $request['table_id'];
            $order->number_of_people = $request['number_of_people'];

            $order->created_at = now();
            $order->updated_at = now();

            $tokenCheck = $this->tableOrder->where(['table_id' => $request->table_id, 'branch_table_token' => $request->branch_table_token, 'branch_table_token_is_expired' => '0'])->first();

            if (isset($tokenCheck)) {
                $order->table_order_id = $tokenCheck->id;

                if ($request->payment_status == 'paid') {
                    $checkUnpaidOrders = $this->order->where(['table_order_id' => $tokenCheck->id])->get();
                    foreach ($checkUnpaidOrders as $checkUnpaidOrder) {
                        $checkUnpaidOrder->payment_status = 'paid';
                        $checkUnpaidOrder->save();
                    }
                }
            } else {
                $tableOrder = $this->tableOrder;
                $tableOrder->table_id = $request->table_id;
                $tableOrder->branch_table_token = Str::random(50);
                $tableOrder->branch_table_token_is_expired = 0;
                $tableOrder->save();

                $order->table_order_id = $tableOrder->id;
            }

            $order->save();

            $totalAddonTax = 0;

            foreach ($request['cart'] as $c) {
                $product = $this->product->find($c['product_id']);

                //new variation price calculation
                $branch_product = $this->productByBranch->where(['product_id' => $c['product_id'], 'branch_id' => $request['branch_id']])->first();

                //daily and fixed stock quantity validation
                if($branch_product->stock_type == 'daily' || $branch_product->stock_type == 'fixed' ){
                    $available_stock = $branch_product->stock - $branch_product->sold_quantity;
                    if ($available_stock < $c['quantity']){
                        return response()->json(['errors' => [['code' => 'stock', 'message' => translate('stock limit exceeded')]]], 403);
                    }
                }

                $discount_data = [];

                if ($branch_product) {
                    $branch_product_variations = $branch_product->variations;
                    $variations = [];
                    if (count($branch_product_variations)) {
                        $variation_data = Helpers::get_varient($branch_product_variations, $c['variations']);
                        $price = $branch_product['price'] + $variation_data['price'];
                        $variations = $variation_data['variations'];
                    } else {
                        $price = $product['price'];
                    }
                    $discount_data = [
                        'discount_type' => $branch_product['discount_type'],
                        'discount' => $branch_product['discount'],
                    ];
                } else {
                    $product_variations = json_decode($product->variations, true);
                    $variations = [];
                    if (count($product_variations)) {
                        $variation_data = Helpers::get_varient($product_variations, $c['variations']);
                        $price = $product['price'] + $variation_data['price'];
                        $variations = $variation_data['variations'];
                    } else {
                        $price = $product['price'];
                    }
                    $discount_data = [
                        'discount_type' => $product['discount_type'],
                        'discount' => $product['discount'],
                    ];
                }

                $discount_on_product = Helpers::discount_calculate($discount_data, $price);

                /*calculation for addon and addon tax start*/
                $add_on_quantities = $c['add_on_qtys'];
                $add_on_prices = [];
                $add_on_taxes = [];

                foreach($c['add_on_ids'] as $key =>$id){
                    $addon = AddOn::find($id);
                    $add_on_prices[] = $addon['price'];
                    $add_on_taxes[] = ($addon['price']*$addon['tax'])/100;
                }

                $totalAddonTax = array_reduce(
                    array_map(function ($a, $b) {
                        return $a * $b;
                    }, $add_on_quantities, $add_on_taxes),
                    function ($carry, $item) {
                        return $carry + $item;
                    },
                    0
                );
                /*calculation for addon and addon tax end*/

                $order_d = [
                    'order_id' => $order->id,
                    'product_id' => $c['product_id'],
                    'product_details' => $product,
                    'quantity' => $c['quantity'],
                    'price' => $price,
                    'tax_amount' => Helpers::tax_calculate($product, $price),
                    'discount_on_product' => $discount_on_product,
                    'discount_type' => 'discount_on_product',
                    'variant' => json_encode($c['variant']),
                    'variation' => json_encode($variations),
                    'add_on_ids' => json_encode($c['add_on_ids']),
                    'add_on_qtys' => json_encode($c['add_on_qtys']),
                    'add_on_prices' => json_encode($add_on_prices),
                    'add_on_taxes' => json_encode($add_on_taxes),
                    'add_on_tax_amount' => $totalAddonTax,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $this->order_detail->insert($order_d);

                //update product popularity point
                $this->product->find($c['product_id'])->increment('popularity_count');

                //daily and fixed stock quantity update
                if($branch_product->stock_type == 'daily' || $branch_product->stock_type == 'fixed' ){
                    $branch_product->sold_quantity += $c['quantity'];
                    $branch_product->save();
                }
            }

            $notification = $this->notification;
            $notification->title = "You have a new order from Table - (Order Confirmed). ";
            $notification->description = $order->id;
            $notification->status = 1;

            try {
                Helpers::send_push_notif_to_topic(data: $notification, topic: "kitchen-{$order->branch_id}", type: 'general', isNotificationPayloadRemove: true);

                $data = [
                    'title' => translate('New Order Notification'),
                    'description' => translate('You have new order, Check Please'),
                    'order_id' => $order->id,
                    'image' => '',
                    'type' => 'new_order_admin',
                ];

                Helpers::send_push_notif_to_topic(data: $data, topic: 'admin_message', type: 'order_request', web_push_link: route('admin.orders.list',['status'=>'all']));
                Helpers::send_push_notif_to_topic(data: $data, topic: 'branch-order-'. $order->branch_id .'-message', type: 'order_request', web_push_link: route('branch.orders.list',['status'=>'all']));

                Toastr::success(translate('Notification sent successfully!'));
            } catch (\Exception $e) {
                Toastr::warning(translate('Push notification failed!'));
            }
            $token = $this->tableOrder->where('id', $order['table_order_id'])->first();

            return response()->json([
                'message' => translate('order_placed_successfully!!'),
                'order_id' => $order->id,
                'branch_table_token' => $token->branch_table_token,
            ], 200);


        } catch (\Exception $e) {
            return response()->json([$e], 403);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function orderDetails(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'branch_table_token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $branchTableToken = $this->tableOrder->where(['branch_table_token' => $request->branch_table_token])->first();

        if (isset($branchTableToken)) {
            $order = $this->order->where(['id' => $request->order_id, 'table_order_id' => $branchTableToken->id])->first();
            $details = $this->order_detail->where(['order_id' => $order->id])->get();
            $details = isset($details) ? Helpers::order_details_formatter($details) : null;

            return response()->json([
                'order' => $order,
                'details' => $details
            ], 200);
        }

        return response()->json(['message' => 'No data found']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function tableOrderList(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'branch_table_token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $tokenCheck = $this->tableOrder->where(['branch_table_token' => $request->branch_table_token, 'branch_table_token_is_expired' => '0'])->first();
        if (isset($tokenCheck)) {
            $order = $this->order->where(['table_order_id' => $tokenCheck->id])->get();
            return response()->json(['order' => $order], 200);
        }

        return response()->json(['message' => 'no data found']);
    }
}
