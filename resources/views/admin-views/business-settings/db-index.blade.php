@extends('layouts.admin.app')

@section('title', translate('Settings'))

@push('css_or_js')
    <script src="https://use.fontawesome.com/74721296a6.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/cloud-database.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('system_setup')}}
                </span>
            </h2>
        </div>

        @include('admin-views.business-settings.partials._system-settings-inline-menu')

        <div class="row g-2">
            <div class="col-12">
                <div class="alert alert--danger alert-danger mb-3" role="alert">
                    <i class="tio-info alert--icon"></i> <strong>{{translate('Note :')}}</strong>
                    {{translate('This_page_contains_sensitive_information.Make_sure_before_changing.')}}
                </div>

                <form action="{{route('admin.business-settings.web-app.system-setup.clean-db')}}" method="post"
                        enctype="multipart/form-data">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <div class="check--item-wrapper clean--database-checkgroup mt-0">
                                @foreach($tables as $key=>$table)
                                    <div class="check-item">
                                        <div class="form-group form-check">
                                            <input type="checkbox" name="tables[]" value="{{$table}}"
                                                class="form-check-input"
                                                id="business_section_{{$key}}">
                                            <label class="form-check-label text-dark" for="business_section_{{$key}}">{{ Str::limit(translate($table), 20) }}</label>
                                            <span class="badge-pill badge-secondary fz-12 ml-2">{{$rows[$key]}}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="btn--container mt-3">
                                <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                                <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                        class="btn btn-primary call-demo">{{translate('Clean')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        $(document).ready(function () {
            $("#purchase_code_div").click(function () {
                var type = $('#purchase_code').get(0).type;
                if (type === 'password') {
                    $('#purchase_code').get(0).type = 'text';
                } else if (type === 'text') {
                    $('#purchase_code').get(0).type = 'password';
                }
            });
        })
    </script>

    <script>
        $("form").on('submit',function(e) {
            e.preventDefault();
            Swal.fire({
                title: '{{translate('Are you sure?')}}',
                text: "{{translate('Sensitive_data! Make_sure_before_changing.')}}",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2750E6',
                cancelButtonColor: 'default',
                cancelButtonText: '{{translate('No?')}}',
                confirmButtonText:'{{translate('Yes?')}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    this.submit();
                }else{
                    e.preventDefault();
                    toastr.success("{{translate('Cancelled')}}");
                    location.reload();
                }
            })
        });
    </script>
@endpush
