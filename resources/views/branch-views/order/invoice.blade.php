@extends('layouts.branch.app')

@section('title','')

@push('css_or_js')
    <style>
        @media print {
            .non-printable {
                display: none;
            }

            .printable {
                display: block;
            }
        }

        .hr-style-2 {
            border: 0;
            height: 1px;
            background-image: linear-gradient(to right, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0));
        }

        .hr-style-1 {
            overflow: visible;
            padding: 0;
            border: none;
            border-top: medium double #000000;
            text-align: center;
        }
    </style>

    <style type="text/css" media="print">
        @page {
            size: auto;   /* auto is the initial value */
            margin: 2px;
        }

    </style>
@endpush

@section('content')

    <div class="content container-fluid">
        <div class="row" id="printableArea">
            <div class="col-md-12">
                <div class="text-center">
                    <input type="button" class="btn btn-primary non-printable" onclick="printDiv('printableArea')"
                           value="{{translate('Proceed, If thermal printer is ready.')}}"/>
                    <a href="{{url()->previous()}}" class="btn btn-danger non-printable">{{translate('Back')}}</a>
                </div>
                <hr class="non-printable">
            </div>
            <div class="col-5" id="printableAreaContent">
                <div class="text-center pt-4 mb-3">
                    <h2 style="line-height: 1">{{\App\Model\BusinessSetting::where(['key'=>'restaurant_name'])->first()->value}}</h2>
                    <h5 style="font-size: 20px;font-weight: lighter;line-height: 1">
                        {{\App\Model\BusinessSetting::where(['key'=>'address'])->first()->value}}
                    </h5>
                    <h5 style="font-size: 16px;font-weight: lighter;line-height: 1">
                        {{translate('Phone')}} : {{\App\Model\BusinessSetting::where(['key'=>'phone'])->first()->value}}
                    </h5>
                </div>

                <hr class="text-dark hr-style-1">
                <div class="row mt-4">
                    <div class="col-6">
                        <h5>{{translate('Order ID')}} : {{$order['id']}}</h5>
                    </div>
                    <div class="col-6">
                        <h5 style="font-weight: lighter">
                            {{date('d/M/Y h:m a',strtotime($order['created_at']))}}
                        </h5>
                    </div>
                    <div class="col-12">
                        @if($order->is_guest == 0)
                            @if(isset($order->customer))
                                <h5>
                                    {{translate('Customer Name : ')}}<span class="font-weight-normal">{{$order->customer['f_name'].' '.$order->customer['l_name']}}</span>
                                </h5>
                                <h5>
                                    {{translate('Phone : ')}}<span class="font-weight-normal">{{$order->customer['phone']}}</span>
                                </h5>
                                @php($address=\App\Model\CustomerAddress::find($order['delivery_address_id']))
                                <h5>
                                    {{translate('Address : ')}}<span class="font-weight-normal">{{isset($address)?$address['address']:''}}</span>
                                </h5>
                            @endif
                        @endif
                        @if($order->is_guest == 1)
                            @if($order->order_type == 'delivery')
                                @if(isset($order->delivery_address))
                                    <h5>
                                        {{translate('Customer Name : ')}}<span class="font-weight-normal">{{$order->delivery_address->contact_person_name}}</span>
                                    </h5>
                                    <h5>
                                        {{translate('Phone : ')}}<span class="font-weight-normal">{{$order->delivery_address->contact_person_number}}</span>
                                    </h5>
                                    <h5>
                                        {{translate('Address : ')}}<span class="font-weight-normal">{{$order->delivery_address->address}}</span>
                                    </h5>
                                @endif
                            @endif
                        @endif
                    </div>
                </div>
                <h5 class="text-uppercase"></h5>
                <hr class="text-dark hr-style-2">
                <table class="table table-bordered mt-3">
                    <thead>
                    <tr>
                        <th style="width: 10%">{{translate('QTY')}}</th>
                        <th class="">{{translate('DESC')}}</th>
                        <th style="text-align:right; padding-right:4px">{{translate('Price')}}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @php($subTotal=0)
                    @php($totalTax=0)
                    @php($totalDiscountOnProduct=0)
                    @php($addonCost=0)
                    @php($add_on_tax=0)
                    @php($addonTaxCost=0)
                    @foreach($order->details as $detail)
                        @if($detail->product)
                            @php($addonQtys=json_decode($detail['add_on_qtys'],true))
                            @php($addonPrices=json_decode($detail['add_on_prices'],true))
                            @php($addonTaxes=json_decode($detail['add_on_taxes'],true))

                            <tr>
                                <td class="">
                                    {{$detail['quantity']}}
                                </td>
                                <td class="">
                                    <span style="word-break: break-all;"> {{ Str::limit($detail->product['name'], 200) }}</span><br>
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
                                                        <strong>{{\App\CentralLogics\Helpers::set_symbol( $value['optionPrice'])}}</strong>
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
                                                class="font-weight-bold">{{ \App\CentralLogics\Helpers::set_symbol($detail->price) }}</span>
                                        </div>
                                    @endif

                                    @foreach(json_decode($detail['add_on_ids'],true) as $key2 =>$id)
                                        @php($addon=\App\Model\AddOn::find($id))
                                        @if($key2==0)<strong><u>Addons : </u></strong>@endif

                                        @if($addonQtys==null)
                                            @php($addonQuantity=1)
                                        @else
                                            @php($addonQuantity=$addonQtys[$key2])
                                        @endif

                                        <div class="font-size-sm text-body">
                                            <span>{{$addon ? $addon['name'] : translate('addon deleted')}} :  </span>
                                            <span class="font-weight-bold">
                                                {{$addonQuantity}} x {{ \App\CentralLogics\Helpers::set_symbol($addonPrices[$key2]) }}
                                            </span>
                                        </div>
                                        @php($addonCost+=$addonPrices[$key2] * $addonQuantity)
                                        @php($addonTaxCost +=  $addonTaxes[$key2] * $addonQuantity)
                                    @endforeach

                                    {{translate('Discount')}}
                                    : {{ \App\CentralLogics\Helpers::set_symbol($detail['discount_on_product']) }}
                                </td>
                                <td style="width: 28%;padding-right:4px; text-align:right">
                                    @php($amount=($detail['price']-$detail['discount_on_product'])*$detail['quantity'])
                                    {{ \App\CentralLogics\Helpers::set_symbol($amount) }}
                                </td>
                            </tr>
                            @php($subTotal+=$amount)
                            @php($totalTax+=$detail['tax_amount']*$detail['quantity'])
                        @endif
                    @endforeach
                    </tbody>
                </table>

                <div class="row justify-content-md-end mb-3 m-0" style="width: 99%">
                    <div class="col-md-10 p-0">
                        <dl class="row text-right" style="color: black!important;">
                            <dt class="col-6">{{translate('Items Price')}}:</dt>
                            <dd class="col-6">{{ \App\CentralLogics\Helpers::set_symbol($subTotal) }}</dd>
                            <dt class="col-6">{{translate('Tax')}} / {{translate('VAT')}}:</dt>
                            <dd class="col-6">{{ \App\CentralLogics\Helpers::set_symbol($totalTax+$addonTaxCost) }}</dd>
                            <dt class="col-6">{{translate('Addon Cost')}}:</dt>
                            <dd class="col-6">
                                {{ \App\CentralLogics\Helpers::set_symbol($addonCost) }}
                                <hr>
                            </dd>

                            <dt class="col-6">{{translate('Subtotal')}}:</dt>
                            <dd class="col-6">
                                {{ \App\CentralLogics\Helpers::set_symbol($subTotal+$totalTax+$addonCost+$addonTaxCost) }}</dd>
                            <dt class="col-6">{{translate('Coupon Discount')}}:</dt>
                            <dd class="col-6">
                                - {{ \App\CentralLogics\Helpers::set_symbol($order['coupon_discount_amount']) }}</dd>
                            <dt class="col-6">{{translate('Delivery Fee')}}:</dt>
                            <dd class="col-6">
                                @if($order['order_type']=='take_away')
                                    @php($deliveryCharge=0)
                                @else
                                    @php($deliveryCharge=$order['delivery_charge'])
                                @endif
                                {{ \App\CentralLogics\Helpers::set_symbol($deliveryCharge) }}
                                <hr>
                            </dd>

                            <dt class="col-6" style="font-size: 20px">{{translate('Total')}}:</dt>
                            <dd class="col-6"
                                style="font-size: 20px">{{ \App\CentralLogics\Helpers::set_symbol($subTotal+$deliveryCharge+$totalTax+$addonCost-$order['coupon_discount_amount']+$addonTaxCost) }}</dd>

                            @if ($order->order_partial_payments->isNotEmpty())
                                @foreach($order->order_partial_payments as $partial)
                                    <dt class="col-6">
                                        <div class="">
                                            <span>
                                                {{translate('Paid By')}} ({{str_replace('_', ' ',$partial->paid_with)}})</span>
                                            <span>:</span>
                                        </div>
                                    </dt>
                                    <dd class="col-6 text-dark text-right">
                                        {{ \App\CentralLogics\Helpers::set_symbol($partial->paid_amount) }}
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
                                    {{ \App\CentralLogics\Helpers::set_symbol($dueAmount) }}
                                </dd>
                            @endif

                        </dl>
                    </div>
                </div>
                <hr class="text-dark hr-style-2">
                <h5 class="text-center pt-3">
                    """{{translate('THANK YOU')}}"""
                </h5>
                <hr class="text-dark hr-style-2">
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script>

        function printDiv(divName) {

            if($('html').attr('dir') === 'rtl') {
                $('html').attr('dir', 'ltr')
                var printContents = document.getElementById(divName).innerHTML;
                var originalContents = document.body.innerHTML;
                document.body.innerHTML = printContents;
                $('#printableAreaContent').attr('dir', 'rtl')
                window.print();
                document.body.innerHTML = originalContents;
                $('html').attr('dir', 'rtl')
                $('#printableAreaContent').attr('dir', '')
            }else{
                var printContents = document.getElementById(divName).innerHTML;
                var originalContents = document.body.innerHTML;
                document.body.innerHTML = printContents;
                window.print();
                document.body.innerHTML = originalContents;
            }

        }


    </script>
@endpush
