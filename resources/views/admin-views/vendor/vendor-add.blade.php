@extends('layouts.admin.app')

@section('title', translate('Vendor Details'))

@section('content')
<div class="content container-fluid">
        <div class="card">
            <div  class="card-header d-flex justify-content-between gap-2 align-items-center mb-2">
                <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                    <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/customer.png')}}" alt="">
                    <span class="page-header-title">
                    Add New {{translate('Vendor')}}
                    </span>
                </h2>
            </div>
            <div class="card-body px-card pt-4">
                <form id="groupForm" action="{{ route('admin.vendor.savevendor') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="mb-2 col-12">
                            <input type="text" class="form-control " name="first_name" id="signinSrEmail"
                            tabindex="1" placeholder="{{ translate('First Name') }}"
                            aria-label="email@address.com" required
                            data-msg="{{ translate('Please enter first name') }}">
                            @error('first_name')
                            <p class="text-sm text-danger">{{$message}}</p>
                            @enderror
                        </div>
                        <div class="mb-2 col-12">
                            <input type="text" class="form-control " name="last_name" id="signinSrEmail"
                            tabindex="1" placeholder="{{ translate('Last Name') }}"
                            aria-label="email@address.com" required
                            data-msg="{{ translate('Please enter last name') }}">
                            @error('last_name')
                            <p class="text-sm text-danger">{{$message}}</p>
                            @enderror
                        </div>
                        <div class="mb-2 col-12">
                            <input type="email" class="form-control " name="email" id="signinSrEmail"
                            tabindex="1" placeholder="{{ translate('email@address.com') }}"
                            aria-label="email@address.com" required
                            data-msg="{{ translate('Please enter a valid email address') }}">
                            @error('email')
                            <p class="text-sm text-danger">{{$message}}</p>
                            @enderror
                        </div>
                        <div class="mb-2 col-12">
                           <div class="input-group input-group-merge">
                                <input type="password" class="js-toggle-password form-control form-control-lg"
                                    name="password" id="signupSrPassword"
                                    placeholder="{{ translate('Please enter password') }}"
                                   >
                                   @error('password')
                                   <p class="text-sm text-danger">{{$message}}</p>
                                   @enderror
                            </div>
                        </div>
                        <div class="mb-2 col-12">
                            <div class="input-group input-group-merge">
                                 <input type="password" class="js-toggle-password form-control form-control-lg"
                                     name="confirm_password" id="signupSrPassword"
                                     placeholder="{{ translate('Please enter confirm password') }}"
                               >
                               @error('confirm_password')
                               <p class="text-sm text-danger">{{$message}}</p>
                               @enderror
                             </div>
                         </div>
                         <div class="mb-2 col-12">
                            <div class="input-group input-group-merge">
                                 <input type="text" class="js-toggle-password form-control form-control-lg"
                                     name="phone" id="signupSrPassword" maxlength="10"
                                     placeholder="{{ translate('Please enter phone') }}"
                               >
                            </div>
                            @error('phone')
                            <p class="text-sm text-danger">{{$message}}</p>
                            @enderror
                         </div>



                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('admin.vendor.list') }}" class="btn btn-secondary">Cancel</a>
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
