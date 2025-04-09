<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Mail\DMSelfRegistration;
use App\Model\Branch;
use App\Model\DeliveryMan;
use App\Model\DMReview;
use App\Model\Order;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Support\Renderable;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function React\Promise\all;

class DeliveryManController extends Controller
{
    public function __construct(
        private DeliveryMan $deliveryman,
        private DMReview    $deliverymanReview,
        private Order $order,
        private Branch $branch,
    ){}

    /**
     * @return Renderable
     */
    public function index(): Renderable
    {
        return view('admin-views.delivery-man.index');
    }

    /**
     * @param Request $request
     * @return Renderable
     */
    public function list(Request $request)
    {
        $queryParam = [];
        $search = $request->input('search');
        $dateRange = $request->input('date');
        $status = $request->input('status');

        if ($dateRange) {
            // Split the date range into start and end dates
            [$startDate, $endDate] = explode(' - ', $dateRange);

            // Parse dates to Carbon instances
            $startDateTime = \Carbon\Carbon::parse($startDate)->startOfDay();
            $endDateTime = \Carbon\Carbon::parse($endDate)->endOfDay();
        } else {
            $startDateTime = null;
            $endDateTime = null;
        }

        $query = $this->deliveryman
            ->withCount([
                'orders',
                'orders as ongoing_orders_count' => function ($query) {
                    $query->whereIn('order_status', ['pending', 'confirmed', 'processing', 'out_for_delivery']);
                },
                'orders as canceled_orders_count' => function ($query) {
                    $query->where('order_status', 'canceled');
                },
                'orders as completed_orders_count' => function ($query) {
                    $query->where('order_status', 'delivered');
                }
            ])
            ->when($search, function ($query) use ($search, &$queryParam) {
                $key = explode(' ', $search);
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('f_name', 'like', "%{$value}%")
                            ->orWhere('l_name', 'like', "%{$value}%")
                            ->orWhere('email', 'like', "%{$value}%")
                            ->orWhere('phone', 'like', "%{$value}%");
                    }
                });
                $queryParam['search'] = $search;
            })
            ->when(($startDateTime != null && $endDateTime != null), function ($query) use ($startDateTime, $endDateTime) {
                $query->whereBetween('created_at', [$startDateTime, $endDateTime]);
            })
            ->when(($request->has('status') && $status == 'active'), function ($query) use ($status) {
                $query->where(['is_active' => 1]);
            })
            ->when(($request->has('status') && $status == 'inactive'), function ($query) use ($status) {
                $query->where(['is_active' => 0]);
            })
            ->where('application_status', 'approved');

        $activeCount = (clone $query)->where('is_active', 1)->count();
        $inactiveCount = (clone $query)->where('is_active', 0)->count();

        $deliverymen = $query
            ->withSum('orders as order_amount', 'order_amount')
            ->withSum('orders as delivery_charge', 'delivery_charge')
            ->latest()
            ->paginate(Helpers::getPagination())
            ->appends($queryParam);

        $deliverymen->getCollection()->transform(function ($deliveryman) {
            $deliveryman->total_order_amount = $deliveryman->order_amount + $deliveryman->delivery_charge;
            return $deliveryman;
        });

        return view('admin-views.delivery-man.list', compact('deliverymen', 'search', 'status', 'activeCount', 'inactiveCount', 'startDateTime', 'endDateTime'));
    }

    /**
     * @param Request $request
     * @return Renderable
     */
    public function reviewsList(Request $request): Renderable
    {
        $queryParam = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $deliverymenIds = $this->deliveryman->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%");
                }
            })->pluck('id')->toArray();

            $reviews = $this->deliverymanReview->whereIn('delivery_man_id', $deliverymenIds);
            $queryParam = ['search' => $request['search']];
        } else {
            $reviews = $this->deliverymanReview;
        }

        $reviews = $reviews->with(['delivery_man', 'customer'])->latest()->paginate(Helpers::getPagination())->appends($queryParam);
        return view('admin-views.delivery-man.reviews-list', compact('reviews', 'search'));
    }

    /**
     * @param $id
     * @return Renderable
     */
    public function preview($id): Renderable
    {
        $deliveryman = $this->deliveryman->with(['reviews'])->where(['id' => $id])->first();
        $reviews = $this->deliverymanReview->where(['delivery_man_id' => $id])->latest()->paginate(Helpers::getPagination());
        return view('admin-views.delivery-man.view', compact('deliveryman', 'reviews'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        //dd($request->all());
        $request->validate([
            'f_name' => 'required',
            'email' => 'required|regex:/(.+)@(.+)\.(.+)/i|unique:delivery_men',
            'phone' => 'required|unique:delivery_men',
            'confirm_password' => 'same:password'
        ], [
            'f_name.required' => translate('First name is required!')
        ]);

        $identityImageNames = [];
        if (!empty($request->file('identity_image'))) {
            foreach ($request->identity_image as $img) {
                $identityImageNames[] = Helpers::upload('delivery-man/', 'png', $img);
            }
            $identityImage = json_encode($identityImageNames);
        } else {
            $identityImage = json_encode([]);
        }

        $deliveryman = $this->deliveryman;
        $deliveryman->f_name = $request->f_name;
        $deliveryman->l_name = $request->l_name;
        $deliveryman->email = $request->email;
        $deliveryman->phone = $request->phone;
        $deliveryman->identity_number = $request->identity_number;
        $deliveryman->identity_type = $request->identity_type;
        $deliveryman->branch_id = $request->branch_id;
        $deliveryman->identity_image = $identityImage;
        $deliveryman->image = Helpers::upload('delivery-man/', 'png', $request->file('image'));
        $deliveryman->password = bcrypt($request->password);
        $deliveryman->application_status= 'approved';
        $deliveryman->language_code = $request->language_code ?? 'en';
        $deliveryman->save();

        try{
            $emailServices = Helpers::get_business_settings('mail_config');
            $mailStatus = Helpers::get_business_settings('registration_mail_status_dm');
            if(isset($emailServices['status']) && $emailServices['status'] == 1 && $mailStatus == 1){
                Mail::to($deliveryman->email)->send(new DMSelfRegistration('approved', $deliveryman->f_name.' '.$deliveryman->l_name, $deliveryman->language_code));
            }

        }catch(\Exception $ex){
            info($ex);
        }

        Toastr::success(translate('Delivery-man added successfully!'));
        return redirect('admin/delivery-man/list');
    }

    /**
     * @param $id
     * @return Renderable
     */
    public function edit($id): Renderable
    {
        $deliveryman = $this->deliveryman->find($id);
        return view('admin-views.delivery-man.edit', compact('deliveryman'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxIsActive(Request $request): JsonResponse
    {
        $deliveryman = $this->deliveryman->find($request->id);
        $deliveryman->is_active = $request->status;
        $deliveryman->save();

        return response()->json(['status' => $deliveryman->is_active, 'message' => translate('status changed successfully')]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'f_name' => 'required',
            'email' => 'required|regex:/(.+)@(.+)\.(.+)/i',
        ], [
            'f_name.required' => translate('First name is required!')
        ]);

        if ($request->password) {
            $request->validate([
                'confirm_password' => 'same:password'
            ]);
        }

        $deliveryman = $this->deliveryman->find($id);

        if ($deliveryman['email'] != $request['email']) {
            $request->validate([
                'email' => 'required|unique:delivery_men',
            ]);
        }

        if ($deliveryman['phone'] != $request['phone']) {
            $request->validate([
                'phone' => 'required|unique:delivery_men',
            ]);
        }

        if (!empty($request->file('identity_image'))) {
            foreach (json_decode($deliveryman['identity_image'], true) as $img) {
                if (Storage::disk('public')->exists('delivery-man/' . $img)) {
                    Storage::disk('public')->delete('delivery-man/' . $img);
                }
            }
            $imgKeeper = [];
            foreach ($request->identity_image as $img) {
                $imgKeeper[] = Helpers::upload('delivery-man/', 'png', $img);
            }
            $identityImage = json_encode($imgKeeper);
        } else {
            $identityImage = $deliveryman['identity_image'];
        }
        $deliveryman->f_name = $request->f_name;
        $deliveryman->l_name = $request->l_name;
        $deliveryman->email = $request->email;
        $deliveryman->phone = $request->phone;
        $deliveryman->identity_number = $request->identity_number;
        $deliveryman->identity_type = $request->identity_type;
        $deliveryman->branch_id = $request->branch_id;
        $deliveryman->identity_image = $identityImage;
        $deliveryman->image = $request->has('image') ? Helpers::update('delivery-man/', $deliveryman->image, 'png', $request->file('image')) : $deliveryman->image;
        $deliveryman->password = strlen($request->password) > 1 ? bcrypt($request->password) : $deliveryman['password'];
        $deliveryman->save();

        Toastr::success(translate('Delivery-man updated successfully!'));
        return redirect('admin/delivery-man/list');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $deliveryman = $this->deliveryman->find($request->id);
        if (Storage::disk('public')->exists('delivery-man/' . $deliveryman['image'])) {
            Storage::disk('public')->delete('delivery-man/' . $deliveryman['image']);
        }

        foreach (json_decode($deliveryman['identity_image'], true) as $img) {
            if (Storage::disk('public')->exists('delivery-man/' . $img)) {
                Storage::disk('public')->delete('delivery-man/' . $img);
            }
        }
        $deliveryman->delete();

        Toastr::success(translate('Delivery-man removed!'));
        return back();
    }


    /**
     * @param Request $request
     * @return string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function excelExport(Request $request): StreamedResponse|string
    {
        $search = $request->input('search');
        $dateRange = $request->input('date');
        $status = $request->input('status');

        if ($dateRange) {
            // Split the date range into start and end dates
            [$startDate, $endDate] = explode(' - ', $dateRange);

            // Parse dates to Carbon instances
            $startDateTime = \Carbon\Carbon::parse($startDate)->startOfDay();
            $endDateTime = \Carbon\Carbon::parse($endDate)->endOfDay();
        } else {
            $startDateTime = null;
            $endDateTime = null;
        }

        $deliveryman = $this->deliveryman->where('application_status', 'approved')
            ->when($search, function ($query) use ($search) {
                $key = explode(' ', $search);
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('f_name', 'like', "%{$value}%")
                            ->orWhere('l_name', 'like', "%{$value}%")
                            ->orWhere('email', 'like', "%{$value}%")
                            ->orWhere('phone', 'like', "%{$value}%");
                    }
                });
            })
            ->when(($startDateTime != null && $endDateTime != null), function ($query) use ($startDateTime, $endDateTime) {
                $query->whereBetween('created_at', [$startDateTime, $endDateTime]);
            })
            ->when(($request->has('status') && $status == 'active'), function ($query) use ($status) {
                $query->where(['is_active' => 1]);
            })
            ->when(($request->has('status') && $status == 'inactive'), function ($query) use ($status) {
                $query->where(['is_active' => 0]);
            })
            ->select('f_name as First Name', 'l_name as Last Name', 'phone as Phone', 'identity_type', 'identity_number')
            ->latest()
            ->get();

        return (new FastExcel($deliveryman))->download('deliveryman.xlsx');

    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function pendingList(Request $request): Factory|View|Application
    {
        $queryParam = [];
        $search = $request['search'];
        if($request->has('search'))
        {
            $key = explode(' ', $request['search']);
            $deliverymen = $this->deliveryman->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        }else{
            $deliverymen = $this->deliveryman;
        }
        $deliverymen = $deliverymen->where('application_status', 'pending')->latest()->paginate(Helpers::getPagination())->appends($queryParam);

        return view('admin-views.delivery-man.pending-list', compact('deliverymen','search'));
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function deniedList(Request $request): Factory|View|Application
    {
        $queryParam = [];
        $search = $request['search'];
        if($request->has('search'))
        {
            $key = explode(' ', $request['search']);
            $deliverymen = $this->deliveryman->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        }else{
            $deliverymen = $this->deliveryman;
        }
        $deliverymen = $deliverymen->where('application_status', 'denied')->latest()->paginate(Helpers::getPagination())->appends($queryParam);

        return view('admin-views.delivery-man.denied-list', compact('deliverymen','search'));
    }


    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function update_application(Request $request): RedirectResponse
    {
        $deliveryman = $this->deliveryman->findOrFail($request->id);
        $deliveryman->application_status = $request->status;

        if ($request->status == 'approved') {
            $deliveryman->is_active = 1;
        }

        $deliveryman->save();

        try {
            $emailServices = Helpers::get_business_settings('mail_config');
            $approvedMailStatus = Helpers::get_business_settings('approve_mail_status_dm');
            $deniedMailStatus = Helpers::get_business_settings('deny_mail_status_dm');

            if (isset($emailServices['status']) && $emailServices['status'] == 1) {
                $mailType = ($request->status == 'approved') ? 'approved' : 'denied';
                $fullName = $deliveryman->f_name . ' ' . $deliveryman->l_name;
                $languageCode = $deliveryman->language_code;
                if ($mailType == 'approved' && $approvedMailStatus == 1){
                    Mail::to($deliveryman->email)->send(new DMSelfRegistration($mailType, $fullName, $languageCode));
                }
                if ($mailType == 'denied' && $deniedMailStatus == 1){
                    Mail::to($deliveryman->email)->send(new DMSelfRegistration($mailType, $fullName, $languageCode));
                }
            }
        } catch (\Exception $ex) {
            info($ex);
        }

        Toastr::success(translate('application_status_updated_successfully'));
        return back();
    }


    /**
     * @param Request $request
     * @param $id
     * @return Application|Factory|View|\Illuminate\Foundation\Application
     */
    public function details(Request $request, $id): Factory|\Illuminate\Foundation\Application|View|Application
    {
        $deliveryman = $this->deliveryman->find($id);
        $branches = $this->branch->get();

        $branchId = $request->input('branch_id');
        $search = $request->input('search');
        $dateRange = $request->input('date');

        if ($dateRange) {
            // Split the date range into start and end dates
            [$startDate, $endDate] = explode(' - ', $dateRange);

            // Parse dates to Carbon instances
            $startDateTime = \Carbon\Carbon::parse($startDate)->startOfDay();
            $endDateTime = \Carbon\Carbon::parse($endDate)->endOfDay();
        } else {
            $startDateTime = null;
            $endDateTime = null;
        }

        // Base query with filters applied once
        $ordersQuery = $this->order
            ->where('delivery_man_id', $id)
            ->withCount('details')
            ->when($search, function ($query) use ($search) {
                $key = explode(' ', $search);
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('id', 'like', "%{$value}%");
                    }
                });
            })
            ->when(($startDateTime != null && $endDateTime != null), function ($query) use ($startDateTime, $endDateTime) {
                $query->whereBetween('created_at', [$startDateTime, $endDateTime]);
            })
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            });

        // Paginate the filtered orders
        $orders = (clone $ordersQuery)
            ->latest()
            ->paginate(Helpers::getPagination());

        // Calculate total amounts and order statuses using cloned queries
        $orderAmount = (clone $ordersQuery)->sum('order_amount');
        $deliveryCharge = (clone $ordersQuery)->sum('delivery_charge');
        $totalOrderAmount = $orderAmount + $deliveryCharge;

        $pendingOrders = (clone $ordersQuery)
            ->whereIn('order_status', ['pending', 'confirmed', 'processing'])
            ->count();

        $outForDeliveryOrders = (clone $ordersQuery)
            ->where('order_status', 'out_for_delivery')
            ->count();

        $completedOrders = (clone $ordersQuery)
            ->where('order_status', 'delivered')
            ->count();

        return view('admin-views.delivery-man.details', compact('deliveryman', 'branches', 'orders', 'totalOrderAmount', 'pendingOrders', 'outForDeliveryOrders', 'completedOrders', 'branchId', 'search', 'startDateTime', 'endDateTime'));
    }

    /**
     * @param Request $request
     * @return string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function orderExcelExport(Request $request): StreamedResponse|string
    {
        $deliveryman_id = $request->input('deliveryman_id');
        $branchId = $request->input('branch_id');
        $search = $request->input('search');
        $dateRange = $request->input('date');

        if ($dateRange) {
            // Split the date range into start and end dates
            [$startDate, $endDate] = explode(' - ', $dateRange);

            // Parse dates to Carbon instances
            $startDateTime = \Carbon\Carbon::parse($startDate)->startOfDay();
            $endDateTime = \Carbon\Carbon::parse($endDate)->endOfDay();
        } else {
            $startDateTime = null;
            $endDateTime = null;
        }

        // Base query with filters applied once
        $orders = $this->order
            ->where('delivery_man_id', $deliveryman_id)
            ->when($search, function ($query) use ($search) {
                $key = explode(' ', $search);
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('id', 'like', "%{$value}%");
                    }
                });
            })
            ->when(($startDateTime != null && $endDateTime != null), function ($query) use ($startDateTime, $endDateTime) {
                $query->whereBetween('created_at', [$startDateTime, $endDateTime]);
            })
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->latest()
            ->get();

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

        return (new FastExcel($data))->download('Deliveryman-Order-List.xlsx');

    }

}
