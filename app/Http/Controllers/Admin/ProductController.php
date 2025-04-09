<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Category;
use App\Model\Product;
use App\Model\ProductByBranch;
use App\Model\Review;
use App\Model\Tag;
use App\Model\Translation;
use App\Models\Cuisine;
use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Support\Renderable;
use Symfony\Component\HttpFoundation\StreamedResponse;


class ProductController extends Controller
{
    public function __construct(
        private Product         $product,
        private Category        $category,
        private ProductByBranch $productByBranch,
        private Translation     $translation,
        private Cuisine         $cuisine,
    )
    {}


    public function variantCombination(Request $request): JsonResponse
    {
        $options = [];
        $price = $request->price;
        $product_name = $request->name;

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                $my_str = implode('', $request[$name]);
                $options[] = explode(',', $my_str);
            }
        }

        $result = [[]];
        foreach ($options as $property => $property_values) {
            $tmp = [];
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $tmp[] = array_merge($result_item, [$property => $property_value]);
                }
            }
            $result = $tmp;
        }
        $combinations = $result;
        return response()->json([
            'view' => view('admin-views.product.partials._variant-combinations', compact('combinations', 'price', 'product_name'))->render(),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCategories(Request $request): JsonResponse
    {
        $cat = $this->category->where(['parent_id' => $request->parent_id])->get();
        $res = '<option value="' . 0 . '" disabled selected>---Select---</option>';
        foreach ($cat as $row) {
            if ($row->id == $request->sub_category) {
                $res .= '<option value="' . $row->id . '" selected >' . $row->name . '</option>';
            } else {
                $res .= '<option value="' . $row->id . '">' . $row->name . '</option>';
            }
        }

        return response()->json([
            'options' => $res,
        ]);
    }

    /**
     * @return Renderable
     */
    public function index(): Renderable
    {
        $categories = $this->category->where(['position' => 0])->get();
        $cuisines = $this->cuisine::active()->orderBy('priority', 'ASC')->get();
        return view('admin-views.product.index', compact('categories', 'cuisines'));
    }

    /**
     * @param Request $request
     * @return Renderable
     */
    public function list(Request $request): Renderable
    {
        Helpers::update_daily_product_stock();

        $queryParam = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $query = $this->product->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                        ->orWhere('name', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        } else {
            $query = $this->product;
        }

        $products = $query->with('main_branch_product')->orderBy('id', 'DESC')->paginate(Helpers::getPagination())->appends($queryParam);
        return view('admin-views.product.list', compact('products', 'search'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $key = explode(' ', $request['search']);
        $products = $this->product->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }
        })->get();

        return response()->json([
            'view' => view('admin-views.product.partials._table', compact('products'))->render()
        ]);
    }

    /**
     * @param $id
     * @return Renderable
     */
    public function view($id): Renderable
    {
        $product = $this->product->where(['id' => $id])->first();
        $reviews = Review::where(['product_id' => $id])->latest()->paginate(20);

        return view('admin-views.product.view', compact('product', 'reviews'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:products',
            'category_id' => 'required',
            'image' => 'required',
            'price' => 'required|numeric',
            'item_type' => 'required',
            'product_type' => 'required|in:veg,non_veg',
            'discount_type' => 'required',
            'tax_type' => 'required',
            'stock_type' => 'required|in:unlimited,daily,fixed',
            'product_stock' => 'required_if:stock_type,daily,fixed',
        ], [
            'name.required' => translate('Product name is required!'),
            'name.unique' => translate('Product name has been taken.'),
            'category_id.required' => translate('category  is required!'),
        ]);

        if (in_array(request('stock_type'), ['daily', 'fixed'])) {
            if($request->product_stock < 1){
                $validator->getMessageBag()->add('product_stock', translate('Product stock must be at least 1!'));
            }
        }

        if ($request['discount_type'] == 'percent') {
            $discount = ($request['price'] / 100) * $request['discount'];
        } else {
            $discount = $request['discount'];
        }


        if ($request['price'] <= $discount) {
            $validator->getMessageBag()->add('unit_price', translate('Discount can not be more or equal to the price!'));
        }

        if ($request['price'] <= $discount || (in_array(request('stock_type'), ['daily', 'fixed']) && $request->product_stock < 1) || $validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $tagIds = [];
        if ($request->tags != null) {
            $tags = explode(",", $request->tags);
        }
        if (isset($tags)) {
            foreach ($tags as $key => $value) {
                $tag = Tag::firstOrNew(
                    ['tag' => $value]
                );
                $tag->save();
                $tagIds[] = $tag->id;
            }
        }

        $imageNames = [];
        if (!empty($request->file('images'))) {
            foreach ($request->images as $img) {
                $imageData = Helpers::upload('product/', 'png', $img);
                $imageNames[] = $imageData;
            }
            $imageData = json_encode($imageNames);
        } else {
            $imageData = json_encode([]);
        }

        $product = $this->product;
        $product->name = $request->name[array_search('en', $request->lang)];

        $category = [];
        if ($request->category_id != null) {
            $category[] = [
                'id' => $request->category_id,
                'position' => 1,
            ];
        }
        if ($request->sub_category_id != null) {
            $category[] = [
                'id' => $request->sub_category_id,
                'position' => 2,
            ];
        }
        if ($request->sub_sub_category_id != null) {
            $category[] = [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ];
        }

        $product->category_ids = json_encode($category);
        $product->description = strip_tags($request->description[array_search('en', $request->lang)]);

        $choiceOptions = [];
        $product->choice_options = json_encode($choiceOptions);

        //new variation
        $variations = [];
        if (isset($request->options)) {
            foreach (array_values($request->options) as $key => $option) {
                $temp_variation['name'] = $option['name'];
                $temp_variation['type'] = $option['type'];
                $temp_variation['min'] = $option['min'] ?? 0;
                $temp_variation['max'] = $option['max'] ?? 0;
                $temp_variation['required'] = $option['required'] ?? 'off';
                if ($option['min'] > 0 && $option['min'] >= $option['max']) {
                    $validator->getMessageBag()->add('name', translate('maximum_value_can_not_be_smaller_or_equal_then_minimum_value'));
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if (!isset($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('please_add_options_for') . ' ' . $option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if ($option['max'] > count($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('please_add_more_options_or_change_the_max_value_for') . ' ' . $option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                $temp_value = [];

                foreach (array_values($option['values']) as $value) {
                    if (isset($value['label'])) {
                        $temp_option['label'] = $value['label'];
                    }
                    $temp_option['optionPrice'] = $value['optionPrice'];
                    $temp_value[] = $temp_option;
                }
                $temp_variation['values'] = $temp_value;
                $variations[] = $temp_variation;
            }
        }

        $product->variations = json_encode($variations);
        $product->price = $request->price;
        $product->set_menu = $request->item_type;
        $product->product_type = $request->product_type;
        $product->image = Helpers::upload('product/', 'png', $request->file('image'));
        $product->available_time_starts = $request->available_time_starts;
        $product->available_time_ends = $request->available_time_ends;

        $product->tax = $request->tax_type == 'amount' ? $request->tax : $request->tax;
        $product->tax_type = $request->tax_type;

        $product->discount = $request->discount_type == 'amount' ? $request->discount : $request->discount;
        $product->discount_type = $request->discount_type;

        $product->attributes = $request->has('attribute_id') ? json_encode($request->attribute_id) : json_encode([]);
        $product->add_ons = $request->has('addon_ids') ? json_encode($request->addon_ids) : json_encode([]);
        $product->status = $request->status == 'on' ? 1 : 0;
        $product->is_recommended = $request->is_recommended == 'on' ? 1 : 0;
        $product->save();

        $product->tags()->sync($tagIds);
        $product->cuisines()->sync($request->cuisines);

        $mainBranchProduct = $this->productByBranch;
        $mainBranchProduct->product_id = $product->id;
        $mainBranchProduct->price = $request->price;
        $mainBranchProduct->discount_type = $request->discount_type;
        $mainBranchProduct->discount = $request->discount;
        $mainBranchProduct->branch_id = 1;
        $mainBranchProduct->is_available = 1;
        $mainBranchProduct->variations = $variations;
        $mainBranchProduct->stock_type = $request->stock_type;
        $mainBranchProduct->stock = $request->product_stock ?? 0;
        $mainBranchProduct->save();

        $data = [];
        foreach ($request->lang as $index => $key) {
            if ($request->name[$index] && $key != 'en') {
                $data[] = array(
                    'translationable_type' => 'App\Model\Product',
                    'translationable_id' => $product->id,
                    'locale' => $key,
                    'key' => 'name',
                    'value' => $request->name[$index],
                );
            }
            if ($request->description[$index] && $key != 'en') {
                $data[] = array(
                    'translationable_type' => 'App\Model\Product',
                    'translationable_id' => $product->id,
                    'locale' => $key,
                    'key' => 'description',
                    'value' => strip_tags($request->description[$index]),
                );
            }
        }
        $this->translation->insert($data);

        return response()->json([], 200);
    }


    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id): View|Factory|Application
    {
        $product = $this->product->withoutGlobalScopes()->with(['translations', 'main_branch_product', 'cuisines'])->find($id);
        $product_category = json_decode($product->category_ids);
        $categories = $this->category->where(['parent_id' => 0])->get();
        $cuisines = $this->cuisine::active()->orderBy('priority', 'ASC')->get();

        return view('admin-views.product.edit', compact('product', 'product_category', 'categories', 'cuisines'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
    {
        $product = $this->product->find($request->id);
        $product->status = $request->status;
        $product->save();

        Toastr::success(translate('Product status updated!'));
        return back();
    }


    /**
     * @param $label
     * @param $array
     * @return int|string|null
     */
    function searchForLabel($label, $array): int|string|null
    {
        foreach ($array as $key => $val) {
            if ($val['label'] === $label) {
                return $key;
            }
        }
        return null;
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:products,name,' . $id,
            'category_id' => 'required',
            'price' => 'required|numeric',
            'product_type' => 'required|in:veg,non_veg',
            'item_type' => 'required',
            'discount_type' => 'required',
            'tax_type' => 'required',
            'stock_type' => 'required|in:unlimited,daily,fixed',
            'product_stock' => 'required_if:stock_type,daily,fixed',
        ], [
            'name.required' => translate('Product name is required!'),
            'category_id.required' => translate('category  is required!'),
        ]);

        if (in_array(request('stock_type'), ['daily', 'fixed'])) {
            if($request->product_stock < 1){
                $validator->getMessageBag()->add('product_stock', translate('Product stock must be at least 1!'));
            }
        }

        if ($request['discount_type'] == 'percent') {
            $discount = ($request['price'] / 100) * $request['discount'];
        } else {
            $discount = $request['discount'];
        }

        if ($request['price'] <= $discount) {
            $validator->getMessageBag()->add('unit_price', translate('Discount can not be more or equal to the price!'));
        }

        if ($request['price'] <= $discount || (in_array(request('stock_type'), ['daily', 'fixed']) && $request->product_stock < 1) || $validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $tagIds = [];
        if ($request->tags != null) {
            $tags = explode(",", $request->tags);
        }
        if (isset($tags)) {
            foreach ($tags as $key => $value) {
                $tag = Tag::firstOrNew(
                    ['tag' => $value]
                );
                $tag->save();
                $tagIds[] = $tag->id;
            }
        }

        $product = $this->product->find($id);
        $product->name = $request->name[array_search('en', $request->lang)];

        $category = [];
        if ($request->category_id != null) {
            $category[] = [
                'id' => $request->category_id,
                'position' => 1,
            ];
        }
        if ($request->sub_category_id != null) {
            $category[] = [
                'id' => $request->sub_category_id,
                'position' => 2,
            ];
        }
        if ($request->sub_sub_category_id != null) {
            $category[] = [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ];
        }

        $product->category_ids = json_encode($category);
        $product->description = strip_tags($request->description[array_search('en', $request->lang)]);

        $choiceOptions = [];
        $product->choice_options = json_encode($choiceOptions);

        //new variation
        $variations = [];
        if (isset($request->options)) {
            foreach (array_values($request->options) as $key => $option) {
                $temp_variation['name'] = $option['name'];
                $temp_variation['type'] = $option['type'];
                $temp_variation['min'] = $option['min'] ?? 0;
                $temp_variation['max'] = $option['max'] ?? 0;
                if ($option['min'] > 0 && $option['min'] >= $option['max']) {
                    $validator->getMessageBag()->add('name', translate('maximum_value_can_not_be_smaller_or_equal_then_minimum_value'));
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if (!isset($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('please_add_options_for') . ' ' . $option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if ($option['max'] > count($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('please_add_more_options_or_change_the_max_value_for') . ' ' . $option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                $temp_variation['required'] = $option['required'] ?? 'off';

                $temp_value = [];

                foreach (array_values($option['values']) as $value) {
                    if (isset($value['label'])) {
                        $temp_option['label'] = $value['label'];
                    }
                    $temp_option['optionPrice'] = $value['optionPrice'];
                    $temp_value[] = $temp_option;
                }
                $temp_variation['values'] = $temp_value;
                $variations[] = $temp_variation;
            }
        }

        $product->variations = json_encode($variations);

        // branch variation update start
        $branch_products = $this->productByBranch->where(['product_id' => $id])->get();

        foreach ($branch_products as $branch_product) {
            $mapped = array_map(function ($variation) use ($branch_product) {
                $variation_array = [];
                $variation_array['name'] = $variation['name'];
                $variation_array['type'] = $variation['type'];
                $variation_array['min'] = $variation['min'];
                $variation_array['max'] = $variation['max'];
                $variation_array['required'] = $variation['required'];
                $variation_array['values'] = array_map(function ($value) use ($branch_product, $variation) {
                    $option_array = [];
                    $option_array['label'] = $value['label'];

                    $price = $value['optionPrice'];
                    foreach ($branch_product['variations'] as $branch_variation) {
                        if ($branch_variation['name'] == $variation['name']) {
                            foreach ($branch_variation['values'] as $branch_value) {
                                if ($branch_value['label'] == $value['label']) {
                                    $price = $branch_value['optionPrice'];
                                }
                            }
                        }
                    }
                    $option_array['optionPrice'] = $price;

                    return $option_array;
                }, $variation['values']);
                return $variation_array;
            }, $variations);

            $data = [
                'variations' => $mapped,
            ];

            $this->productByBranch->whereIn('product_id', [$id])->update($data);
        }

        // branch variation update end
        $product->price = $request->price;
        $product->set_menu = $request->item_type;
        $product->product_type = $request->product_type;
        $product->image = $request->has('image') ? Helpers::update('product/', $product->image, 'png', $request->file('image')) : $product->image;
        $product->available_time_starts = $request->available_time_starts;
        $product->available_time_ends = $request->available_time_ends;

        $product->tax = $request->tax_type == 'amount' ? $request->tax : $request->tax;
        $product->tax_type = $request->tax_type;

        $product->discount = $request->discount_type == 'amount' ? $request->discount : $request->discount;
        $product->discount_type = $request->discount_type;

        $product->attributes = $request->has('attribute_id') ? json_encode($request->attribute_id) : json_encode([]);
        $product->add_ons = $request->has('addon_ids') ? json_encode($request->addon_ids) : json_encode([]);
        $product->status = $request->status == 'on' ? 1 : 0;
        $product->is_recommended = $request->is_recommended == 'on' ? 1 : 0;
        $product->save();

        $product->tags()->sync($tagIds);
        $product->cuisines()->sync($request->cuisines);

        $updatedProduct = $this->productByBranch->updateOrCreate([
            'product_id' => $product->id,
            'branch_id' => 1,
        ], [
                'product_id' => $product->id,
                'price' => $request->price,
                'discount_type' => $request->discount_type,
                'discount' => $request->discount,
                'branch_id' => 1,
                'is_available' => 1,
                'variations' => $variations,
                'stock_type' => $request->stock_type,
                'stock' =>  $request->product_stock ?? 0,
            ]
        );

        if ($updatedProduct->wasChanged('stock_type') || $updatedProduct->wasChanged('stock')) {
            $updatedProduct->sold_quantity = 0;
            $updatedProduct->save();
        }

        foreach ($request->lang as $index => $key) {
            if ($request->name[$index] && $key != 'en') {
                $this->translation->updateOrInsert(
                    ['translationable_type' => 'App\Model\Product',
                        'translationable_id' => $product->id,
                        'locale' => $key,
                        'key' => 'name'],
                    ['value' => $request->name[$index]]
                );
            }
            if ($request->description[$index] && $key != 'en') {
                $this->translation->updateOrInsert(
                    ['translationable_type' => 'App\Model\Product',
                        'translationable_id' => $product->id,
                        'locale' => $key,
                        'key' => 'description'],
                    ['value' => strip_tags($request->description[$index])]
                );
            }
        }

        return response()->json([], 200);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $product = $this->product->find($request->id);
        Helpers::delete('product/' . $product['image']);
        $product->delete();

        Toastr::success(translate('Product removed!'));
        return back();
    }

    /**
     * @return Renderable
     */
    public function bulkImportIndex(): Renderable
    {
        return view('admin-views.product.bulk-import');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function bulkImportData(Request $request): RedirectResponse
    {
        try {
            $collections = (new FastExcel)->import($request->file('products_file'));
        } catch (\Exception $exception) {
            Toastr::error(translate('You have uploaded a wrong format file, please upload the right file.'));
            return back();
        }

        $fieldArray = ['name', 'description', 'price', 'tax', 'category_id', 'sub_category_id', 'discount', 'discount_type', 'tax_type', 'set_menu', 'available_time_starts', 'available_time_ends', 'product_type'];
        if (count($collections) < 1) {
            Toastr::error(translate('At least one product have to import.'));
            return back();
        }
        foreach ($fieldArray as $field) {
            if (!array_key_exists($field, $collections->first())) {
                Toastr::error(translate($field) . translate(' must not be empty.'));
                return back();
            }
        }

        $data = [];
        foreach ($collections as $key => $collection) {
            if ($collection['name'] === "") {
                Toastr::error(translate('Please fill name field of row') . ' ' . ($key + 2));
                return back();
            }
            if ($collection['description'] === "") {
                Toastr::error(translate('Please fill description field of row') . ' ' . ($key + 2));
                return back();
            }
            if ($collection['price'] === "") {
                Toastr::error(translate('Please fill price field of row') . ' ' . ($key + 2));
                return back();
            }
            if ($collection['tax'] === "") {
                Toastr::error(translate('Please fill tax field of row') . ' ' . ($key + 2));
                return back();
            }
            if ($collection['category_id'] === "") {
                Toastr::error(translate('Please fill category_id field of row') . ' ' . ($key + 2));
                return back();
            }
            if ($collection['sub_category_id'] === "") {
                Toastr::error(translate('Please fill sub_category_id field of row') . ' ' . ($key + 2));
                return back();
            }
            if ($collection['discount'] === "") {
                Toastr::error(translate('Please fill discount field of row') . ' ' . ($key + 2));
                return back();
            }
            if ($collection['discount_type'] === "") {
                Toastr::error(translate('Please fill discount_type field of row') . ' ' . ($key + 2));
                return back();
            }
            if ($collection['tax_type'] === "") {
                Toastr::error(translate('Please fill tax_type field of row') . ' ' . ($key + 2));
                return back();
            }
            if ($collection['set_menu'] === "") {
                Toastr::error(translate('Please fill set_menu field of row') . ' ' . ($key + 2));
                return back();
            }

            if ($collection['product_type'] === "") {
                Toastr::error(translate('Please fill product_type field of row') . ' ' . ($key + 2));
                return back();
            }

            if (!is_numeric($collection['price'])) {
                Toastr::error(translate('Price of row') . ' ' . ($key + 2) . ' ' . translate('must be number'));
                return back();
            }

            if (!is_numeric($collection['discount'])) {
                Toastr::error(translate('Discount of row') . ' ' . ($key + 2) . ' ' . translate('must be number'));
                return back();
            }

            if (!is_numeric($collection['tax'])) {
                Toastr::error(translate('Tax of row') . ' ' . ($key + 2) . ' ' . ' must be number');
                return back();
            }

            $product = [
                'discount_type' => $collection['discount_type'],
                'discount' => $collection['discount'],
            ];
            if ($collection['price'] <= Helpers::discount_calculate($product, $collection['price'])) {
                Toastr::error(translate('Discount can not be more or equal to the price in row') . ' ' . ($key + 2));
                return back();
            }
            if (!isset($collection['available_time_starts'])) {
                Toastr::error(translate('Please fill available_time_starts field'));
                return back();
            } elseif ($collection['available_time_starts'] === "") {
                Toastr::error(translate('Please fill available_time_starts field of row') . ' ' . ($key + 2));
                return back();
            }
            if (!isset($collection['available_time_ends'])) {
                Toastr::error(translate('Please fill available_time_ends field'));
                return back();
            } elseif ($collection['available_time_ends'] === "") {
                Toastr::error(translate('Please fill available_time_ends field of row ') . ' ' . ($key + 2));
                return back();
            }
        }

        foreach ($collections as $collection) {
            $data[] = [
                'name' => $collection['name'],
                'description' => $collection['description'],
                'image' => 'def.png',
                'price' => $collection['price'],
                'variations' => json_encode([]),
                'add_ons' => json_encode([]),
                'tax' => $collection['tax'],
                'available_time_starts' => $collection['available_time_starts'],
                'available_time_ends' => $collection['available_time_ends'],
                'status' => 1,
                'attributes' => json_encode([]),
                'category_ids' => json_encode([['id' => (string)$collection['category_id'], 'position' => 1], ['id' => (string)$collection['sub_category_id'], 'position' => 2]]),
                'choice_options' => json_encode([]),
                'discount' => $collection['discount'],
                'discount_type' => $collection['discount_type'],
                'tax_type' => $collection['tax_type'],
                'set_menu' => $collection['set_menu'],
                'product_type' => $collection['product_type'],
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        $this->product->insert($data);

        Toastr::success(count($data) . ' - ' . translate('Products imported successfully!'));
        return back();
    }


    /**
     * @param Request $request
     * @return RedirectResponse|string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function bulkExportData(Request $request): StreamedResponse|string|RedirectResponse
    {
        if ($request->type == 'date_wise') {
            $request->validate([
                'start_date' => 'required',
                'end_date' => 'required'
            ]);
        }
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $products = $this->product->when($request['type'] == 'date_wise', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })->get();

        $storage = [];

        if ($products->count() < 1) {
            Toastr::info(translate('no_product_found'));
            return back();
        }

        foreach ($products as $item) {
            $categoryId = 0;
            $subCategoryId = 0;
            foreach (json_decode($item->category_ids, true) as $category) {
                if ($category['position'] == 1) {
                    $categoryId = $category['id'];
                } else if ($category['position'] == 2) {
                    $subCategoryId = $category['id'];
                }
            }

            if (!isset($item->name)) {
                $item->name = 'Demo Product';
            }

            if (!isset($item->description)) {
                $item->description = 'No description available';
            }

            $storage[] = [
                'name' => $item->name,
                'description' => $item->description,
                'category_id' => $categoryId,
                'sub_category_id' => $subCategoryId,
                'price' => $item->price,
                'tax' => $item->tax,
                'available_time_starts' => $item->available_time_starts,
                'available_time_ends' => $item->available_time_ends,
                'status' => $item->status,
                'discount' => $item->discount,
                'discount_type' => $item->discount_type,
                'tax_type' => $item->tax_type,
                'set_menu' => $item->set_menu,
                'product_type' => $item->product_type,
            ];
        }
        return (new FastExcel($storage))->download('products.xlsx');
    }


    /**
     * @param Request $request
     * @return string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function excelImport(Request $request): StreamedResponse|string
    {
        $storage = [];
        $search = $request['search'];
        $products = $this->product->when($search, function ($query) use ($search) {
            $key = explode(' ', $search);
            foreach ($key as $value) {
                $query->orWhere('id', 'like', "%{$value}%")
                    ->orWhere('name', 'like', "%{$value}%");
            };
        })->get();

        foreach ($products as $item) {
            $categoryId = 0;
            $subCategoryId = 0;
            foreach (json_decode($item->category_ids, true) as $category) {
                if ($category['position'] == 1) {
                    $categoryId = $category['id'];
                } elseif ($category['position'] == 2) {
                    $subCategoryId = $category['id'];
                }
            }
            if (!isset($item->name)) {
                $item->name = 'Demo Product';
            }
            if (!isset($item->description)) {
                $item->description = 'No description available';
            }
            $storage[] = array(
                'Name' => $item->name,
                'Description' => $item->description,
                'Category ID' => $categoryId,
                'Sub Category ID' => $subCategoryId,
                'Price' => $item->price,
                'Tax' => $item->tax,
                'Available Time Starts' => $item->available_time_starts,
                'Available Time Ends' => $item->available_time_ends,
                'Status' => $item->status,
                'Discount' => $item->discount,
                'Discount Type' => $item->discount_type,
                'Tax Type' => $item->tax_type,
                'Set Menu' => $item->set_menu,
                'Product Type' => $item->product_type,
            );
        }
        return (new FastExcel($storage))->download('products.xlsx');
    }

    /**
     * @return Renderable
     */
    public function bulkExportIndex(): Renderable
    {
        return view('admin-views.product.bulk-export');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function recommended(Request $request): RedirectResponse
    {
        $product = $this->product->find($request->id);
        $product->is_recommended = $request->status;
        $product->save();

        Toastr::success(translate('updated successfully!'));
        return back();
    }
}
