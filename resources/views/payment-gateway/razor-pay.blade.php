@extends('payment-gateway.layouts.master')

@push('script')
    <style>
        .razorpay-payment-button{
            padding: 10px; text-align: center; text-decoration: none; border-radius: 4px;
        }
    </style>
@endpush

@section('content')
    <center><h1>Please do not refresh this page...</h1></center>


    <form action="{!!route('razor-pay.payment',['payment_id'=>$data->id])!!}" id="form" method="POST"  style="text-align: center">
    @csrf
    <!-- Note that the amount is in paise = 50 INR -->
        <!--amount need to be in paisa-->
        <script src="https://checkout.razorpay.com/v1/checkout.js"
                data-key="{{ config()->get('razor_config.api_key') }}"
                data-amount="{{round($data->payment_amount, 2)*100}}"
                data-buttontext="Pay {{ round($data->payment_amount, 2) . ' ' . $data->currency_code }}"
                data-name="{{$business_name}}"
                data-description="{{$data->payment_amount}}"
                data-image="{{$business_logo}}"
                data-prefill.name="{{$payer->name ?? ''}}"
                data-prefill.email="{{$payer->email ?? ''}}"
                data-theme.color="#ff7529">
        </script>
        <?php
            $token_string = 'payment_method=' . $data->payment_method . '&&attribute_id=' . $data->attribute_id . '&&transaction_reference=' . $data->transaction_id;
            $payment_flag = 'fail';
        ?>

        <button class="btn btn-block" id="pay-button" type="submit" style="display:none"></button>
        <a href="{{ ($data['external_redirect_link'] . '?flag=' . $payment_flag . '&&token=' . base64_encode($token_string)) }}" style="padding: 10px; background-color: #dc3545; color: white; text-align: center; text-decoration: none; border-radius: 4px;">Cancel</a>
    </form>

    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("pay-button").click();
        });
    </script>
@endsection
