<div class="footer">
    <div class="row justify-content-between align-items-center">
        <div class="col">
            <p class="font-size-sm mb-0">
                <span class="d-none d-sm-inline-block">{{\App\Model\BusinessSetting::where(['key'=>'footer_text'])->first()->value}}</span>
            </p>
        </div>
        <div class="col-auto">
            <div class="d-flex justify-content-end">
                <ul class="list-inline-menu justify-content-center justify-content-md-end">
                    <li>
                        <a href="{{route('branch.settings')}}">
                            <span>{{translate('Profile')}}</span>
                            <img width="12" class="avatar-img rounded-0" src="{{asset('public/assets/admin/img/icons/profile.png')}}" alt="{{ translate('profile_image') }}">
                        </a>
                    </li>

                    <li>
                        <a href="{{route('branch.dashboard')}}">
                            <span>{{translate('Home')}}</span>
                            <img width="12" class="avatar-img rounded-0" src="{{asset('public/assets/admin/img/icons/home.png')}}" alt="{{ translate('image') }}">
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
