<?php

namespace App\Http\Controllers\Branch;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Table;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Support\Renderable;

class TableController extends Controller
{
    public function __construct(
        private Table $table,
    )
    {}

    /**
     * @return Application|Factory|View
     */
    public function index(): Factory|View|Application
    {
        $tables = $this->table
            ->with(['order' => function ($q) {
                $q->whereHas('table_order', function ($q) {
                    $q->where('branch_table_token_is_expired', 0);
                });
            }])
            ->where(['branch_id' => auth('branch')->user()->id, 'is_active' => '1'])
            ->get()
            ->toArray();

        return view('branch-views.table.index2', compact('tables'));
    }

    /**
     * @param Request $request
     * @return Renderable
     */
    public function list(Request $request): Renderable
    {
        $search = $request['search'];
        $key = explode(' ', $request['search']);

        $tables = $this->table->with('branch')
            ->where('branch_id', auth('branch')->user()->id)
            ->when($search != null, function ($query) use ($key) {
                foreach ($key as $value) {
                    $query->where('number', 'like', "%{$value}%");
                }
            })
            ->orderBy('id', 'DESC')
            ->paginate(Helpers::getPagination());

        return view('branch-views.table.list', compact('tables', 'search'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'number' => [
                'required',
                Rule::unique('tables')->where(function ($query) use ($request) {
                    return $query->where(['number' => $request->number, 'branch_id' => auth('branch')->user()->id]);
                }),
            ],
            'capacity' => 'required|min:1|max:99',
        ], [
            'number.required' => translate('Table number is required!'),
            'number.unique' => translate('Table number is already exist in this branch!'),
            'capacity.required' => translate('Table capacity is required!'),
        ]);

        $table = $this->table;
        $table->number = $request->number;
        $table->capacity = $request->capacity;
        $table->branch_id = auth('branch')->user()->id;
        $table->is_active = 1;
        $table->save();

        Toastr::success(translate('Table added successfully!'));
        return redirect()->route('branch.table.list');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
    {
        $table = $this->table->find($request->id);
        $table->is_active = $request->status;
        $table->save();

        Toastr::success(translate('Table status updated!'));
        return back();
    }

    /**
     * @param $id
     * @return Renderable
     */
    public function edit($id): Renderable
    {
        $table = $this->table->where(['id' => $id, 'branch_id' => auth('branch')->user()->id])->first();
        return view('branch-views.table.edit', compact('table'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'number' => [
                'required',
                Rule::unique('tables')->where(function ($query) use ($request, $id) {
                    return $query->where(['number' => $request->number, 'branch_id' => auth('branch')->user()->id])
                        ->whereNotIn('id', [$id]);
                }),
            ],
            'capacity' => 'required|min:1|max:99',
        ], [
            'number.required' => translate('Table number is required!'),
            'number.unique' => translate('Table number is already exist in this branch!'),
            'capacity.required' => translate('Table capacity is required!'),
        ]);

        $table = $this->table->where(['id' => $id, 'branch_id' => auth('branch')->user()->id])->first();
        $table->number = $request->number;
        $table->capacity = $request->capacity;
        $table->update();

        Toastr::success(translate('Table updated successfully!'));
        return redirect()->route('branch.table.list');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $table = $this->table->where(['id' => $request->id, 'branch_id' => auth('branch')->user()->id])->first();
        $table->delete();

        Toastr::success(translate('Table removed!'));
        return back();
    }
}
