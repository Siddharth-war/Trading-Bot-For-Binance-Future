<div class="print-area-content"  id="printableAreaContent">
    <div class="text-center pt-4 mb-3 w-100">
        <h2 class="custom-title">{{\App\Model\BusinessSetting::where(['key'=>'restaurant_name'])->first()->value}}</h2>
        <h5 class="custom-h5">
            {{\App\Model\BusinessSetting::where(['key'=>'address'])->first()->value}}
        </h5>
        <h5 class="custom-phone">
            {{translate('Phone')}}
            : {{\App\Model\BusinessSetting::where(['key'=>'phone'])->first()->value}}
        </h5>
    </div>

    <span>--------------------------------------------</span>
    <div class="row mt-3">
        <div class="col-6">
            <h5>{{translate('Order ID')}} : {{$order['id']}}</h5>
        </div>
        <div class="col-6">
            <h5 style="font-weight: lighter">
                {{date('d/M/Y h:i a',strtotime($order['created_at']))}}
            </h5>
        </div>
        @if($order->customer)
            <div class="col-12">
                <h5>{{translate('Customer Name')}} : {{$order->customer['f_name'].' '.$order->customer['l_name']}}</h5>
                <h5>{{translate('Phone')}} : {{$order->customer['phone']}}</h5>
            </div>
        @endif
    </div>
    <h5 class="text-uppercase"></h5>
    <span>--------------------------------------------</span>
    <table class="table table-bordered mt-3 custom-table">
        <thead>
        <tr>
            <th class="custom-qty">{{translate('QTY')}}</th>
            <th class="">{{translate('DESC')}}</th>
            <th class="custom-price">{{translate('Price')}}</th>
        </tr>
        </thead>

        <tbody>
        @php($subTotal=0)
        @php($totalTax=0)
        @php($totalDisOnPro=0)
        @php($addOnsCost=0)
        @php($addOnTax=0)
        @php($addOnsTaxCost=0)
        @foreach($order->details as $detail)
            @if($detail->product)
                @php($addOnQtys=json_decode($detail['add_on_qtys'],true))
                @php($addOnPrices=json_decode($detail['add_on_prices'],true))
                @php($addOnTaxes=json_decode($detail['add_on_taxes'],true))
                <tr>
                    <td class="">
                        {{$detail['quantity']}}
                    </td>
                    <td class="">
                        <span  class="custom-span"> {{ Str::limit($detail->product['name'], 200) }}</span><br>
                        @if (count(json_decode($detail['variation'], true)) > 0)
                            <strong><u>{{ translate('variation') }} : </u></strong>
                            @foreach(json_decode($detail['variation'],true) as  $variation)
                                @if ( isset($variation['name'])  && isset($variation['values']))
                                    <span class="d-block text-capitalize">
                                                        <strong>{{  $variation['name']}} - </strong>
                                                </span>
                                    @foreach ($variation['values'] as $value)
                                        <span class="d-block text-capitalize">
                                            {{ $value['label']}} :
                                                    <strong>{{Helpers::set_symbol( $value['optionPrice'])}}</strong>
                                                    </span>
                                    @endforeach
                                @else
                                    @if (isset(json_decode($detail['variation'],true)[0]))
                                        @foreach(json_decode($detail['variation'],true)[0] as $key1 =>$variation)
                                            <div class="font-size-sm text-body">
                                                <span>{{$key1}} :  </span>
                                                <span class="font-weight-bold">{{$variation}}</span>
                                            </div>
                                        @endforeach
                                    @endif
                                    @break
                                @endif
                            @endforeach
                        @else
                            <div class="font-size-sm text-body">
                                <span>{{ translate('Price') }} : </span>
                                <span
                                    class="font-weight-bold">{{ Helpers::set_symbol($detail->price) }}</span>
                            </div>
                        @endif

                        @foreach(json_decode($detail['add_on_ids'],true) as $key2 =>$id)
                            @php($addon=\App\Model\AddOn::find($id))
                            @if($key2==0)<strong><u>{{translate('Addons')}} : </u></strong>@endif

                            @if($addOnQtys==null)
                                @php($addOnQty=1)
                            @else
                                @php($addOnQty=$addOnQtys[$key2])
                            @endif

                            <div class="font-size-sm text-body">
                                <span>{{$addon ? $addon['name'] : translate('addon deleted')}} :  </span>
                                <span class="font-weight-bold">
                                    {{$addOnQty}} x {{ Helpers::set_symbol($addOnPrices[$key2])}}
                                </span>
                            </div>
                            @php($addOnsCost+=$addOnPrices[$key2] * $addOnQty)
                            @php($addOnsTaxCost +=  $addOnTaxes[$key2] * $addOnQty)
                        @endforeach

                        {{translate('Discount')}} : {{ Helpers::set_symbol($detail['discount_on_product']*$detail['quantity']) }}
                    </td>
                    <td class="custom-td">
                        @php($amount=($detail['price']-$detail['discount_on_product'])*$detail['quantity'])
                        {{ Helpers::set_symbol($amount) }}
                    </td>
                </tr>
                @php($subTotal+=$amount)
                @php($totalTax+=$detail['tax_amount']*$detail['quantity'])
            @endif
        @endforeach
        </tbody>
    </table>
    <span>--------------------------------------------</span>
    <div class="row justify-content-end">
        <div class="col-md-8 col-lg-8">
            <dl class="row text-right custom-dl">
                <dt class="col-8">{{translate('Items Price')}}:</dt>
                <dd class="col-4">{{ Helpers::set_symbol($subTotal) }}</dd>
                <dt class="col-8">{{translate('Tax')}} / {{translate('VAT')}}:</dt>
                <dd class="col-4">{{ Helpers::set_symbol($totalTax + $addOnsTaxCost) }}</dd>
                <dt class="col-8">{{translate('Addon Cost')}}:</dt>
                <dd class="col-4">{{ Helpers::set_symbol($addOnsCost) }}
                    <hr>
                </dd>

                <dt class="col-8">{{translate('Subtotal')}}:</dt>
                <dd class="col-4">{{ Helpers::set_symbol($subTotal+$totalTax+$addOnsCost+$addOnsTaxCost) }}</dd>
                <dt class="col-8">{{translate('Coupon Discount')}}:</dt>
                <dd class="col-4">
                    -{{ Helpers::set_symbol($order['coupon_discount_amount']) }}</dd>
                <dt class="col-8">{{translate('Extra Discount')}}:</dt>
                <dd class="col-4">
                    -{{ Helpers::set_symbol($order['extra_discount']) }}</dd>
                <dt class="col-8">{{translate('Delivery Fee')}}:</dt>
                <dd class="col-4">
                    @if($order['order_type']=='take_away')
                        @php($deliveryCharge=0)
                    @else
                        @php($deliveryCharge=$order['delivery_charge'])
                    @endif
                    {{ Helpers::set_symbol($deliveryCharge) }}
                    <hr>
                </dd>
                <dt class="col-6 custom-text-size">{{translate('Total')}}:</dt>
                <dd class="col-6 custom-text-size">{{ Helpers::set_symbol($order->order_amount) }}</dd>

                @if ($order->order_partial_payments->isNotEmpty())
                    @foreach($order->order_partial_payments as $partial)
                        <dt class="col-6">
                            <div>
                                            <span>
                                                {{translate('Paid By')}} ({{str_replace('_', ' ',$partial->paid_with)}})</span>
                                <span>:</span>
                            </div>
                        </dt>
                        <dd class="col-6 text-dark text-right">
                            {{ Helpers::set_symbol($partial->paid_amount) }}
                        </dd>
                    @endforeach
                        <?php
                        $dueAmount = 0;
                        $dueAmount = $order->order_partial_payments->first()?->due_amount;
                        ?>
                    <dt class="col-6">
                        <div class="">
                                            <span>
                                                {{translate('Due Amount')}}</span>
                            <span>:</span>
                        </div>
                    </dt>
                    <dd class="col-6 text-dark text-right">
                        {{ Helpers::set_symbol($dueAmount) }}
                    </dd>
                @endif
            </dl>
        </div>
    </div>
    <div class="d-flex flex-row justify-content-between border-top">
        <span>{{translate('Paid_by')}}: {{translate($order->payment_method)}}</span>
    </div>
    <span>--------------------------------------------</span>
    <h5 class="text-center pt-3">
        """{{translate('THANK YOU')}}"""
    </h5>
    <span>--------------------------------------------</span>
</div>
