@extends('layouts.admin.app')

@section('title',translate('Wallet Bonus Setup'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/wallet.png')}}" class="width-24" alt="">
                </span>
                <span class="ml-2">{{translate('Wallet Bonus Setup')}}</span>
            </h1>
            <div class="text-primary d-flex flex-wrap align-items-center" type="button" data-toggle="modal" data-target="#how-it-works">
                <strong class="mr-2">{{translate('See_how_it_works!')}}</strong>
                <div class="blinkings">
                    <i class="tio-info-outined"></i>
                </div>
            </div>
        </div>

        <div class="row g-2">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.customer.wallet.bonus.store')}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 col-lg-6 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{ translate('Bonus_Title') }}</label>
                                        <input type="text" name="title" class="form-control" placeholder="{{ translate('Ex:_EID_Dhamaka') }}">
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-6 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{ translate('Short Description') }}</label>
                                        <input type="text" name="description" class="form-control" placeholder="{{ translate('Ex:_EID_Dhamaka') }}">
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('Bonus_Type')}}</label>
                                        <select name="bonus_type" class="form-control" id="bonus_type" required>
                                            <option value="percentage">{{translate('percentage')}} (%)</option>
                                            <option value="amount">{{translate('amount')}}</option>
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
                                               id="bonus_amount" class="form-control" required>
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
                                               id="minimum_add_amount" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6" id="maximum_bonus_amount_div">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('Maximum_Bonus')}} ({{Helpers::currency_symbol() }})
                                            <span
                                                class="input-label-secondary text--title" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ translate('Set_the_maximum_bonus_amount_a_customer_can_receive_for_adding_money_to_his_wallet.') }}">
                                                <i class="tio-info-outined"></i>
                                            </span>

                                        </label>
                                        <input type="number" step="0.01" min="1" max="999999999999.99"  placeholder="{{ translate('Ex:_1000') }}" name="maximum_bonus_amount" id="maximum_bonus_amount" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('start_date')}}</label>
                                        <input type="date" name="start_date" class="form-control" id="date_from" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('expire_date')}}</label>
                                        <input type="date" name="end_date" class="form-control" id="date_to" required>
                                    </div>
                                </div>
                            </div>
                            <div class="btn--container justify-content-end">
                                <button type="reset" id="reset_btn" class="btn btn-secondary">{{translate('reset')}}</button>
                                <button type="submit" class="btn btn-primary">{{translate('submit')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-top px-card pt-4">
                        <div class="row justify-content-between align-items-center gy-2">
                            <div class="col-sm-4 col-md-6 col-lg-8">
                                <h5 class="d-flex align-items-center gap-2 mb-0">
                                    {{translate('Bonus Table')}}
                                    <span class="badge badge-soft-dark rounded-50 fz-12">{{ $bonuses->total() }}</span>
                                </h5>
                            </div>
                            <div class="col-sm-8 col-md-6 col-lg-4">
                                <form action="{{url()->current()}}" class="mb-0" method="GET">
                                    <div class="input-group">
                                        <input id="datatableSearch_" type="search" name="search" class="form-control" placeholder="{{translate('Search by title or description')}}" aria-label="Search" value="{{$search}}" required="" autocomplete="off">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">
                                                {{translate('Search')}}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="py-4">
                        <div class="table-responsive datatable-custom">
                            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                <tr>
                                    <th class="border-0">{{translate('sl')}}</th>
                                    <th class="border-0">{{translate('bonus_title')}}</th>
                                    <th class="border-0">{{translate('bonus_info')}}</th>
                                    <th class="border-0">{{translate('bonus_amount')}}</th>
                                    <th class="border-0">{{translate('started_on')}}</th>
                                    <th class="border-0">{{translate('expires_on')}}</th>
                                    <th class="border-0">{{translate('status')}}</th>
                                    <th class="border-0 text-center">{{translate('action')}}</th>
                                </tr>
                                </thead>

                                <tbody>
                                @foreach($bonuses as $key=>$bonus)
                                    <tr>
                                        <td>{{$key+$bonuses->firstItem()}}</td>
                                        <td>
                                            <span class="d-block font-size-sm text-body">
                                                {{Str::limit($bonus['title'],25,'...')}}
                                            </span>
                                        </td>
                                        <td>{{ translate('minimum_add_amount') }} - {{Helpers::set_symbol($bonus['minimum_add_amount'])}} <br>

                                            {{ $bonus->bonus_type == 'percentage' ? translate('maximum_bonus') .' - '.Helpers::set_symbol($bonus['maximum_bonus_amount']) : ''}}</td>
                                        <td>{{$bonus->bonus_type == 'amount'?Helpers::set_symbol($bonus['bonus_amount']): $bonus['bonus_amount'].'%'}}</td>
                                        <td>{{ \Carbon\Carbon::parse($bonus->start_date)->format('d M Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($bonus->end_date)->format('d M Y') }}</td>
                                        <td>
                                            <label class="switcher">
                                                <input id="{{$bonus['id']}}" class="switcher_input status-change" type="checkbox" {{$bonus['status']==1? 'checked': '' }}
                                                data-url="{{route('admin.customer.wallet.bonus.status',[$bonus['id'],1])}}"
                                                >
                                                <span class="switcher_control"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a class="btn btn-outline-info btn-sm edit square-btn"
                                                   href="{{route('admin.customer.wallet.bonus.edit',[$bonus['id']])}}"><i class="tio-edit"></i></a>
                                                <button type="button" class="btn btn-outline-danger btn-sm delete square-btn form-alert"
                                                        data-id="bonus-{{$bonus['id']}}" data-message="{{translate('Want to delete this bonus ?')}}"><i class="tio-delete"></i></button>
                                            </div>
                                            <form action="{{route('admin.customer.wallet.bonus.delete',[$bonus['id']])}}"
                                                  method="post" id="bonus-{{$bonus['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="table-responsive mt-4 px-3">
                            <div class="d-flex justify-content-lg-end">
                                {!! $bonuses->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="how-it-works">
            <div class="modal-dialog status-warning-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true" class="tio-clear"></span>
                        </button>
                    </div>
                    <div class="modal-body pb-5 pt-0">
                        <div class="single-item-slider owl-carousel">
                            <div class="item">
                                <div class="mb-20">
                                    <div class="text-center">
                                        <img src="{{asset('/public/assets/admin/img/image_127.png')}}" alt="" class="mb-20">
                                        <h5 class="modal-title">{{translate('Wallet_bonus_is_only_applicable_when_a_customer_add_fund_to_wallet_via_outside_payment_gateway_!')}}</h5>
                                    </div>
                                    <ul class="list-unstyled">
                                        <li>
                                            {{ translate('Customer_will_get_extra_amount_to_his_/_her_wallet_additionally_with_the_amount_he_/_she_added_from_other_payment_gateways._The_bonus_amount_will_be_deduct_from_admin_wallet_&_will_consider_as_admin_expense.') }}
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endsection

        @push('script_2')
            <script src="{{asset('public/assets/admin/js/wallet-bonus.js')}}"></script>
        @endpush
