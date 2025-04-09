<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;

use App\User;
use App\Model\CustomerAddress;
use App\Models\DayDelivery;
use Carbon\Carbon;
use App\Model\Order;
use Brian2694\Toastr\Facades\Toastr;

class NextDayOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:next-day-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $locations = $this->getLocation();
        $customer =  $this->getCustomers($locations);
        $orders = $this->getOrders($customer);
        if(!empty($orders)){
            foreach($orders as $resp){
                $resp->order_status  = "Processing";
               $resp->save();
            };
        }

    }

    private function getCustomers($locations){
        $users = CustomerAddress::where(function ($query) use ($locations) {
            foreach ($locations as $location) {
                $query->orWhereRaw("CONCAT(address, ' ', house, ' ', road, ' ', floor) LIKE ?", ["%{$location}%"]);
            }
        })->pluck('user_id');
        return $users;
    }

    private function getLocation(){
        $tomorrow = strtolower(Carbon::now()->addDay()->format('l'));
        $locations =  DayDelivery::whereHas('day',function($q) use($tomorrow){
            return $q->where('name',$tomorrow);
        })->pluck('location');
        return  $locations;
    }

    public function getOrders($user_id){
        $orders = Order::with('details')->whereIn("user_id",$user_id)->where(["order_status"=>"pending"])->get();
        return $orders;
    }
}
