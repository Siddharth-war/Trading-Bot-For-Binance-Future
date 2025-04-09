@extends('layouts.admin.app')

@section('title', translate('Delivery_Fee_Setup'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/business_setup2.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('business_setup')}}
                </span>
            </h2>
        </div>

        @include('admin-views.business-settings.partials._business-setup-inline-menu')

        <h4>{{ translate('Delivery Charge Setup') }}</h4>
        <ul class="nav nav-tabs" id="branchTabs" role="tablist">
            @foreach($branches as $branch)
                <li class="nav-item">
                    <a class="nav-link" id="branch{{ $branch->id }}-tab" data-toggle="tab" href="#branch{{ $branch->id }}" role="tab"
                       aria-controls="branch{{ $branch->id }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                        {{ $branch->name }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content" id="branchTabsContent">
            @foreach($branches as $branch)
                <div class="tab-pane fade" id="branch{{ $branch->id }}" role="tabpanel" aria-labelledby="branch{{ $branch->id }}-tab">
                    @include('admin-views.business-settings.partials.delivery_charge_form', ['branch' => $branch])
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('script_2')
    <script>

        $(document).ready(function() {
            $('#addAreaModal').on('shown.bs.modal', function () {
                $('#areaName').trigger('focus');
            });

            $('[data-toggle="modal"]').on('click', function() {
                var branchId = $(this).data('branch-id');
                $('.branchIdInput').val(branchId);
            });

            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var tabId = $(e.target).attr('href'); // e.g., #branch1
                localStorage.setItem('activeTab', tabId);
            });

            var activeTab = localStorage.getItem('activeTab');
            var defaultTab = '#branch1'; // Default tab to select if the active one is deleted

            if (activeTab && $(activeTab).length) {
                $('#branchTabs a[href="' + activeTab + '"]').tab('show');
            } else {
                $('#branchTabs a[href="' + defaultTab + '"]').tab('show');
            }
        });

    </script>

@endpush
