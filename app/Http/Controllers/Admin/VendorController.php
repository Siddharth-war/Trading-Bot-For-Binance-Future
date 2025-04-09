<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Conversation;
use App\Model\Newsletter;
use App\Model\Order;
use App\Model\PointTransitions;
use App\Model\BusinessSetting;
use App\Models\Vendor;
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
use App\Http\Requests\VendorRegistrationRequest;
use Hash;
use Carbon\Carbon;
use App\Models\Group;
use App\Models\Day;
use App\Model\Branch;

class VendorController extends Controller
{
    public function __construct(
        private Vendor             $vendor,
    )
    {
    }



    /**
     * @param Request $request
     * @return Renderable
     */
    public function vendorList(Request $request): Renderable
    {
        $queryParam = [];
        $search = $request['search'];

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $customers = $this->vendor->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('first_name', 'like', "%{$value}%")
                        ->orWhere('last_name', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        } else {
            $customers = $this->vendor;
        }

        $customers = $customers->latest()->paginate(Helpers::getPagination())->appends($queryParam);
        return view('admin-views.vendor.list', compact('customers', 'search'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return RedirectResponse|Renderable
     */
    public function view($id, Request $request): RedirectResponse|Renderable
    {
        $search = $request->search;
        $vendor = $this->vendor->with('invoice')->find($id);

        if (!isset($vendor)) {
            Toastr::error(translate('vendor not found!'));
            return back();
        }

        return view('admin-views.vendor.vendor-view', compact('vendor', 'search'));
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserInfo(Request $request): JsonResponse
    {
        $user = $this->vendor->find($request['id']);
        $unchecked = $this->conversation->where(['user_id' => $request['id'], 'checked' => 0])->count();

        $output = [
            'id' => $user->id ?? '',
            'first_name' => $user->f_name ?? '',
            'last_name' => $user->l_name ?? '',
            'email' => $user->email ?? '',
            'image' => ($user && $user->image) ? asset('storage/app/public') . '/' . $user->image : asset('/public/assets/admin/img/160x160/img1.jpg'),
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
     * @param $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $this->vendor->findOrFail($id)->update(['is_active' => $request['status']]);
        return response()->json($request['status']);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        try {
            $this->vendor->findOrFail($request['id'])->delete();
            Toastr::success(translate('vendor_deleted_successfully!'));

        } catch (\Exception $e) {
            Toastr::error(translate('user_not_found!'));
        }
        return back();
    }


    public function saveViewPage(){

        return view('admin-views.vendor.vendor-add');
    }

    public function saveVendor(VendorRegistrationRequest $request){
        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $data['phone_number'] = $data['phone'];

        Vendor::updateOrCreate(["id"=>$request->id],$data);
        if(empty($request->id)){
            Toastr::success("Vendor been created successfully");

        }else{
            Toastr::success("Vendor been updated successfully");

        }
        return redirect()->route("admin.vendor.list");
    }

    public function editVendor($id){
        $vendor = $this->vendor->find($id);
        return view('admin-views.vendor.vendor-edit',compact('vendor'));

    }

}
