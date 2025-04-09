@extends('layouts.admin.app')

@section('title', translate('Offline Payment Method'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/business_setup2.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Add Offline Payment Method')}}
                </span>
            </h2>
        </div>

        <form action="{{route('admin.business-settings.web-app.third-party.offline-payment.store')}}" method="post">
            @csrf

            <div class="d-flex justify-content-end my-3">
                <div class="d-flex gap-2 justify-content-end align-items-center text-primary font-weight-bold" id="bkashInfoModalButton">
                    {{ translate('Section View ') }}<i class="tio-info" data-toggle="tooltip" title="Section View Info"></i>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header flex-wrap gap-2">
                    <div class="justify-content-between align-items-center gy-2">
                        <h4 class="mb-0">
                            <i class="tio-settings mr-1"></i>
                            {{translate('Payment Information')}}
                        </h4>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" id="add-payment-method-field"><i class="tio-add"></i>{{translate('Add New Field')}}</button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="d-flex align-items-end gap-3 mb-4">
                        <div class="flex-grow-1">
                            <label class="input-label">{{translate('Payment Method Name')}}</label>
                            <input type="text" maxlength="255" name="method_name" id="method_name" class="form-control"
                                   placeholder="{{ translate('ABC Company') }}" required>
                        </div>
                    </div>
                    <div class="d-flex align-items-end gap-3 mb-4 flex-wrap">
                        <div class="flex-grow-1">
                            <div class="">
                                <label class="input-label">{{translate('Title')}} </label>
                                <input type="text" name="field_name[]" class="form-control" maxlength="255" placeholder="{{ translate('bank_Name') }}" required>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="">
                                <label class="input-label">{{translate('Data')}} </label>
                                <input type="text" name="field_data[]" class="form-control" maxlength="255" placeholder="{{ translate('ABC_Bank') }}" required>
                            </div>
                        </div>
                    </div>

                    <div id="method-field"></div>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-3 mt-4">
                <div class="d-flex gap-2 justify-content-end text-primary align-items-center font-weight-bold" id="paymentInfoModalButton">
                    {{ translate('Section View') }} <i class="tio-info" data-toggle="tooltip" title="Section View Info"></i>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header flex-wrap gap-2">
                    <div class="justify-content-between align-items-center gy-2">
                        <h4 class="mb-0 align-items-center">
                            <i class="tio-settings mr-1"></i>
                            {{translate('Required Information from Customer')}}
                            <i class="tio-info ml-1" data-toggle="tooltip" title="{{ translate('Must need to add at least one input field') }}"></i>
                        </h4>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" id="add-payment-information-field"><i class="tio-add"></i>{{translate('add_New_Field')}}</button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="d-flex align-items-end gap-3 mb-4">
                        <div class="flex-grow-1">
                            <label class="input-label">{{translate('Payment Note')}}</label>
                            <textarea name="payment_note" class="form-control" placeholder="{{ translate('payment_Note') }}" id="payment_note"
                                      data-toggle="tooltip" title="Field is not editable" style="background-color: #e9ecef;" readonly></textarea>
                        </div>
                    </div>

                    <div id="information-field"></div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-4">
                <button type="reset" id="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                <button type="submit"  class="btn btn-primary">{{translate('submit')}}</button>
            </div>
        </form>
    </div>

    <div class="modal fade" id="sectionViewModal" tabindex="-1" aria-labelledby="sectionViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center flex-column gap-3 text-center">
                    <h3>{{ translate('Offline Payment') }}</h3>
                    <img width="100" src="{{asset('public/assets/admin-module/img/offline_payment.png')}}" alt="">
                    <p class="text-muted">{{ translate('Pay your bill using the information below and') }} <br class="d-none d-sm-block"> {{ translate('input the information in the form') }}</p>
                </div>

                <div class="rounded p-4 mt-3" id="offline_payment_top_part">
                    <div class="d-flex justify-content-between gap-2 mb-3">
                        <h4>{{ translate('Bkash Info') }}</h4>
                        <div class="text-primary d-flex align-items-center gap-2">
                            {{ translate('Pay on this account') }}
                            <i class="tio-checkmark-circle"></i>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex gap-3 align-items-center">
                            <span>{{ translate('Payment Method') }}</span>   :  <span>{{ translate('Bkash') }}</span>
                        </div>
                        <div class="d-flex gap-3 align-items-center">
                            <span>{{ translate('Phone Number') }}</span>   :  <a href="tel:880124165456" class="text-dark">+880124165456</a>
                        </div>
                        <div class="d-flex gap-3 align-items-center">
                            <span>A/C Holder</span>   :  <span>Jhone Doe</span>
                        </div>
                    </div>
                </div>

                <div class="rounded p-4 mt-3 mt-4" id="offline_payment_bottom_part">
                    <h2 class="text-center mb-4">{{ translate('Amount') }} : $2,560</h2>

                    <h4 class="mb-3">{{ translate('Payment Info') }}</h4>
                    <div class="d-flex flex-column gap-3">
                        <input type="text" class="form-control" name="payment_by" id="payment_by" placeholder="Payment By">
                        <input type="tel" class="form-control" name="bkash_no" id="bkash_no" placeholder="Bkash Phone No.">
                        <input type="text" class="form-control" name="trx_id" id="trx_id" placeholder="Transaction ID*">
                        <textarea name="payment_note" id="payment_note" class="form-control" rows="10" placeholder="Note"></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-3">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        function openModal(contentArgument) {
            if (contentArgument === "bkashInfo") {
                $("#sectionViewModal #offline_payment_top_part").addClass("active");
                $("#sectionViewModal #offline_payment_bottom_part").removeClass("active");
            } else {
                $("#sectionViewModal #offline_payment_top_part").removeClass("active");
                $("#sectionViewModal #offline_payment_bottom_part").addClass("active");
            }

            $("#sectionViewModal").modal("show");
        }

        $(document).ready(function() {
            $("#bkashInfoModalButton").on('click', function() {
                console.log("something");
                var contentArgument = "bkashInfo";
                openModal(contentArgument);
            });
            $("#paymentInfoModalButton").on('click', function() {
                var contentArgument = "paymentInfo";
                openModal(contentArgument);
            });
        });
    </script>

    <script>

        function delete_input_field(row_id) {
            $( `#field-row--${row_id}` ).remove();
            count--;
        }

        function delete_information_input_field(row_id) {
            $( `#information-row--${row_id}` ).remove();
            count_info--;
        }

        jQuery(document).ready(function ($) {
            count = 1;
            $('#add-payment-method-field').on('click', function (event) {
                if(count <= 15) {
                    event.preventDefault();

                    $('#method-field').append(
                        `<div class="d-flex align-items-end gap-3 mb-4 flex-wrap" id="field-row--${count}">
                            <div class="flex-grow-1">
                                <div class="">
                                    <label class="input-label">{{translate('Input Field Name')}} </label>
                                    <input type="text" name="field_name[]" class="form-control" maxlength="255" placeholder="{{ translate('Bank Name') }}" id="field_name_${count}" required>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="">
                                    <label class="input-label">{{translate('Input Data')}} </label>
                                    <input type="text" name="field_data[]" class="form-control" maxlength="255" placeholder="{{ translate('ABC Bank') }}" required>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 justify-content-end" data-toggle="tooltip" data-placement="top" title="{{translate('Remove the input field')}}">
                                <div class="btn btn-outline-danger delete" onclick="delete_input_field(${count})">
                                    <i class="tio-delete"></i>
                                </div>
                            </div>
                        </div>`
                    );

                    count++;
                } else {
                    Swal.fire({
                        title: '{{translate('Reached maximum')}}',
                        confirmButtonText: '{{translate('ok')}}',
                    });
                }
            })


            count_info = 1;
            $('#add-payment-information-field').on('click', function (event) {
                if(count_info <= 15) {
                    event.preventDefault();

                    $('#information-field').append(
                        `<div class="d-flex align-items-end gap-3 mb-4 flex-wrap" id="information-row--${count_info}">
                            <div class="flex-grow-1">
                                <div class="">
                                    <label class="input-label">{{translate('Input Field Name')}} </label>
                                    <input type="text" name="information_name[]" class="form-control" maxlength="255" placeholder="" id="information_name_${count_info}" required>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="">
                                    <label class="input-label">{{translate('Input Field Placeholder/Hints')}} </label>
                                    <input type="text" name="information_placeholder[]" class="form-control" maxlength="255" placeholder="" required>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-10 mb-2">
                                    <input class="custom-control" type="checkbox" name="information_required[]">
                                    <label class="input-label mb-0">{{translate('Is Required')}}? </label>
                                </div>
                            </div>
                            <div class="" data-toggle="tooltip" data-placement="top" title="{{translate('Remove the input field')}}">
                                <div class="btn btn-outline-danger delete" onclick="delete_information_input_field(${count_info})">
                                    <i class="tio-delete"></i>
                                </div>
                            </div>
                        </div>`
                    );

                    count_info++;
                } else {
                    Swal.fire({
                        title: '{{translate('Reached maximum')}}',
                        confirmButtonText: '{{translate('ok')}}',
                    });
                }
            })

            $('#reset').on('click', function (event) {
                $('#method-field').html("");
                $('#method_name').val("");

                $('#information-field').html("");
                $('#payment_note').val("");
                count=1;
                count_info=1;
            })
        });
    </script>

@endpush
