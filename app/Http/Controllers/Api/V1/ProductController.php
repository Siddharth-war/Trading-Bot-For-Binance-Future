<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\CentralLogics\ProductLogic;
use App\Http\Controllers\Controller;
use App\Model\Category;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\Product;
use App\Model\ProductByBranch;
use App\Model\Review;
use App\Model\Tag;
use App\Model\Translation;
use App\Models\Cuisine;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function __construct(
        private Product     $product,
        private Translation $translation,
        private Review      $review,
        private Order      $order
    ){}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function latestProducts(Request $request): JsonResponse
    {
        Helpers::update_daily_product_stock();

        $products = ProductLogic::get_latest_products($request['limit'], $request['offset'], $request['product_type'], $request['name'], $request['category_ids'], $request['sort_by']);
        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return response()->json($products, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function popularProducts(Request $request): JsonResponse
    {
        $products = ProductLogic::get_popular_products(limit: $request['limit'], offset: $request['offset'], product_type: $request['product_type'], name: $request['name']);
        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return response()->json($products, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function searchedProducts(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $productType = $request['product_type'];
        $name = $request['name'];

        $products = ProductLogic::search_products(
            name: $request['name'],
            rating: $request['rating'],
            category_id: $request['category_id'],
            cuisine_id: $request['cuisine_id'],
            product_type: $productType,
            sort_by: $request['sort_by'],
            limit: $request['limit'],
            offset: $request['offset'],
            min_price: $request['min_price'],
            max_price: $request['max_price'],
        );

        if ($productType != 'veg' && $productType != 'non_veg') {
            $productType = 'all';
        }

        if (count($products['products']) == 0) {
            $key = explode(' ', $request['name']);

            $ids = $this->translation
                ->where(['key' => 'name'])
                ->where(function ($query) use ($key) {
                    foreach ($key as $value) {
                        $query->orWhere('value', 'like', "%{$value}%");
                    }
                })
                ->pluck('translationable_id')
                ->toArray();

            $ratingProductIds = [];
            if (isset($rating)){
                $ratingProductIds = Product::active()->with('reviews')
                    ->whereHas('reviews', function ($q) use ($request) {
                        $q->select('product_id')
                            ->groupBy('product_id')
                            ->havingRaw("AVG(rating) >= ?", [$request['rating']]);
                        })
                    ->pluck('id')
                    ->toArray();
            }

            $productIdsForCategory = [];
            if (isset($request['category_id'])){
                foreach (gettype($request['category_id']) != 'array' ? json_decode($request['category_id']) : $request['category_id'] as $categoryId) {
                    $productIds = Product::active()
                        ->where(function ($query) use ($categoryId) {
                            $query->whereJsonContains('category_ids', ['id' => (string)$categoryId]);
                        })
                        ->pluck('id')
                        ->toArray();
                    $productIdsForCategory = array_unique(array_merge($productIdsForCategory, $productIds));
                }
            }

            $paginator = $this->product->active()
                ->with(['rating', 'cuisines', 'branch_product'])
                ->whereIn('id', $ids)
                ->withCount(['wishlist'])
                ->whereHas('branch_product.branch', function ($query) {
                    $query->where('status', 1);
                })
                ->branchProductAvailability()
                ->when(isset($productType) && ($productType != 'all'), function ($query) use ($productType) {
                    return $query->productType(($productType == 'veg') ? 'veg' : 'non_veg');
                })
                ->when(($request['min_price'] != null && $request['max_price'] != null), function ($query) use ($request) {
                    return $query->where('price', '>=', $request['min_price'])
                        ->where('price', '<', $request['max_price']);
                })
                ->when(isset($request['category_id']), function ($query) use ($productIdsForCategory) {
                    $query->whereIn('id', $productIdsForCategory);
                })
                ->when(isset($request['rating']), function ($query) use ($ratingProductIds) {
                    $query->whereIn('id', $ratingProductIds);
                })
                ->when(isset($request['cuisine_id']), function ($query) use ($request) {
                    $cuisineIds = is_array($request['cuisine_id']) ? $request['cuisine_id'] : explode(',', $request['cuisine_id']);
                    $query->whereHas('cuisines', function ($q) use ($cuisineIds) {
                        $q->whereIn('cuisine_id', $cuisineIds);
                    });
                })
                ->when(isset($request['sort_by']) && $request['sort_by'] == 'new_arrival', function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->subMonth(3), Carbon::now()]);
                })
                ->when(isset($request['sort_by']) && $request['sort_by'] == 'popular', function ($query) {
                    return $query->orderBy('popularity_count', 'DESC');
                })
                ->when(isset($request['sort_by']) && $request['sort_by'] == 'price_high_to_low', function ($query) {
                    return $query->orderBy('price', 'DESC');
                })
                ->when(isset($request['sort_by']) && $request['sort_by'] == 'price_low_to_high', function ($query) {
                    return $query->orderBy('price', 'ASC');
                })
                ->when(isset($request['sort_by']) && $request['sort_by'] == 'a_to_z', function ($query) {
                    return $query->orderBy('name', 'ASC');
                })
                ->when(isset($request['sort_by']) && $request['sort_by'] == 'z_to_a', function ($query) {
                    return $query->orderBy('name', 'DESC');
                })
                ->when(is_null($request['sort_by']), function ($query) use ($name){
                    $query->orderByRaw("
                    CASE
                        WHEN name = '$name' THEN 0
                        WHEN name LIKE '$name%' THEN 1
                        WHEN name LIKE '%$name%' THEN 2
                        WHEN name LIKE '%$name' THEN 3
                        ELSE 4
                    END
                ");
                })
                ->latest()
                ->paginate($request['limit'], ['*'], 'page', $request['offset']);

            $productMaxPrice = Product::active()->max('price') ?? 0;

            $products = [
                'total_size' => $paginator->total(),
                'product_max_price' => $productMaxPrice,
                'limit' => $request['limit'],
                'offset' => $request['offset'],
                'products' => $paginator->items()
            ];
        }

        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return response()->json($products, 200);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function getProduct($id): JsonResponse
    {
        try {
            $product = ProductLogic::get_product($id);
            $product = Helpers::product_data_formatting($product, false);

            return response()->json($product, 200);

        } catch (\Exception $e) {
            return response()->json([
                'errors' => ['code' => 'product-001', 'message' => translate('no_data_found')]
            ], 404);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function relatedProducts($id): JsonResponse
    {
        if ($this->product->find($id)) {
            $products = ProductLogic::get_related_products($id);
            $products = Helpers::product_data_formatting($products, true);

            return response()->json($products, 200);
        }

        return response()->json([
            'errors' => ['code' => 'product-001', 'message' => translate('no_data_found')]
        ], 404);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function setMenus(Request $request): JsonResponse
    {
        $setMenuProducts = $this->product->active()
            ->with(['rating', 'branch_product'])
            ->whereHas('branch_product.branch', function ($query) {
                $query->where('status', 1);
            })
            ->whereHas('branch_product', function ($q) {
                $q->where('is_available', 1);
            })
            ->where(['set_menu' => 1])
            ->latest()
            ->paginate($request['limit'], ['*'], 'page', $request['offset']);

        $products = [
            'total_size' => $setMenuProducts->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'products' => $setMenuProducts->items()
        ];

        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return response()->json($products, 200);

    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function productReviews($id): JsonResponse
    {
        $reviews = $this->review
            ->with(['customer'])
            ->where(['product_id' => $id])
            ->get();

        $storage = [];
        foreach ($reviews as $item) {
            $item['attachment'] = json_decode($item['attachment']);
            $storage[] = $item;
        }

        return response()->json($storage, 200);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function productRating($id): JsonResponse
    {
        try {
            $product = $this->product->find($id);
            $overallRating = ProductLogic::get_overall_rating($product->reviews);
            return response()->json(floatval($overallRating[0]), 200);

        } catch (\Exception $e) {
            return response()->json(['errors' => $e], 403);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function submitProductReview(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'order_id' => 'required',
            'comment' => 'required',
            'rating' => 'required|numeric|max:5',
        ]);

        $product = $this->product->find($request->product_id);
        if (isset($product) == false) {
            $validator->errors()->add('product_id', translate('no_data_found'));
        }

        $multipleReview = $this->review->where(['product_id' => $request->product_id, 'user_id' => $request->user()->id])->first();
        $review = $multipleReview ?? $this->review;

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $imageArray = [];
        if (!empty($request->file('attachment'))) {
            foreach ($request->file('attachment') as $image) {
                $imageName = Helpers::upload('review/', 'png', $image);
                $imageArray[] = $imageName;
            }
        }

        $review->user_id = $request->user()->id;
        $review->product_id = $request->product_id;
        $review->order_id = $request->order_id;
        $review->comment = $request->comment;
        $review->rating = $request->rating;
        $review->attachment = json_encode($imageArray);
        $review->save();

        return response()->json(['message' => translate('review_submit_success')], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function recommendedProducts(Request $request): JsonResponse
    {
        Helpers::update_daily_product_stock();

        $products = ProductLogic::get_recommended_products(limit: $request['limit'], offset: $request['offset'], name: $request['name']);
        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return response()->json($products, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function frequentlyBoughtProducts(Request $request): JsonResponse
    {
        $products = ProductLogic::get_frequently_bought_products($request['limit'], $request['offset']);
        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return response()->json($products, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function searchSuggestion(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $name = $request->name;
        if (empty($name)) {
            return response()->json([], 200);
        }

        $searchWords = explode(' ', $name);
        $products = Product::active()
            ->with(['branch_product'])
            ->whereHas('branch_product.branch', function ($query) {
                $query->where('status', 1);
            })
            ->branchProductAvailability();

        $bindings = [
            $name,
            "$name%",
            "%$name%",
            "%$name"
        ];

        $searchQuery = $products->orderByRaw("
        CASE
            WHEN name = ? THEN 0
            WHEN name LIKE ? THEN 1
            WHEN name LIKE ? THEN 2
            WHEN name LIKE ? THEN 3
            ELSE 4
        END
    ", $bindings);

        foreach ($searchWords as $word) {
            $searchQuery->where('name', 'LIKE', "%$word%");
        }

        // Add tags condition
        $searchQuery->orWhereHas('tags', function ($query) use ($searchWords) {
            $query->where(function ($q) use ($searchWords) {
                foreach ($searchWords as $word) {
                    $q->orWhere('tag', 'LIKE', "%{$word}%");
                }
            });
        });

        $productResult = $searchQuery->pluck('name');

        $categoryIds = Category::where('name', 'LIKE', "%$name%")->pluck('id');

        if ($categoryIds->isNotEmpty()) {
            $categoryProducts = Product::active()
                ->with(['branch_product'])
                ->whereHas('branch_product.branch', function ($query) {
                    $query->where('status', 1);
                })
                ->branchProductAvailability()
                ->where(function ($query) use ($categoryIds) {
                    foreach ($categoryIds as $id) {
                        $query->whereJsonContains('category_ids', ['id' => (string) $id]);
                    }
                })
                ->pluck('name');
        } else {
            // If no category IDs, return an empty collection
            $categoryProducts = collect();
        }

        $cuisineProducts = $this->product->with(['branch_product'])
            ->whereHas('branch_product.branch', function ($query) {
                $query->where('status', 1);
            })
            ->branchProductAvailability()
            ->whereHas('cuisines', function ($query) use ($name) {
                $query->where('name', 'LIKE', "%$name%");
            })
            ->pluck('name');

        $results = $productResult
            ->merge($categoryProducts)
            ->merge($cuisineProducts)
            ->unique();

        return response()->json($results->values());
    }


    public function changeBranchProductUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_branch_id' => 'required',
            'to_branch_id' => 'required',
            'products' => 'required',
            'product_ids' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $fromBranchId = $request['from_branch_id'];
        $toBranchId = $request['to_branch_id'];
        $fromBranchProducts = $request['products'];
        $productIds = $request['product_ids'];

        $newProducts = $this->product->active()
            ->with(['rating', 'b_product' => function ($query) use ($toBranchId, $productIds) {
                $query->where('branch_id', $toBranchId)
                    ->whereIn('product_id', $productIds);
            }])
            ->whereHas('b_product', function ($query) use ($toBranchId, $productIds) {
                $query->where('branch_id', $toBranchId)
                    ->whereIn('product_id', $productIds);
            })
            ->get();


        $formattedProducts = [];

        foreach ($fromBranchProducts as $fromBranchProduct){
            $newProductList = $newProducts->where('id', $fromBranchProduct['product_id'])->first();
            $newProductList['is_changed'] = 0;
            $newProductList['change_reason'] = '';

            $toBranchProduct = ProductByBranch::where(['product_id' => $fromBranchProduct['product_id'], 'branch_id' => $toBranchId])->first();
            if (!isset($toBranchProduct)){
                $newProductList['is_changed'] = 1;
                $newProductList['change_reason'] = 'unavailable';
            }

            if ($toBranchProduct->is_available == 0){
                $newProductList['is_changed'] = 1;
                $newProductList['change_reason'] = 'unavailable';
            }

            if($toBranchProduct->stock_type == 'daily' || $toBranchProduct->stock_type == 'fixed' ){
                $availableStock = $toBranchProduct->stock - $toBranchProduct->sold_quantity;
                if ($availableStock < $fromBranchProduct['quantity']){
                    $newProductList['is_changed'] = 1;
                    $newProductList['change_reason'] = 'stock';
                }
            }

            $fromProduct = ProductByBranch::where(['product_id' => $fromBranchProduct['product_id'], 'branch_id' => $fromBranchId])->first();
            if ($fromProduct->price != $toBranchProduct->price){
                $newProductList['is_changed'] = 1;
                $newProductList['change_reason'] = 'price';
            }

            if ($fromProduct->discount_type != $toBranchProduct->discount_type){
                $newProductList['is_changed'] = 1;
                $newProductList['change_reason'] = 'discount';
            }

            if ($fromProduct->discount != $toBranchProduct->discount){
                $newProductList['is_changed'] = 1;
                $newProductList['change_reason'] = 'discount';
            }

            if (count($fromBranchProduct['variations'])){
                foreach ($fromBranchProduct['variations'] as $k => $selectedVariation){
                    foreach($toBranchProduct as $toProductVariation) {
                        if(isset($selectedVariation['values']) && isset($toProductVariation['values']) && $selectedVariation['name'] == $toProductVariation['name']) {
                            foreach($toProductVariation['values'] as $key=> $option){
                                if(in_array($option['label'], $selectedVariation['values']['label'])){
                                    if ($option['optionPrice'] !=  $selectedVariation['values']['optionPrice'] ){
                                        $newProductList['is_changed'] = 1;
                                        $newProductList['change_reason'] = 'variation';
                                    }
                                }else{
                                    $newProductList['is_changed'] = 1;
                                    $newProductList['change_reason'] = 'variation';
                                }
                            }
                        }else{
                            $newProductList['is_changed'] = 1;
                            $newProductList['change_reason'] = 'variation';
                        }
                    }
                }
            }
            $formattedProducts[] = $newProductList;

        }

        return  Helpers::product_data_formatting($formattedProducts, true);

    }

    public function reOrderProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $order = $this->order->find($request['order_id']);
        if (!$order || !$order->details) {
            return response()->json(['error' => 'Order not found or has no details'], 404);
        }

        $productIds = $order->details->pluck('product_id')->toArray();

        $currentProducts = Product::active()
            ->with(['branch_product', 'rating'])
            ->whereIn('id', $productIds)
            ->whereHas('branch_product.branch', function ($query) {
                $query->where('status', 1);
            })
            ->branchProductAvailability()
            ->get();

        foreach ($order->details as $detail) {
            $productDetails = gettype($detail['product_details']) != 'array' ? (array) json_decode($detail['product_details'], true) : (array) $detail['product_details'];
            $productDetails = Helpers::product_formatter($productDetails);

            foreach ($currentProducts as $currentProduct) {
                $currentProduct->is_changed = 0;  // Reset the flag for each product

                if (
                    $currentProduct->branch_product->price != $productDetails['price'] ||
                    $currentProduct->branch_product->discount_type != $productDetails['discount_type'] ||
                    $currentProduct->branch_product->discount != $productDetails['discount']
                ) {
                    $currentProduct->is_changed = 1;
                }

                if (!empty($currentProduct->branch_product['variations'])) {
                    foreach ($currentProduct->branch_product['variations'] as $currentVariation) {
                        $variationChanged = true;

                        foreach ($productDetails['variations'] as $orderVariation) {
                            // Check if 'name' key exists in both variations
                            if (
                                isset($currentVariation['name']) &&
                                isset($orderVariation['name']) &&
                                $currentVariation['name'] == $orderVariation['name']
                            ) {
                                foreach ($orderVariation['values'] as $orderValue) {
                                    foreach ($currentVariation['values'] as $currentValue) {
                                        if (
                                            isset($orderValue['label']) &&
                                            isset($currentValue['label']) &&
                                            isset($orderValue['optionPrice']) &&
                                            isset($currentValue['optionPrice']) &&
                                            $orderValue['label'] == $currentValue['label'] &&
                                            $orderValue['optionPrice'] == $currentValue['optionPrice']
                                        ) {
                                            $variationChanged = false;
                                            break;
                                        }
                                    }
                                    if (!$variationChanged) break;
                                }
                            }
                            if (!$variationChanged) break;
                        }
                        if ($variationChanged) {
                            $currentProduct->is_changed = 1;
                            break;
                        }
                    }
                }
            }
        }

        $formattedProducts = [];
        foreach ($currentProducts as $product) {
            $formattedProducts[] = Helpers::product_data_formatting($product, false);
        }

        $response = [
            'requested_product_count' => count($productIds),
            'response_product_count' => count($formattedProducts),
            'order_branch' => $order ? $order->branch_id : null,
            'current_branch' => (integer)Config::get('branch_id'),
            'products' => $formattedProducts,
        ];

        return response()->json($response);
    }

    /**
     * @return array{categories: mixed, cuisines: mixed}
     */
    public function searchRecommendedData(Request $request): array
    {
        $branchId = Config::get('branch_id') ?? 1;
        $orderDetailsProductIds = OrderDetail::whereHas('order', function ($query) use ($branchId) {
            $query->where('branch_id', $branchId);
        })
            ->pluck('product_id')
            ->unique();

        $products = Product::with('cuisines')
            ->whereIn('id', $orderDetailsProductIds)
            ->orderBy('popularity_count', 'DESC')
            ->get();

        // Extract category IDs where position is 1(main category)
        $categoryIds = $products->flatMap(function ($product) {
            $categoryData = json_decode($product->category_ids, true);
            return collect($categoryData)->filter(function ($category) {
                return $category['position'] == 1;
            })->pluck('id');
        })->unique();

        $categoryList = Category::whereIn('id', $categoryIds)->select('id', 'name', 'image', 'banner_image')->get();

        $cuisineList = $products->pluck('cuisines')->flatten()
            ->filter(function ($cuisine) {
                return isset($cuisine['is_active']) && $cuisine['is_active'] == 1;
            })
            ->pluck('name')
            ->unique()
            ->values();

        // Ensure there are 8 categories by filling with random ones if needed
        if ($categoryList->count() < 8) {
            $additionalCategories = Category::whereNotIn('id', $categoryIds)
                ->inRandomOrder()
                ->limit(8 - $categoryList->count())
                ->select('id', 'name', 'image', 'banner_image')
                ->get();
            $categoryList = $categoryList->merge($additionalCategories);
        }

        // Ensure there are 8 cuisines by filling with random ones if needed
        if ($cuisineList->count() < 8) {
            $additionalCuisines = Cuisine::whereNotIn('name', $cuisineList)
                ->active()
                ->inRandomOrder()
                ->limit(8 - $cuisineList->count())
                ->pluck('name');
            $cuisineList = $cuisineList->merge($additionalCuisines);
        }

        // Limit both lists to exactly 8 items
        $categoryList = $categoryList->take(8);
        $cuisineList = $cuisineList->take(8);

        return [
            'categories' => $categoryList,
            'cuisines' => $cuisineList,
        ];

    }

}
