@extends('layouts.admin.app')

@section('title', translate('Order Details'))

@section('content')
<div class="content container-fluid">
    <div class="d-flex justify-content-between gap-2 align-items-center mb-4">
        <h2 class="h1 mb-0 d-flex align-items-center gap-2">
            <span class="page-header-title">{{ translate('Next Day Orders') }}</span>
        </h2>
    </div>
    <div class="row g-3">
        <div class="col-12">
            <div class="card card-body">

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="py-4">

                    <div class="table-responsive datatable-custom">
                        @if(count($orders) == 0)
                            <p>No orders for the next day.</p>
                        @else
                            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ translate('Product Name') }}</th>

                                        <th >{{ translate('Total Quantity') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $productName => $totalQuantity)
                                    <tr>
                                        <td>{{ $productName }}</td>
                                        <td>{{ $totalQuantity }}</td>
                                    </tr>
                                @endforeach
                                    {{-- @foreach($orders as $index=>$order)
                                        <tr>
                                            <td>{{$index+1}}</td>
                                            <td>{{$order}}</td>

                                            <td>
                                              @foreach ($order->details as $detail)
                                                {{json_decode($detail->product_details,true)['name']}}
                                                {{!$loop->last ? ',' : ''}}
                                              @endforeach
                                            </td>
                                            <td>
                                                @foreach ($order->details as $detail)
                                                  {{$detail->quantity}}
                                                {{!$loop->last ? ',' : ''}}

                                                @endforeach
                                              </td>
                                        </tr>
                                    @endforeach --}}
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

