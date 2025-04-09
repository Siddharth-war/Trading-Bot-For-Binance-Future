<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\AddOn;
use App\Model\Translation;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AddonController extends Controller
{
    public function __construct(
        private AddOn       $addon,
        private Translation $translation
    ){}

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
            $addons = $this->addon->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        } else {
            $addons = $this->addon;
        }

        $addons = $addons->orderBy('id', 'DESC')->paginate(Helpers::getPagination())->appends($queryParam);
        return view('admin-views.addon.index', compact('addons', 'search'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|unique:add_ons',
            'price' => 'required|max:8',
            'tax' => 'required|max:100'
        ]);

        foreach ($request->name as $name) {
            if (strlen($name) > 255) {
                toastr::error(translate('Name is too long!'));
                return back();
            }
        }

        $addon = $this->addon;
        $addon->name = $request->name[array_search('en', $request->lang)];
        $addon->price = $request->price;
        $addon->tax = $request->tax;
        $addon->save();

        $data = [];
        foreach ($request->lang as $index => $key) {
            if ($request->name[$index] && $key != 'en') {
                $data[] = array(
                    'translationable_type' => 'App\Model\AddOn',
                    'translationable_id' => $addon->id,
                    'locale' => $key,
                    'key' => 'name',
                    'value' => $request->name[$index],
                );
            }
        }
        if (count($data)) {
            $this->translation->insert($data);
        }

        Toastr::success(translate('Addon added successfully!'));
        return back();
    }

    /**
     * @param $id
     * @return Renderable
     */
    public function edit($id): Renderable
    {
        $addon = $this->addon->withoutGlobalScopes()->with('translations')->find($id);
        return view('admin-views.addon.edit', compact('addon'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'name' => 'required|unique:add_ons,name,' . $id,
            'price' => 'required|max:9'
        ]);

        foreach ($request->name as $name) {
            if (strlen($name) > 255) {
                toastr::error('Name is too long!');
                return back();
            }
        }

        $addon = $this->addon->find($id);
        $addon->name = $request->name[array_search('en', $request->lang)];
        $addon->price = $request->price;
        $addon->tax = $request->tax;
        $addon->save();

        foreach ($request->lang as $index => $key) {
            if ($request->name[$index] && $key != 'en') {
                $this->translation->updateOrInsert(
                    ['translationable_type' => 'App\Model\AddOn',
                        'translationable_id' => $addon->id,
                        'locale' => $key,
                        'key' => 'name'],
                    ['value' => $request->name[$index]]
                );
            }
        }
        Toastr::success(translate('Addon updated successfully!'));
        return redirect()->route('admin.addon.add-new');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $addon = $this->addon->find($request->id);
        $addon->delete();
        Toastr::success(translate('Addon removed!'));
        return back();
    }
}
