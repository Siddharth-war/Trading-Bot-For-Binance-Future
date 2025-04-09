<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor as VendorModel;
use Auth;
use App\Models\Invoice;
use Carbon\Carbon;
class DahboardController extends Controller
{
    // nicyguge@mailinator.com
    public function invoice(Request $req){
        $status = null;
        $invoice  = Invoice::latest();
        if($req->param == 1){
            $invoice->where('status',1);
        }
        if($req->param == 2){
            $invoice->where('status',2);
        }
        if($req->param == "due"){
            $invoice->where('due_date',Carbon::now()->format('Y-m-d'));
        }

        if($req->param == 2){
            $invoice->where('status',3);
        }
        $invoices = $invoice->where("vendor_id",Auth::guard('vendor_users')->user()->id)->paginate(20);
        return view('vendor-views.dashboard',compact('invoices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bussiness_name' => 'required|string|max:255',
            'vat_number' => 'required|string|max:50',
            'address' => 'required|string|max:255',
        ]);


        $vendor = VendorModel::updateOrCreate(["id"=>Auth::guard('vendor_users')->user()->id],[
            'bussiness_name' => $request->bussiness_name,
            'vat_number' => $request->vat_number,
            'address' => $request->address,
        ]);

        return response()->json(['success' => 'Vendor saved successfully!', 'vendor' => $vendor]);
    }

}
