<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Model\Admin;
use App\Models\Invoice;
use Carbon\Carbon;
use Brian2694\Toastr\Facades\Toastr;

class InvoiceController extends Controller
{
    public function create(Request $req){
        $user = Auth::guard('vendor_users')->user();
        $admin = Admin::first();

        $invoices = Invoice::latest()->latest()->first();

        return view('vendor-views.invoice.create',compact('user','admin','invoices'));
    }




    public function save(Request $request)
        {
            $request->validate([
                'amount' => 'required|numeric',
                'upload_pdf' => 'nullable|mimes:pdf|max:2048',
                'notes' => 'required|string',
                "invoice_number"=>"required",
                "due_date"=>"required",

            ]);

            $invoices = Invoice::latest()->latest()->first();

            // Save data to the database
            $invoiceAdd = [
                "invoice_id"=>$invoices->invoice_id ?? 1001,
                "invoice_number" => $request->invoice_number,
                "amount" => $request->amount,
                "due_date" => $request->due_date,
                "notes" => $request->notes,
                "vendor_id"=>Auth::guard('vendor_users')->user()->id
            ];
            if ($request->hasFile('upload_pdf')) {
                $pdfPath = $request->file('upload_pdf')->store('invoices', 'public');
                $invoiceAdd['upload_pdf'] = $pdfPath;
            }

            $invoice =  Invoice::updateOrCreate(["id"=> $request->id],$invoiceAdd);
            Toastr::success(translate("Invoice has been saved successfully"));

            return back()->with("message",);
            // return redirect()->route("vendor.invoice_create",["id"=>$invoice->id]);
        }


        public function sendPdf($id){
            $invoices = Invoice::find($id);
            $invoices->update(["is_send"=>1]);
            Toastr::success(translate("Invoice has been sent successfully"));

            return back();

        }

        public function editInvoice($id){
            $invoices = Invoice::find($id);
            return response()->json(["status"=>200,"invoice"=>$invoices]);
        }

}
