@extends('layouts.branch.app')

@section('title', translate('Table Availability'))

@push('css_or_js')
<style>
    .bg-gray{
        background: #e4e4e4;
    }
    .bg-c1 {
        background-color: #FF6767 !important;
    }
    .c1 {
        color: #FF6767 !important;
    }
</style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/table.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Table_Availability')}}
                </span>
            </h2>
        </div>

        <div class="card card-body">
            <div class="d-flex gap-3 flex-wrap align-items-center justify-content-between mb-4">
            </div>
            <div class="table_box_list justify-content-center gap-2 gap-md-3" id="table_list">
                @if($tables != null)
                    @foreach($tables as $table)
                        <div class="dropright">
                            <div class="card table_hover-btn py-4 {{ $table['order'] != null ? 'bg-c1' : 'bg-gray'}} stopPropagation">
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
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha384-3ceskX3iaEnIogmQchP8opvBy3Mi7Ce34nWjpBIwVTHfGYWQS9jwHDVRnpKKHJg7" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function (){
            $('#select_branch').on('change', function (){
                var branch = this.value;
                console.log(branch);
                $('#table_list').html('');
                $('#table_title').html('');
                $.ajax({
                    url: "{{ url('admin/table/branch-table') }}",
                    type: "POST",
                    data: {
                        branch_id : branch,
                        _token : '{{ csrf_token() }}',
                    },
                    dataType : 'json',
                    success: function (result){
                        $('#table_list').html(result.view);
                    },
                });
            });
        });
    </script>
@endpush


