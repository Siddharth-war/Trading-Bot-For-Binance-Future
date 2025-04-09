<div class="pos-product-item card quick-view-trigger" data-product-id="{{$product->id}}">
    <div class="pos-product-item_thumb">
        <img class="img-fit" src="{{$product['imageFullPath']}}" alt="{{ translate('product') }}">
    </div>
    <?php
        $pb = json_decode($product->product_by_branch, true);
        $price = 0;
        $discountData = [];
        if(isset($pb[0])){
            $price = $pb[0]['price'];
            $discountData =[
                'discount_type' => $pb[0]['discount_type'],
                'discount' => $pb[0]['discount']
            ];
        }
    ?>

    <div class="pos-product-item_content clickable">
        <div class="pos-product-item_title">{{ Str::limit($product['name'], 15) }}</div>

        <div class="pos-product-item_price">
            {{ Helpers::set_symbol(($price - Helpers::discount_calculate($discountData, $price))) }}
        </div>
    </div>
</div>
