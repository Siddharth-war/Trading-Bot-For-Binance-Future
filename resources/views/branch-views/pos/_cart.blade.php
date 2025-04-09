<div class="table-responsive pos-cart-table border">
    <table class="table table-align-middle mb-0">
        <thead class="text-dark bg-light">
        <tr>
            <th class="text-capitalize border-0 min-w-120">{{translate('item')}}</th>
            <th class="text-capitalize border-0">{{translate('qty')}}</th>
            <th class="text-capitalize border-0">{{translate('price')}}</th>
            <th class="text-capitalize border-0">{{translate('delete')}}</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $subtotal = 0;
        $addonPrice = 0;
        $discount = 0;
        $discountType = 'amount';
        $discountOnProduct = 0;
        $addonTotalTax =0;
        $totalTax = 0;
        ?>
        @if(session()->has('cart') && count( session()->get('cart')) > 0)
                <?php
                $cart = session()->get('cart');
                if(isset($cart['discount']))
                {
                    $discount = $cart['discount'];
                    $discountType = $cart['discount_type'];
                }
                ?>
            @foreach(session()->get('cart') as $key => $cartItem)
                @if(is_array($cartItem))
                        <?php
                        $productSubtotal = ($cartItem['price'])*$cartItem['quantity'];
                        $discountOnProduct += ($cartItem['discount']*$cartItem['quantity']);
                        $subtotal += $productSubtotal;
                        $addonPrice += $cartItem['addon_price'];
                        $addonTotalTax += $cartItem['addon_total_tax'];
                        $product = \App\Model\Product::find($cartItem['id']);
                        $totalTax +=Helpers::tax_calculate($product, $cartItem['price'])*$cartItem['quantity'];

                        ?>
                    <tr>
                        <td>
                            <div class="media align-items-center gap-10">
                                <img class="avatar avatar-sm" src="{{asset('storage/app/public/product')}}/{{$cartItem['image']}}"
                                     onerror="this.src='{{asset('public/assets/admin/img/160x160/img2.jpg')}}'" alt="{{$cartItem['name']}} image">
                                <div class="media-body">
                                    <h5 class="text-hover-primary mb-0">{{Str::limit($cartItem['name'], 10)}}</h5>
                                    <small>{{Str::limit($cartItem['variant'], 20)}}</small>
                                    <small class="d-block">
                                        @php($addOnQtys=$cartItem['add_on_qtys'])
                                        @foreach($cartItem['add_ons'] as $key2 =>$id)
                                            @php($addon=\App\Model\AddOn::find($id))
                                            @if($key2==0)<strong><u>Addons : </u></strong>@endif

                                            @if($addOnQtys==null)
                                                @php($addOnQty=1)
                                            @else
                                                @php($addOnQty=$addOnQtys[$key2])
                                            @endif

                                            <div class="font-size-sm text-body">
                                                <span>{{$addon['name']}} :  </span>
                                                <span class="font-weight-bold">
                                                    {{ $addOnQty}} x {{Helpers::set_symbol($addon['price']) }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <input type="number" class="form-control qty" data-key="{{$key}}" value="{{$cartItem['quantity']}}" min="1" onkeyup="updateQuantity(event)">
                        </td>
                        <td>
                            <div class="">
                                {{Helpers::set_symbol($productSubtotal) }}
                            </div>
                        </td>
                        <td class="justify-content-center gap-2">
                            <a href="javascript:removeFromCart({{$key}})" class="btn btn-sm btn-outline-danger square-btn form-control">
                                <i class="tio-delete"></i>
                            </a>
                        </td>
                    </tr>
                @endif
            @endforeach
        @endif
        </tbody>
    </table>
</div>

<?php
$total = $subtotal+$addonPrice;
$discountAmount = ($discountType=='percent' && $discount>0)?(($total * $discount)/100):$discount;
$discountAmount += $discountOnProduct;
$total -= $discountAmount;

$extraDiscount = session()->get('cart')['extra_discount'] ?? 0;
$extraDiscountType = session()->get('cart')['extra_discount_type'] ?? 'amount';
if($extraDiscountType == 'percent' && $extraDiscount > 0){
    $extraDiscount = ($subtotal * $extraDiscount) / 100;
}
if($extraDiscount) {
    $total -= $extraDiscount;
}

$deliveryCharge = 0;
if (session()->get('order_type') == 'home_delivery'){
    $distance = 0;
    $areaId = 1;
    if (session()->has('address')){
        $address = session()->get('address');
        $distance = $address['distance'];
        $areaId = $address['area_id'];
    }
    $deliveryCharge = Helpers::get_delivery_charge(branchId: auth('branch')->id() ?? 1, distance:  $distance, selectedDeliveryArea: $areaId);
}else{
    $deliveryCharge = 0;
}
?>
<div class="pos-data-table p-3">
    <dl class="row">
        <dt  class="col-6">{{translate('addon')}} : </dt>
        <dd class="col-6 text-right">{{Helpers::set_symbol($addonPrice) }}</dd>

        <dt  class="col-6">{{translate('subtotal')}} : </dt>
        <dd class="col-6 text-right">{{\App\CentralLogics\Helpers::set_symbol($subtotal+$addonPrice) }}</dd>

        <dt  class="col-6">{{translate('product')}} {{translate('discount')}} :</dt>
        <dd class="col-6 text-right">- {{Helpers::set_symbol(round($discountAmount,2)) }}</dd>

        <dt  class="col-6">{{translate('extra')}} {{translate('discount')}} :</dt>
        <dd class="col-6 text-right">
            <button class="btn btn-sm" type="button" data-toggle="modal" data-target="#add-discount">
                <i class="tio-edit"></i>
            </button>
            - {{Helpers::set_symbol($extraDiscount) }}
        </dd>

        <dt  class="col-6">{{translate('VAT/TAX:')}} : </dt>
        <dd class="col-6 text-right">{{Helpers::set_symbol(round($totalTax + $addonTotalTax,2)) }}</dd>

        <dt  class="col-6">{{translate('Delivery Charge')}} :</dt>
        <dd class="col-6 text-right"> {{Helpers::set_symbol(round($deliveryCharge,2)) }}</dd>

        <dt  class="col-6 border-top font-weight-bold pt-2">{{translate('total')}} : </dt>
        <dd class="col-6 text-right border-top font-weight-bold pt-2">{{Helpers::set_symbol(round($total+$totalTax+$addonTotalTax+$deliveryCharge, 2)) }}</dd>
    </dl>

    <form action="{{route('branch.pos.order')}}" id='order_place' method="post">
        @csrf

        <div class="pt-4 mb-4">
            <div class="text-dark d-flex mb-2">{{translate('Paid_By')}} :</div>
            <ul class="list-unstyled option-buttons">
                <li id="cash_payment_li" style="display: {{ session('order_type') != 'home_delivery' ?  'block' : 'none' }}">
                    <input type="radio" id="cash" value="cash" name="type" hidden="" {{ session('order_type') != 'home_delivery' ?  'checked' : '' }}>
                    <label for="cash" class="btn btn-bordered px-4 mb-0">{{translate('Cash')}}</label>
                </li>
                <li id="card_payment_li" style="display: {{ session('order_type') != 'home_delivery' ?  'block' : 'none' }}">
                    <input type="radio" value="card" id="card" name="type" hidden="">
                    <label for="card" class="btn btn-bordered px-4 mb-0">{{translate('Card')}}</label>
                </li>
                <li id="pay_after_eating_li" style="display: {{ session('order_type') == 'dine_in' ?  'block' : 'none' }}">
                    <input type="radio" value="pay_after_eating" id="pay_after_eating" name="type" hidden="">
                    <label for="pay_after_eating" class="btn btn-bordered px-4 mb-0">{{translate('pay_after_eating')}}</label>
                </li>
                <li id="cash_on_delivery_li" style="display: {{ session('order_type') == 'home_delivery' ?  'block' : 'none' }}">
                    <input type="radio" value="cash_on_delivery" id="cash_on_delivery" name="type" hidden="" {{ session('order_type') == 'home_delivery' ?  'checked' : '' }}>
                    <label for="cash_on_delivery" class="btn btn-bordered px-4 mb-0">{{translate('cash_on_delivery')}}</label>
                </li>
            </ul>
        </div>

        <div class="row mt-4 gy-2">
            <div class="col-md-6">
                <a href="#" class="btn btn-outline-danger btn--danger btn-block empty-cart-button">
                    <i class="fa fa-times-circle"></i> {{translate('Cancel_Order')}}
                </a>
            </div>
            <div class="col-md-6">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fa fa-shopping-bag"></i>
                    {{translate('Place_Order')}}
                </button>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="add-discount" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{translate('update_discount')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('branch.pos.discount')}}" method="post" class="row mb-0">
                    @csrf
                    <div class="form-group col-sm-6">
                        <label class="text-dark">{{translate('discount')}}</label>
                        <input type="number" class="form-control" name="discount" value="{{ session()->get('cart')['extra_discount'] ?? 0 }}" min="0" step="0.1">
                    </div>
                    <div class="form-group col-sm-6">
                        <label class="text-dark">{{translate('type')}}</label>
                        <select name="type" class="form-control">
                            <option
                                value="amount" {{$extraDiscountType=='amount'?'selected':''}}>{{translate('amount')}}
                                ({{\App\CentralLogics\Helpers::currency_symbol()}})
                            </option>
                            <option
                                value="percent" {{$extraDiscountType=='percent'?'selected':''}}>{{translate('percent')}}
                                (%)
                            </option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-end col-sm-12">
                        <button class="btn btn-sm btn-primary" type="submit">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add-tax" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{translate('update_tax')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('branch.pos.tax')}}" method="POST" class="row">
                    @csrf
                    <div class="form-group col-12">
                        <label for="">{{translate('tax')}} (%)</label>
                        <input type="number" class="form-control" name="tax" min="0">
                    </div>

                    <div class="form-group col-sm-12">
                        <button class="btn btn-sm btn-primary" type="submit">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    "use strict";

    $('.empty-cart-button').click(function(event) {
        event.preventDefault();
        emptyCart();
    });
</script>


