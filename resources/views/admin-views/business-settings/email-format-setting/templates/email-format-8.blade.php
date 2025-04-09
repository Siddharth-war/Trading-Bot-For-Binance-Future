<?php
$company_name = App\Models\BusinessSetting::where('key', 'business_name')->first()->value;
?>
<table class="main-table">
    <tbody>
        <tr>
            <td class="main-table-td">
                <img class="mail-img-1" onerror="this.src='{{ asset('/public/assets/admin/img/blank1.png') }}'"
                src="{{ asset('storage/app/public/email_template/') }}/{{ $data['logo']??'' }}" id="logoViewer" alt="">
                <h2 id="mail-title" class="mt-2">{{ $data['title']?? translate('Main_Title_or_Subject_of_the_Mail') }}</h2>
                <div class="mb-1" id="mail-body">{!! $data['body']?? translate('Hi_Sabrina,') !!}</div>
                <hr>
                <div class="mb-2" id="mail-footer">
                    {{ $data['footer_text'] ?? translate('Please_contact_us_for_any_queries,_we’re_always_happy_to_help.') }}
                </div>
                <div>
                    {{ translate('Thanks_&_Regards') }},
                </div>
                <div class="mb-4">
                    {{ $company_name }}
                </div>
            </td>
        </tr>
        <tr>
            <td>
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
