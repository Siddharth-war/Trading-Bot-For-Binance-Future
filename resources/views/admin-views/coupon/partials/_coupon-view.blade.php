<div class="coupon__details-left">
    <div class="text-center">
        @if($coupon->discount_type != "amount")
            <h6 class="title" id="title">{{$coupon->discount}}% {{translate('discount')}}</h6>
        @else
            <h6 class="title" id="title">{{Helpers::set_symbol($coupon->discount)}} {{translate('discount')}}</h6>
        @endif
        <h6 class="subtitle">{{translate('code')}} : <span id="coupon_code">{{$coupon->code}}</span></h6>
        <div class="text-capitalize">
            <span>{{translate('discount_in')}}</span>
            <strong id="discount_on">{{$coupon->discount_type}}</strong>
        </div>
    </div>
    <div class="coupon-info">
        <div class="coupon-info-item">
            <span>{{translate('min_purchase')}} :</span>
            <strong id="min_purchase">{{Helpers::set_symbol($coupon->min_purchase)}}</strong>
        </div>
        @if($coupon->discount_type != "amount")
        <div class="coupon-info-item" id="max_discount_modal_div">
            <span>{{translate('max_discount')}} : </span>
            <strong id="max_discount">{{Helpers::set_symbol($coupon->max_discount)}}</strong>
        </div>
        @endif
        <div class="coupon-info-item">
            <span>{{translate('start_date')}} : </span>
            <span id="start_date">{{date_format($coupon->start_date, 'Y-m-d')}}</span>
        </div>
        <div class="coupon-info-item">
            <span>{{translate('expire_date')}} : </span>
            <span id="expire_date">{{date_format($coupon->expire_date, 'Y-m-d')}}</span>
        </div>
    </div>
</div>
<div class="coupon__details-right">
    <div class="coupon">
        <div class="d-flex">
            @if($coupon->discount_type != "amount")
                <h2 class="" id="">{{$coupon->discount}}%</h2>
            @else
                <h2 class="" id="">{{Helpers::set_symbol($coupon->discount)}}</h2>
            @endif
        </div>

        <span>{{translate('off')}}</span>
    </div>
</div>
