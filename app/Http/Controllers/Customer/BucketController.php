<?php

namespace App\Http\Controllers\Customer;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Model\Category;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\CustomBucket;
use App\Models\Group as ManageGroup;

use App\Models\CustomBucketProduct;
use App\Model\Product;
use App\Http\Requests\CustomBucketRequest;
use App\Model\ProductByBranch;
use Illuminate\Http\JsonResponse;
use Auth;

class BucketController extends Controller
{
    public $customBucketModel;
    public $customBucketProductModel;
    public $product;

    public function __construct(CustomBucket $customBucket,CustomBucketProduct $customBucketProduct,Product $product,  private ProductByBranch $productByBranch){
        $this->customBucketModel = $customBucket;
        $this->customBucketProductModel = $customBucketProduct;
        $this->product = $product;
    }


    public function index() {
        $userId = Auth::guard('customer')->user()->id;

        $customBuckets =$this->customBucketModel->with('products','custom_products')->where("user_id",$userId)->latest()->paginate(5);
        return view('customer-views.custom-bucket.index', compact('customBuckets'));
    }

    public function create() {
        $products = $this->product->get();
        return view('customer-views.custom-bucket.create',["products"=>$products]);
    }
// CustomBucketRequest
    public function store(Request $request) {
        $cateId = [];
        $data = [
            "name"=>$request->name,
            "user_id"=>Auth::guard('customer')->user()->id
        ];

       $bucket =  $this->customBucketModel->updateOrCreate(["id"=>$request->id],$data);

        if(!empty($request->product)){
            foreach($request->product as $val){
                $category = $this->customBucketProductModel->updateOrCreate(
                    ["custom_bucket_id"=>$bucket->id,"product_id"=>$val['product_id']],
                    [
                        "custom_bucket_id"=>$bucket->id,"product_id"=>$val['product_id'],"qty"=>$val['qty']
                    ]
                );
                $cateId[] = $category->id;
            }

            $this->customBucketProductModel->where("custom_bucket_id",$bucket->id)->whereNotIn("id",$cateId)->delete();
        }else{
            $this->customBucketProductModel->where("custom_bucket_id",$bucket->id)->delete();
        }

        if(empty($request->id)){
            Toastr::success("Custom bucket has been created successfully.");
        }else{
       Toastr::success("Custom bucket has been updated successfully.");

        }

        return redirect()->route('customer.custom-bucket.bucket_list');
    }

    public function edit($id) {
        $products = $this->product->get();
        $bucket = $this->customBucketModel->find($id);
        $selected_products =  $this->customBucketProductModel->where("custom_bucket_id",$bucket->id)->get();
        return view('customer-views.custom-bucket.edit', compact('bucket','products','selected_products'));
    }


    public function destroy($id) {
        $customBucket = $this->customBucketModel->find($id);
        $customBucket->custom_products()->delete();
        $customBucket->delete();
       Toastr::success("Custom Bucket has been deleted successfully.");

        return back();
    }

//   public function addToCart(Request $request): JsonResponse
// {

//     session()->forget('cart');
//     if (!$request->hasSession()) {
//         $request->setLaravelSession(app('session.store'));
//     }

//     $cart = collect($request->session()->get('cart', []));
//     $data = array();
//     foreach ($request->products as $productData) {

//         $product = $this->product->find($productData['id']);
//         $data['id'] = $product->id;
//         $str = '';
//         $variations = [];
//         $price = 0;
//         $addon_price = 0;
//         $addon_total_tax = 0;
//         $variation_price = 0;
//         $branch_product = $this->productByBranch->where(['product_id' => $product->id, 'branch_id' => Auth::guard('customer')->user()->branch_option])->first();


//         $branch_product_price = 0;
//         $discount_data = [];
//         if (isset($branch_product)) {
//             $branch_product_variations = $branch_product->variations;

//             if ($request->variations && count($branch_product_variations)) {
//                 foreach ($request->variations as $key => $value) {

//                     if ($value['required'] == 'on' && !isset($value['values'])) {
//                         return response()->json([
//                             'data' => 'variation_error',
//                             'message' => translate('Please select items from') . ' ' . $value['name'],
//                         ]);
//                     }
//                     if (isset($value['values']) && $value['min'] != 0 && $value['min'] > count($value['values']['label'])) {
//                         return response()->json([
//                             'data' => 'variation_error',
//                             'message' => translate('Please select minimum ') . $value['min'] . translate(' For ') . $value['name'] . '.',
//                         ]);
//                     }
//                     if (isset($value['values']) && $value['max'] != 0 && $value['max'] < count($value['values']['label'])) {
//                         return response()->json([
//                             'data' => 'variation_error',
//                             'message' => translate('Please select maximum ') . $value['max'] . translate(' For ') . $value['name'] . '.',
//                         ]);
//                     }
//                 }
//                 $variation_data = Helpers::get_varient($branch_product_variations, $request->variations);
//                 $variation_price = $variation_data['price'];
//                 $variations = $request->variations;

//             }


//             $branch_product_price = $branch_product['price'];

//             $discount_data = [
//                 'discount_type' => $branch_product['discount_type'],
//                 'discount' => $branch_product['discount']
//             ];
//         }

//         $price = $branch_product_price + $variation_price;
//         $data['variation_price'] = $variation_price;

//         $discount_on_product = Helpers::discount_calculate($discount_data, $price);

//         $data['variations'] = $variations;
//         $data['variant'] = $str;

//         $data['quantity'] = $productData['addon-quantity'] ?? 1;
//         $data['price'] = $price;
//         $data['name'] = $product->name;
//         $data['discount'] = $discount_on_product;
//         $data['image'] = $product->image;
//         $data['add_ons'] = [];
//         $data['add_on_qtys'] = [];
//         $data['add_on_prices'] = [];
//         $data['add_on_tax'] = [];

//         if ($request['addon_id']) {
//             foreach ($request['addon_id'] as $id) {
//                 $addon_price += $request['addon-price' . $id] * $request['addon-quantity' . $id];
//                 $data['add_on_qtys'][] = $request['addon-quantity' . $id];

//                 $add_on = AddOn::find($id);
//                 $data['add_on_prices'][] = $add_on['price'];
//                 $add_on_tax = ($add_on['price'] * $add_on['tax']/100);
//                 $addon_total_tax += (($add_on['price'] * $add_on['tax']/100) * $request['addon-quantity' . $id]);
//                 $data['add_on_tax'][] = $add_on_tax;
//             }
//             $data['add_ons'] = $request['addon_id'];
//         }

//         $data['addon_price'] = $addon_price;
//         $data['addon_total_tax'] = $addon_total_tax;

//         if ($request->session()->has('cart')) {
//             $cart = $request->session()->get('cart', collect([]));
//             $cart->push($data);
//         } else {
//             $cart = collect([$data]);
//             $request->session()->put('cart', $cart);
//         }
//     }


//     return response()->json(['data' => $cart]);
// }

public function addToCart(Request $request): JsonResponse
{

    session()->forget('cart');
    if (!$request->hasSession()) {
        $request->setLaravelSession(app('session.store'));
    }

    $cart = collect($request->session()->get('cart', []));
    $data = array();
    foreach ($request->products as $productData) {

        $product = $this->product->find($productData['id']);
	$data = json_decode($product->category_ids, true);
        $ids = array_column($data, 'id');
        $user = Auth::guard('customer')->user();

        $group = ManageGroup::with('price')->find($user->group_id);

        $prices = $group?->price->whereIn("category_id",$ids)->first();
        $priced = $prices ? $prices->price ? $prices->price : 0 :0;

        $data['id'] = $product->id;
        $str = '';
        $variations = [];
        $price = 0;
        $addon_price = 0;
        $addon_total_tax = 0;
        $variation_price = 0;
        $branch_product = $this->productByBranch->where(['product_id' => $product->id, 'branch_id' => Auth::guard('customer')->user()->branch_option])->first();


        $branch_product_price = 0;
        $discount_data = [];
        if (isset($branch_product)) {
            $branch_product_variations = $branch_product->variations;

            if ($request->variations && count($branch_product_variations)) {
                foreach ($request->variations as $key => $value) {

                    if ($value['required'] == 'on' && !isset($value['values'])) {
                        return response()->json([
                            'data' => 'variation_error',
                            'message' => translate('Please select items from') . ' ' . $value['name'],
                        ]);
                    }
                    if (isset($value['values']) && $value['min'] != 0 && $value['min'] > count($value['values']['label'])) {
                        return response()->json([
                            'data' => 'variation_error',
                            'message' => translate('Please select minimum ') . $value['min'] . translate(' For ') . $value['name'] . '.',
                        ]);
                    }
                    if (isset($value['values']) && $value['max'] != 0 && $value['max'] < count($value['values']['label'])) {
                        return response()->json([
                            'data' => 'variation_error',
                            'message' => translate('Please select maximum ') . $value['max'] . translate(' For ') . $value['name'] . '.',
                        ]);
                    }
                }
                $variation_data = Helpers::get_varient($branch_product_variations, $request->variations);
                $variation_price = $variation_data['price'];
                $variations = $request->variations;

            }


            $branch_product_price = $branch_product['price'];

            $discount_data = [
                'discount_type' => $branch_product['discount_type'],
                'discount' => $branch_product['discount']
            ];
        }

        $price = $branch_product_price + $variation_price;
        $price = $price + $priced;

        $data['variation_price'] = $variation_price;

        $discount_on_product = Helpers::discount_calculate($discount_data, $price);

        $data['variations'] = $variations;
        $data['variant'] = $str;

        $data['quantity'] = $productData['addon-quantity'] ?? 1;
        $data['price'] = $price;
        $data['name'] = $product->name;
        $data['discount'] = $discount_on_product;
        $data['image'] = $product->image;
        $data['add_ons'] = [];
        $data['add_on_qtys'] = [];
        $data['add_on_prices'] = [];
        $data['add_on_tax'] = [];

        if ($request['addon_id']) {
            foreach ($request['addon_id'] as $id) {
                $addon_price += $request['addon-price' . $id] * $request['addon-quantity' . $id];
                $data['add_on_qtys'][] = $request['addon-quantity' . $id];

                $add_on = AddOn::find($id);
                $data['add_on_prices'][] = $add_on['price'];
                $add_on_tax = ($add_on['price'] * $add_on['tax']/100);
                $addon_total_tax += (($add_on['price'] * $add_on['tax']/100) * $request['addon-quantity' . $id]);
                $data['add_on_tax'][] = $add_on_tax;
            }
            $data['add_ons'] = $request['addon_id'];
        }

        $data['addon_price'] = $addon_price;
        $data['addon_total_tax'] = $addon_total_tax;

        if ($request->session()->has('cart')) {
            $cart = $request->session()->get('cart', collect([]));
            $cart->push($data);
        } else {
            $cart = collect([$data]);
            $request->session()->put('cart', $cart);
        }
    }


    return response()->json(['data' => $cart]);
}




    public function placeOrder($id)
    {
        $customBucket = $this->customBucketModel->with('products','custom_products')->find($id);
        $quantity = [];
        if(!empty($customBucket->custom_products)){
            foreach($customBucket->custom_products as $qty){
                $quantity[$qty->product_id] = $qty->qty;
            }
        }


        if (!$customBucket || $customBucket->products->isEmpty()) {
            return response()->json(['message' => 'No products found in this bucket'], 400);
        }

        // Format products for `addToCart()`
        $formattedProducts = $customBucket->products->map(function ($product) use($quantity){
            return [
                'id' => $product->id,
                'quantity' => 1, // Default quantity (can be adjusted)
                'variations' => [], // Add variations if applicable
                'addon_id' => [], // Add-ons if applicable
                'addon-quantity' => $quantity[$product->id],
            ];
        })->toArray();

        // Create a new request object with the formatted products
        $request = new Request();
        $request->merge(['products' => $formattedProducts]);
        $this->addToCart($request);
        // Call addToCart with the formatted request
        return redirect()->route("customer.pos.index");
    }


}
