<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\CategoryLogic;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function __construct(
        private Category $category,
    )
    {}

    /**
     * @return JsonResponse
     */
    public function getCategories(): JsonResponse
    {
        $categories = Cache::rememberForever(CATEGORIES_WITH_CHILDES, function () {
            return $this->category
                ->with('childes')
                ->where(['position' => 0, 'status' => 1])
                ->orderBy('priority', 'ASC')
                ->get();
        });

        return response()->json($categories, 200);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function getChildes($id): JsonResponse
    {
        $categories = $this->category->where(['parent_id' => $id, 'status' => 1])->get();
        return response()->json($categories, 200);
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function getProducts($id, Request $request): JsonResponse
    {
        $productType = $request['product_type'];
        $name = $request['name'];
        $products = CategoryLogic::products(category_id: $id, type: $productType, name: $name, limit: $request['limit'], offset: $request['offset']);
        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return response()->json($products, 200);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function getAllProducts($id): JsonResponse
    {
        return response()->json(Helpers::product_data_formatting(CategoryLogic::all_products($id), true), 200);
    }
}
