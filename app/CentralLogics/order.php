<?php

namespace App\CentralLogics;

use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\Product;
use App\Model\OrderTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderLogic
{
    public static function track_order($order_id)
    {
        $order = Order::with(['details', 'delivery_address', 'delivery_man.rating','order_partial_payments', 'branch', 'offline_payment'])
            ->where(['id' => $order_id])
            ->first();

        $orderDetails = OrderDetail::where('order_id', $order->id)->first();
        $productId = $orderDetails?->product_id;
        $order['is_product_available'] = $productId ? Product::find($productId) ? 1 : 0 : 0;

        $order->offline_payment_information = $order->offline_payment ? json_decode($order->offline_payment->payment_info, true): null;

        return Helpers::order_data_formatting($order, false);
    }

    public static function place_order($customer_id, $email, $customer_info, $cart, $payment_method, $discount, $coupon_code = null)
    {
        try {
            $or = [
                'id' => 100000 + Order::all()->count() + 1,
                'user_id' => $customer_id,
                'order_amount' => CartManager::cart_grand_total($cart) - $discount,
                'payment_status' => 'unpaid',
                'order_status' => 'pending',
                'payment_method' => $payment_method,
                'transaction_ref' => null,
                'discount_amount' => $discount,
                'coupon_code' => $coupon_code,
                'discount_type' => $discount == 0 ? null : 'coupon_discount',
                'shipping_address' => $customer_info['address_id'],
                'created_at' => now(),
                'updated_at' => now()
            ];

            $o_id = DB::table('orders')->insertGetId($or);

            foreach ($cart as $c) {
                $product = Product::where('id', $c['id'])->first();
                $or_d = [
                    'order_id' => $o_id,
                    'product_id' => $c['id'],
                    'seller_id' => $product->added_by == 'seller' ? $product->user_id : '0',
                    'product_details' => $product,
                    'qty' => $c['quantity'],
                    'price' => $c['price'],
                    'tax' => $c['tax'] * $c['quantity'],
                    'discount' => $c['discount'] * $c['quantity'],
                    'discount_type' => 'discount_on_product',
                    'variant' => $c['variant'],
                    'variation' => json_encode($c['variations']),
                    'delivery_status' => 'pending',
                    'shipping_method_id' => $c['shipping_method_id'],
                    'payment_status' => 'unpaid',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                DB::table('order_details')->insert($or_d);
            }

            $emailServices = Helpers::get_business_settings('mail_config');
            if (isset($emailServices['status']) && $emailServices['status'] == 1) {
                Mail::to($email)->send(new \App\Mail\OrderPlaced($o_id));
            }

        } catch (\Exception $e) {

        }

        return $o_id;
    }

    public static function create_transaction($order, $received_by=false)
    {
        try{
            $order_transaction = new OrderTransaction;
            $order_transaction->delivery_man_id = $order->delivery_man_id;
            $order_transaction->order_id = $order->id;
            $order_transaction->order_amount = $order->order_amount;
            $order_transaction->delivery_charge = $order->delivery_charge;
            $order_transaction->original_delivery_charge = $order->delivery_charge;
            $order_transaction->tax = $order->total_tax_amount;
            $order_transaction->received_by = $received_by?$received_by:'admin';
            //dd($order_transaction);
            $order_transaction->save();

            //if($order->user_id) CustomerLogic::create_loyalty_point_transaction($order->user_id, $order->id, $order->order_amount, 'order_place');

        } catch(\Exception $e) {
            DB::rollBack();
            info($e);
            return false;
        }
        return true;
    }
}
