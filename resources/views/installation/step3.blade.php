@extends('layouts.blank')

@section('content')
    <div class="text-center text-white mb-4">
        <h2>{{ translate('eFood Software Installation') }}</h2>
        <h6 class="fw-normal">{{ translate('Please proceed step by step with proper data according to instructions') }}</h6>
    </div>

    <div class="pb-2">
        <div class="progress cursor-pointer" role="progressbar" aria-label="eFood Software Installation"
             aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" data-bs-toggle="tooltip"
             data-bs-placement="top" data-bs-custom-class="custom-progress-tooltip" data-bs-title="Third Step!"
             data-bs-delay='{"hide":1000}'>
            <div class="progress-bar width-60-percent"></div>
        </div>
    </div>

    <div class="card mt-4 position-relative">
        <div class="d-flex justify-content-end mb-2 position-absolute top-end">
            <a href="#" class="d-flex align-items-center gap-1">
                <span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                      data-bs-title="Follow our documentation">
                    <img src="{{asset('public/assets/installation')}}/assets/img/svg-icons/info.svg" alt="" class="svg">
                </span>
            </a>
        </div>
        <div class="p-4 mb-md-3 mx-xl-4 px-md-5">
            <div class="d-flex align-items-center column-gap-3 flex-wrap">
                <h5 class="fw-bold fs text-uppercase">{{ translate('Step 3.') }} </h5>
                <h5 class="fw-normal">{{ translate('Update Database Information') }}</h5>
            </div>
            <p class="mb-4">{{ translate('Provide your database information.') }}
                <a href="https://docs.6amtech.com/docs-efood/admin-panel/install-on-server" target="_blank">
                    {{ translate('Where to get this information ?') }}</a>
            </p>

            @if (isset($error) || session()->has('error'))
                <div class="row mt-5">
                    <div class="col-md-12">
                        <div class="alert alert-danger">
                            {{ translate('Invalid Database Credentials or Host. Please check your database credentials carefully.') }}
                        </div>
                    </div>
                </div>
            @elseif(session()->has('success'))
                <div class="row mt-5">
                    <div class="col-md-12">
                        <div class="alert alert-success">
                            <strong>{{session('success')}}</strong>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('install.db',['token'=>bcrypt('step_4')]) }}">
                @csrf
                <div class="bg-light p-4 rounded mb-4">
                    <div class="px-xl-2 pb-sm-3">
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <div class="from-group">
                                    <label for="database-host"
                                           class="d-flex align-items-center gap-2 mb-2">{{ translate('Database Host') }}</label>
                                    <input type="text" id="database-host" class="form-control" name="DB_HOST"
                                           required
                                           placeholder="Ex: localhost">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="from-group">
                                    <label for="database-name"
                                           class="d-flex align-items-center gap-2 mb-2">{{ translate('Database Name') }}</label>
                                    <input type="text" id="database-name" class="form-control" name="DB_DATABASE"
                                           required
                                           placeholder="Ex: project-name-db">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="from-group">
                                    <label for="database-username"
                                           class="d-flex align-items-center gap-2 mb-2">{{ translate('Database Username') }}</label>
                                    <input type="text" id="database-username" class="form-control"
                                           name="DB_USERNAME" required
                                           placeholder="Ex: root">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="from-group">
                                    <label for="database-password" class="d-flex align-items-center gap-2 mb-2">{{ translate('Database Password') }}</label>
                                    <div class="input-inner-end-ele position-relative">
                                        <input type="password" id="database-password" min="8"
                                               autocomplete="new-password" class="form-control" name="DB_PASSWORD"
                                               required
                                               placeholder="Ex: password">
                                        <div class="togglePassword">
                                            <img
                                                src="{{asset('public/assets/installation')}}/assets/img/svg-icons/eye.svg"
                                                alt="" class="svg eye">
                                            <img
                                                src="{{asset('public/assets/installation')}}/assets/img/svg-icons/eye-off.svg"
                                                alt=""
                                                class="svg eye-off">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-dark px-sm-5">{{ translate('Continue') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
