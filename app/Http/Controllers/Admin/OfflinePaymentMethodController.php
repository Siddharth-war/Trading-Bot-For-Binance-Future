<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\helpers;
use App\Http\Controllers\Controller;
use App\Model\Order;
use App\Models\OfflinePaymentMethod;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OfflinePaymentMethodController extends Controller
{
    public function __construct(
        private OfflinePaymentMethod $offlinePaymentMethod,
        private Order $order

    ){}

    public function list(Request $request)
    {
        $search = $request['search'];

        $methods = $this->offlinePaymentMethod
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('method_name', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()
            ->paginate(Helpers::getPagination());

        return view('admin-views.business-settings.offline-payment.list', compact('methods', 'search'));
    }

    /**
     * @return Application|Factory|View
     */
    public function add(): Factory|View|Application
    {
        return view('admin-views.business-settings.offline-payment.add');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'method_name' => 'required',
            'field_name' => 'required|array',
            'field_data' => 'required|array',
            'information_name' => 'required|array',
            'information_placeholder' => 'required|array',
            'information_required' => '',
        ]);

        $methodFields = [];
        foreach ($request->field_name as $key=>$field_name) {
            $methodFields[] = [
                'field_name' => $request->field_name[$key],
                'field_data' => $request->field_data[$key],
            ];
        }

        $methodInformation = [];
        foreach ($request->information_name as $key=>$field_name) {
            $methodInformation[] = [
                'information_name' => $request->information_name[$key],
                'information_placeholder' => $request->information_placeholder[$key],
                'information_required' => isset($request['information_required']) && isset($request['information_required'][$key]) ? 1 : 0,
            ];
        }

        $method = $this->offlinePaymentMethod;
        $method->method_name = $request->method_name;
        $method->method_fields = $methodFields;
        $method->payment_note = $request->payment_note;
        $method->method_informations = $methodInformation;
        $method->save();

        Toastr::success(translate('successfully added'));
        return redirect()->route('admin.business-settings.web-app.third-party.offline-payment.list');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
    {
        $method = $this->offlinePaymentMethod->find($request->id);
        $method->status = $request->status;
        $method->save();

        Toastr::success(translate('Status updated'));
        return back();
    }

    public function edit($id)
    {
        $method = $this->offlinePaymentMethod->find($id);
        return view('admin-views.business-settings.offline-payment.edit', compact('method'));

    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'method_name' => 'required',
            'field_name' => 'required|array',
            'field_data' => 'required|array',
            'information_name' => 'required|array',
            'information_placeholder' => 'required|array',
            'information_required' => '',
        ]);


        $methodFields = [];
        foreach ($request->field_name as $key=>$field_name) {
            $methodFields[] = [
                'field_name' => $request->field_name[$key],
                'field_data' => $request->field_data[$key],
            ];
        }

        $methodInformation = [];
        foreach ($request->information_name as $key=>$field_name) {
            $methodInformation[] = [
                'information_name' => $request->information_name[$key],
                'information_placeholder' => $request->information_placeholder[$key],
                'information_required' => isset($request['information_required']) && isset($request['information_required'][$key]) ? 1 : 0,
            ];
        }

        $method = $this->offlinePaymentMethod->find($id);
        $method->method_name = $request->method_name;
        $method->method_fields = $methodFields;
        $method->payment_note = $request->payment_note;
        $method->method_informations = $methodInformation;
        $method->save();

        Toastr::success(translate('successfully updated'));
        return redirect()->route('admin.business-settings.web-app.third-party.offline-payment.list');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $method = $this->offlinePaymentMethod->find($request->id);
        $method->delete();

        Toastr::success(translate('successfully removed'));
        return back();
    }

    /**
     * @param Request $request
     * @param $status
     * @return Application|Factory|View
     */
    public function offlinePaymentList(Request $request, $status): Factory|View|Application
    {
        $search = $request['search'];
        $statusMapping = [
            'pending' => 0,
            'denied' => 2,
        ];

        $status = $statusMapping[$status];

        $orders = $this->order->with(['offline_payment'])
            ->where(['payment_method' => 'offline_payment'])
            ->whereHas('offline_payment', function ($query) use($status){
                $query->where('status', $status);
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('id', 'LIKE', '%' . $key . '%')
                            ->orWhere('order_status', 'LIKE', "%{$key}%")
                            ->orWhere('payment_status', 'LIKE', "{$key}%");
                    }
                });
            })
            ->latest()
            ->paginate(Helpers::getPagination());

        return view('admin-views.order.offline-payment.list', compact('orders', 'search'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function quickViewDetails(Request $request): JsonResponse
    {
        $order = $this->order->find($request->id);

        return response()->json([
            'view' => view('admin-views.order.offline-payment.details-quick-view', compact('order'))->render(),
        ]);
    }
}
