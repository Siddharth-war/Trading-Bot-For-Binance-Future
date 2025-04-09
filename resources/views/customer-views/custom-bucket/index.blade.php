@extends('layouts.customer.app')

@section('title', translate('Manage Bucket'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex justify-content-between gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                {{-- <img width="20" class="avatar-img" src="{{ asset('public/assets/admin/img/icons/group.png') }}" alt=""> --}}
                <span class="page-header-title">{{ translate('Manage Buckets') }}</span>
            </h2>
            <a href="{{ route('customer.custom-bucket.bucket_create') }}" class="btn btn-primary mb-3">{{ translate('Add New Bucket') }}</a>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <div class="card card-body">

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="py-4">
                        <div class="table-responsive datatable-custom">
                            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ translate('Name') }}</th>
                                        <th>{{ translate('Product Name') }}</th>


                                        <th class="text-center">{{ translate('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customBuckets as $key => $customBucket)
                                        <tr>
                                            <td>{{ $customBuckets->firstItem() + $key }}</td>
                                            <td class="text-capitalize">{{ $customBucket->name }}</td>

                                            <td class="text-capitalize">
                                                @if(!empty($customBucket->custom_products))
                                                @foreach ($customBucket->custom_products as $qty)
                                                    {{$customBucket->products->find($qty->product_id)?->name}} (qty: {{ $qty->qty}})
                                                    {{!$loop->last ? ',' : ''}}
                                                @endforeach
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="{{ route('customer.custom-bucket.bucket_placeOrder', $customBucket->id) }}" class="btn btn-outline-info btn-sm edit square-btn">
                                                        <i class="tio-shopping"></i>
                                                    </a>

                                                    <a href="{{ route('customer.custom-bucket.bucket_edit', $customBucket->id) }}" class="btn btn-outline-info btn-sm edit square-btn">
                                                        <i class="tio-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger btn-sm delete square-btn form-alert"
                                                        data-id="group-{{ $customBucket->id }}" data-message="{{ translate('Want to delete this?') }}">
                                                        <i class="tio-delete"></i>
                                                    </button>
                                                </div>
                                                <form action="{{ route('customer.custom-bucket.bucket_delete', $customBucket->id) }}" method="POST" id="group-{{ $customBucket->id }}">
                                                    @csrf @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="table-responsive mt-4 px-3">
                            <div class="d-flex justify-content-lg-end">
                                {!! $customBuckets->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script src="{{ asset('public/assets/admin/js/select2.min.js') }}"></script>
@endpush

@push('script_2')
    <script src="{{ asset('public/assets/admin/js/image-upload.js') }}"></script>
@endpush
