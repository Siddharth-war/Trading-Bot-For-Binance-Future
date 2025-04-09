<div class="card-header">
    <h5 class="card-header-title">
        <i class="tio-company"></i> {{translate('top_store_by_order_received')}}
    </h5>
    <i class="tio-award-outlined text-muted-size"></i>
</div>

<div class="card-body">
    <div class="row">
        @foreach($topStoreByOrderReceived as $key=>$item)
            @php($shop=\App\Model\Shop::where('seller_id',$item['seller_id'])->first())
            @if(isset($shop))
                <div class="col-6 col-md-4 mt-2 custom-div-design redirect-url"
                     data-url="{{route('admin.sellers.view',$item['seller_id'])}}">
                    <div class="grid-card custom-grid-card">
                        <label class="label_1">Orders : {{$item['count']}}</label>
                        <div class="text-center mt-6">
                            <img class="custom-img-design"
                                 src="{{asset('storage/app/public/shop/'.$shop->image  ?? '' )}}">
                        </div>
                        <div class="text-center mt-2">
                            <span class="custom-text-size">{{$shop['name']??'Not exist'}}</span>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>
