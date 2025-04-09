<?php

namespace App\Http\Controllers\Branch;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Branch;
use App\Model\BranchPromotion;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Support\Renderable;

class BranchPromotionController extends Controller
{
    public function __construct(
        private Branch          $branch,
        private BranchPromotion $branchPromotion,
    )
    {}

    /**
     * @param Request $request
     * @return Renderable
     */
    public function create(Request $request): Renderable
    {
        $search = $request['search'];
        $key = explode(' ', $request['search']);
        $branch = $this->branch->where('id', auth('branch')->user()->id)->first();

        $promotions = $this->branchPromotion
            ->where('branch_id', auth('branch')->user()->id)
            ->when($search != null, function ($query) use ($key) {
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->where('branch_id', 'like', "%{$value}%")
                            ->orWhere('promotion_type', 'like', "%{$value}%")
                            ->orWhere('promotion_name', 'like', "%{$value}%");
                    }
                });
            })
            ->orderBy('id', 'DESC')
            ->paginate(Helpers::getPagination());

        return view('branch-views.branch_promotion.create', compact('search', 'promotions', 'branch'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $promotion = $this->branchPromotion;
        $promotion->branch_id = auth('branch')->user()->id;
        $promotion->promotion_type = $request->banner_type;;
        if ($request->video) {
            $promotion->promotion_name = $request->video;
        }
        if ($request->image) {
            $promotion->promotion_name = Helpers::upload('promotion/', 'png', $request->file('image'));
        }
        $promotion->save();

        Toastr::success(translate('Promotional campaign added successfully!'));
        return back();
    }

    /**
     * @param Request $request
     * @return Renderable
     */
    public function edit(Request $request): Renderable
    {
        $promotion = $this->branchPromotion->find($request->id);
        return view('branch-views.branch_promotion.edit', compact('promotion'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $promotion = $this->branchPromotion->find($request->id);
        $promotion->branch_id = auth('branch')->user()->id;
        $promotion->promotion_type = $request->banner_type;;
        if ($request->video) {
            $promotion->promotion_name = $request->video;
        }
        if ($request->image) {
            $promotion->promotion_name = $request->has('image') ? Helpers::update('promotion/', $promotion->image, 'png', $request->file('image')) : $promotion->image;
        }
        $promotion->update();

        Toastr::success(translate('Promotional campaign updated successfully!'));
        return redirect(url('branch/promotion/create'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $promotion = $this->branchPromotion->find($request->id);
        Helpers::delete('promotion/' . $promotion['promotion_name']);
        $promotion->delete();

        Toastr::success(translate('Promotional campaign removed!'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
    {
        $branch = $this->branch->find($request->id);
        $branch->branch_promotion_status = $request->status;
        $branch->save();

        Toastr::success(translate('Promotion campaign status updated!'));
        return back();
    }
}
