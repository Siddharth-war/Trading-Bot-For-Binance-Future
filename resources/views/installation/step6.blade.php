@extends('layouts.blank')

@section('content')
    <div class="text-center text-white mb-4">
        <h2>{{ translate('eFood Software Installation') }}</h2>
        <h6 class="fw-normal">{{ translate('All Done, Great Job. Your software is ready to run.') }}</h6>
    </div>

    <div class="card mt-4">
        <div class="p-4 mb-md-3 mx-xl-4 px-md-5">
            <div class="p-4 rounded mb-4 text-center">
                <h5 class="fw-bold">{{ translate('Configure the following setting to run the system properly') }}</h5>

                <ul class="list-group mar-no mar-top bord-no">
                    <li class="list-group-item">{{ translate('Business Setting') }}</li>
                    <li class="list-group-item">{{ translate('Mail Setting') }}</li>
                    <li class="list-group-item">{{ translate('Payment Method Configuration') }}</li>
                    <li class="list-group-item">{{ translate('SMS Module Configuration') }}</li>
                    <li class="list-group-item">{{ translate('3rd Party APIs<') }}<li>
                </ul>
            </div>

            <div class="text-center">
                <a href="{{ env('APP_URL') }}/admin/auth/login" target="_blank" class="btn btn-dark px-sm-5">{{ translate('Admin Panel') }}</a>
                <a href="{{ env('APP_URL') }}/branch/auth/login" target="_blank" class="btn btn-secondary px-sm-5">{{ translate('Branch Panel') }}</a>
            </div>
        </div>
    </div>
@endsection
