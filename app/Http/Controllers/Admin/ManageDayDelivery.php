<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\DayDelivery;
use App\Models\Day;
use App\Http\Requests\DayDeliveryRequest;
use Brian2694\Toastr\Facades\Toastr;
class ManageDayDelivery extends Controller
{
    public $dayDevliveryModel;
    public $dayModel;
    public function __construct(DayDelivery $dayDevlivery,Day $days){
        $this->dayDevliveryModel = $dayDevlivery;
        $this->dayModel = $days;
    }

    public function index(){
        $result = $this->dayModel->with('location')->paginate(10);
        $weekdays = $this->dayModel->count();
        return view('admin-views.manage-day-devlivery.index',["results"=>$result,"weekdays"=>$weekdays]);
    }


    public function create(){
        $weekdays = $this->dayModel->get();
        return view('admin-views.manage-day-devlivery.create',["weekdays"=>$weekdays]);
    }

    public function store(DayDeliveryRequest $request) {
        if(!empty($request->location)){
            foreach($request->location as $loc){
                $this->dayDevliveryModel->updateOrCreate(["day_id"=>$request->day_id,"location"=>$loc],["day_id"=>$request->day_id,"location"=>$loc]);
            }
        }
        Toastr::success("Day delivery has been stored successfully.");
        return redirect()->route('admin.day_delivery_list');
    }

    public function edit($id) {
        $days = $this->dayModel->with('location')->find($id);
        $weekdays = $this->dayModel->get();
        return view('admin-views.manage-day-devlivery.edit', compact('days','weekdays'));
    }



    public function destroy(Request $req) {
        $id = $req->id;
        $days = $this->dayModel->with('location')->find($id);
        $days->location()->delete();
       Toastr::success("Day has been deleted successfully.");

        return back();
    }







}
