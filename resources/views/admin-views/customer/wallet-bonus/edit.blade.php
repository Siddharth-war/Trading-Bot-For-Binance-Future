@extends('layouts.admin.app')

@section('title',translate('Wallet Bonus Update'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/wallet.png')}}" class="width-24" alt="">
                </span>
                <span class="ml-2">{{translate('Wallet Bonus Update')}}</span>
            </h1>
        </div>

        <div class="row g-2">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.customer.wallet.bonus.update', [$bonus['id']])}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 col-lg-6 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{ translate('Bonus_Title') }}</label>
                                        <input type="text" name="title" class="form-control" value="{{ $bonus['title'] }}" placeholder="{{ translate('Ex:_EID_Dhamaka') }}">
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-6 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{ translate('Short Description') }}</label>
                                        <input type="text" name="description" class="form-control" value="{{ $bonus['description'] }}" placeholder="{{ translate('Ex:_EID_Dhamaka') }}">
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('Bonus_Type')}}</label>
                                        <select name="bonus_type" class="form-control" id="bonus_type" required>
                                            <option value="amount" {{$bonus['bonus_type']=='amount'?'selected':''}}>{{translate('amount')}}</option>
                                            <option value="percentage" {{$bonus['bonus_type']=='percentage'?'selected':''}}>{{translate('percentage')}} (%)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('Bonus_Amount')}}
                                            <span  class="d-none" id='currency_symbol'>({{Helpers::currency_symbol() }})</span>
                                            <span id="percentage">(%)</span>
                                            <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ translate('Set_the_bonus_amount/percentage_a_customer_will_receive_after_adding_money_to_his_wallet.') }}">
                                                <i class="tio-info-outined"></i>
                                            </span>
                                        </label>
                                        <input type="number" step="0.01" min="1" max="999999999999.99"  placeholder="{{ translate('Ex:_100') }}"  name="bonus_amount"
                                               id="bonus_amount" value="{{$bonus['bonus_amount']}}" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('Minimum_Add_Money_Amount')}} ({{Helpers::currency_symbol() }})
                                            <span
                                                class="input-label-secondary text--title" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ translate('Set_the_minimum_add_money_amount_for_a_customer_to_be_eligible_for_the_bonus.') }}">
                                                <i class="tio-info-outined"></i>
                                            </span>
                                        </label>
                                        <input type="number" step="0.01" min="1" max="999999999999.99" placeholder="{{ translate('Ex:_10') }}" name="minimum_add_amount"
                                               id="minimum_add_amount" value="{{$bonus['minimum_add_amount']}}" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6" id="maximum_bonus_amount_div">
                                    <div class="form-group m-0">
                                        <label class="input-label" for="exampleFormControlInput1">
                                            {{translate('Maximum_Bonus')}} ({{Helpers::currency_symbol() }})
                                            <span
                                                class="input-label-secondary text--title" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ translate('Set_the_maximum_bonus_amount_a_customer_can_receive_for_adding_money_to_his_wallet.') }}">
                                                <i class="tio-info-outined"></i>
                                            </span>
                                        </label>
                                        <input type="number" min="0" max="999999999999.99" step="0.01" value="{{$bonus['maximum_bonus_amount']}}" name="maximum_bonus_amount"
                                               id="maximum_bonus_amount" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('start_date')}}</label>
                                        <input type="date" name="start_date" class="form-control" value="{{date('Y-m-d',strtotime($bonus['start_date']))}}"  id="date_from" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('expire_date')}}</label>
                                        <input type="date" name="end_date" class="form-control" value="{{date('Y-m-d',strtotime($bonus['end_date']))}}"  id="date_to" required>
                                    </div>
                                </div>
                            </div>
                            <div class="btn--container justify-content-end">
                                <button type="reset" id="reset_btn" class="btn btn-secondary">{{translate('reset')}}</button>
                                <button type="submit" class="btn btn-primary">{{translate('update')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @endsection

        @push('script_2')
            <script src="{{asset('public/assets/admin/js/wallet-bonus.js')}}"></script>
            <script>
                "use strict";

                @if($bonus['bonus_type']=='amount')
                    $('#maximum_bonus_amount').removeAttr("required", true);
                    $('#maximum_bonus_amount_div').addClass('d-none');
                @endif

            </script>
        @endpush
