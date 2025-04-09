@extends('layouts.blank')

@section('content')
    <div class="text-center text-white mb-4">
        <h2>{{ translate('eFood Software Installation') }}</h2>
        <h6 class="fw-normal">{{ translate('Please proceed step by step with proper data according to instructions') }}</h6>
    </div>

    <div class="pb-2 px-2 px-sm-5 mx-xl-4">
        <div class="progress cursor-pointer" role="progressbar" aria-label="eFood Software Installation"
             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" data-bs-toggle="tooltip"
             data-bs-placement="top" data-bs-custom-class="custom-progress-tooltip" data-bs-title="Intro Step!"
             data-bs-delay='{"hide":1000}'>
            <div class="progress-bar width-0-percent"></div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="p-4 my-md-3 mx-xl-4 px-md-5">
            <p class="text-center mb-4 top-info-text">{{ translate('Before starting the installation process please collect this
                information. Without this information, you would not be able to complete the installation process') }}</p>

            <div class="bg-light p-4 rounded mb-4">
                <div class="d-flex justify-content-between gap-1 align-items-center flex-wrap mb-4 pb-sm-3">
                    <h6 class="fw-bold text-uppercase fs m-0 letter-spacing" style="--fs: 14px">
                        {{ translate('Required Database Information') }}
                    </h6>
                    <a href="https://docs.6amtech.com/docs-efood/admin-panel/install-on-server"
                       target="_blank">{{ translate('Where to get this information') }}?
                    </a>
                </div>

                <div class="px-md-4 pb-sm-3">
                    <div class="row gy-sm-5 g-4">
                        <div class="col-sm-6">
                            <div class="d-flex gap-4 align-items-center flex-wrap">
                                <img
                                    src="{{asset('public/assets/installation')}}/assets/img/svg-icons/database-name.svg"
                                    alt="">
                                <div>{{ translate('Database Name') }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex gap-4 align-items-center flex-wrap">
                                <img
                                    src="{{asset('public/assets/installation')}}/assets/img/svg-icons/database-password.svg"
                                    alt="">
                                <div>{{ translate('Database Password') }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex gap-4 align-items-center flex-wrap">
                                <img
                                    src="{{asset('public/assets/installation')}}/assets/img/svg-icons/database-username.svg"
                                    alt="">
                                <div>{{ translate('Database Username') }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex gap-4 align-items-center flex-wrap">
                                <img
                                    src="{{asset('public/assets/installation')}}/assets/img/svg-icons/database-hostname.svg"
                                    alt="">
                                <div>{{ translate('Database Host Name') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <p>{{ translate('Are you ready to start installation process') }} ?</p>

                <a href="{{ route('step1',['token'=>bcrypt('step_1')]) }}" class="btn btn-dark px-sm-5">
                    {{ translate('Get Started') }}</a>
            </div>
        </div>
    </div>
@endsection
