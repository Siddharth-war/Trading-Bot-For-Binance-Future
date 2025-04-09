@if($tables != null)
    @foreach($tables as $table)
        <div class="dropright">
            <div class="card table_hover-btn py-4 {{ $table['order'] != null ? 'bg-c1' : 'bg-gray'}} stopPropagation"
            >
                <div class="card-body mx-3 position-relative text-center">
                    <h3 class="card-title mb-2">{{ translate('table') }}</h3>
                    <h5 class="card-title mb-1">{{ $table['number'] }}</h5>
                    <h5 class="card-title mb-1">{{ translate('capacity') }}: {{ $table['capacity'] }}</h5>
                </div>
            </div>
            <div class="table_hover-menu px-3">
                <h3 class="mb-3">{{ translate('Table - D2 ') }}</h3>
                @if(($table['order'] != null))
                    @foreach($table['order'] as $order)
                        <div class="fz-14 mb-1">{{ translate('order id') }}: <strong>{{ $order['id'] }}</strong></div>
                    @endforeach
                @else
                    <div class="fz-14 mb-1">{{ translate('current status') }} - <strong>{{ translate('empty') }}</strong></div>
                    <div class="fz-14 mb-1">{{ translate('any reservation') }} - <strong>{{ translate('N/A') }}</strong></div>
                @endif
            </div>
        </div>
    @endforeach
@else
    <div class="col-md-12 text-center">
        <h4 class="">{{translate('This branch has no table')}}</h4>
    </div>
@endif
