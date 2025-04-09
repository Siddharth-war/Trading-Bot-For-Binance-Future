<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Translation;
use App\Models\Cuisine;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CuisineController extends Controller
{
    public function __construct(
        private Cuisine $cuisine,
        private Translation     $translation,

    )
    {}

    /**
     * @param Request $request
     * @return Renderable
     */
    function index(Request $request): Renderable
    {
        $queryParam = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);

            $cuisines = $this->cuisine
                ->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                });
            $queryParam = ['search' => $request['search']];
        } else {
            $cuisines = $this->cuisine;
        }

        $cuisines = $cuisines->orderBy('priority', 'ASC')->paginate(Helpers::getPagination())->appends($queryParam);
        return view('admin-views.cuisine.index', compact('cuisines', 'search'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required',
            'sub_title' => 'required',
            'image' => 'required|image|mimes:jpeg,png,gif|max:2048',
        ]);

        if (!empty($request->file('image'))) {
            $imageName = Helpers::upload('cuisine/', 'png', $request->file('image'));
        } else {
            $imageName = 'def.png';
        }

        $cuisine = $this->cuisine;
        $cuisine->name = $request->name[array_search('en', $request->lang)];;
        $cuisine->sub_title = $request->sub_title[array_search('en', $request->lang)];
        $cuisine->image = $imageName;
        $cuisine->is_active = 1;
        $cuisine->priority = 1;
        $cuisine->save();

        $data = [];
        foreach ($request->lang as $index => $key) {
            if ($request->name[$index] && $key != 'en') {
                $data[] = array(
                    'translationable_type' => 'App\Models\Cuisine',
                    'translationable_id' => $cuisine->id,
                    'locale' => $key,
                    'key' => 'name',
                    'value' => $request->name[$index],
                );
            }
            if ($request->sub_title[$index] && $key != 'en') {
                $data[] = array(
                    'translationable_type' => 'App\Models\Cuisine',
                    'translationable_id' => $cuisine->id,
                    'locale' => $key,
                    'key' => 'sub_title',
                    'value' => strip_tags($request->sub_title[$index]),
                );
            }
        }
        $this->translation->insert($data);

        Toastr::success(translate('Cuisine Added Successfully!'));
        return back();
    }

    /**
     * @param $id
     * @return Renderable
     */
    public function edit($id): Renderable
    {
        $cuisine = $this->cuisine->withoutGlobalScopes()->with('translations')->find($id);
        return view('admin-views.cuisine.edit', compact('cuisine'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
    {
        $cuisine = $this->cuisine->find($request->id);
        $cuisine->is_active = $request->status;
        $cuisine->save();

        Toastr::success(translate('cuisine status updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function featureStatus(Request $request): RedirectResponse
    {
        $cuisine = $this->cuisine->find($request->id);
        $cuisine->is_featured = $request->status;
        $cuisine->save();

        Toastr::success(translate('cuisine status updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'name' => 'required',
            'sub_title' => 'required',
            'image' => 'image|mimes:jpeg,png,gif|max:2048',
        ]);

        $cuisine = $this->cuisine->find($id);
        $cuisine->name = $request->name[array_search('en', $request->lang)];;
        $cuisine->sub_title = $request->sub_title[array_search('en', $request->lang)];
        $cuisine->image = $request->has('image') ? Helpers::update('cuisine/', $cuisine->image, 'png', $request->file('image')) : $cuisine->image;
        $cuisine->save();

        foreach ($request->lang as $index => $key) {
            if ($request->name[$index] && $key != 'en') {
                $this->translation->updateOrInsert(
                    ['translationable_type' => 'App\Models\Cuisine',
                        'translationable_id' => $cuisine->id,
                        'locale' => $key,
                        'key' => 'name'
                    ],
                    ['value' => $request->name[$index]]
                );
            }
            if ($request->sub_title[$index] && $key != 'en') {
                $this->translation->updateOrInsert(
                    ['translationable_type' => 'App\Models\Cuisine',
                        'translationable_id' => $cuisine->id,
                        'locale' => $key,
                        'key' => 'sub_title'
                    ],
                    ['value' => strip_tags($request->sub_title[$index])]
                );
            }
        }

        Toastr::success(translate('cuisine updated successfully!'));
        return redirect()->route('admin.cuisine.add');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $cuisine = $this->cuisine->find($request->id);
        Helpers::delete('cuisine/' . $cuisine['image']);
        $cuisine->delete();

        Toastr::success(translate('cuisine removed!'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function priority(Request $request): RedirectResponse
    {
        $cuisine = $this->cuisine->find($request->id);
        $cuisine->priority = $request->priority;
        $cuisine->save();

        Toastr::success(translate('priority updated!'));
        return back();
    }
}
