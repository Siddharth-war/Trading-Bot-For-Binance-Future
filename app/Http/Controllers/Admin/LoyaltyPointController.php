<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\PointTransitions;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;

class LoyaltyPointController extends Controller
{
    public function __construct(
        private PointTransitions $pointTransaction
    )
    {}

    /**
     * @param Request $request
     * @return Renderable
     */
    public function report(Request $request): Renderable
    {
        $data = $this->pointTransaction
            ->selectRaw('sum(credit) as total_credit, sum(debit) as total_debit')
            ->when(($request->from && $request->to), function ($query) use ($request) {
                $query->whereBetween('created_at', [$request->from . ' 00:00:00', $request->to . ' 23:59:59']);
            })
            ->when($request->transaction_type, function ($query) use ($request) {
                $query->where('type', $request->transaction_type);
            })
            ->when($request->customer_id, function ($query) use ($request) {
                $query->where('user_id', $request->customer_id);
            })
            ->get();

        $transactions = $this->pointTransaction->with(['customer'])
            ->when(($request->from && $request->to), function ($query) use ($request) {
                $query->whereBetween('created_at', [$request->from . ' 00:00:00', $request->to . ' 23:59:59']);
            })
            ->when($request->transaction_type, function ($query) use ($request) {
                $query->where('type', $request->transaction_type);
            })
            ->when($request->customer_id, function ($query) use ($request) {
                $query->where('user_id', $request->customer_id);
            })
            ->latest()
            ->paginate(Helpers::getPagination());

        return view('admin-views.customer.loyalty-point.report', compact('data', 'transactions'));
    }
}
