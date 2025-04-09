<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\DeliveryMan;
use App\Model\Order;
use App\Model\Table;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Support\Renderable;

class TableOrderController extends Controller
{
    public function __construct(
        private Table $table,
        private Order $order,
        private DeliveryMan $deliveryman
    )
    {
    }

    /**
     * @param Request $request
     * @param $status
     * @return Renderable
     */
    public function orderList(Request $request, $status): Renderable
    {
        $queryParam = [];
        $search = $request['search'];
        $from = $request['from'];
        $to = $request['to'];

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $orders = $this->order->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                        ->orWhere('order_status', 'like', "%{$value}%")
                        ->orWhere('transaction_reference', 'like', "%{$value}%");
                    }
                })
                ->when($from && $to, function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, $to]);
                });
            $queryParam = ['search' => $request['search']];
        } else {
            if (!session()->has('branch_filter')) {
                session()->put('branch_filter', 0);
            }
            $this->order->where(['checked' => 0])->update(['checked' => 1]);

            //all branch
            if (session('branch_filter') == 0) {
                if ($status == 'all') {
                    $orders = $this->order->with(['customer', 'branch', 'table'])
                        ->when($from && $to, function ($query) use ($from, $to) {
                            $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                        });
                } else {
                    $orders = $this->order->with(['customer', 'branch', 'table'])
                        ->where(['order_status' => $status])
                        ->when($from && $to, function ($query) use ($from, $to) {
                            $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                        });
                }
            } //selected branch
            else {
                if ($status == 'all') {
                    $orders = $this->order->with(['customer', 'branch', 'table'])->where('branch_id', session('branch_filter'))
                        ->when($from && $to, function ($query) use ($from, $to) {
                            $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                        });
                } else {
                    $orders = $this->order->with(['customer', 'branch', 'table'])
                        ->where(['order_status' => $status, 'branch_id' => session('branch_filter')])
                        ->when($from && $to, function ($query) use ($from, $to) {
                            $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                        });
                }
            }
        }

        $orderCount = [
            'confirmed' => $this->order->notPos()->dineIn()->where(['order_status' => 'confirmed'])->notSchedule()
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })->count(),
            'cooking' => $this->order->notPos()->dineIn()->where(['order_status' => 'cooking'])->notSchedule()
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })->count(),
            'done' => $this->order->notPos()->dineIn()->where(['order_status' => 'done'])->notSchedule()
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })->count(),
            'completed' => $this->order->notPos()->dineIn()->where(['order_status' => 'completed'])->notSchedule()
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })->count(),
            'canceled' => $this->order->notPos()->dineIn()->where(['order_status' => 'canceled'])->notSchedule()
                ->when(!is_null($from) && !is_null($to), function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, Carbon::parse($to)->endOfDay()]);
                })->count(),
        ];

        $orders = $orders->dineIn()->latest()->paginate(Helpers::getPagination())->appends($queryParam);
        return view('admin-views.table.order.list', compact('orders', 'search', 'status', 'from', 'to', 'orderCount'));
    }

    /**
     * @param $id
     * @return Renderable|RedirectResponse
     */
    public function orderDetails($id): Renderable|RedirectResponse
    {
        $order = $this->order->with('details')->where(['id' => $id])->first();

        if (!isset($order)) {
            Toastr::info(translate('No more orders!'));
            return back();
        }

        $deliverymen = $this->deliveryman->where(['is_active'=>1])
            ->where(function($query) use ($order) {
                $query->where('branch_id', $order->branch_id)
                    ->orWhere('branch_id', 0);
            })
            ->get();

        //remaining delivery time
        $deliveryDateTime = $order['delivery_date'] . ' ' . $order['delivery_time'];
        $orderedTime = Carbon::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s", strtotime($deliveryDateTime)));
        $remainingTime = $orderedTime->add($order['preparation_time'], 'minute')->format('Y-m-d H:i:s');
        $order['remaining_time'] = $remainingTime;

        return view('admin-views.order.order-view', compact('order','deliverymen'));
    }

    public function getTablesByBranch($branchId)
    {
        $tables = $this->table->with('order')
            ->whereHas('order', function ($q) {
                $q->whereHas('table_order', function ($q) {
                    $q->where('branch_table_token_is_expired', 0);
                });
            })
            ->where(['branch_id' => $branchId])
            ->get();

        return response()->json($tables);
    }

    /**
     * @param Request $request
     * @return Renderable
     */
    public function tableRunningOrder(Request $request): Renderable
    {
        $table_id = $request->table_id;
        $branchId = $request->branch ?? 1;

        $tables = $this->table->with('order')
            ->whereHas('order', function ($q) {
                $q->whereHas('table_order', function ($q) {
                    $q->where('branch_table_token_is_expired', 0);
                });
            })
            ->where(['branch_id' => $branchId])
            ->get();

        $orders = $this->order->with('table_order')
            ->whereHas('table_order', function ($q) {
                $q->where('branch_table_token_is_expired', 0);
            })
            ->where(['branch_id' => $branchId])
            ->when(!is_null($table_id), function ($query) use ($table_id) {
                return $query->where('table_id', $table_id);
            })
            ->latest()
            ->paginate(Helpers::getPagination());

        return view('admin-views.table.order.table_running_order', compact('tables', 'orders', 'table_id', 'branchId'));
    }

    /**
     * @param Request $request
     * @return Renderable
     */
    public function runningOrderInvoice(Request $request): Renderable
    {
        $table_id = $request->table_id;
        $orders = $this->order
            ->with('table_order')
            ->whereHas('table_order', function ($q) {
                $q->where('branch_table_token_is_expired', 0);
            })
            ->when(!is_null($table_id), function ($query) use ($table_id) {
                return $query->where('table_id', $table_id);
            })->get();

        return view('admin-views.table.order.running_order_invoice', compact('orders', 'table_id'));
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function branchFilter($id): RedirectResponse
    {
        session()->put('branch_filter', $id);
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
    public function exportExcel(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|string|RedirectResponse
    {
        $status = $request->status;
        $search = $request['search'];
        $from = $request['from'];
        $to = $request['to'];

        if (session()->has('branch_filter') == false) {
            session()->put('branch_filter', 0);
        }

        $orders = $this->order->query()->dineIn()
            ->when($search, function ($q) use ($search) {
                $key = explode(' ', $search);
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                        ->orWhere('order_status', 'like', "%{$value}%")
                        ->orWhere('transaction_reference', 'like', "%{$value}%");
                }
            })
            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()]);
            })
            ->when($status != 'all', function ($q) use ($status) {
                $q->where('order_status', $status);
            })
            ->when(session('branch_filter') != 0, function ($q) {
                $q->where('branch_id', session('branch_filter'));
            })
            ->latest()
            ->get();

        if ($orders->count() < 1) {
            Toastr::warning('No Data Available');
            return back();
        }

        $data = array();
        foreach ($orders as $key => $order) {
            $data[] = array(
                'SL' => ++$key,
                'Order ID' => $order->id,
                'Order Date' => date('d M Y h:m A', strtotime($order['created_at'])),
                'Customer Info' => $order['user_id'] == null ? 'Walk in Customer' : ($order->customer == null ? 'Customer Unavailable' : $order->customer['f_name'] . ' ' . $order->customer['l_name']),
                'Branch' => $order->branch ? $order->branch->name : 'Branch Deleted',
                'Total Amount' => Helpers::set_symbol($order['order_amount']),
                'Payment Status' => $order->payment_status == 'paid' ? 'Paid' : 'Unpaid',
                'Order Status' => $order['order_status'] == 'pending' ? 'Pending' : ($order['order_status'] == 'confirmed' ? 'Confirmed' : ($order['order_status'] == 'processing' ? 'Processing' : ($order['order_status'] == 'delivered' ? 'Delivered' : ($order['order_status'] == 'picked_up' ? 'Out For Delivery' : str_replace('_', ' ', $order['order_status']))))),
            );
        }
        return (new FastExcel($data))->download('Order_Details.xlsx');
    }

}
