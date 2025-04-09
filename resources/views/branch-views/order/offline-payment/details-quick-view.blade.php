<button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <i class="tio-clear"></i>
</button>
<div class="details">
    <div>
        <div class="text-center">
            <div class="modal-header justify-content-center">
                <h4 class="modal-title pb-2">{{translate('Payment_Verification')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="card">
                <div class="modal-body">
                    <p class="text-danger">{{translate('Please Check & Verify the payment information whether it is correct or not before confirm the order.')}}</p>

                    <h5>{{translate('customer_Information')}}</h5>

                    <div class="card-body">
                        @if($order->is_guest == 0)
                            <p>{{ translate('name') }} : {{ $order->customer ? $order->customer->f_name.' '. $order->customer->l_name: ''}} </p>
                            <p>{{ translate('contact') }} : {{ $order->customer ? $order->customer->phone: ''}}</p>
                        @else
                            <p>{{ translate('guest_customer') }} </p>
                        @endif
                    </div>

                    <h5>{{translate('Payment_Information')}}</h5>
                    @php($payment = json_decode($order->offline_payment?->payment_info, true))
                    <div class="row card-body">
                        <div class="col-md-6">
                            <p>{{ translate('Payment_Method') }} : {{ $payment['payment_name'] }}</p>
                            @foreach($payment['method_fields'] as $fields)
                                @foreach($fields as $field_key => $field)
                                    <p>{{ $field_key }} : {{ $field }}</p>
                                @endforeach
                            @endforeach
                        </div>
                        <div class="col-md-6">
                            <p>{{ translate('payment_note') }} : {{ $payment['payment_note'] }}</p>
                            @foreach($payment['method_information'] as $infos)
                                @foreach($infos as $info_key => $info)
                                    <p>{{ $info_key }} : {{ $info }}</p>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="btn--container justify-content-center my-2 mx-3">
                @if($order->offline_payment?->status == 0)
                    <a type="reset" class="btn btn-secondary update-verification-status"  data-id="{{ $order['id'] }}" data-status="2">{{ translate('Payment_Did_Not_Received') }}</a>
                @endif
                <a type="submit" class="btn btn-primary update-verification-status"  data-id="{{ $order['id'] }}" data-status="1">{{ translate('Yes') }}, {{ translate('Payment_Received') }}</a>
            </div>

        </div>

    </div>
</div>
<script>
    $('.update-verification-status').on('click', function() {
        let id = $(this).data('id');
        let status = $(this).data('status');
        verify_offline_payment(id, status);
    });
</script>
