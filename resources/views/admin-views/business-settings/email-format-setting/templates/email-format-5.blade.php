<?php
$company_name = App\Model\BusinessSetting::where('key', 'business_name')->first()?->value;
$logo = \App\Model\BusinessSetting::where('key','fav_icon')->first()?->value;

?>
<table style="width:100%;max-width:500px;margin:0 auto;text-align:center;background:#fff">
    <tbody>
    <tr>
        <td style="padding:30px 30px 0">
            <img class="mail-img-2" onerror="this.src='{{ asset('/public/assets/admin/img/blank3.png') }}'"
            src="{{ asset('storage/app/public/email_template/') }}/{{ $data['icon']??'' }}" id="iconViewer" alt="">
            <h3 style="font-size:17px;font-weight:500" class="mt-2" id="mail-title">{{ $data['title']?? translate('Main_Title_or_Subject_of_the_Mail') }}</h3>

        </td>
    </tr>
    <tr>
        <td style="padding:0 30px 30px; text-align:left">
            <span style="font-weight:500;display:block;margin: 20px 0 11px;" id="mail-body">{!! $data['body']??'Please click the link below to change your password' !!}</span>
            {{-- <span style="display:block;margin-bottom:14px">
                Please click <a href="" style="font-weight:500;color:#0177CD">Here</a>  or click the link below to change your password
            </span> --}}
            <span style="display:block;margin-bottom:14px">
                {{-- <span style="display:block" id="mail-button">{{ $data['button_name']??'Click Here' }}</span> --}}
                <a href="#" style="color: #0177CD">{{ translate('Generated_link') }}</a>
            </span>

            <span class="border-top"></span>
            <span class="d-block" style="margin-bottom:14px" id="mail-footer">{{ $data['footer_text'] ?? translate('Please_contact_us_for_any_queries,_we’re_always_happy_to_help.') }}</span>
            <span class="d-block">{{ translate('Thanks_&_Regards') }},</span>
            <span class="d-block" style="margin-bottom:20px">{{ $company_name }}</span>

            <img style="width:120px;display:block;margin:10px auto" onerror="this.src='{{asset('/public/assets/admin/img/favicon.png')}}'" src="{{ asset('storage/app/public/business/' . $logo) }}" alt="public/img">
        </td>
    </tr>
    <tr>
        <td style="font-size:12px;">

            <span class="privacy">
                <a href="#" id="privacy-check" style="{{ (isset($data['privacy']) && $data['privacy'] == 1)?'':'display:none;' }}">{{ translate('Privacy_Policy')}}</a>
                <a href="#" id="refund-check" style="{{ (isset($data['refund']) && $data['refund'] == 1)?'':'display:none;' }}">{{ translate('Refund_Policy') }}</a>
                <a href="#" id="cancelation-check" style="{{ (isset($data['cancelation']) && $data['cancelation'] == 1)?'':'display:none;' }}">{{ translate('Cancelation_Policy') }}</a>
                <a href="#" id="contact-check" style="{{ (isset($data['contact']) && $data['contact'] == 1)?'':'display:none;' }}">{{ translate('Contact_us') }}</a>
            </span>
            <span class="social" style="text-align:center">
                <a href="#" id="facebook-check" style="margin: 0 5px;text-decoration:none;{{ (isset($data['facebook']) && $data['facebook'] == 1)?'':'display:none;' }}">
                    <img src="{{asset('/public/assets/admin/img/img/facebook.png')}}" alt="">
                </a>
                <a href="#" id="instagram-check" style="margin: 0 5px;text-decoration:none;{{ (isset($data['instagram']) && $data['instagram'] == 1)?'':'display:none;' }}">
                    <img src="{{asset('/public/assets/admin/img/img/instagram.png')}}" alt="">
                </a>
                <a href="#" id="twitter-check" style="margin: 0 5px;text-decoration:none;{{ (isset($data['twitter']) && $data['twitter'] == 1)?'':'display:none;' }}">
                    <img src="{{asset('/public/assets/admin/img/img/twitter.png')}}" alt="">
                </a>
                <a href="#" id="linkedin-check" style="margin: 0 5px;text-decoration:none;{{ (isset($data['linkedin']) && $data['linkedin'] == 1)?'':'display:none;' }}">
                    <img src="{{asset('/public/assets/admin/img/img/linkedin.png')}}" alt="">
                </a>
                <a href="#" id="pinterest-check" style="margin: 0 5px;text-decoration:none;{{ (isset($data['pinterest']) && $data['pinterest'] == 1)?'':'display:none;' }}">
                    <img src="{{asset('/public/assets/admin/img/img/pinterest.png')}}" alt="">
                </a>
            </span>
            <span class="copyright" id="mail-copyright">
                {{ $data['copyright_text']?? translate('Copyright_2023_eFood._All_right_reserved') }}
            </span>
        </td>
    </tr>
</tbody>
</table>
