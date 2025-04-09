@extends('layouts.blank')

@push('css_or_js')
    <style>
        input{
            display: none!important;
        }
    </style>
@endpush

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12 mt-3">
                <div class="card mt-3">
                    <div class="card-body text-center">
                        {!! \App\Model\BusinessSetting::where(['key'=>'privacy_policy'])->first()->value !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function () {
            document.getElementsByClassName("ql-editor")[0].contentEditable = "false";
        });
    </script>
@endpush

