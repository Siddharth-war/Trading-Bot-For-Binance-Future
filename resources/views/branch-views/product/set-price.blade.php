@extends('layouts.branch.app')

@section('title', translate('Product update'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/product.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Update Product Price')}}
                </span>
            </h2>
        </div>

        <form action="javascript:" method="post" id="set_price_form" enctype="multipart/form-data">
            @csrf

            @php($productByBranch = json_decode($product->product_by_branch, true))
            <?php
            if(isset($productByBranch[0])){
                $price = $productByBranch[0]['price'];
                $discountType = $productByBranch[0]['discount_type'];
                $discount = $productByBranch[0]['discount'];
            }else{
                $price = $product['price'];
                $discountType = $product['discount_type'];
                $discount = $product['discount'];
            }
            ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0 d-flex gap-2 align-items-center">
                                <i class="tio-premium"></i>
                                {{translate('Product Information')}}
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="card p-4" id="">
                                <div class="form-group">
                                    <label class="input-label">{{translate('name')}} (EN)</label>
                                    <input type="text" name="" value="{{$product['name']}}" class="form-control" placeholder="{{translate('Product Name')}}" readonly required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h4 class="mb-0 d-flex gap-2 align-items-center">
                                <i class="tio-dollar"></i>
                                {{translate('Stock Information')}}
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('Stock Type')}}</label>
                                        <select name="stock_type" class="form-control js-select2-custom" id="stock_type">
                                            @if($product->sub_branch_product)
                                                <option value="unlimited" {{ $product->sub_branch_product?->stock_type == 'unlimited' ? 'selected' : '' }}>{{translate('unlimited')}}</option>
                                                <option value="daily" {{ $product->sub_branch_product?->stock_type == 'daily' ? 'selected' : '' }}>{{translate('daily')}}</option>
                                                <option value="fixed" {{ $product->sub_branch_product?->stock_type == 'fixed' ? 'selected' : '' }}>{{translate('Fixed')}}</option>
                                            @else
                                                <option value="unlimited" {{ $mainBranchProduct?->stock_type == 'unlimited' ? 'selected' : '' }}>{{translate('unlimited')}}</option>
                                                <option value="daily" {{ $mainBranchProduct?->stock_type == 'daily' ? 'selected' : '' }}>{{translate('daily')}}</option>
                                                <option value="fixed" {{ $mainBranchProduct?->stock_type == 'fixed' ? 'selected' : '' }}>{{translate('Fixed')}}</option>
                                            @endif

                                        </select>
                                    </div>
                                </div>
                                <?php
                                $stock = $product->sub_branch_product?->stock ?? $mainBranchProduct?->stock ?? null;

                                ?>
                                <div class="col-sm-6 d-none" id="product_stock_div">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('Product Stock')}}</label>
                                        <input id="product_stock" type="number" name="product_stock" class="form-control"
                                               value="{{ $stock}}" placeholder="{{translate('Ex : 10')}}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card h-100 mt-3">
                        <div class="card-header">
                            <h4 class="mb-0 d-flex gap-2 align-items-center">
                                <i class="tio-dollar"></i>
                                {{translate('Price_Information')}}
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('default_Price')}}</label>
                                        <input type="number" value="{{ $price }}" min="0.1" name="price" class="form-control" step="0.01"
                                               placeholder="{{translate('Ex : 100')}}" required>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('discount_Type')}}</label>
                                        <select name="discount_type" class="form-control js-select2-custom">
                                            <option value="percent" {{$discountType =='percent'?'selected':''}}>{{translate('percent')}}</option>
                                            <option value="amount" {{$discountType =='amount'?'selected':''}}>{{translate('amount')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('discount')}}</label>
                                        <input type="number" min="0" value="{{$discount}}"
                                               name="discount" class="form-control" required placeholder="Ex : 100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-2">
                <div class="col-12">
                    <div class="card mt-5">
                        <div class="card-header">
                            <h4 class="mb-0 d-flex gap-2 align-items-center">
                                <i class="tio-canvas-text"></i>
                                {{ translate('product_variations') }}
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-12" >
                                    <div id="add_new_option">
                                        @if (isset($product->product_by_branch) && count($product->product_by_branch))
                                            @foreach($product->product_by_branch as $branch_product)
                                                @forelse ($branch_product->variations as $key_choice_options=>$item)
                                                    @include('branch-views.product.partials._new_variations',['item'=>$item,'key'=>$key_choice_options+1])
                                                @empty
                                                    <h5 class="text-center">{{ translate('This product has no variation') }}</h5>
                                                @endforelse
                                            @endforeach
                                        @else
                                            @if (isset($product->variations))
                                                @forelse (json_decode($product->variations,true) as $key_choice_options=>$item)
                                                    @if (isset($item["price"]))
                                                        <h5 class="text-center">{{ translate('This product have old variation, please update variation first') }}</h5>
                                                        @break
                                                    @else
                                                        @include('branch-views.product.partials._new_variations',['item'=>$item,'key'=>$key_choice_options+1])
                                                    @endif
                                                @empty
                                                    <h5 class="text-center">{{ translate('This product has no variation') }}</h5>
                                                @endforelse
                                            @endif
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-3 mt-4">
                <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                <button type="submit" class="btn btn-primary">{{translate('update')}}</button>
            </div>
        </form>
    </div>

@endsection

@push('script_2')
    <script>
        "use strict";

        function show_min_max(data){
            $('#min_max1_'+data).removeAttr("readonly");
            $('#min_max2_'+data).removeAttr("readonly");
            $('#min_max1_'+data).attr("required","true");
            $('#min_max2_'+data).attr("required","true");
        }
        function hide_min_max (data){
            $('#min_max1_'+data).val(null).trigger('change');
            $('#min_max2_'+data).val(null).trigger('change');
            $('#min_max1_'+data).attr("readonly","true");
            $('#min_max2_'+data).attr("readonly","true");
            $('#min_max1_'+data).attr("required","false");
            $('#min_max2_'+data).attr("required","false");
        }

        $('#set_price_form').on('submit', function () {
            var formData = new FormData(this);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('branch.product.set-price-update',[$product['id']])}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.errors) {
                        for (var i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        toastr.success('{{translate("product updated successfully!")}}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function () {
                            location.href = '{{route('branch.product.list')}}';
                        }, 2000);
                    }
                }
            });
        });

        @if($product->sub_branch_product)
        @php($stock = $product->sub_branch_product?->stock ?? null)
        @if($stock == 'daily' || $stock == 'fixed')
        $("#product_stock_div").removeClass('d-none')
        @endif
        @else
        @php($stock = $mainBranchProduct?->stock ?? null)

        @if($stock == 'daily' || $stock == 'fixed')
        $("#product_stock_div").removeClass('d-none')
        @endif
        @endif


        $("#stock_type").change(function(){
            if(this.value === 'daily' || this.value === 'fixed') {
                $("#product_stock_div").removeClass('d-none')
            }
            else {
                $("#product_stock_div").addClass('d-none')
            }
        });
    </script>
@endpush
