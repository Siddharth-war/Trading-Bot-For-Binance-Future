<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Conversation;
use App\Model\Newsletter;
use App\Model\Order;
use App\Model\PointTransitions;
use App\Model\BusinessSetting;
use App\User;
use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Contracts\Support\Renderable;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Requests\CustomerAddRequest;
use Hash;
use Carbon\Carbon;
use App\Models\Group;
use App\Models\Day;
use App\Model\Branch;

class CustomerController extends Controller
{
    public function __construct(
        private User             $customer,
        private PointTransitions $pointTransitions,
        private Order            $order,
        private Newsletter       $newsletter,
        private Conversation     $conversation,
        private BusinessSetting  $businessSetting,
        private Group $group,
        private Day $day,
        private Branch  $branch,
    )
    {
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function addLoyaltyPoint(Request $request, $id): JsonResponse
    {
        DB::transaction(function () use ($request, $id) {
            $user = $this->customer->find($id);
            $credit = $request['point'];
            $debit = 0;
            $CurrentAmount = $user->point + $credit;

            $loyaltyPointTransaction = $this->pointTransitions;
            $loyaltyPointTransaction->user_id = $user->id;
            $loyaltyPointTransaction->transaction_id = Str::random('30');
            $loyaltyPointTransaction->reference = 'admin';
            $loyaltyPointTransaction->type = 'point_in';
            $loyaltyPointTransaction->amount = $CurrentAmount;
            $loyaltyPointTransaction->credit = $credit;
            $loyaltyPointTransaction->debit = $debit;
            $loyaltyPointTransaction->created_at = now();
            $loyaltyPointTransaction->updated_at = now();
            $loyaltyPointTransaction->save();

            $user->point = $CurrentAmount;
            $user->save();
        });

        if ($request->ajax()) {
            return response()->json([
                'updated_point' => $this->customer->where(['id' => $id])->first()->point
            ]);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function setPointModalData($id): JsonResponse
    {
        $customer = $this->customer->find($id);

        return response()->json([
            'view' => view('admin-views.customer.partials._add-point-modal-content', compact('customer'))->render()
        ]);
    }

    /**
     * @param Request $request
     * @return Renderable
     */
    public function customerList(Request $request): Renderable
    {
        $queryParam = [];
        $search = $request['search'];

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $customers = $this->customer->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        } else {
            $customers = $this->customer;
        }

        $customers = $customers->with(['orders'])->where('user_type', null)->latest()->paginate(Helpers::getPagination())->appends($queryParam);
        return view('admin-views.customer.list', compact('customers', 'search'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return RedirectResponse|Renderable
     */
    public function view($id, Request $request): RedirectResponse|Renderable
    {
        $search = $request->search;
        $customer = $this->customer->find($id);

        if (!isset($customer)) {
            Toastr::error(translate('Customer not found!'));
            return back();
        }

        $orders = $this->order->latest()->where(['user_id' => $id])
            ->when($search, function ($query) use ($search) {
                $key = explode(' ', $search);
                foreach ($key as $value) {
                    $query->where('id', 'like', "%$value%");
                }
            })
            ->paginate(Helpers::getPagination())
            ->appends(['search' => $search]);

        return view('admin-views.customer.customer-view', compact('customer', 'orders', 'search'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function AddPoint(Request $request, $id): RedirectResponse
    {
        $point = $this->customer->where(['id' => $id])->first()->point;

        $requestPoint = $request['point'];
        $point += $requestPoint;

        $this->customer->where(['id' => $id])->update([
            'point' => $point,
        ]);

        $this->pointTransitions->insert([
            'user_id' => $request['id'],
            'description' => 'admin Added point',
            'type' => 'point_in',
            'amount' => $request['point'],
            'created_at' => now(),
            'updated_at' => now(),

        ]);

        Toastr::success(translate('Point Added Successfully !'));
        return back();

    }

    /**
     * @param Request $request
     * @return Renderable
     */
    public function transaction(Request $request): Renderable
    {
        $queryParam = ['search' => $request['search']];
        $search = $request['search'];

        $transition = $this->pointTransitions->with(['customer'])->latest()
            ->when($request->has('search'), function ($q) use ($search) {
                $q->whereHas('customer', function ($query) use ($search) {
                    $key = explode(' ', $search);
                    foreach ($key as $value) {
                        $query->where('f_name', 'like', "%{$value}%")
                            ->orWhere('l_name', 'like', "%{$value}%");
                    }
                });
            })
            ->paginate(Helpers::getPagination())
            ->appends($queryParam);

        return view('admin-views.customer.transaction-table', compact('transition', 'search'));
    }

    /**
     * @param Request $request
     * @return Renderable
     */
    public function subscribedEmails(Request $request): Renderable
    {
        $queryParam = [];
        $search = $request['search'];

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $newsletters = $this->newsletter
                ->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('email', 'like', "%{$value}%");
                    }
                });
            $queryParam = ['search' => $request['search']];
        } else {
            $newsletters = $this->newsletter;
        }

        $newsletters = $newsletters->latest()->paginate(Helpers::getPagination())->appends($queryParam);
        return view('admin-views.customer.subscribed-list', compact('newsletters', 'search'));
    }

    public function subscribedEmailsExport(Request $request): StreamedResponse|string
    {
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $newsletters = $this->newsletter
                ->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('email', 'like', "%{$value}%");
                    }
                });
        } else {
            $newsletters = $this->newsletter;
        }
        $newsletters = $newsletters->latest()->get();

        $data = [];
        foreach ($newsletters as $key => $newsletter) {
            $data[] = [
                'SL' => ++$key,
                'Email' => $newsletter->email,
                'Subscribe At' => date('d M Y h:m A', strtotime($newsletter['created_at'])),
            ];
        }

        return (new FastExcel($data))->download('subscribe-email.xlsx');
    }

    /**
     * @param $id
     * @return Renderable
     */
    public function customerTransaction($id, Request $request): Renderable
    {
        $search = $request['search'];
        $queryParam = ['search' => $search];

        $transition = $this->pointTransitions->with(['customer'])
            ->where(['user_id' => $id])
            ->when($request->has('search'), function ($query) use ($search) {
                $key = explode(' ', $search);
                foreach ($key as $value) {
                    $query->where('transaction_id', 'like', "%{$value}%");
                }
            })
            ->latest()
            ->paginate(Helpers::getPagination())
            ->appends($queryParam);

        return view('admin-views.customer.transaction-table', compact('transition', 'search'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserInfo(Request $request): JsonResponse
    {
        $user = $this->customer->find($request['id']);
        $unchecked = $this->conversation->where(['user_id' => $request['id'], 'checked' => 0])->count();

        $output = [
            'id' => $user->id ?? '',
            'f_name' => $user->f_name ?? '',
            'l_name' => $user->l_name ?? '',
            'email' => $user->email ?? '',
            'image' => ($user && $user->image) ? asset('storage/app/public/profile') . '/' . $user->image : asset('/public/assets/admin/img/160x160/img1.jpg'),
            'cm_firebase_token' => $user->cm_firebase_token ?? '',
            'unchecked' => $unchecked ?? 0

        ];

        $result = get_headers($output['image']);
        if (!stripos($result[0], "200 OK")) {
            $output['image'] = asset('/public/assets/admin/img/160x160/img1.jpg');
        }

        return response()->json($output);
    }

    /**
     * @param Request $request
     * @return bool|array
     */
    public function messageNotification(Request $request): bool|array
    {
        $user = $this->customer->find($request['id']);
        $fcmToken = $user->cm_firebase_token;

        $data = [
            'title' => 'New Message' . ($request->has('image_length') && $request->image_length > 0 ? (' (with ' . $request->image_length . ' attachment)') : ''),
            'description' => $request->message,
            'order_id' => '',
            'image' => $request->has('image_length') ? $request->image_length : null,
            'type' => 'order_status',
        ];

        try {
            Helpers::send_push_notif_to_device($fcmToken, $data);
            return $data;
        } catch (\Exception $exception) {
            return false;
        }

    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function chatImageUpload(Request $request): JsonResponse
    {
        $imageNames = [];
        if (!empty($request->file('images'))) {
            foreach ($request->images as $img) {
                $image = Helpers::upload('conversation/', 'png', $img);
                $image_url = asset('storage/app/public/conversation') . '/' . $image;
                $imageNames[] = $image_url;
            }
            $images = $imageNames;
        } else {
            $images = null;
        }
        return response()->json(['image_urls' => $images], 200);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $this->customer->findOrFail($id)->update(['is_active' => $request['status']]);
        return response()->json($request['status']);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        try {
            $this->customer->findOrFail($request['id'])->delete();
            Toastr::success(translate('user_deleted_successfully!'));

        } catch (\Exception $e) {
            Toastr::error(translate('user_not_found!'));
        }
        return back();
    }


    /**
     * @return string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function excelImport(): StreamedResponse|string
    {
        $users = $this->customer->select('f_name as First Name', 'l_name as Last Name', 'email as Email', 'is_active as Active', 'phone as Phone', 'point as Point')->get();
        return (new FastExcel($users))->download('customers.xlsx');
    }

    /**
     * @return Renderable
     */
    public function settings(): Renderable
    {
        $data = $this->businessSetting->where('key', 'like', 'wallet_%')
            ->orWhere('key', 'like', 'loyalty_%')
            ->orWhere('key', 'like', 'ref_earning_%')
            ->orWhere('key', 'like', 'ref_earning_%')->get();
        $data = array_column($data->toArray(), 'value', 'key');

        return view('admin-views.customer.settings', compact('data'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        if (env('APP_MODE') == 'demo') {
            Toastr::info(translate('update_option_is_disable_for_demo'));
            return back();
        }

        $request->validate([
            'add_fund_bonus' => 'nullable|numeric|max:100|min:0',
            'loyalty_point_exchange_rate' => 'nullable|numeric',
            'ref_earning_exchange_rate' => 'nullable|numeric',
        ]);

        $this->businessSetting->updateOrInsert(['key' => 'wallet_status'], [
            'value' => $request['customer_wallet'] ?? 0
        ]);
        $this->businessSetting->updateOrInsert(['key' => 'loyalty_point_status'], [
            'value' => $request['customer_loyalty_point'] ?? 0
        ]);
        $this->businessSetting->updateOrInsert(['key' => 'ref_earning_status'], [
            'value' => $request['ref_earning_status'] ?? 0
        ]);
        $this->businessSetting->updateOrInsert(['key' => 'loyalty_point_exchange_rate'], [
            'value' => $request['loyalty_point_exchange_rate'] ?? 0
        ]);
        $this->businessSetting->updateOrInsert(['key' => 'ref_earning_exchange_rate'], [
            'value' => $request['ref_earning_exchange_rate'] ?? 0
        ]);
        $this->businessSetting->updateOrInsert(['key' => 'loyalty_point_item_purchase_point'], [
            'value' => $request['item_purchase_point'] ?? 0
        ]);
        $this->businessSetting->updateOrInsert(['key' => 'loyalty_point_minimum_point'], [
            'value' => $request['minimun_transfer_point'] ?? 0
        ]);

        Toastr::success(translate('customer_settings_updated_successfully'));
        return back();
    }


    public function saveViewPage(){
        $groups = $this->group->get();
        $weekdays = $this->day->get();
        $branches = $this->branch->select('id', 'name')->get();

        return view('admin-views.customer.customer-add',compact('groups','weekdays','branches'));
    }

    public function saveCustomer(CustomerAddRequest $request){
        $data = [
            "f_name"=>$request->f_name,
            "l_name"=>$request->l_name,
            "email"=>$request->email,
            "phone"=>$request->phone,
            "group_id"=>$request->group_id,
            'credit_limit'=>$request->credit_limit,'branch_option'=>$request->branch_option,'day_id'=>$request->day_id,
            "business_name"=>$request->business_name,
            "vat_business"=>$request->vat_business,
            "business_address"=>$request->business_address,


        ];
        if(!empty($request->id)){
            if($this->customer->find($request->id)->credit_limit != $request->credit_limit){
                $data["remaining_credit_limit"]=$request->credit_limit;
            }
        }else{
            $data["remaining_credit_limit"]=$request->credit_limit;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }


        $address = [
            "contact_person_number"=>$request->contact_person_number,
            "floor"=>$request->floor,
            "house"=>$request->house,
            "road"=>$request->road,
            "address"=>$request->address,
            "address_type"=>$request->address_type
        ];


        $customer = $this->customer->updateOrCreate(["id"=>$request->id],$data);
        $customer->addresses()->updateOrCreate(["user_id"=>$request->id,"id"=>$request->address_id],$address);

        if(empty($request->id)){
            Toastr::success(translate('Customer has been  created successfully!'));
        }else{
            Toastr::success(translate('Customer has been  updated successfully!'));

        }
        return redirect()->route("admin.customer.list");
    }

    public function editCustomer($id){
        $customer = $this->customer->find($id);
        $groups = $this->group->get();
        $weekdays = $this->day->get();
        $branches = $this->branch->select('id', 'name')->get();

        $addresses = $customer->addresses()->latest()->first();
        return view('admin-views.customer.customer-edit',compact('customer','addresses','groups','weekdays','branches'));

    }

}
