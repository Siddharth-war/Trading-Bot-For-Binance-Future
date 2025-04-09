<div class="card-header d-flex justify-content-between gap-10">
    <h5 class="mb-0">{{translate('Top_Customer')}}</h5>
    <a href="{{route('admin.customer.list')}}" class="btn-link">{{translate('View_All')}}</a>
</div>

<div class="card-body">
    <div class="d-flex flex-column gap-3">
        @foreach($top_customer as $key=>$item)
            @if(isset($item->customer))
                <a class="d-flex justify-content-between align-items-center text-dark" href='{{route('admin.customer.view', [$item['user_id']])}}'>
                    <div class="media align-items-center gap-3">
                        <img class="rounded avatar avatar-lg"
                                src="{{ $item->customer->imageFullPath }}">
                        <div class="media-body d-flex flex-column custom-media-body">
                            <span class="font-weight-semibold text-capitalize">{{$item->customer['f_name']??'Not exist'}}</span>
                            <span class="text-dark">{{ $item->customer['phone']?? translate('Not exist') }}</span>
                        </div>
                    </div>
                    <span class="px-2 py-1 badge-soft-c1 font-weight-bold fz-12 rounded lh-1">{{translate('Orders : ')}}{{$item['count']}}</span>
                </a>
            @endif
        @endforeach
    </div>
</div>
