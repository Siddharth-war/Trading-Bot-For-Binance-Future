@extends('layouts.admin.app')

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
                            <div class="col-lg-8">
                                <div class="d-flex gap-3 justify-content-end text-nowrap flex-wrap">
                                    <div>
                                        <button type="button" class="btn btn-outline-primary" data-toggle="dropdown" aria-expanded="false">
                                            <i class="tio-download-to"></i>
                                            Export
                                            <i class="tio-chevron-down"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li>
                                                <a type="submit" class="dropdown-item d-flex align-items-center gap-2" href="{{route('admin.product.excel-import', ['search' => $search])}}">
                                                    <img width="14" src="{{asset('public/assets/admin/img/icons/excel.png')}}" alt="">
                                                    {{translate('Excel')}}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <a href="{{route('admin.product.add-new')}}" class="btn btn-primary">
                                        <i class="tio-add"></i> {{translate('add_New_Product')}}
                                    </a>
                                </div>
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
                                    <th>{{translate('status')}}</th>
                                    <th>{{translate('recommended')}}</th>
                                    <th class="text-center">{{translate('action')}}</th>
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
                                                    <a class="text-dark" href="{{route('admin.product.view',[$product['id']])}}">
                                                        {{ Str::limit($product['name'], 30) }}
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ \App\CentralLogics\Helpers::set_symbol($product['price']) }}</td>
                                        <td class="text-center">{{\App\Model\OrderDetail::whereHas('order', function ($q){
                                                    $q->where('order_status', 'delivered');
                                                })->where('product_id', $product->id)->sum('quantity')}}
                                        </td>
                                        <td>
                                            <div><span class="">{{ translate('Stock Type') }} : {{ ucfirst($product->main_branch_product?->stock_type) }}</span></div>
                                            @if(isset($product->main_branch_product) && $product->main_branch_product->stock_type != 'unlimited')
                                                <div><span class="">{{ translate('Stock') }} : {{ $product->main_branch_product->stock - $product->main_branch_product->sold_quantity }}</span></div>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <label class="switcher">
                                                    <input id="{{$product['id']}}" class="switcher_input status-change" type="checkbox" {{$product['status']==1? 'checked' : ''}}
                                                        data-url="{{route('admin.product.status',[$product['id'],0])}}">
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <label class="switcher">
                                                    <input id="recommended-{{$product['id']}}" class="switcher_input recommended-status-change" type="checkbox" {{$product['is_recommended']==1? 'checked' : ''}}
                                                    data-url="{{route('admin.product.recommended',[$product['id'],0])}}">
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a class="btn btn-outline-info btn-sm edit square-btn"
                                                href="{{route('admin.product.edit',[$product['id']])}}"><i class="tio-edit"></i></a>
                                                <button type="button" class="btn btn-outline-danger btn-sm delete square-btn form-alert"
                                                        data-id="product-{{$product['id']}}"
                                                        data-message="{{translate('Want to delete this item ?')}}">
                                                    <i class="tio-delete"></i>
                                                </button>
                                            </div>
                                            <form action="{{route('admin.product.delete',[$product['id']])}}"
                                                method="post" id="product-{{$product['id']}}">
                                                @csrf @method('delete')
                                            </form>
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
        "use strict";

        $(".recommended-status-change").change(function() {
            var value = $(this).val();
            let url = $(this).data('url');
            console.log(value, url);
            status_change(this, url);
        });

        function recommended_status_change(t) {
            let url = $(t).data('url');
            let checked = $(t).prop("checked");
            let status = checked === true ? 1 : 0;

            Swal.fire({
                title: 'Are you sure?',
                text: 'Want to change the recommended status',
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
                                toastr.success("{{translate('updated successfully')}}");
                            },
                            error: function (data) {
                                toastr.error("{{translate('updated failed')}}");
                            }
                        });
                    }
                    else if (result.dismiss) {
                        if (status == 1) {
                            $('#' + t.id).prop('checked', false)

                        } else if (status == 0) {
                            $('#'+ t.id).prop('checked', true)
                        }
                        toastr.info("{{translate("recommended status has not changed")}}");
                    }
                }
            )
        }
    </script>
@endpush
