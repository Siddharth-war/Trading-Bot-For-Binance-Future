<?php

namespace App\Http\Controllers\Customer;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\User;
use App\Model\BusinessSetting;
use App\Model\Order;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Support\Renderable;

class SystemController extends Controller
{
    public function __construct(
        private Order           $order,
        private User           $customer,
        private BusinessSetting $businessSetting,
    )
    {
    }

    /**
     * @return JsonResponse
     */
    public function restaurantData(): JsonResponse
    {
        $newOrder = $this->order->where(['checked' => 0])->count();
        return response()->json([
            'success' => 1,
            'data' => ['new_order' => $newOrder]
        ]);
    }


    /**
     * @return Application|Factory|View
     */
    public function settings(): View|Factory|Application
    {
        return view('customer-views.settings');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function settingsUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'f_name' => 'required',
            'email' => ['required', 'unique:users,email,' . auth('customer')->id() . ',id'],
            'phone' => 'required',
        ], [
            'f_name.required' => translate('First name is required!'),
        ]);

        $customer = $this->customer->find(auth('customer')->id());
        $customer->f_name = $request->f_name;
        $customer->l_name = $request->l_name;
        $customer->email = $request->email;
        $customer->phone = $request->phone;
        $customer->image = $request->has('image') ? Helpers::update('customer/', $customer->image, 'png', $request->file('image')) : $customer->image;
        $customer->save();

        Toastr::success(translate('Customer updated successfully!'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function settingsPasswordUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
        ]);
        $customer = $this->customer->find(auth('customer')->id());
        $customer->password = bcrypt($request['password']);
        $customer->save();

        Toastr::success(translate('customer password updated successfully!'));
        return back();
    }

    /**
     * @param Request $request
     * @param $app_id
     * @return Renderable
     */
    public function appActivate(Request $request, $app_id): Renderable
    {
        $app_name = 'default';
        $app_link = 'default';
        foreach (APPS as $app) {
            if ($app['software_id'] == $app_id) {
                $app_name = $app['app_name'];
                $app_link = $app['buy_now_link'];
            }
        }

        return view('customer-views.app-activation', compact('app_id', 'app_name', 'app_link'));
    }

    /**
     * @param Request $request
     * @param $app_id
     * @return RedirectResponse
     */
    public function activationSubmit(Request $request, $app_id): RedirectResponse
    {
        $post = [
            'purchase_key' => $request['purchase_key']
        ];
        $live = 'https://check.6amtech.com';
        $ch = curl_init($live . '/api/v1/software-check');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_body = json_decode($response, true);

        try {
            if ($response_body['is_valid'] && $response_body['result']['item']['id'] == $app_id) {
                $previous_active = json_decode($this->businessSetting->where('key', 'app_activation')->first()->value ?? '[]');
                $found = 0;
                foreach ($previous_active as $key => $item) {
                    if ($item->software_id == $app_id) {
                        $found = 1;
                    }
                }
                if (!$found) {
                    $previous_active[] = [
                        'software_id' => $app_id,
                        'is_active' => 1
                    ];
                    $this->businessSetting->updateOrInsert(['key' => 'app_activation'], [
                        'value' => json_encode($previous_active)
                    ]);
                }

                Toastr::success('succesfully activated');
                return back();
            }

        } catch (\Exception $exception) {
            Toastr::warning('invalid purchase code');
            return back();
        }

        Toastr::warning('invalid purchase code');
        return back();
    }
}
