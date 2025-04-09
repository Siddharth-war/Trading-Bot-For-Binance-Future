@extends('layouts.branch.app')

@section('title', translate('Order Details'))


@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <h2 class="h1 mb-0 d-flex align-items-center gap-1">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/order_details.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Order_Details')}}
                </span>
            </h2>
            <span class="badge badge-soft-dark rounded-50 fz-14">{{$order->details->count()}}</span>
        </div>

        <div class="row" id="printableArea">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="px-card py-3">
                        <div class="row gy-2">
                            <div class="col-sm-6 d-flex flex-column justify-content-between">
                                <div>
                                    <h2 class="page-header-title h1 mb-3">{{translate('order')}} #{{$order['id']}}</h2>
                                    <h5 class="text-capitalize">
                                        <i class="tio-shop"></i>
                                        {{translate('branch')}} :
                                        <label class="badge-soft-info px-2 rounded">
                                            {{$order->branch?$order->branch->name:'Branch deleted!'}}
                                        </label>
                                    </h5>

                                    <div class="mt-2 d-flex flex-column">
                                        @if($order['order_type'] == 'dine_in')
                                            <div class="hs-unfold">
                                                <h5 class="text-capitalize">
                                                    <i class="tio-table"></i>
                                                    {{translate('table no')}} : <label
                                                        class="badge badge-secondary">{{$order->table?$order->table->number:'Table deleted!'}}</label>
                                                </h5>
                                            </div>
                                            @if($order['number_of_people'] != null)
                                                <div class="hs-unfold">
                                                    <h5 class="text-capitalize">
                                                        <i class="tio-user"></i>
                                                        {{translate('number of people')}} : <label
                                                            class="badge badge-secondary">{{$order->number_of_people}}</label>
                                                    </h5>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    <div>
                                        {{translate('Order_Date_&_Time')}}: <i class="tio-date-range"></i>{{date('d M Y',strtotime($order['created_at']))}} {{ date(config('time_format'), strtotime($order['created_at'])) }}
                                    </div>
                                </div>

                                <div>
                                    <h5>{{translate('Cutlery Option')}} : <span class="{{ $order['is_cutlery_required'] == 1 ? 'badge-soft-success' : 'badge-soft-danger' }}">{{$order['is_cutlery_required'] == 1 ? 'On' : 'Off'}}</span></h5>
                                    <h5>{{translate('order')}} {{translate('note')}} : {{$order['order_note']}}</h5>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-sm-right">
                                    <div class="d-flex flex-wrap gap-2 justify-content-sm-end">
                                        @if($order['order_type']!='take_away' && $order['order_type'] != 'pos' && $order['order_type'] != 'dine_in')

                                            @php($googleMapStatus = \App\CentralLogics\Helpers::get_business_settings('google_map_status'))
                                            @if($googleMapStatus)
                                                <div class="hs-unfold ml-1">
                                                    @if($order['order_status']=='out_for_delivery')
                                                        @php($origin=\App\Model\DeliveryHistory::where(['deliveryman_id'=>$order['delivery_man_id'],'order_id'=>$order['id']])->first())
                                                        @php($current=\App\Model\DeliveryHistory::where(['deliveryman_id'=>$order['delivery_man_id'],'order_id'=>$order['id']])->latest()->first())
                                                        @if(isset($origin))
                                                            <a class="btn btn-outline-primary" target="_blank"
                                                               title="{{translate('Delivery Man Last Location')}}" data-toggle="tooltip" data-placement="top"
                                                               href="https://www.google.com/maps/dir/?api=1&origin={{$origin['latitude']}},{{$origin['longitude']}}&destination={{$current['latitude']}},{{$current['longitude']}}">
                                                                <i class="tio-map"></i> {{translate('Show_Location_in_Map')}}
                                                            </a>
                                                        @else
                                                            <a class="btn btn-outline-primary" href="javascript:" data-toggle="tooltip"
                                                               data-placement="top" title="{{translate('Waiting for location...')}}">
                                                                <i class="tio-map"></i> {{translate('Show_Location_in_Map')}}
                                                            </a>
                                                        @endif
                                                    @else
                                                        <a class="btn btn-outline-dark last-location-view" href="javascript:"
                                                           data-toggle="tooltip" data-placement="top"
                                                           title="{{translate('Only available when order is out for delivery!')}}">
                                                            <i class="tio-map"></i> {{translate('Show_Location_in_Map')}}
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif

                                        @endif
                                        <a class="btn btn-info" href={{route('branch.orders.generate-invoice',[$order['id']])}}>
                                            <i class="tio-print"></i> {{translate('Print_Invoice')}}
                                        </a>
                                    </div>

                                    <div class="d-flex gap-3 justify-content-sm-end my-3">
                                        <div class="text-dark font-weight-semibold">{{translate('Status')}} :</div>
                                        @if($order['order_status']=='pending')
                                            <span class="badge-soft-info px-2 rounded text-capitalize">{{translate('pending')}}</span>
                                        @elseif($order['order_status']=='confirmed')
                                            <span class="badge-soft-info px-2 rounded text-capitalize">{{translate('confirmed')}}</span>
                                        @elseif($order['order_status']=='processing')
                                            <span class="badge-soft-warning px-2 rounded text-capitalize">{{translate('processing')}}</span>
                                        @elseif($order['order_status']=='out_for_delivery')
                                            <span class="badge-soft-warning px-2 rounded text-capitalize">{{translate('out_for_delivery')}}</span>
                                        @elseif($order['order_status']=='delivered')
                                            <span class="badge-soft-success px-2 rounded text-capitalize">{{translate('delivered')}}</span>
                                        @elseif($order['order_status']=='failed')
                                            <span class="badge-soft-danger px-2 rounded text-capitalize">{{translate('failed_to_deliver')}}</span>
                                        @else
                                            <span class="badge-soft-danger px-2 rounded text-capitalize">{{str_replace('_',' ',$order['order_status'])}}</span>
                                        @endif
                                    </div>


                                    <div class="text-capitalize d-flex gap-3 justify-content-sm-end mb-3">
                                        <span>{{translate('payment')}} {{translate('method')}} :</span>
                                        <span class="text-dark">{{str_replace('_',' ',$order['payment_method'])}}</span>
                                    </div>

                                    @if(!in_array($order['payment_method'], ['cash_on_delivery', 'wallet_payment', 'offline_payment']))
                                        @if($order['transaction_reference']==null && $order['order_type']!='pos' && $order['order_type'] != 'dine_in')
                                            <div class="d-flex gap-3 justify-content-sm-end align-items-center mb-3">
                                                {{translate('reference')}} {{translate('code')}} :
                                                <button class="btn btn-outline-primary px-3 py-1" data-toggle="modal"
                                                        data-target=".bd-example-modal-sm">
                                                    {{translate('add')}}
                                                </button>
                                            </div>
                                        @elseif($order['order_type']!='pos' && $order['order_type'] != 'dine_in')
                                            <div class="d-flex gap-3 justify-content-sm-end align-items-center mb-3">
                                                {{translate('reference')}} {{translate('code')}}
                                                : {{$order['transaction_reference']}}
                                            </div>
                                        @endif
                                    @endif

                                    <div class="d-flex gap-3 justify-content-sm-end mb-3">
                                        <div>{{translate('Payment_Status')}} :</div>
                                        @if($order['payment_status']=='paid')
                                            <span class="badge-soft-success px-2 rounded text-capitalize">{{translate('paid')}}</span>
                                        @elseif($order['payment_status']=='partial_paid')
                                            <span class="badge-soft-success px-2 rounded text-capitalize">{{translate('partial_paid')}}</span>
                                        @else
                                            <span class="badge-soft-danger px-2 rounded text-capitalize">{{translate('unpaid')}}</span>
                                        @endif
                                    </div>

                                    <div class="d-flex gap-3 justify-content-sm-end mb-3 text-capitalize">
                                        {{translate('order')}} {{translate('type')}}
                                        : <label class="badge-soft-info px-2 rounded">
                                            {{str_replace('_',' ',$order['order_type'])}}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="py-4 table-responsive">
                        <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('Item Details')}}</th>
                                <th>{{translate('Price')}}</th>
                                <th>{{translate('Discount')}}</th>
                                <th>{{translate('Tax')}}</th>
                                <th class="text-right">{{translate('Total_price')}}</th>
                            </tr>
                            </thead>

                            <tbody>
                            <tr>
                            </tr>
                            @php($sub_total=0)
                            @php($total_tax=0)
                            @php($total_dis_on_pro=0)
                            @php($add_ons_cost=0)
                            @php($add_on_tax=0)
                            @php($add_ons_tax_cost=0)
                            @foreach($order->details as $detail)
                                @php($product_details = json_decode($detail['product_details'], true))
                                @php($add_on_qtys=json_decode($detail['add_on_qtys'],true))
                                @php($add_on_prices=json_decode($detail['add_on_prices'],true))
                                @php($add_on_taxes=json_decode($detail['add_on_taxes'],true))

                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="media gap-3 w-max-content">

                                            <img class="img-fluid avatar avatar-lg"
                                                 src="{{ $detail->product?->imageFullPath ?? asset('public/assets/admin/img/160x160/img2.jpg') }}"
                                                 alt="Image Description">

                                            <div class="media-body text-dark fz-12">
                                                <h6 class="text-capitalize">{{$product_details['name']}}</h6>
                                                <div class="d-flex gap-2">
                                                    @if (isset($detail['variation']))
                                                        @foreach(json_decode($detail['variation'],true) as  $variation)
                                                            @if (isset($variation['name'])  && isset($variation['values']))
                                                                <span class="d-block text-capitalize">
                                                                <strong>{{  $variation['name']}} -</strong>
                                                            </span>
                                                                @foreach ($variation['values'] as $value)

                                                                    <span class="d-block text-capitalize">
                                                                     {{ $value['label']}} :
                                                                    <strong>{{\App\CentralLogics\Helpers::set_symbol( $value['optionPrice'])}}</strong>
                                                                </span>
                                                                @endforeach
                                                            @else
                                                                @if (isset(json_decode($detail['variation'],true)[0]))
                                                                    <strong><u> {{  translate('Variation') }} : </u></strong>
                                                                    @foreach(json_decode($detail['variation'],true)[0] as $key1 =>$variation)
                                                                        <div class="font-size-sm text-body">
                                                                            <span>{{$key1}} :  </span>
                                                                            <span class="font-weight-bold">{{$variation}}</span>
                                                                        </div>
                                                                    @endforeach
                                                                @endif
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <div class="font-size-sm text-body">
                                                            <span class="text-dark">{{translate('price')}}  : {{\App\CentralLogics\Helpers::set_symbol($detail['price'])}}</span>
                                                        </div>
                                                    @endif
                                                    <div class="d-flex gap-2">
                                                        <span>{{translate('Qty')}} :  </span>
                                                        <span>{{$detail['quantity']}}</span>
                                                    </div>

                                                    <br>
                                                    @php($addon_ids = json_decode($detail['add_on_ids'],true))
                                                    @if ($addon_ids)
                                                        <span>
                                                        <u><strong>{{translate('addons')}}</strong></u>
                                                        @foreach($addon_ids as $key2 =>$id)
                                                                @php($addon=\App\Model\AddOn::find($id))
                                                                @php($add_on_qtys==null? $add_on_qty=1 : $add_on_qty=$add_on_qtys[$key2])
                                                                <div class="font-size-sm text-body">
                                                                    <span>{{$addon ? $addon['name'] : translate('addon deleted')}} :  </span>
                                                                    <span class="font-weight-semibold">
                                                                        {{$add_on_qty}} x {{ \App\CentralLogics\Helpers::set_symbol($add_on_prices[$key2]) }}
                                                                    </span>
                                                                </div>
                                                                @php($add_ons_cost+=$add_on_prices[$key2] * $add_on_qty)
                                                                @php($add_ons_tax_cost +=  $add_on_taxes[$key2] * $add_on_qty)
                                                            @endforeach
                                                    </span>
                                                    @endif

                                                </div>

                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php($amount=$detail['price']*$detail['quantity'])
                                        {{\App\CentralLogics\Helpers::set_symbol($amount)}}
                                    </td>
                                    <td>
                                        @php($tot_discount = $detail['discount_on_product']*$detail['quantity'])
                                        {{\App\CentralLogics\Helpers::set_symbol($tot_discount)}}
                                    </td>
                                    <td>
                                        @php($product_tax = $detail['tax_amount']*$detail['quantity'])
                                        {{\App\CentralLogics\Helpers::set_symbol($product_tax+$add_ons_tax_cost)}}
                                    </td>
                                    <td class="text-right">{{\App\CentralLogics\Helpers::set_symbol($amount-$tot_discount + $product_tax)}}</td>
                                </tr>
                                @php($total_dis_on_pro += $tot_discount)
                                @php($sub_total += $amount)
                                @php($total_tax += $product_tax)

                            @endforeach
                            </tbody>
                        </table>
                    </div>


                    <div class="card-body pt-0">
                        <hr>
                        <div class="row justify-content-md-end mb-3">
                            <div class="col-md-9 col-lg-8">
                                <dl class="row">
                                    <dt class="col-6">
                                        <div class="d-flex max-w220 ml-auto">
                                            {{translate('items')}} {{translate('price')}}:
                                        </div>
                                    </dt>
                                    <dd class="col-6 text-dark text-right">{{ \App\CentralLogics\Helpers::set_symbol($sub_total) }}</dd>

                                    <dt class="col-6">
                                        <div class="d-flex max-w220 ml-auto">
                                            {{translate('tax')}} / {{translate('vat')}}:
                                        </div>
                                    </dt>
                                    <dd class="col-6 text-dark text-right">{{ \App\CentralLogics\Helpers::set_symbol($total_tax+$add_ons_tax_cost) }}</dd>

                                    <dt class="col-6">
                                        <div class="d-flex max-w220 ml-auto">
                                            {{translate('addon')}} {{translate('cost')}}:
                                        </div>
                                    </dt>
                                    <dd class="col-6 text-dark text-right">{{ \App\CentralLogics\Helpers::set_symbol($add_ons_cost) }}</dd>

                                    <dt class="col-6">
                                        <div class="d-flex max-w220 ml-auto">
                                            {{translate('item')}} {{translate('discount')}}:
                                        </div>
                                    </dt>
                                    <dd class="col-6 text-dark text-right">{{ \App\CentralLogics\Helpers::set_symbol($total_dis_on_pro) }}</dd>

                                    <dt class="col-6">
                                        <div class="d-flex max-w220 ml-auto">
                                            {{translate('subtotal')}}:
                                        </div>
                                    </dt>
                                    <dd class="col-6 text-dark text-right">{{ \App\CentralLogics\Helpers::set_symbol($sub_total =$sub_total+$total_tax+$add_ons_cost-$total_dis_on_pro+$add_ons_tax_cost) }}</dd>

                                    <dt class="col-6">
                                        <div class="d-flex max-w220 ml-auto">
                                            {{translate('coupon')}} {{translate('discount')}}:
                                        </div>
                                    </dt>
                                    <dd class="col-6 text-dark text-right">
                                        - {{ \App\CentralLogics\Helpers::set_symbol($order['coupon_discount_amount']) }}</dd>

                                    <dt class="col-6">
                                        <div class="d-flex max-w220 ml-auto">{{translate('extra discount')}}:</div>
                                    </dt>
                                    <dd class="col-6 text-dark text-right">
                                        - {{ \App\CentralLogics\Helpers::set_symbol($order['extra_discount']) }}</dd>
                                    <dt class="col-6">
                                        <div class="d-flex max-w220 ml-auto">{{translate('delivery')}} {{translate('fee')}}:</div>
                                    </dt>
                                    <dd class="col-6 text-dark text-right">
                                        @if($order['order_type']=='take_away')
                                            @php($del_c=0)
                                        @else
                                            @php($del_c=$order['delivery_charge'])
                                        @endif
                                        {{ \App\CentralLogics\Helpers::set_symbol($del_c) }}
                                    </dd>

                                    <dt class="col-6 border-top pt-2 fz-16 font-weight-bold">
                                        <div class="d-flex max-w220 ml-auto">{{translate('total')}}:</div>
                                    </dt>
                                    <dd class="col-6 border-top pt-2 fz-16 font-weight-bold text-dark text-right">{{ \App\CentralLogics\Helpers::set_symbol($sub_total - $order['coupon_discount_amount'] - $order['extra_discount'] + $del_c) }}</dd>

                                    <!-- partial payment-->
                                    @if ($order->order_partial_payments->isNotEmpty())
                                        @foreach($order->order_partial_payments as $partial)
                                            <dt class="col-6">
                                                <div class="d-flex max-w220 ml-auto">
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
                                            $due_amount = 0;
                                            $due_amount = $order->order_partial_payments->first()?->due_amount;
                                            ?>
                                        <dt class="col-6">
                                            <div class="d-flex max-w220 ml-auto">
                                            <span>
                                                {{translate('Due Amount')}}</span>
                                                <span>:</span>
                                            </div>
                                        </dt>
                                        <dd class="col-6 text-dark text-right">
                                            {{ \App\CentralLogics\Helpers::set_symbol($due_amount) }}
                                        </dd>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                @if($order['order_type'] != 'pos')
                    <div class="card mb-3">
                        <div class="card-body text-capitalize d-flex flex-column gap-4">
                            <h4 class="mb-0 text-center">{{translate('Order_Setup')}}</h4>
                            @if(isset($order->offline_payment))
                                <div class="card mt-3">
                                    <div class="card-body text-center">
                                        @if($order->offline_payment?->status == 1)
                                            <h4>{{ translate('Payment_verified') }}</h4>
                                        @else
                                            <h4>{{ translate('Payment_verification') }}</h4>
                                            <p class="text-danger">{{ translate('please verify the payment before confirm order') }}</p>
                                            <div class="mt-3">
                                                <button class="btn btn-primary" type="button"
                                                        data-id="{{ $order['id'] }}"
                                                        data-target="#payment_verify_modal" data-toggle="modal">{{ translate('Verify_Payment') }}
                                                </button>
                                            </div>
                                        @endif

                                    </div>
                                </div>
                            @endif

                            @if($order['order_type'] != 'pos')

                                <div class="hs-unfold w-100">
                                    <label class="font-weight-bold text-dark fz-14">{{translate('Change_Order_Status')}}</label>
                                    <div class="dropdown">
                                        <button class="form-control h--45px dropdown-toggle d-flex justify-content-between align-items-center w-100" type="button"
                                                id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="false">
                                            {{ translate($order['order_status'])}}
                                        </button>
                                        <div class="dropdown-menu text-capitalize" aria-labelledby="dropdownMenuButton">
                                            @if($order['payment_method'] == 'offline_payment' && $order->offline_payment?->status != 1)
                                                @if($order['order_type'] != 'dine_in')
                                                    <a class="dropdown-item offline-payment-order-alert" href="javascript:">{{translate('pending')}}</a>
                                                @endif

                                                <a class="dropdown-item offline-payment-order-alert" href="javascript:">{{translate('confirmed')}}</a>

                                                @if($order['order_type'] != 'dine_in')
                                                    <a class="dropdown-item offline-payment-order-alert" href="javascript:">{{translate('processing')}}</a>
                                                    <a class="dropdown-item offline-payment-order-alert" href="javascript:">{{translate('out_for_delivery')}}</a>
                                                    <a class="dropdown-item offline-payment-order-alert" href="javascript:">{{translate('delivered')}}</a>
                                                    <a class="dropdown-item route-alert"
                                                       data-route="{{route('branch.orders.status',['id'=>$order['id'],'order_status'=>'returned'])}}"
                                                       data-message="{{ translate("Change status to returned ?") }}"
                                                       href="javascript:">{{translate('returned')}}</a>
                                                    <a class="dropdown-item route-alert"
                                                       data-route="{{route('branch.orders.status',['id'=>$order['id'],'order_status'=>'failed'])}}"
                                                       data-message="{{ translate("Change status to failed ?") }}"
                                                       href="javascript:">{{translate('failed')}}</a>
                                                @endif

                                                @if($order['order_type'] == 'dine_in')
                                                    <a class="dropdown-item offline-payment-order-alert" href="javascript:">{{translate('cooking')}}</a>
                                                    <a class="dropdown-item offline-payment-order-alert" href="javascript:">{{translate('completed')}}</a>
                                                @endif
                                                <a class="dropdown-item route-alert"
                                                   data-route="{{route('branch.orders.status',['id'=>$order['id'],'order_status'=>'canceled'])}}"
                                                   data-message="{{ translate("Change status to canceled ?") }}"
                                                   href="javascript:">{{translate('canceled')}}</a>
                                            @else

                                                @if($order['order_type'] != 'dine_in')
                                                    <a class="dropdown-item route-alert"
                                                       data-route="{{route('branch.orders.status',['id'=>$order['id'],'order_status'=>'pending'])}}"
                                                       data-message="{{ translate("Change status to pending ?") }}"
                                                       href="javascript:">{{translate('pending')}}</a>
                                                @endif

                                                <a class="dropdown-item route-alert"
                                                   data-route="{{route('branch.orders.status',['id'=>$order['id'],'order_status'=>'confirmed'])}}"
                                                   data-message="{{ translate("Change status to confirmed ?") }}"
                                                   href="javascript:">{{translate('confirmed')}}</a>

                                                @if($order['order_type'] != 'dine_in')
                                                    <a class="dropdown-item route-alert"
                                                       data-route="{{route('branch.orders.status',['id'=>$order['id'],'order_status'=>'processing'])}}"
                                                       data-message="{{ translate("Change status to processing ?") }}"
                                                       href="javascript:">{{translate('processing')}}</a>
                                                    <a class="dropdown-item route-alert"
                                                       data-route="{{route('branch.orders.status',['id'=>$order['id'],'order_status'=>'out_for_delivery'])}}"
                                                       data-message="{{ translate("Change status to out for delivery ?") }}"
                                                       href="javascript:">{{translate('out_for_delivery')}}</a>
                                                    <a class="dropdown-item route-alert"
                                                       data-route="{{route('branch.orders.status',['id'=>$order['id'],'order_status'=>'delivered'])}}"
                                                       data-message="{{ translate("Change status to delivered ?") }}"
                                                       href="javascript:">{{translate('delivered')}}</a>
                                                    <a class="dropdown-item route-alert"
                                                       data-route="{{route('branch.orders.status',['id'=>$order['id'],'order_status'=>'returned'])}}"
                                                       data-message="{{ translate("Change status to returned ?") }}"
                                                       href="javascript:">{{translate('returned')}}</a>
                                                    <a class="dropdown-item route-alert"
                                                       data-route="{{route('branch.orders.status',['id'=>$order['id'],'order_status'=>'failed'])}}"
                                                       data-message="{{ translate("Change status to failed ?") }}"
                                                       href="javascript:">{{translate('failed')}}</a>
                                                @endif
                                                @if($order['order_type'] == 'dine_in')
                                                    <a class="dropdown-item route-alert"
                                                       data-route="{{route('branch.orders.status',['id'=>$order['id'],'order_status'=>'cooking'])}}"
                                                       data-message="{{ translate("Change status to cooking ?") }}"
                                                       href="javascript:">{{translate('cooking')}}</a>

                                                    <a class="dropdown-item route-alert"
                                                       data-route="{{route('branch.orders.status',['id'=>$order['id'],'order_status'=>'completed'])}}"
                                                       data-message="{{ translate("Change status to completed ?") }}"
                                                       href="javascript:">{{translate('completed')}}</a>
                                                @endif

                                                <a class="dropdown-item route-alert"
                                                   data-route="{{route('branch.orders.status',['id'=>$order['id'],'order_status'=>'canceled'])}}"
                                                   data-message="{{ translate("Change status to canceled ?") }}"
                                                   href="javascript:">{{translate('canceled')}}</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="d-flex justify-content-between align-items-center gap-10 form-control">
                                        <span class="title-color">{{ translate('Payment Status') }}</span>
                                        @if($order['payment_method'] == 'offline_payment' && $order->offline_payment?->status != 1)
                                            <label class="switcher payment-status-text">
                                                <input class="switcher_input offline-payment-status-alert" type="checkbox" name="payment_status" value="1"
                                                       id="payment_status_switch"{{$order->payment_status == 'paid' ?'checked':''}}>
                                                <span class="switcher_control"></span>
                                            </label>
                                        @else
                                            <label class="switcher payment-status-text">
                                                <input class="switcher_input change-payment-status" type="checkbox" name="payment_status" value="1"
                                                       data-id="{{ $order['id'] }}"
                                                       data-status="{{ $order->payment_status == 'paid' ?'unpaid':'paid' }}"
                                                    {{$order->payment_status == 'paid' ?'checked':''}}>
                                                <span class="switcher_control"></span>
                                            </label>
                                        @endif
                                    </div>

                                </div>
                            @endif
                            @if($order->customer || $order->is_guest == 1)
                                <div>
                                    <label class="font-weight-bold text-dark fz-14">{{translate('Delivery_Date_&_Time')}} {{$order['delivery_date'] > \Carbon\Carbon::now()->format('Y-m-d')? translate('(Scheduled)') : ''}}</label>
                                    <div class="d-flex gap-2 flex-wrap flex-xxl-nowrap">
                                        <input onchange="changeDeliveryTimeDate(this)" name="delivery_date" type="date" class="form-control" value="{{$order['delivery_date'] ?? ''}}">
                                        <input onchange="changeDeliveryTimeDate(this)" name="delivery_time" type="time" class="form-control" value="{{$order['delivery_time'] ?? ''}}">
                                    </div>
                                </div>
                                @if($order['order_type']!='take_away' && $order['order_type'] != 'pos' && $order['order_type'] != 'dine_in' && !$order['delivery_man_id'])

                                    <a href="#" class="btn btn-primary btn-block d-flex gap-1 justify-content-center align-items-center" data-toggle="modal" data-target="#assignDeliveryMan">
                                        <img width="17" src="{{asset('public/assets/admin/img/icons/assain_delivery_man.png')}}" alt="">
                                        {{translate('Assign_Delivery_Man')}}
                                    </a>
                                @endif
                            @endif
                            <div>
                                @if($order['order_type'] != 'pos' && $order['order_type'] != 'take_away' && ($order['order_status'] != DELIVERED && $order['order_status'] != RETURNED && $order['order_status'] != CANCELED && $order['order_status'] != FAILED && $order['order_status'] != COMPLETED))
                                    <label class="font-weight-bold text-dark fz-14">{{translate('Food_Preparation_Time')}}</label>
                                    <div class="form-control justify-content-between">
                                        <span class="ml-2 ml-sm-3 ">
                                        <i class="tio-timer d-none" id="timer-icon"></i>
                                        <span id="counter" class="text-info"></span>
                                        <i class="tio-edit p-2 d-none cursor-pointer" id="edit-icon" data-toggle="modal" data-target="#counter-change" data-whatever="@mdo"></i>
                                        </span>
                                    </div>
                                @endif
                            </div>
                            @if($order->delivery_man_id)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h4 class="mb-4 d-flex gap-2">
                                    <span class="card-header-icon">
                                        <i class="tio-user text-dark"></i>
                                    </span>
                                            <span>{{ translate('delivery_man') }}</span>
                                            <a  href="#"  data-toggle="modal" data-target="#assignDeliveryMan"
                                                class="text--base cursor-pointer ml-auto">
                                                {{translate('Change')}}
                                            </a>
                                        </h4>
                                        <div class="media flex-wrap gap-3">
                                            <a>
                                                <img class="avatar avatar-lg rounded-circle" src="{{$order->delivery_man->imageFullPath}}" alt="Image">
                                            </a>
                                            <div class="media-body d-flex flex-column gap-1">
                                                <a target="" href="#" class="text-dark"><span>{{$order->delivery_man['f_name'].' '.$order->delivery_man['l_name'] ?? ''}}</span></a>
                                                <span class="text-dark"> <span>{{$order->delivery_man['orders_count']}}</span> {{translate('Orders')}}</span>
                                                <span class="text-dark break-all">
                                            <i class="tio-call-talking-quiet mr-2"></i>
                                            <a href="tel:{{$order->delivery_man['phone']}}" class="text-dark">{{$order->delivery_man['phone'] ?? ''}}</a>
                                        </span>
                                                <span class="text-dark break-all">
                                            <i class="tio-email mr-2"></i>
                                            <a href="mailto:{{$order->delivery_man['email']}}" class="text-dark">{{$order->delivery_man['email'] ?? ''}}</a>
                                        </span>
                                            </div>
                                        </div>
                                        <hr class="w-100">
                                        @if($order['order_status']=='out_for_delivery')
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5>{{translate('Last_location')}}</h5>
                                            </div>
                                            @php($origin=\App\Model\DeliveryHistory::where(['deliveryman_id'=>$order['delivery_man_id'],'order_id'=>$order['id']])->first())
                                            @php($current=\App\Model\DeliveryHistory::where(['deliveryman_id'=>$order['delivery_man_id'],'order_id'=>$order['id']])->latest()->first())
                                            @if(isset($origin))
                                                <a target="_blank" class="text-dark"
                                                   title="Delivery Boy Last Location" data-toggle="tooltip" data-placement="top"
                                                   href="http://maps.google.com/maps?z=12&t=m&q=loc:{{$current['latitude']}}+{{$current['longitude']}}">
                                                    <img width="13" src="{{asset('public/assets/admin/img/icons/location.png')}}" alt="">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{$origin['location']?? ''}}
                                                </a>
                                            @else
                                                <a href="javascript:" data-toggle="tooltip" class="text-dark"
                                                   data-placement="top" title="{{translate('Waiting for location...')}}">
                                                    <img width="13" src="{{asset('public/assets/admin/img/icons/location.png')}}" alt="">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{translate('Waiting for location...')}}
                                                </a>
                                            @endif
                                        @else
                                            <a href="javascript:" class="text-dark last-location-view"
                                               data-toggle="tooltip" data-placement="top"
                                               title="{{translate('Only available when order is out for delivery!')}}">
                                                <img width="13" src="{{asset('public/assets/admin/img/icons/location.png')}}" alt="">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{translate('Only available when order is out for delivery!')}}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if($order['order_type']!='take_away' && $order['order_type'] != 'pos' && $order['order_type'] != 'dine_in')
                                <div class="card">
                                    <div class="card-body">
                                        <div class="mb-4 d-flex gap-2 justify-content-between">
                                            <h4 class="mb-0 d-flex gap-2">
                                                <i class="tio-user text-dark"></i>
                                                {{translate('Delivery_Informatrion')}}
                                            </h4>

                                            <div class="edit-btn cursor-pointer" data-toggle="modal" data-target="#deliveryInfoModal">
                                                <i class="tio-edit"></i>
                                            </div>
                                        </div>
                                        <div class="delivery--information-single flex-column">
                                            @php($address=\App\Model\CustomerAddress::find($order['delivery_address_id']))
                                            <div class="d-flex">
                                                <div class="name">{{ translate('Name') }}</div>
                                                <div class="info">{{ $address? $address['contact_person_name']: '' }}</div>
                                            </div>
                                            <div class="d-flex">
                                                <div class="name">{{translate('Contact')}}</div>
                                                <a href="tel:{{ $address? $address['contact_person_number']: '' }}" class="info">{{ $address? $address['contact_person_number']: '' }}</a>
                                            </div>
                                            <div class="d-flex">
                                                <div class="name">{{translate('floor')}}</div>
                                                <div class="info">{{$address['floor'] ?? ''}}</div>
                                            </div>
                                            <div class="d-flex">
                                                <div class="name">{{translate('house')}}</div>
                                                <div class="info">{{$address['house'] ?? ''}}</div>
                                            </div>
                                            <div class="d-flex">
                                                <div class="name">{{translate('address')}}</div>
                                                <div class="info">{{$address['address'] ?? ''}}</div>
                                            </div>
                                            <div class="d-flex">
                                                <div class="name">{{translate('road')}}</div>
                                                <div class="info">{{$address['road'] ?? ''}}</div>
                                            </div>
                                            @if($order->order_area)
                                                <div class="d-flex">
                                                    <div class="name">{{translate('Area')}}</div>
                                                    <div class="info edit-btn cursor-pointer">
                                                        {{ $order?->order_area?->area?->area_name }}
                                                        @if($order?->branch?->delivery_charge_setup?->delivery_charge_type == 'area')
                                                            <i class="tio-edit" data-toggle="modal" data-target="#editArea"></i>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                            @php($googleMapStatus = \App\CentralLogics\Helpers::get_business_settings('google_map_status'))
                                            @if($googleMapStatus)
                                                @if(isset($address['address']) && isset($address['latitude']) && isset($address['longitude']))
                                                    <hr class="w-100">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <a target="_blank" class="text-dark"
                                                           href="http://maps.google.com/maps?z=12&t=m&q=loc:{{$address['latitude']}}+{{$address['longitude']}}">
                                                            <img width="13" src="{{asset('public/assets/admin/img/icons/location.png')}}" alt="">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                            {{$address['address']}}
                                                        </a>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                @endif

                    @if($order->offline_payment)
                        @php($payment = json_decode($order->offline_payment?->payment_info, true))

                        <div class="card mt-2">
                            <div class="card-body">
                                <h5 class="form-label mb-3">
                                    <span class="card-header-icon"><i class="tio-shopping-basket"></i></span>
                                    <span>{{translate('Offline payment information')}}</span>
                                </h5>
                                <div class="offline-payment--information-single flex-column mt-3">
                                    <div class="d-flex">
                                        <span class="name">{{ translate('payment_note') }}</span>
                                        <span class="info">{{ $payment['payment_note'] }}</span>
                                    </div>
                                    @foreach($payment['method_information'] as $infos)
                                        @foreach($infos as $info_key => $info)
                                            <div class="d-flex">
                                                <span class="name">{{ $info_key }}</span>
                                                <span class="info">{{ $info }}</span>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif


                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="mb-4 d-flex gap-2">
                                <i class="tio-user text-dark"></i>
                                {{ translate('Customer Information') }}
                            </h4>
                            @if($order->is_guest == 1)
                                <div class="media flex-wrap gap-3 align-items-center">
                                    <a target="#" >
                                        <img class="avatar avatar-lg rounded-circle" src="{{asset('public/assets/admin/img/160x160/img1.jpg')}}" alt="Image">
                                    </a>
                                    <div class="media-body d-flex flex-column gap-1">
                                        <a target="#"  class="text-dark text-capitalize"><strong>{{translate('Guest Customer')}}</strong></a>
                                    </div>
                                </div>
                            @else
                                @if($order->customer)
                                    <div class="media flex-wrap gap-3">
                                        <a>
                                            <img class="avatar avatar-lg rounded-circle" src="{{$order->customer['imageFullPath']}}" alt="Image">
                                        </a>
                                        <div class="media-body d-flex flex-column gap-1">
                                            <a><strong>{{$order->customer['f_name'].' '.$order->customer['l_name']}}</strong></a>
                                            <span class="text-dark">{{$order->customer['orders_count']}} {{translate('Orders')}}</span>
                                            <span class="text-dark">
                                            <i class="tio-call-talking-quiet mr-2"></i>
                                            <a class="text-dark break-all" href="tel:{{$order->customer['phone']}}">{{$order->customer['phone']}}</a>
                                        </span>
                                            <span class="text-dark">
                                            <i class="tio-email mr-2"></i>
                                            <a class="text-dark break-all" href="mailto:{{$order->customer['email']}}">{{$order->customer['email']}}</a>
                                        </span>
                                        </div>
                                    </div>
                                @endif
                                @if($order->user_id == null)
                                    <div class="media flex-wrap gap-3 align-items-center">
                                        <a target="#" >
                                            <img class="avatar avatar-lg rounded-circle" src="{{asset('public/assets/admin/img/160x160/img1.jpg')}}" alt="Image">
                                        </a>
                                        <div class="media-body d-flex flex-column gap-1">
                                            <a target="#"  class="text-dark text-capitalize"><strong>{{translate('walking_customer')}}</strong></a>
                                        </div>
                                    </div>
                                @endif
                                @if($order->user_id != null && !isset($order->customer))
                                    <div class="media flex-wrap gap-3 align-items-center">
                                        <a target="#" >
                                            <img class="avatar avatar-lg rounded-circle" src="{{asset('public/assets/admin/img/160x160/img1.jpg')}}" alt="Image">
                                        </a>
                                        <div class="media-body d-flex flex-column gap-1">
                                            <a target="#"  class="text-dark text-capitalize"><strong>{{translate('Customer_not_available')}}</strong></a>
                                        </div>
                                    </div>
                                @endif

                            @endif
                        </div>
                    </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <h4 class="mb-4 d-flex gap-2">
                            <i class="tio-user text-dark"></i>
                            {{translate('Branch Information')}}
                        </h4>
                        <div class="media flex-wrap gap-3">
                            <div>
                                <img class="avatar avatar-lg rounded-circle" src="{{$order->branch?->imageFullPath}}" alt="Image">
                            </div>
                            <div class="media-body d-flex flex-column gap-1">
                                @if(isset($order->branch))
                                    <span class="text-dark"><span>{{$order->branch['name']}}</span></span>
                                    <span class="text-dark"> <span>{{$order->branch['orders_count']}}</span> {{translate('Orders served')}}</span>
                                    @if($order->branch['phone'])
                                        <span class="text-dark break-all">
                                        <i class="tio-call-talking-quiet mr-2"></i>
                                        <a class="text-dark" href="tel:+{{$order->branch['phone']}}">{{$order->branch['phone']}}</a>
                                    </span>
                                    @endif
                                    <span class="text-dark break-all">
                                        <i class="tio-email mr-2"></i>
                                        <a class="text-dark" href="mailto:{{$order->branch['email']}}">{{$order->branch['email']}}</a>
                                    </span>
                                @else
                                    <span class="fz--14px text--title font-semibold text-hover-primary d-block">
                                        {{translate('Branch Deleted')}}
                                    </span>
                                @endif

                            </div>
                        </div>
                        @if(isset($order->branch))
                            <hr class="w-100">
                            <div class="d-flex align-items-center text-dark gap-3">
                                <img width="13" src="{{asset('public/assets/admin/img/icons/location.png')}}" alt="">
                                <a target="_blank" class="text-dark"
                                   href="http://maps.google.com/maps?z=12&t=m&q=loc:{{$order->branch['latitude']}}+{{$order->branch['longitude']}}">
                                    {{$order->branch['address']}}<br>
                                </a>
                            </div>
                        @endif

                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="assignDeliveryMan" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title fs-5" id="assignDeliveryManLabel">{{translate('Assign_Delivery_Man')}}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="list-group">
                        @foreach(\App\Model\DeliveryMan::where(['is_active'=> 1])->whereIn('branch_id', [0, auth('branch')->id()])->get() as $deliveryMan)
                            <li class="list-group-item d-flex flex-wrap align-items-center gap-3 justify-content-between">
                                <div class="media align-items-center gap-2 flex-wrap">
                                    <div class="avatar">
                                        <img class="img-fit rounded-circle" loading="lazy" decoding="async"
                                             src="{{$deliveryMan->imageFullPath}}" alt="{{ translate('deliveryman') }}">
                                    </div>
                                    <span>{{$deliveryMan['f_name'].' '.$deliveryMan['l_name']}}</span>
                                </div>
                                <a id="{{$deliveryMan->id}}" class="btn btn-primary btn-sm assign-deliveryman" data-id="{{$deliveryMan->id}}">{{translate('Assign')}}</a>
                            </li>
                        @endforeach

                    </ul>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4"
                        id="mySmallModalLabel">{{translate('reference')}} {{translate('code')}} {{translate('add')}}</h5>
                    <button type="button" class="btn btn-xs btn-icon btn-ghost-secondary" data-dismiss="modal"
                            aria-label="Close">
                        <i class="tio-clear tio-lg"></i>
                    </button>
                </div>

                <form action="{{route('branch.orders.add-payment-ref-code',[$order['id']])}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <input type="text" name="transaction_reference" class="form-control"
                                   placeholder="{{translate('EX : Code123')}}" required>
                        </div>
                        <button class="btn btn-primary">{{translate('submit')}}</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="deliveryInfoModal" id="deliveryInfoModal"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4" id="mySmallModalLabel">{{translate('Update_Delivery_Informatrion')}}</h5>
                    <button type="button" class="btn btn-xs btn-icon btn-ghost-secondary" data-dismiss="modal" aria-label="Close">
                        <i class="tio-clear tio-lg"></i>
                    </button>
                </div>
                @if($order['delivery_address_id'])
                    <form action="{{route('branch.order.update-shipping',[$order['delivery_address_id']])}}" method="post">
                        @csrf
                        <input type="hidden" name="user_id" value="{{$order->user_id}}">
                        <input type="hidden" name="order_id" value="{{$order->id}}">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>{{translate('Type')}}</label>
                                <input type="text" name="address_type" class="form-control"
                                       placeholder="{{translate('EX : Home')}}" value="{{ $address['address_type'] ?? '' }}" required>
                            </div>
                            <div class="form-group">
                                <label>{{translate('Name')}}</label>
                                <input type="text" class="form-control" name="contact_person_name"
                                       placeholder="{{translate('EX : Jhon Doe')}}" value="{{ $address['contact_person_name'] ?? '' }}" required>
                            </div>
                            <div class="form-group">
                                <label>{{translate('Contact_Number')}}</label>
                                <input type="text" class="form-control" name="contact_person_number"
                                       placeholder="{{translate('EX : 01888888888')}}" value="{{ $address['contact_person_number']?? '' }}" required>
                            </div>
                            <div class="form-group">
                                <label>{{translate('floor')}}</label>
                                <input type="text" class="form-control" name="floor"
                                       placeholder="{{translate('EX : 5')}}" value="{{ $address['floor'] ?? '' }}" >
                            </div>
                            <div class="form-group">
                                <label>{{translate('house')}}</label>
                                <input type="text" class="form-control" name="house"
                                       placeholder="{{translate('EX : 21/B')}}" value="{{ $address['house'] ?? '' }}" >
                            </div>
                            <div class="form-group">
                                <label>{{translate('road')}}</label>
                                <input type="text" class="form-control" name="road"
                                       placeholder="{{translate('EX : Baker Street')}}" value="{{ $address['road'] ?? '' }}" >
                            </div>
                            <div class="form-group">
                                <label>{{translate('Address')}}</label>
                                <input type="text" class="form-control" name="address"
                                       placeholder="{{translate('EX : Dhaka,_Bangladesh')}}" value="{{ $address['address'] ?? '' }}" required>
                            </div>
                            @php($googleMapStatus = \App\CentralLogics\Helpers::get_business_settings('google_map_status'))
                            @if($googleMapStatus)
                                @if($order?->branch?->delivery_charge_setup?->delivery_charge_type == 'distance')
                                    <div class="form-group">
                                        <label>{{translate('latitude')}}</label>
                                        <input type="text" class="form-control" name="latitude"
                                               placeholder="{{translate('EX : 23.796584198263794')}}" value="{{ $address['latitude'] ?? '' }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label>{{translate('longitude')}}</label>
                                        <input type="text" class="form-control" name="longitude"
                                               placeholder="{{translate('EX : 23.796584198263794')}}" value="{{ $address['longitude'] ?? '' }}" required>
                                    </div>
                                @endif
                            @endif
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-primary">{{translate('submit')}}</button>
                            </div>
                        </div>
                    </form>
                @endif

            </div>
        </div>
    </div>

    @if($order['order_type'] != 'pos' && $order['order_type'] != 'take_away' && ($order['order_status'] != DELIVERED && $order['order_status'] != RETURNED && $order['order_status'] != CANCELED && $order['order_status'] != FAILED && $order['order_status'] != COMPLETED))
        <div class="modal fade" id="counter-change" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ translate('Need time to prepare the food') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{route('branch.orders.increase-preparation-time', ['id' => $order->id])}}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group text-center">
                                <input type="number" min="0" name="extra_minute" id="extra_minute" class="form-control" placeholder="{{translate('EX : 20')}}" required>
                            </div>

                            <div class="form-group flex-between">
                                <div class="badge text-info shadow cursor-pointer change-food-preparation-time" data-minute="10">{{ translate('10min') }}</div>
                                <div class="badge text-info shadow cursor-pointer change-food-preparation-time" data-minute="20">{{ translate('20min') }}</div>
                                <div class="badge text-info shadow cursor-pointer change-food-preparation-time" data-minute="30">{{ translate('30min') }}</div>
                                <div class="badge text-info shadow cursor-pointer change-food-preparation-time" data-minute="40">{{ translate('40min') }}</div>
                                <div class="badge text-info shadow cursor-pointer change-food-preparation-time" data-minute="50">{{ translate('50min') }}</div>
                                <div class="badge text-info shadow cursor-pointer change-food-preparation-time" data-minute="60">{{ translate('60min') }}</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Close') }}</button>
                            <button type="submit" class="btn btn-primary">{{ translate('Submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($order->offline_payment)
        <div class="modal fade" id="payment_verify_modal">
            <div class="modal-dialog modal-lg offline-details">
                <div class="modal-content">
                    <div class="modal-header justify-content-center">
                        <h4 class="modal-title pb-2">{{translate('Payment_Verification')}}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="card">
                        <div class="modal-body mx-2">
                            <p class="text-danger">{{translate('Please Check & Verify the payment information whether it is correct or not before confirm the order.')}}</p>
                            <h5>{{translate('customer_Information')}}</h5>

                            <div class="card-body">
                                @if($order->is_guest == 0)
                                    <p>{{ translate('name') }} : {{ $order->customer ? $order->customer->f_name.' '. $order->customer->l_name: ''}} </p>
                                    <p>{{ translate('contact') }} : {{ $order->customer ? $order->customer->phone: ''}}</p>
                                @else
                                    <p>{{ translate('guest_customer') }} </p>
                                @endif
                            </div>

                            <h5>{{translate('Payment_Information')}}</h5>
                            @php($payment = json_decode($order->offline_payment?->payment_info, true))
                            <div class="row card-body">
                                <div class="col-md-6">
                                    <p>{{ translate('Payment_Method') }} : {{ $payment['payment_name'] }}</p>
                                    @foreach($payment['method_fields'] as $fields)
                                        @foreach($fields as $field_key => $field)
                                            <p>{{ $field_key }} : {{ $field }}</p>
                                        @endforeach
                                    @endforeach
                                </div>
                                <div class="col-md-6">
                                    <p>{{ translate('payment_note') }} : {{ $payment['payment_note'] }}</p>
                                    @foreach($payment['method_information'] as $infos)
                                        @foreach($infos as $info_key => $info)
                                            <p>{{ $info_key }} : {{ $info }}</p>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-center my-2 mx-3">
                        @if($order->offline_payment?->status == 0)
                            <a type="reset" class="btn btn-secondary update-verification-status" data-status="2">{{ translate('Payment_Did_Not_Received') }}</a>
                        @endif
                        <a type="submit" class="btn btn-primary update-verification-status" data-status="1">{{ translate('Yes,_Payment_Received') }}</a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editArea" id="editArea"
         aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4" id="mySmallModalLabel">{{translate('Update_Delivery_Area')}}</h5>
                    <button type="button" class="btn btn-xs btn-icon btn-ghost-secondary" data-dismiss="modal" aria-label="Close">
                        <i class="tio-clear tio-lg"></i>
                    </button>
                </div>
                <form action="{{ route('branch.orders.update-order-delivery-area', ['order_id' => $order->id]) }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="row">

                            <?php
                            $branch = \App\Model\Branch::with(['delivery_charge_setup', 'delivery_charge_by_area'])
                                ->where(['id' => $order['branch_id']])
                                ->first(['id', 'name', 'status']);
                            ?>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{translate('Delivery Area')}}</label>
                                    <select name="selected_area_id" class="form-control js-select2-custom-x mx-1" id="areaDropdown" >
                                        <option value="">{{ translate('Select Area') }}</option>
                                        @foreach($branch->delivery_charge_by_area as $area)
                                            <option value="{{$area['id']}}" {{ (isset($order->order_area) && $order->order_area->area_id == $area['id']) ? 'selected' : '' }}
                                            data-charge="{{$area['delivery_charge']}}" >{{ $area['area_name'] }} - ({{ Helpers::set_symbol($area['delivery_charge']) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="input-label" for="">{{ translate('Delivery Charge') }} ({{ Helpers::currency_symbol() }})</label>
                                <input type="number" class="form-control" name="delivery_charge" id="deliveryChargeInput" value="" readonly>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary">{{translate('update')}}</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        $('.assign-deliveryman').on('click', function (){
            let id = $(this).data('id');
            addDeliveryMan(id);
        });

        function addDeliveryMan(id) {
            $.ajax({
                type: "GET",
                url: '{{url('/')}}/branch/orders/add-delivery-man/{{$order['id']}}/' + id,
                data: $('#product_form').serialize(),
                success: function (data) {
                    if(data.status == true) {
                        toastr.success('{{translate("Delivery man successfully assigned/changed")}}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function () {
                            location.reload();
                        }, 2000)
                    }else{
                        toastr.error('{{translate("Deliveryman man can not assign/change in that status")}}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                error: function () {
                    toastr.error('{{translate("Add valid data")}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }

        $('.last-location-view').on('click', function() {
            toastr.warning('{{ translate("Only available when order is out for delivery!") }}', {
                CloseButton: true,
                ProgressBar: true
            });
        })

        $('.change-food-preparation-time').on('click', function (){
            let min = $(this).data('minute');
            document.getElementById("extra_minute").value = min;

        });

    </script>
    @if($order['order_type'] != 'pos' && $order['order_type'] != 'take_away' && ($order['order_status'] != DELIVERED && $order['order_status'] != RETURNED && $order['order_status'] != CANCELED && $order['order_status'] != FAILED && $order['order_status'] != COMPLETED))
        <script>
            const expire_time = "{{ $order['remaining_time'] }}";
            var countDownDate = new Date(expire_time).getTime();
            const time_zone = "{{ \App\CentralLogics\Helpers::get_business_settings('time_zone') ?? 'UTC' }}";

            var x = setInterval(function() {
                var now = new Date(new Date().toLocaleString("en-US", {timeZone: time_zone})).getTime();

                var distance = countDownDate - now;

                var days = Math.trunc(distance / (1000 * 60 * 60 * 24));
                var hours = Math.trunc((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.trunc((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.trunc((distance % (1000 * 60)) / 1000);


                document.getElementById("timer-icon").classList.remove("d-none");
                document.getElementById("edit-icon").classList.remove("d-none");
                var $text = (distance < 0) ? "{{ translate('over') }}" : "{{ translate('left') }}";
                document.getElementById("counter").innerHTML = Math.abs(days) + "d " + Math.abs(hours) + "h " + Math.abs(minutes) + "m " + Math.abs(seconds) + "s " + $text;
                if (distance < 0) {
                    var element = document.getElementById('counter');
                    element.classList.add('text-danger');
                }
            }, 1000);
        </script>
    @endif

    <script>
        function changeDeliveryTimeDate(t) {
            let name = t.name
            let value = t.value
            $.ajax({
                type: "GET",
                url: '{{url('/')}}/branch/orders/ajax-change-delivery-time-date/{{$order['id']}}?' + t.name + '=' + t.value,
                data: {
                    name : name,
                    value : value
                },
                success: function (data) {
                    console.log(data)
                    if(data.status == true && name == 'delivery_date') {
                        toastr.success('{{translate("Delivery date changed successfully")}}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }else if(data.status == true && name == 'delivery_time'){
                        toastr.success('{{translate("Delivery time changed successfully")}}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }else {
                        toastr.error('{{translate("Order No is not valid")}}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                error: function () {
                    toastr.error('{{translate("Add valid data")}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }

        $('.update-verification-status').on('click', function() {
            let status = $(this).data('status');
            verify_offline_payment(status);
        });

        function verify_offline_payment(status) {
            $.ajax({
                type: "GET",
                url: '{{url('/')}}/branch/orders/verify-offline-payment/{{$order['id']}}/' + status,
                success: function (data) {
                    location.reload();
                    if(data.status == true) {
                        toastr.success('{{ translate("offline payment verify status changed") }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }else{
                        toastr.error('{{ translate("offline payment verify status not changed") }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }

                },
                error: function () {
                }
            });
        }

        $('.offline-payment-status-alert').on('click', function () {
            Swal.fire({
                title: '{{translate("Payment_is_Not_Verified")}}',
                text: '{{ translate("You can not change status of unverified offline payment") }}',
                type: 'question',
                showCancelButton: true,
                showConfirmButton: false,
                cancelButtonColor: 'default',
                confirmButtonColor: '#01684b',
                cancelButtonText: '{{translate("Close")}}',
                confirmButtonText: '',
                reverseButtons: true
            }).then((result) => {
                $('#payment_status_switch').prop('checked', false);
            })
        })

        $('.offline-payment-order-alert').on('click', function () {
            Swal.fire({
                title: '{{translate("Payment_is_Not_Verified")}}',
                text: '{{ translate("You can not change order status to this status. Please Check & Verify the payment information whether it is correct or not. You can only change order status to failed or cancel if payment is not verified.") }}',
                type: 'question',
                showCancelButton: true,
                showConfirmButton: false,
                cancelButtonColor: 'default',
                confirmButtonColor: '#01684b',
                cancelButtonText: '{{translate("Close")}}',
                confirmButtonText: '{{translate("Proceed")}}',
                reverseButtons: true
            }).then((result) => {

            })
        })

        $('.change-payment-status').on('click', function(){
            let id = $(this).data('id');
            let status = $(this).data('status');
            let paymentStatusRoute = "{{ route('branch.orders.payment-status') }}";
            location.href = paymentStatusRoute + '?id=' + encodeURIComponent(id) + '&payment_status=' + encodeURIComponent(status);
        });

        $(document).ready(function() {
            const $areaDropdown = $('#areaDropdown');
            const $deliveryChargeInput = $('#deliveryChargeInput');

            $areaDropdown.change(function() {
                const selectedOption = $(this).find('option:selected');
                const charge = selectedOption.data('charge');
                $deliveryChargeInput.val(charge);
            });
        });

    </script>
@endpush
