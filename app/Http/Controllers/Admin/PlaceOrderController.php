<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Order;
use App\Model\CustomerAddress;
use App\Models\DayDelivery;
use Carbon\Carbon;
use DB;
use App\Model\OrderDetail;

class PlaceOrderController extends Controller
{

    public function nextDayOrders()
    {
        $locations = $this->getLocation();
        $customerIds = $this->getCustomers($locations);
        $orders = $this->getOrders($customerIds);


        return view('admin-views.order.place_orders', compact('orders'));
    }

    private function getCustomers($locations)
    {
        return CustomerAddress::where(function ($query) use ($locations) {
            foreach ($locations as $location) {
                $query->orWhereRaw("CONCAT(address, ' ', house, ' ', road, ' ', floor) LIKE ?", ["%{$location}%"]);
            }
        })->pluck('user_id');
    }

    private function getLocation()
    {
        $tomorrow = strtolower(Carbon::now()->addDay()->format('l'));
        return DayDelivery::whereHas('day', function ($q) use ($tomorrow) {
            return $q->where('name', $tomorrow);
        })->pluck('location');
    }

    private function getOrders($userIds)
    {

        //
        //  Order::with('details')
        //     ->whereIn("user_id", $userIds)
        //     ->where("order_status", "Processing")
        //     ->get();

        return OrderDetail::whereHas('order', function ($query) use ($userIds) {
            $query->whereIn('user_id', $userIds)
                  ->where('order_status', 'Processing');
        })
        ->join('products', 'order_details.product_id', '=', 'products.id') // Join with products table
        ->select('products.name', DB::raw('SUM(order_details.quantity) as total_quantity'))
        ->groupBy('products.name')
        ->pluck('total_quantity', 'products.name');


    }

}
