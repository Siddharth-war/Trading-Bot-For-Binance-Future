<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Model\Branch;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BusinessSettingsController extends Controller
{
    /**
     * @return Application|Factory|View
     */
    public function branchIndex(): Factory|View|Application
    {
        $branch = Branch::find(auth('branch')->user()->id);
        return view('branch-views.business-settings.branch-index', compact('branch'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function settingsUpdate(Request $request): RedirectResponse
    {
        $branch = Branch::find(auth('branch')->user()->id);
        $branch->name = $request->name;
        $branch->preparation_time = $request->preparation_time;
        $branch->save();

        Toastr::success(translate('settings updated!'));
        return back();
    }
}
