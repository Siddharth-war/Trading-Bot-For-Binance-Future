<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Branch;
use App\Model\BranchPromotion;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BranchPromotionController extends Controller
{
    public function __construct(
        private Branch          $branch,
        private BranchPromotion $branchPromotion
    )
    {}

    /**
     * @param Request $request
     * @return Renderable
     */
    public function create(Request $request): Renderable
    {
        $branches = $this->branch->orderBy('id', 'DESC')->where(['status' => 1])->get();
        $search = $request['search'];
        $key = explode(' ', $request['search']);

        $promotions = $this->branchPromotion->with('branch')
            ->when($search != null, function ($query) use ($key) {
                foreach ($key as $value) {
                    $query->where('branch_id', 'like', "%{$value}%")
                        ->orWhere('promotion_type', 'like', "%{$value}%")
                        ->orWhere('promotion_name', 'like', "%{$value}%");
                }
            })
            ->orderBy('id', 'DESC')
            ->paginate(Helpers::getPagination());

        return view('admin-views.branch_promotion.create', compact('branches', 'search', 'promotions'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'branch_id' => 'required',
        ], [
            'branch_id.required' => translate('Branch select is required!'),
        ]);

        $promotion = $this->branchPromotion;
        $promotion->branch_id = $request->branch_id;
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
        $branches = $this->branch->orderBy('id', 'DESC')->get();
        return view('admin-views.branch_promotion.edit', compact('promotion', 'branches'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'branch_id' => 'required',
        ], [
            'branch_id.required' => translate('Branch select is required!'),
        ]);

        $promotion = $this->branchPromotion->find($request->id);
        $promotion->branch_id = $request->branch_id;
        $promotion->promotion_type = $request->banner_type;;
        if ($request->video) {
            $promotion->promotion_name = $request->video;
        }
        if ($request->image) {
            $promotion->promotion_name = $request->has('image') ? Helpers::update('promotion/', $promotion->image, 'png', $request->file('image')) : $promotion->image;

        }
        $promotion->update();

        Toastr::success(translate('Promotional campaign updated successfully!'));
        return back();
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
     * @return Renderable
     */
    public function branchWiseList(Request $request): Renderable
    {
        $search = $request['search'];
        $key = explode(' ', $request['search']);
        $branch = $this->branch->where('id', $request->id)->first();

        $promotions = $this->branchPromotion->where('branch_id', $request->id)
            ->with('branch')
            ->when($search != null, function ($query) use ($key) {
                foreach ($key as $value) {
                    $query->where('branch_id', 'like', "%{$value}%")
                        ->orWhere('promotion_type', 'like', "%{$value}%")
                        ->orWhere('promotion_name', 'like', "%{$value}%");
                }
            })
            ->paginate(Helpers::getPagination());

        return view('admin-views.branch_promotion.branch_wise_list', compact('search', 'promotions', 'branch'));
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
