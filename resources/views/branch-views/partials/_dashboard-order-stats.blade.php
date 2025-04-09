
<div class="col-sm-6 col-lg-3">
    <a href="{{route('branch.orders.list',['pending'])}}" class="dashboard--card">
        <h5 class="dashboard--card__subtitle">{{translate('pending')}}</h5>
        <h2 class="dashboard--card__title">{{$data['pending']}}</h2>
        <img width="30" src="{{asset('public/assets/admin/img/icons/pending.png')}}" class="dashboard--card__img" alt="">
    </a>
</div>
<div class="col-sm-6 col-lg-3">
    <a href="{{route('branch.orders.list',['confirmed'])}}" class="dashboard--card">
        <h5 class="dashboard--card__subtitle">{{translate('confirmed')}}</h5>
        <h2 class="dashboard--card__title">{{$data['confirmed']}}</h2>
        <img width="30" src="{{asset('public/assets/admin/img/icons/confirmed.png')}}" class="dashboard--card__img" alt="">
    </a>
</div>
<div class="col-sm-6 col-lg-3">
    <a href="{{route('branch.orders.list',['processing'])}}" class="dashboard--card">
        <h5 class="dashboard--card__subtitle">{{translate('processing')}}</h5>
        <h2 class="dashboard--card__title">{{$data['processing']}}</h2>
        <img width="30" src="{{asset('public/assets/admin/img/icons/packaging.png')}}" class="dashboard--card__img" alt="">
    </a>
</div>
<div class="col-sm-6 col-lg-3">
    <a href="{{route('branch.orders.list',['out_for_delivery'])}}" class="dashboard--card">
        <h5 class="dashboard--card__subtitle">{{translate('out_for_delivery')}}</h5>
        <h2 class="dashboard--card__title">{{$data['out_for_delivery']}}</h2>
        <img width="30" src="{{asset('public/assets/admin/img/icons/out_for_delivery.png')}}" class="dashboard--card__img" alt="">
    </a>
</div>


<div class="col-sm-6 col-lg-3">
    <a class="order-stats order-stats_pending" href="{{route('branch.orders.list',['delivered'])}}">
        <div class="order-stats__content">
            <img width="20" src="{{asset('public/assets/admin/img/icons/delivered.png')}}" class="order-stats__img" alt="">
            <h6 class="order-stats__subtitle">{{__('messages.delivered')}}</h6>
        </div>
        <span class="order-stats__title">
            {{$data['delivered']}}
        </span>
    </a>
</div>
<div class="col-sm-6 col-lg-3">
    <a class="order-stats order-stats_canceled" href="{{route('branch.orders.list',['canceled'])}}">
        <div class="order-stats__content">
            <img width="20" src="{{asset('public/assets/admin/img/icons/canceled.png')}}" class="order-stats__img" alt="">
            <h6 class="order-stats__subtitle">{{translate('canceled')}}</h6>
        </div>
        <span class="order-stats__title">
            {{$data['canceled']}}
        </span>
    </a>
</div>
<div class="col-sm-6 col-lg-3">
    <a class="order-stats order-stats_returned" href="{{route('branch.orders.list',['returned'])}}">
        <div class="order-stats__content">
            <img width="20" src="{{asset('public/assets/admin/img/icons/returned.png')}}" class="order-stats__img" alt="">
            <h6 class="order-stats__subtitle">{{__('messages.returned')}}</h6>
        </div>
        <span class="order-stats__title">
            {{$data['returned']}}
        </span>
    </a>
</div>
<div class="col-sm-6 col-lg-3">
    <a class="order-stats order-stats_failed" href="{{route('branch.orders.list',['failed'])}}">
        <div class="order-stats__content">
            <img width="20" src="{{asset('public/assets/admin/img/icons/failed_to_deliver.png')}}" class="order-stats__img" alt="">
            <h6 class="order-stats__subtitle">{{translate('failed_to_deliver')}}</h6>
        </div>
        <span class="order-stats__title">
            {{$data['failed']}}
        </span>
    </a>
</div>
