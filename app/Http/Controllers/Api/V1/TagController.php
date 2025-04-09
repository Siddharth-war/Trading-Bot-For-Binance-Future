<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Model\Tag;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function getPopularTags(): JsonResponse
    {
        $tags = Tag::select('tags.*', 'products.popularity_count')
            ->leftJoin('product_tag', 'tags.id', '=', 'product_tag.tag_id')
            ->leftJoin('products', 'product_tag.product_id', '=', 'products.id')
            ->where('products.popularity_count', '>', 0)
            ->orderBy('products.popularity_count', 'DESC')
            ->distinct()
            ->get();

        $tagNames = $tags->map(function($tag) {
            return $tag->tag;
        })->toArray();

        return response()->json($tagNames, 200);
    }
}
