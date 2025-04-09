<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Coupon;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    public function __construct(
        private Coupon $coupon
    )
    {}

    /**
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request): Renderable
    {
        $queryParam = [];
        $search = $request['search'];

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $coupons = $this->coupon->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('title', 'like', "%{$value}%")
                        ->orWhere('code', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        } else {
            $coupons = $this->coupon;
        }

        $coupons = $coupons->latest()->paginate(Helpers::getPagination())->appends($queryParam);
        return view('admin-views.coupon.index', compact('coupons', 'search'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required',
            'title' => 'required|max:255',
            'start_date' => 'required',
            'expire_date' => 'required',
            'discount' => 'required|max:9',
            'min_purchase' => 'max:9',
            'max_discount' => 'max:9',
        ], [
            'title.max' => translate('Title is too long!'),
        ]);

        if ($request->discount_type == 'percent' && (int)$request->discount > 100) {
            Toastr::error(translate('discount_can_not_be_more_than_100%'));
            return back();
        }

        $this->coupon->insert([
            'title' => $request->title,
            'code' => $request->code,
            'limit' => $request->limit,
            'coupon_type' => $request->coupon_type,
            'start_date' => $request->start_date,
            'expire_date' => $request->expire_date,
            'min_purchase' => $request->min_purchase != null ? $request->min_purchase : 0,
            'max_discount' => $request->max_discount != null ? $request->max_discount : 0,
            'discount' => $request->discount,
            'discount_type' => $request->discount_type,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Toastr::success(translate('Coupon added successfully!'));
        return back();
    }

    /**
     * @param $id
     * @return Renderable
     */
    public function edit($id): Renderable
    {
        $coupon = $this->coupon->where(['id' => $id])->first();
        return view('admin-views.coupon.edit', compact('coupon'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'code' => 'required',
            'title' => 'required|max:255',
            'start_date' => 'required',
            'expire_date' => 'required',
            'discount' => 'required|max:9',
            'min_purchase' => 'max:9',
            'max_discount' => 'max:9',
        ], [
            'title.max' => translate('Title is too long!'),
        ]);

        $this->coupon->where(['id' => $id])->update([
            'title' => $request->title,
            'code' => $request->code,
            'limit' => $request->limit,
            'coupon_type' => $request->coupon_type,
            'start_date' => $request->start_date,
            'expire_date' => $request->expire_date,
            'min_purchase' => $request->min_purchase != null ? $request->min_purchase : 0,
            'max_discount' => $request->max_discount != null ? $request->max_discount : 0,
            'discount' => $request->discount_type == 'amount' ? $request->discount : $request['discount'],
            'discount_type' => $request->discount_type,
            'updated_at' => now()
        ]);

        Toastr::success(translate('Coupon updated successfully!'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
    {
        $coupon = $this->coupon->find($request->id);
        $coupon->status = $request->status;
        $coupon->save();

        Toastr::success(translate('Coupon status updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $coupon = $this->coupon->find($request->id);
        $coupon->delete();

        Toastr::success(translate('Coupon removed!'));
        return back();
    }

    /**
     * @return JsonResponse
     */
    public function generateCouponCode(): JsonResponse
    {
        return response()->json(Str::random(10));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function couponDetails(Request $request): JsonResponse
    {
        $coupon = $this->coupon->findOrFail($request->id);

        return response()->json([
            'success' => 1,
            'view' => view('admin-views.coupon.partials._coupon-view', compact('coupon'))->render(),
        ]);
    }

}
