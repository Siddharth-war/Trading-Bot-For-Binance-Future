@extends('layouts.branch.app')

@section('title', translate('Product List'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/product.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Product_List')}}
                </span>
            </h2>
            <span class="badge badge-soft-dark rounded-50 fz-14">{{ $products->total() }}</span>
        </div>

        <div class="row g-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-top px-card pt-4">
                        <div class="row justify-content-between align-items-center gy-2">
                            <div class="col-lg-4">
                                <form action="{{url()->current()}}" method="GET">
                                    <div class="input-group">
                                        <input id="datatableSearch_" type="search" name="search" class="form-control" placeholder="{{translate('search_by_product_name')}}" aria-label="Search" value="{{$search}}" required="" autocomplete="off">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">{{translate('Search')}}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="py-4">
                        <div class="table-responsive datatable-custom">
                            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                <tr>
                                    <th>{{translate('SL')}}</th>
                                    <th>{{translate('product_name')}}</th>
                                    <th>{{translate('selling_price')}}</th>
                                    <th class="text-center">{{translate('total_sale')}}</th>
                                    <th>{{translate('stock')}}</th>
                                    <th>{{translate('Availability')}}</th>
                                    <th class="text-center">{{translate('update_price')}}</th>
                                </tr>
                                </thead>

                                <tbody id="set-rows">
                                @foreach($products as $key=>$product)
                                    <tr>
                                        <td>{{$products->firstitem()+$key}}</td>
                                        <td>
                                            <div class="media align-items-center gap-3">
                                                <div class="avatar">
                                                    <img src="{{$product['imageFullPath']}}" class="rounded img-fit" alt="{{ translate('product') }}">
                                                </div>
                                                <div class="media-body">
                                                        {{ Str::limit($product['name'], 30) }}
                                                </div>
                                            </div>
                                        </td>

                                        @php($productByBranch = json_decode($product->product_by_branch, true))
                                        @if(isset($productByBranch[0]))
                                            <td>{{ Helpers::set_symbol($productByBranch[0]['price']) }}</td>
                                        @else
                                            <td>{{ Helpers::set_symbol($product['price']) }}</td>
                                        @endif
                                        <td class="text-center">{{\App\Model\OrderDetail::whereHas('order', function ($q){
                                                    $q->where('order_status', 'delivered')
                                                        ->where('branch_id',  auth('branch')->id());
                                                })->where('product_id', $product->id)->sum('quantity')}}
                                        </td>
                                        <td>
                                            <div><span class="">{{ translate('Stock Type') }} : {{ ucfirst($product->sub_branch_product?->stock_type) }}</span></div>
                                            @if(isset($product->sub_branch_product) && $product->sub_branch_product->stock_type != 'unlimited')
                                                <div><span class="">{{ translate('Stock') }} : {{ $product->sub_branch_product->stock - $product->sub_branch_product->sold_quantity }}</span></div>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <label class="switcher">
                                                    @forelse($product->product_by_branch as $item)
                                                        <input id="{{$product['id']}}" class="switcher_input"
                                                            type="checkbox" {{ ($item->product_id == $product->id) && $item->is_available == 1 ? 'checked' : ''}}
                                                            data-url="{{route('branch.product.status',[$product['id'],0])}}" onchange="status_change(this)">
                                                        <span class="switcher_control"></span>
                                                    @empty
                                                        <input id="{{$product['id']}}" class="switcher_input" type="checkbox"
                                                            data-url="{{route('branch.product.status',[$product['id'],0])}}" onchange="status_change(this)">
                                                        <span class="switcher_control"></span>
                                                    @endforelse
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a class="btn btn-outline-info btn-sm edit square-btn"
                                                    href="{{route('branch.product.set-price',[$product['id']])}}"><i class="tio-edit"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="table-responsive mt-4 px-3">
                            <div class="d-flex justify-content-lg-end">
                                {!! $products->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        "use strict"

        $('#search-form').on('submit', function () {
            var formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.product.search')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#set-rows').html(data.view);
                    $('.page-area').hide();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });

        function status_change(t) {
            let url = $(t).data('url');
            let checked = $(t).prop("checked");
            let status = checked === true ? 1 : 0;

            Swal.fire({
                title: 'Are you sure?',
                text: 'Want to change status',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2750E6',
                cancelButtonColor: 'default',
                cancelButtonText: '{{translate("No")}}',
                confirmButtonText: '{{translate("Yes")}}',
                reverseButtons: true
            }).then((result) => {
                    if (result.value) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            }
                        });
                        $.ajax({
                            url: url,
                            data: {
                                status: status
                            },
                            success: function (data, status) {

                                if(data.variation_message !== undefined ){
                                    toastr.error(data.variation_message);

                                }
                                if(data.success_message !== undefined ){
                                    toastr.success(data.success_message);

                                }
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);

                            },
                            error: function (data) {
                                toastr.error("{{translate('Status changed failed')}}");
                            },
                        });
                    }

                    else if (result.dismiss) {
                        if (status == 1) {
                            $('#' + t.id).prop('checked', false)

                        } else if (status == 0) {
                            $('#'+ t.id).prop('checked', true)
                        }
                        toastr.info("{{translate("Status hasn't changed")}}");
                    }
                }
            )
        }
    </script>

@endpush
