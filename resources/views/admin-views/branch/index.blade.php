@extends('layouts.admin.app')

@section('title', translate('Add New Branch'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/branch.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Add_New_Branch')}}
                </span>
            </h2>
        </div>

        <div class="row g-2">
            <div class="col-12">
                <form action="{{route('admin.branch.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0 d-flex gap-2 align-items-center">
                                <i class="tio-user"></i>
                                {{translate('Branch_Information')}}
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="input-label"
                                                       for="exampleFormControlInput1">{{translate('name')}}<span class="text-danger ml-1">*</span></label>
                                                <input value="{{old('name')}}" type="text" name="name" class="form-control" maxlength="255"
                                                       placeholder="{{translate('New branch')}}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="input-label" for="">{{translate('address')}}<span class="text-danger ml-1">*</span></label>
                                                <input value="{{old('address')}}" type="text" name="address" class="form-control" placeholder="{{ translate('Enter Address') }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="input-label">{{translate('phone')}}<span class="text-danger ml-1">*</span></label>
                                                <input value="{{old('phone')}}" type="tel" name="phone" class="form-control"
                                                       placeholder="{{translate('Ex: +098538534')}}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="input-label">{{translate('email')}}<span class="text-danger ml-1">*</span></label>
                                                <input value="{{old('email')}}" type="email" name="email" class="form-control" maxlength="255"
                                                       placeholder="{{translate('EX : example@example.com')}}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="input-label">{{translate('password')}}<span class="text-danger ml-1">*</span></label>
                                                <div class="input-group input-group-merge">
                                                    <input type="password" name="password" class="js-toggle-password form-control form-control input-field" id="password" required
                                                           placeholder="{{translate('Ex: 8+ Characters')}}"
                                                           data-hs-toggle-password-options='{
                                                "target": "#changePassTarget",
                                                "defaultClass": "tio-hidden-outlined",
                                                "showClass": "tio-visible-outlined",
                                                "classChangeTarget": "#changePassIcon"
                                                }'>
                                                    <div id="changePassTarget" class="input-group-append">
                                                        <a class="input-group-text" href="javascript:">
                                                            <i id="changePassIcon" class="tio-visible-outlined"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="input-label">{{translate('food_preparation_time')}}<span class="text-danger ml-1">*</span>
                                                    <i class="tio-info-outined"
                                                       data-toggle="tooltip"
                                                       data-placement="top"
                                                       title="{{ translate('Food preparation time will show to customer.') }}">
                                                    </i>
                                                </label>
                                                <input value="{{old('preparation_time')}}" type="number" name="preparation_time" class="form-control"
                                                       placeholder="{{ translate(' Ex')}}: {{ translate('30') }}" min="1" required>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <label class="mb-0">{{translate('branch_Image')}}</label>
                                            <small class="text-danger">* ( {{translate('ratio 1:1')}} )</small>
                                        </div>
                                        <div class="d-flex justify-content-center mt-4">
                                            <div class="upload-file">
                                                <input type="file" name="image" accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" class="upload-file__input">
                                                <div class="upload-file__img_drag upload-file__img">
                                                    <img width="150" id="viewer" src="{{asset('public/assets/admin/img/icons/upload_img.png')}}" alt="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <label class="mb-0">{{translate('branch_cover_image')}}</label>
                                            <small class="text-danger">* ( {{translate('ratio 3:1')}} )</small>
                                        </div>
                                        <div class="d-flex justify-content-center mt-4">
                                            <div class="upload-file">
                                                <input type="file" name="cover_image" accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" class="upload-file__input">
                                                <div class="upload-file__img_drag upload-file__img">
                                                    <img width="150" id="viewer_2" src="{{asset('public/assets/admin/img/icons/upload_img.png')}}" alt="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @php($googleMapStatus = \App\CentralLogics\Helpers::get_business_settings('google_map_status'))
                    @if($googleMapStatus)
                        <div class="card mt-3">
                            <div class="card-header">
                                <h4 class="mb-0 d-flex gap-2 align-items-center">
                                    <i class="tio-map"></i>
                                    {{translate('branch_Location')}}
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <div class="form-group mb-0">
                                                    <label class="form-label text-capitalize"
                                                           for="latitude">{{ translate('latitude') }}
                                                        <i class="tio-info-outined"
                                                           data-toggle="tooltip"
                                                           data-placement="top"
                                                           title="{{ translate('Click on the map select your default location.') }}"></i><span
                                                            class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                                            data-original-title="{{ translate('click_on_the_map_select_your_default_location') }}"></span></label>
                                                    <input type="number" step="any" id="latitude" name="latitude" class="form-control"
                                                           placeholder="{{ translate('Ex:') }} 23.8118428"
                                                           value="{{ old('latitude') }}" required >
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-group mb-0">
                                                    <label class="form-label text-capitalize"
                                                           for="longitude">
                                                        {{ translate('longitude') }}
                                                        <i class="tio-info-outined"
                                                           data-toggle="tooltip"
                                                           data-placement="top"
                                                           title="{{ translate('Click on the map select your default location.') }}"></i>
                                                        <span class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                                              data-original-title="{{ translate('click_on_the_map_select_your_default_location') }}">
                                                       </span>
                                                    </label>
                                                    <input type="number" step="any" name="longitude" class="form-control"
                                                           placeholder="{{ translate('Ex:') }} 90.356331" id="longitude"
                                                           value="{{ old('longitude') }}" required>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-group mb-0">
                                                    <label class="input-label">
                                                        {{translate('coverage (km)')}}
                                                        <i class="tio-info-outined"
                                                           data-toggle="tooltip"
                                                           data-placement="top"
                                                           title="{{ translate('This value is the radius from your restaurant location, and customer can order food inside  the circle calculated by this radius.') }}"></i>
                                                    </label>
                                                    <input type="number" name="coverage" min="1" max="1000" class="form-control" placeholder="{{ translate('Ex : 3') }}" value="{{ old('coverage') }}" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6" id="location_map_div">
                                        <input id="pac-input" class="controls rounded" data-toggle="tooltip"
                                               data-placement="right"
                                               data-original-title="{{ translate('search_your_location_here') }}"
                                               type="text" placeholder="{{ translate('search_here') }}" />
                                        <div id="location_map_canvas" class="overflow-hidden rounded location-map-canvas"></div>
                                    </div>

                                </div>

                            </div>
                        </div>
                    @endif
                    <div class="btn--container mt-4">
                        <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                        <button type="submit" class="btn btn-primary">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>

        </div>
    </div>

@endsection

@push('script_2')

    <script src="https://maps.googleapis.com/maps/api/js?key={{ \App\Model\BusinessSetting::where('key', 'map_api_client_key')->first()?->value }}&libraries=places&v=3.51"></script>
    <script src="{{asset('public/assets/admin/js/read-url.js')}}"></script>

    <script>
        "use strict";

        $( document ).ready(function() {
            function initAutocomplete() {
                var myLatLng = {

                    lat: 23.811842872190343,
                    lng: 90.356331
                };
                const map = new google.maps.Map(document.getElementById("location_map_canvas"), {
                    center: {
                        lat: 23.811842872190343,
                        lng: 90.356331
                    },
                    zoom: 13,
                    mapTypeId: "roadmap",
                });

                var marker = new google.maps.Marker({
                    position: myLatLng,
                    map: map,
                });

                marker.setMap(map);
                var geocoder = geocoder = new google.maps.Geocoder();
                google.maps.event.addListener(map, 'click', function(mapsMouseEvent) {
                    var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                    var coordinates = JSON.parse(coordinates);
                    var latlng = new google.maps.LatLng(coordinates['lat'], coordinates['lng']);
                    marker.setPosition(latlng);
                    map.panTo(latlng);

                    document.getElementById('latitude').value = coordinates['lat'];
                    document.getElementById('longitude').value = coordinates['lng'];


                    geocoder.geocode({
                        'latLng': latlng
                    }, function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (results[1]) {
                                document.getElementById('address').innerHtml = results[1].formatted_address;
                            }
                        }
                    });
                });

                const input = document.getElementById("pac-input");
                const searchBox = new google.maps.places.SearchBox(input);
                map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);

                map.addListener("bounds_changed", () => {
                    searchBox.setBounds(map.getBounds());
                });
                let markers = [];

                searchBox.addListener("places_changed", () => {
                    const places = searchBox.getPlaces();

                    if (places.length == 0) {
                        return;
                    }

                    markers.forEach((marker) => {
                        marker.setMap(null);
                    });
                    markers = [];

                    const bounds = new google.maps.LatLngBounds();
                    places.forEach((place) => {
                        if (!place.geometry || !place.geometry.location) {
                            console.log("Returned place contains no geometry");
                            return;
                        }
                        var mrkr = new google.maps.Marker({
                            map,
                            title: place.name,
                            position: place.geometry.location,
                        });
                        google.maps.event.addListener(mrkr, "click", function(event) {
                            document.getElementById('latitude').value = this.position.lat();
                            document.getElementById('longitude').value = this.position.lng();
                        });

                        markers.push(mrkr);

                        if (place.geometry.viewport) {

                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(place.geometry.location);
                        }
                    });
                    map.fitBounds(bounds);
                });
            };
            initAutocomplete();
        });


        $('.__right-eye').on('click', function(){
            if($(this).hasClass('active')) {
                $(this).removeClass('active')
                $(this).find('i').removeClass('tio-invisible')
                $(this).find('i').addClass('tio-hidden-outlined')
                $(this).siblings('input').attr('type', 'password')
            }else {
                $(this).addClass('active')
                $(this).siblings('input').attr('type', 'text')

                $(this).find('i').addClass('tio-invisible')
                $(this).find('i').removeClass('tio-hidden-outlined')
            }
        })
    </script>

@endpush
