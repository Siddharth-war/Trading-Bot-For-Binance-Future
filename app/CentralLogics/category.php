<?php

namespace App\CentralLogics;

use App\Model\Category;
use App\Model\Product;

class CategoryLogic
{
    public static function parents()
    {
        return Category::where('position', 0)->get();
    }

    public static function child($parent_id)
    {
        return Category::where(['parent_id' => $parent_id])->get();
    }

    public static function products($category_id, $type, $name, $limit, $offset)
    {
        $limit = is_null($limit) ? null : $limit;
        $offset = is_null($offset) ? 1 : $offset;
        $key = explode(' ', $name);
        $productType = ($type == 'veg') ? 'veg' : ($type == 'non_veg' ? 'non_veg' : 'all');

        $productsQuery = Product::active()
            ->with(['branch_product', 'rating'])
            ->whereHas('branch_product.branch', function ($query) {
                $query->where('status', 1);
            })
            ->branchProductAvailability()
            ->when($productType != 'all', function ($query) use ($productType) {
                return $query->where('product_type', $productType);
            })
            ->when(isset($name), function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->where('name', 'like', "%{$value}%");
                }
                $q->orWhereHas('tags',function($query) use ($key){
                    $query->where(function($q) use ($key){
                        foreach ($key as $value) {
                            $q->where('tag', 'like', "%{$value}%");
                        };
                    });
                });
            })
            ->when($category_id, function ($q) use ($category_id) {
                // Direct JSON query to filter by category_id within category_ids JSON array
                $q->whereJsonContains('category_ids', [['id' => $category_id]]);
            })
            ->latest();

        if (is_null($limit)) {
            // Fetch all products if limit is null
            $categoryProducts = $productsQuery->get();
            $totalSize = $categoryProducts->count();
        } else {
            // Apply pagination if limit is set
            $categoryProducts = $productsQuery->paginate($limit, ['*'], 'page', $offset);
            $totalSize = $categoryProducts->total();
        }

        return [
            'total_size' => $totalSize,
            'limit' => $limit,
            'offset' => $offset,
            'products' => is_null($limit) ? $categoryProducts : $categoryProducts->items(),
        ];
    }

    public static function all_products($id)
    {
        $cate_ids=[];
        array_push($cate_ids,(int)$id);
        foreach (CategoryLogic::child($id) as $ch1){
            array_push($cate_ids,$ch1['id']);
            foreach (CategoryLogic::child($ch1['id']) as $ch2){
                array_push($cate_ids,$ch2['id']);
            }
        }

        $products = Product::active()->branchProductAvailability()->get();
        $productIds = [];
        foreach ($products as $product) {
            foreach (json_decode($product['category_ids'], true) as $category) {
                if (in_array($category['id'],$cate_ids)) {
                    array_push($productIds, $product['id']);
                }
            }
        }

        return Product::with(['rating','branch_product'])->whereIn('id', $productIds)->get();
    }
}
