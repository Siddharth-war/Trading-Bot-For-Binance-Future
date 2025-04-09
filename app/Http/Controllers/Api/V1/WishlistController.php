<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\CentralLogics\ProductLogic;
use App\Http\Controllers\Controller;
use App\Model\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    public function __construct(
        private Wishlist $wishlist,
    )
    {}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addToWishlist(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $wishlist = $this->wishlist->where('user_id', $request->user()->id)->where('product_id', $request->product_id)->first();

        if (empty($wishlist)) {
            $wishlist = $this->wishlist;
            $wishlist->user_id = $request->user()->id;
            $wishlist->product_id = $request->product_id;
            $wishlist->save();

            return response()->json(['message' => translate('successfully_added')], 200);
        }

        return response()->json(['message' => translate('already_added')], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeFromWishlist(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:all,single',
            'product_id' => 'required_if:type,single'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $userId = $request->user()->id;
        $type = $request->type;

        if ($type == 'all') {
            $this->wishlist->where('user_id', $userId)->delete();
        } else {
            $wishlist = $this->wishlist->where('user_id', $userId)->where('product_id', $request->product_id)->first();
            if (!$wishlist) {
                return response()->json(['message' => translate('no_data_found')], 404);
            }
            $wishlist->delete();
        }
        return response()->json(['message' => translate('successfully_removed')]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function wishlist(Request $request): JsonResponse
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $offset = $request->has('offset') ? $request->offset : 1;

        $products = ProductLogic::get_wishlished_products($limit, $offset, $request);
        $products['products'] = Helpers::product_data_formatting($products['products'], true);

        return response()->json($products, 200);
    }
}
