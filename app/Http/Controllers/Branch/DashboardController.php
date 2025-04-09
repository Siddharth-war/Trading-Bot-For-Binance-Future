<?php

namespace App\Http\Controllers\Branch;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Branch;
use App\Model\Order;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Support\Renderable;

class DashboardController extends Controller
{
    public function __construct(
        private Order  $order,
        private Branch $branch,
    )
    {}

    /**
     * @return Renderable
     */
    public function dashboard(): Renderable
    {
        Helpers::update_daily_product_stock();

        $data = self::orderStatisticsData();

        $from = Carbon::now()->startOfYear()->format('Y-m-d');
        $to = Carbon::now()->endOfYear()->format('Y-m-d');

        $earning = [];
        $earningData = $this->order->where([
            'order_status' => 'delivered',
            'branch_id' => auth('branch')->id()
        ])->select(
            DB::raw('IFNULL(sum(order_amount),0) as sums'),
            DB::raw('YEAR(created_at) year, MONTH(created_at) month')
        )
            ->whereBetween('created_at', [Carbon::parse(now())->startOfYear(), Carbon::parse(now())->endOfYear()])
            ->groupby('year', 'month')->get()->toArray();

        for ($inc = 1; $inc <= 12; $inc++) {
            $earning[$inc] = 0;
            foreach ($earningData as $match) {
                if ($match['month'] == $inc) {
                    $earning[$inc] = Helpers::set_price($match['sums']);
                }
            }
        }


        $orderStatisticsChart = [];
        $orderStatisticsChartData = $this->order->where([
            'order_status' => 'delivered',
            'branch_id' => auth('branch')->id()
        ])
            ->select(
                DB::raw('(count(id)) as total'),
                DB::raw('YEAR(created_at) year, MONTH(created_at) month')
            )
            ->whereBetween('created_at', [Carbon::parse(now())->startOfYear(), Carbon::parse(now())->endOfYear()])
            ->groupby('year', 'month')->get()->toArray();

        for ($inc = 1; $inc <= 12; $inc++) {
            $orderStatisticsChart[$inc] = 0;
            foreach ($orderStatisticsChartData as $match) {
                if ($match['month'] == $inc) {
                    $orderStatisticsChart[$inc] = $match['total'];
                }
            }
        }

        $donut = [];
        $donutData = $this->order->where('branch_id', auth('branch')->id())->get();
        $donut['pending'] = $donutData->where('order_status', 'pending')->count();
        $donut['ongoing'] = $donutData->whereIn('order_status', ['confirmed', 'processing', 'out_for_delivery'])->count();
        $donut['delivered'] = $donutData->where('order_status', 'delivered')->count();
        $donut['canceled'] = $donutData->where('order_status', 'canceled')->count();
        $donut['returned'] = $donutData->where('order_status', 'returned')->count();
        $donut['failed'] = $donutData->where('order_status', 'failed')->count();

        $data['recent_orders'] = $this->order->latest()
            ->where('branch_id', auth('branch')->id())
            ->take(5)
            ->get();


        return view('branch-views.dashboard', compact('data', 'earning', 'orderStatisticsChart', 'donut'));
    }

    /**
     * @return Renderable
     */
    public function settings(): Renderable
    {
        return view('branch-views.settings');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function settingsUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required'
        ]);

        $branch = $this->branch->find(auth('branch')->id());

        if ($request->has('image')) {
            $imageName = Helpers::update('branch/', $branch->image, 'png', $request->file('image'));
        } else {
            $imageName = $branch['image'];
        }

        $branch->name = $request->name;
        $branch->image = $imageName;
        $branch->phone = $request->phone;
        $branch->save();

        Toastr::success(translate('Branch updated successfully!'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function settingsPasswordUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|same:confirm_password|min:8|max:255',
            'confirm_password' => 'required|max:255',
        ]);

        $branch = $this->branch->find(auth('branch')->id());
        $branch->password = bcrypt($request['password']);
        $branch->save();

        Toastr::success(translate('Branch password updated successfully!'));
        return back();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function orderStats(Request $request): JsonResponse
    {
        session()->put('statistics_type', $request['statistics_type']);
        $data = self::orderStatisticsData();

        return response()->json([
            'view' => view('branch-views.partials._dashboard-order-stats', compact('data'))->render()
        ], 200);
    }

    /**
     * @return array
     */
    public function orderStatisticsData(): array
    {
        $today = session()->has('statistics_type') && session('statistics_type') == 'today' ? 1 : 0;
        $thisMonth = session()->has('statistics_type') && session('statistics_type') == 'this_month' ? 1 : 0;

        $pending = $this->order
            ->where(['order_status' => 'pending', 'branch_id' => auth('branch')->id()])
            ->when($today, function ($query) {
                return $query->whereDate('created_at', Carbon::today());
            })
            ->when($thisMonth, function ($query) {
                return $query->whereMonth('created_at', Carbon::now());
            })
            ->count();

        $confirmed = $this->order
            ->where(['order_status' => 'confirmed', 'branch_id' => auth('branch')->id()])
            ->when($today, function ($query) {
                return $query->whereDate('created_at', Carbon::today());
            })
            ->when($thisMonth, function ($query) {
                return $query->whereMonth('created_at', Carbon::now());
            })
            ->count();

        $processing = $this->order
            ->where(['order_status' => 'processing', 'branch_id' => auth('branch')->id()])
            ->when($today, function ($query) {
                return $query->whereDate('created_at', Carbon::today());
            })
            ->when($thisMonth, function ($query) {
                return $query->whereMonth('created_at', Carbon::now());
            })
            ->count();

        $outForDelivery = $this->order
            ->where(['order_status' => 'out_for_delivery', 'branch_id' => auth('branch')->id()])
            ->when($today, function ($query) {
                return $query->whereDate('created_at', Carbon::today());
            })
            ->when($thisMonth, function ($query) {
                return $query->whereMonth('created_at', Carbon::now());
            })
            ->count();

        $delivered = $this->order
            ->where(['order_status' => 'delivered', 'branch_id' => auth('branch')->id()])
            ->when($today, function ($query) {
                return $query->whereDate('created_at', Carbon::today());
            })
            ->when($thisMonth, function ($query) {
                return $query->whereMonth('created_at', Carbon::now());
            })
            ->count();

        $canceled = $this->order
            ->where(['order_status' => 'canceled', 'branch_id' => auth('branch')->id()])
            ->when($today, function ($query) {
                return $query->whereDate('created_at', Carbon::today());
            })
            ->when($thisMonth, function ($query) {
                return $query->whereMonth('created_at', Carbon::now());
            })
            ->count();

        $all = $this->order
            ->where(['branch_id' => auth('branch')->id()])
            ->when($today, function ($query) {
                return $query->whereDate('created_at', Carbon::today());
            })
            ->when($thisMonth, function ($query) {
                return $query->whereMonth('created_at', Carbon::now());
            })
            ->count();

        $returned = $this->order
            ->where(['order_status' => 'returned', 'branch_id' => auth('branch')->id()])
            ->when($today, function ($query) {
                return $query->whereDate('created_at', Carbon::today());
            })
            ->when($thisMonth, function ($query) {
                return $query->whereMonth('created_at', Carbon::now());
            })
            ->count();

        $failed = $this->order
            ->where(['order_status' => 'failed', 'branch_id' => auth('branch')->id()])
            ->when($today, function ($query) {
                return $query->whereDate('created_at', Carbon::today());
            })
            ->when($thisMonth, function ($query) {
                return $query->whereMonth('created_at', Carbon::now());
            })
            ->count();

        $data = [
            'pending' => $pending,
            'confirmed' => $confirmed,
            'processing' => $processing,
            'out_for_delivery' => $outForDelivery,
            'delivered' => $delivered,
            'all' => $all,
            'returned' => $returned,
            'failed' => $failed,
            'canceled' => $canceled
        ];

        return $data;
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function orderStatistics(Request $request): JsonResponse
    {
        $dateType = $request->type;

        $orderData = array();
        if ($dateType == 'yearOrder') {
            $number = 12;
            $from = Carbon::now()->startOfYear()->format('Y-m-d');
            $to = Carbon::now()->endOfYear()->format('Y-m-d');

            $orders = $this->order->where(['order_status' => 'delivered', 'branch_id' => auth('branch')->id()])
                ->select(
                    DB::raw('(count(id)) as total'),
                    DB::raw('YEAR(created_at) year, MONTH(created_at) month')
                )
                ->whereBetween('created_at', [Carbon::parse(now())->startOfYear(), Carbon::parse(now())->endOfYear()])
                ->groupby('year', 'month')->get()->toArray();

            for ($inc = 1; $inc <= $number; $inc++) {
                $orderData[$inc] = 0;
                foreach ($orders as $match) {
                    if ($match['month'] == $inc) {
                        $orderData[$inc] = $match['total'];
                    }
                }
            }
            $keyRange = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");

        } elseif ($dateType == 'MonthOrder') {
            $from = date('Y-m-01');
            $to = date('Y-m-t');
            $number = date('d', strtotime($to));
            $keyRange = range(1, $number);

            $orders = $this->order->where(['order_status' => 'delivered', 'branch_id' => auth('branch')->id()])
                ->select(
                    DB::raw('(count(id)) as total'),
                    DB::raw('YEAR(created_at) year, MONTH(created_at) month, DAY(created_at) day')
                )
                ->whereBetween('created_at', [Carbon::parse(now())->startOfMonth(), Carbon::parse(now())->endOfMonth()])
                ->groupby('created_at')
                ->get()
                ->toArray();

            for ($inc = 1; $inc <= $number; $inc++) {
                $orderData[$inc] = 0;
                foreach ($orders as $match) {
                    if ($match['day'] == $inc) {
                        $orderData[$inc] += $match['total'];
                    }
                }
            }

        } elseif ($dateType == 'WeekOrder') {
            Carbon::setWeekStartsAt(Carbon::SUNDAY);
            Carbon::setWeekEndsAt(Carbon::SATURDAY);

            $from = Carbon::now()->startOfWeek();
            $to = Carbon::now()->endOfWeek();
            $orders = $this->order->where(['order_status' => 'delivered', 'branch_id' => auth('branch')->id()])
                ->whereBetween('created_at', [$from, $to])->get();

            $datRange = CarbonPeriod::create($from, $to)->toArray();
            $keyRange = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
            $orderData = [];
            foreach ($datRange as $date) {

                $orderData[] = $orders->whereBetween('created_at', [$date, Carbon::parse($date)->endOfDay()])->count();
            }
        }

        $label = $keyRange;
        $orderDataFinal = $orderData;

        $data = array(
            'orders_label' => $label,
            'orders' => array_values($orderDataFinal),
        );

        return response()->json($data);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function earningStatistics(Request $request): JsonResponse
    {
        $dateType = $request->type;

        $earningData = array();
        if ($dateType == 'yearEarn') {

            $earning = [];
            $earningData = $this->order->where(['order_status' => 'delivered', 'branch_id' => auth('branch')->id()])->select(
                    DB::raw('IFNULL(sum(order_amount),0) as sums'),
                    DB::raw('YEAR(created_at) year, MONTH(created_at) month')
                )
                ->whereBetween('created_at', [Carbon::parse(now())->startOfYear(), Carbon::parse(now())->endOfYear()])
                ->groupby('year', 'month')->get()->toArray();

            for ($inc = 1; $inc <= 12; $inc++) {
                $earning[$inc] = 0;
                foreach ($earningData as $match) {
                    if ($match['month'] == $inc) {
                        $earning[$inc] = Helpers::set_price($match['sums']);
                    }
                }
            }

            $keyRange = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
            $orderData = $earning;

        } elseif ($dateType == 'MonthEarn') {
            $from = date('Y-m-01');
            $to = date('Y-m-t');
            $number = date('d', strtotime($to));
            $keyRange = range(1, $number);

            $earning = $this->order
                ->where(['order_status' => 'delivered', 'branch_id' => auth('branch')->id()])
                ->select(DB::raw('IFNULL(sum(order_amount),0) as sums'), DB::raw('YEAR(created_at) year, MONTH(created_at) month, DAY(created_at) day'))
                ->whereBetween('created_at', [Carbon::parse(now())->startOfMonth(), Carbon::parse(now())->endOfMonth()])
                ->groupby('created_at')
                ->get()
                ->toArray();

            for ($inc = 1; $inc <= $number; $inc++) {
                $earningData[$inc] = 0;
                foreach ($earning as $match) {
                    if ($match['day'] == $inc) {
                        $earningData[$inc] += $match['sums'];
                    }
                }
            }

            $orderData = $earningData;
        } elseif ($dateType == 'WeekEarn') {
            Carbon::setWeekStartsAt(Carbon::SUNDAY);
            Carbon::setWeekEndsAt(Carbon::SATURDAY);

            $from = Carbon::now()->startOfWeek();
            $to = Carbon::now()->endOfWeek();
            $orders = $this->order
                ->where(['order_status' => 'delivered', 'branch_id' => auth('branch')->id()])
                ->whereBetween('created_at', [$from, $to])->get();

            $datRange = CarbonPeriod::create($from, $to)->toArray();
            $keyRange = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
            $orderData = [];
            foreach ($datRange as $date) {
                $orderData[] = $orders->whereBetween('created_at', [$date, Carbon::parse($date)->endOfDay()])->sum('order_amount');
            }
        }

        $label = $keyRange;
        $earningDataFinal = $orderData;

        $data = array(
            'earning_label' => $label,
            'earning' => array_values($earningDataFinal),
        );

        return response()->json($data);
    }

}

