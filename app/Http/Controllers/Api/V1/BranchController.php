<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Branch;
use App\Model\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    public function __construct(
        private Branch $branch
    ){}

    /**
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $branches = $this->branch->latest()->get();
        return response()->json($branches, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function products(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $branchId = $request['branch_id'];
        $name = $request['name'];
        $categoryId = $request['category_id'];
        $productType = $request['product_type'];
        $sortBy = $request['sort_by'];
        $limit = is_null($request['limit']) ? 10 : $request['limit'];
        $offset = is_null($request['offset']) ? 1 : $request['offset'];

        $key = explode(' ', $name);
        $paginator = Product::active()
            ->with(['b_product', 'rating'])
            ->whereHas('b_product', function ($query) use($branchId){
                $query->where(['branch_id' => $branchId, 'is_available' => 1]);
            })
            ->whereHas('b_product.branch', function ($query) {
                $query->where('status', 1);
            })
            ->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }})
            ->when(isset($productType) && ($productType == 'veg' || $productType == 'non_veg'), function ($query) use ($productType) {
                return $query->productType(($productType == 'veg') ? 'veg' : 'non_veg');
            })
            ->when(isset($categoryId), function ($query) use ($categoryId) {
                return $query->whereJsonContains('category_ids', ['id'=>$categoryId]);
            })
            ->when($sortBy == 'popular', function ($query){
                return $query->orderBy('popularity_count', 'desc');
            })
            ->when($sortBy == 'price_high_to_low', function ($query){
                return $query->orderBy('price', 'desc');
            })
            ->when($sortBy == 'price_low_to_high', function ($query){
                return $query->orderBy('price', 'asc');
            })
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        $products = [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $paginator->items(),
        ];

        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return response()->json($products, 200);
    }
}
