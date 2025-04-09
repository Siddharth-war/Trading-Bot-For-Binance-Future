<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\Model\Vendor;
use App\Models\Invoice;
use Carbon\Carbon;
use Auth;

class InvoiceController extends Controller
{
    public function index(Request $req){
        $status = null;
        $invoice  = Invoice::latest()->with('vendor');
        if($req->param == 1){
            $invoice->where('status',1);
        }
        if($req->param == 2){
            $invoice->where('status',2);
        }
        if($req->param == "due"){
            $invoice->where('due_date',Carbon::now()->format('Y-m-d'));
        }
        if($req->param == 3){
            $invoice->where('status',3);
        }

        $invoices = $invoice->where('is_send',1)->paginate(20);
        return view('admin-views.invoice.index',compact('invoices'));
    }

    public function viewInvoice(Request $req){
        $user = Auth::guard('admin')->user();
        $admin = Auth::guard('admin')->user();

        $invoices = Invoice::latest()->latest()->first();

        return view('admin-views.invoice.create',compact('user','admin','invoices'));
    }

}
