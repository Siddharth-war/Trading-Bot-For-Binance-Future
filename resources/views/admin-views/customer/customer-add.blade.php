@extends('layouts.admin.app')

@section('title', translate('Customer Details'))

@section('content')
<div class="content container-fluid">
        <div class="card">
            <div  class="card-header d-flex justify-content-between gap-2 align-items-center mb-2">
                <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                    <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/customer.png')}}" alt="">
                    <span class="page-header-title">
                    Add New {{translate('customer')}}
                    </span>
                </h2>
            </div>
            <div class="card-body px-card pt-4">
                <form id="groupForm" action="{{ route('admin.customer.saveCustomer') }}" method="POST">
                    @csrf
                    <div class="mb-2 mt-2 font-weight-bold">
                        <p class="text-xl">General Customer Details </p>
                    </div>
                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">First Name <small class="text-danger">* </small></label>
                                <input type="text" name="f_name" class="form-control" placeholder="Please Enter First Name">
                                @error('f_name')
                                <span class="text-danger error-name">{{$message}}</span>

                                @enderror
                                <span class="text-danger error-name"></span>

                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">Last Name <small class="text-danger">* </small></label>
                                <input type="text" name="l_name" class="form-control" placeholder="Please Enter Last Name">
                                @error('l_name')
                                <span class="text-danger error-name">{{$message}}</span>

                                @enderror
                                <span class="text-danger error-name"></span>

                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">Email <small class="text-danger">* </small></label>
                                <input type="text" name="email" class="form-control" placeholder="Please Enter Email">
                                @error('email')
                                <span class="text-danger error-name">{{$message}}</span>

                                @enderror
                                <span class="text-danger error-name"></span>

                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">Password <small class="text-danger">* </small></label>
                                <input type="password" name="password" class="form-control" placeholder="Please Enter Password">
                                @error('password')
                                <span class="text-danger error-name">{{$message}}</span>

                                @enderror
                                <span class="text-danger error-name"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">Confirm Password <small class="text-danger">* </small></label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Please Enter Password">
                                @error('confirm_password')
                                <span class="text-danger error-name">{{$message}}</span>

                                @enderror
                                <span class="text-danger error-name"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">Phone Number <small class="text-danger">* </small></label>
                                <input type="text" name="phone" class="form-control" placeholder="Please Enter Phone Number" maxlength="10">
                                @error('phone')
                                <span class="text-danger error-name">{{$message}}</span>

                                @enderror
                                <span class="text-danger error-name"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">Contact Person Number <small class="text-danger">* </small></label>
                                <input type="text" name="contact_person_number" class="form-control" placeholder="Please Enter Contact Person Number" maxlength="10">
                                @error('contact_person_number')
                                <span class="text-danger error-name">{{$message}}</span>
                                @enderror
                                <span class="text-danger error-name"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">Select Group <small class="text-danger">* </small></label>
                                <select name="group_id" class="form-control" id="groupSelect">
                                    <option value="">Please Select</option>
                                    @foreach ($groups as $group)
                                    <option value="{{$group->id}}" data-id="{{$group->credit_limit}}">{{$group->name}}(₹ {{$group->credit_limit}})</option>
                                    @endforeach

                                </select>
                                @error('group_id')
                                <span class="text-danger error-name">{{$message}}</span>
                                @enderror
                                <span class="text-danger error-name"></span>
                            </div>
                        </div>


                    </div>
                    <hr>

                    <div class="mb-2 mt-2 font-weight-bold">
                        <p class="text-xl">Address Info</p>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">Address Type <small class="text-danger">* </small></label>
                                <input type="text" name="address_type" class="form-control" placeholder="Please Enter Address Type">
                                @error('address_type')
                                <span class="text-danger error-name">{{$message}}</span>

                                @enderror
                                <span class="text-danger error-name"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">Floor <small class="text-danger">* </small></label>
                                <input type="text" name="floor" class="form-control" placeholder="Please Enter Floor" maxlength="10">
                                @error('floor')
                                <span class="text-danger error-name">{{$message}}</span>

                                @enderror
                                <span class="text-danger error-name"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">House <small class="text-danger">* </small></label>
                                <input type="text" name="house" class="form-control" placeholder="Please Enter House" maxlength="50">
                                @error('house')
                                <span class="text-danger error-name">{{$message}}</span>

                                @enderror
                                <span class="text-danger error-name"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">Road <small class="text-danger">* </small></label>
                                <input type="text" name="road" class="form-control" placeholder="Please Enter Road" maxlength="50">
                                @error('road')
                                <span class="text-danger error-name">{{$message}}</span>

                                @enderror
                                <span class="text-danger error-name"></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="input-label">Address <small class="text-danger">* </small></label>
                                <textarea  name="address" class="form-control" placeholder="Please Enter Address" maxlength="250"></textarea>
                                @error('address')
                                <span class="text-danger error-name">{{$message}}</span>

                                @enderror
                                <span class="text-danger error-name"></span>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="mb-2 mt-2 font-weight-bold">
                        <p class="text-xl">Business Info</p>
                    </div> --}}
                    <div class="row">
                        <div class="col-md-4">
                            <label class="input-label">Delivery day <small class="text-danger">* </small></label>
                            <select type="text" name="day_id" class="form-control">
                                <option value="">Select Delivery day</option>
                                @foreach ($weekdays as $weekday)
                                <option value="{{$weekday->id}}">{{$weekday->name}}</option>
                                @endforeach
                            </select>
                            @error('day_id')
                            <span class="text-danger error-name">{{$message}}</span>

                            @enderror
                            <span class="text-danger error-name"></span>
                        </div>
                        <div class="col-md-4">
                            <label class="input-label">Credit Limit <small class="text-danger">* </small></label>
                            <input type="text" name="credit_limit" id="creditLimit" class="form-control" placeholder="Please Enter Credit Limit">
                            @error('credit_limit')
                            <span class="text-danger error-name">{{$message}}</span>

                            @enderror
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="input-label">Branch Option <small class="text-danger">* </small></label>
                            <select type="text" name="branch_option" class="form-control">
                                <option value="">Select Branch Option</option>
                                @foreach ($branches as $branch)
                                <option value="{{$branch->id}}">{{$branch->name}}</option>
                                @endforeach
                            </select>
                            @error('branch_option')
                            <span class="text-danger error-name">{{$message}}</span>

                            @enderror
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="input-label">Business Name <small class="text-danger">* </small></label>
                                <input type="text" name="business_name"   class="form-control" placeholder="Please Enter Business Name" maxlength="250">
                                @error('business_name')
                                <span class="text-danger error-name">{{$message}}</span>

                                @enderror
                                <span class="text-danger error-name"></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="input-label">VAT registeration number <small class="text-danger">* </small></label>
                                <input type="text" name="vat_business"  class="form-control" placeholder="Please Enter VAT registeration number" maxlength="250">
                                @error('vat_business')
                                <span class="text-danger error-name">{{$message}}</span>

                                @enderror
                                <span class="text-danger error-name"></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="input-label">Business Address <small class="text-danger">* </small></label>
                                <textarea name="business_address"   class="form-control" placeholder="Please Enter business address" maxlength="250">

                                </textarea>
                                @error('business_address')
                                <span class="text-danger error-name">{{$message}}</span>

                                @enderror
                                <span class="text-danger error-name"></span>
                            </div>
                        </div>
                    </div>


                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('admin.customer.list') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>

</div>
@endsection

@push('script_2')
        <script src="{{asset('public/assets/admin/js/customer-view.js')}}"></script>
@endpush

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#groupSelect').change(function () {
            let selectedCreditLimit = $(this).find(':selected').data('id'); // Get data-id from selected option
            console.log(selectedCreditLimit)
            $('#creditLimit').val(selectedCreditLimit); // Display value
        });
    });
</script>
