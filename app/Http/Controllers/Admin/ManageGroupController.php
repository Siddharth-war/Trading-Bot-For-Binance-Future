<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupRequest;
use App\Http\Requests\GroupAddRequest;
use App\Model\Category;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\GroupCategory;

class ManageGroupController extends Controller
{
    public $groupModel;
    public $categoryModel;
    public function __construct(Group $group,Category $category){
        $this->groupModel = $group;
        $this->categoryModel = $category;

    }


    public function index() {
        $groups =$this->groupModel->with('cate')->latest()->paginate(5);
        return view('admin-views.manage-group.index', compact('groups'));
    }

    public function create() {
        $categoryInstance = $this->categoryModel->where(['position' => 0])->active()->get();
        return view('admin-views.manage-group.create',["categories"=>$categoryInstance]);
    }

    public function store(GroupAddRequest $request) {
        $data = [
            "name"=>$request->name,
            "credit_limit"=>$request->credit_limit,
        ];
       $group =  $this->groupModel->create($data);

        if(!empty($request->add)){
            foreach($request->add as $val){
                $category = $this->categoryModel->updateOrCreate(
                    ['name' => $val['categories']],
                    [
                        'name' => $val['categories'],
                        "parent_id" => 0,
                        "position" => 0,

                    ]
                );

                $gcData['price'] =  $val['prices'];
                $gcData['category_id'] =  $category->id;
                $gcData['group_id'] =  $group->id;
                $gc = GroupCategory::create($gcData);
            }
        }
       Toastr::success("Group has been created successfully.");
        return redirect()->route('admin.groups.index');
    }

    public function edit(Group $group) {
        $categories = $this->categoryModel->with('price')->where(['position' => 0])->active()->get();

        return view('admin-views.manage-group.edit', compact('group','categories'));
    }

    public function update(GroupRequest $request, Group $group) {

        $data = [
            "name"=>$request->name,
            "credit_limit"=>$request->credit_limit,
        ];
        $group->update($data);
        $gcId = [];
        if(!empty($request->add)){
            foreach($request->add as $val){
                $category = $this->categoryModel->updateOrCreate(
                    ['name' => $val['categories']],
                    [
                        'name' => $val['categories'],
                        "parent_id" => 0,
                        "position" => 0,

                    ]
                );

                $gcData['price'] =  $val['prices'];
                $gcData['category_id'] =  $category->id;
                $gcData['group_id'] =  $group->id;
                $gc = GroupCategory::updateOrCreate(["category_id"=>$category->id,"group_id"=>$group->id],$gcData);
                $gcId[] = $gc->id;
            }
            GroupCategory::where(["group_id"=>$group->id])->whereNotIn("id",$gcId)->delete();

        }else{
            GroupCategory::where(["group_id"=>$group->id])->delete();
        }

       Toastr::success("Group has been updated successfully.");
        return redirect()->route('admin.groups.index');
    }

    public function destroy(Group $group) {
        $group->delete();
       Toastr::success("Group has been deleted successfully.");

        return back();
    }


}
