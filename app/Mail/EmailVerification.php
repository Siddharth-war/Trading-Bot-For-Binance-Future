<?php

namespace App\Mail;

use App\CentralLogics\Helpers;
use App\Model\BusinessSetting;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $token;

    public function __construct($token = '', $language_code)
    {
        $this->token = $token;
        $this->language_code = $language_code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $code = $this->token;
        //return $this->view('email-templates.customer-email-verification', ['token' => $token]);

        $data= EmailTemplate::with('translations')->where('type','user')->where('email_type', 'registration_otp')->first();
        $local = $this->language_code ?? 'en';

        $content = [
            'title' => $data->title,
            'body' => $data->body,
            'footer_text' => $data->footer_text,
            'copyright_text' => $data->copyright_text
        ];

        if ($local != 'en'){
            if (isset($data->translations)){
                foreach ($data->translations as $translation){
                    if ($local == $translation->locale){
                        $content[$translation->key] = $translation->value;
                    }
                }
            }
        }

        $template=$data?$data->email_template:4;
        $url = '';
        $company_name = BusinessSetting::where('key', 'restaurant_name')->first()->value;
        $title = Helpers::text_variable_data_format( value:$content['title']??'');
        $body = Helpers::text_variable_data_format( value:$content['body']??'');
        $footer_text = Helpers::text_variable_data_format( value:$content['footer_text']??'');
        $copyright_text = Helpers::text_variable_data_format( value:$content['copyright_text']??'');
        return $this->subject(translate('Customer_Password_Reset_mail'))->view('email-templates.new-email-format-'.$template, ['company_name'=>$company_name,'data'=>$data,'title'=>$title,'body'=>$body,'footer_text'=>$footer_text,'copyright_text'=>$copyright_text,'url'=>$url, 'code'=>$code]);

    }
}
