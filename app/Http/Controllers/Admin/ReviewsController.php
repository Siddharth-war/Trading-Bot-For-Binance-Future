<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Product;
use App\Model\Review;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;

class ReviewsController extends Controller
{
    public function __construct(
        private Review  $review,
        private Product $product,
    )
    {}

    /**
     * @return Renderable
     */
    public function list(Request $request): Renderable
    {
        if ($request->has('search')){
            $key = explode(' ', $request['search']);
            $products = $this->product
                ->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                })
                ->pluck('id')
                ->toArray();

            $reviews = $this->review->whereIn('product_id', $products)
                ->with(['product', 'customer'])
                ->latest()
                ->paginate(Helpers::getPagination());
        }else{
            $reviews = $this->review->with(['product', 'customer'])->latest()->paginate(Helpers::getPagination());

        }
        return view('admin-views.reviews.list', compact('reviews'));
    }
}
