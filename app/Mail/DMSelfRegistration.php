<?php

namespace App\Mail;

use App\CentralLogics\Helpers;
use App\Model\BusinessSetting;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DMSelfRegistration extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    protected $status;
    protected $name;

    public function __construct($status, $name, $language_code)
    {
        $this->status = $status;
        $this->name = $name;
        $this->language_code = $language_code;
    }

    /**
     * @return DMSelfRegistration
     */
    public function build()
    {
        //return $this->view('email-templates.dm-self-registration', ['status' => $status, 'name' => $name]);

        $status = $this->status;

        if($status == 'approved'){
            $data= EmailTemplate::with('translations')->where('type','dm')->where('email_type', 'approve')->first();
        }elseif($status == 'denied'){
            $data= EmailTemplate::with('translations')->where('type','dm')->where('email_type', 'deny')->first();
        }else{
            $data= EmailTemplate::with('translations')->where('type','dm')->where('email_type', 'registration')->first();
        }

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

        $template=$data?$data->email_template:5;
        $url = '';
        $dm_name = $this->name;
        $company_name = BusinessSetting::where('key', 'restaurant_name')->first()->value;
        $title = Helpers::text_variable_data_format( value:$content['title']??'',delivery_man_name:$dm_name??'');
        $body = Helpers::text_variable_data_format( value:$content['body']??'',delivery_man_name:$dm_name??'');
        $footer_text = Helpers::text_variable_data_format( value:$content['footer_text']??'',delivery_man_name:$dm_name??'');
        $copyright_text = Helpers::text_variable_data_format( value:$content['copyright_text']??'',delivery_man_name:$dm_name??'');
        return $this->subject(translate('Deliveryman_Registration_Mail'))->view('email-templates.new-email-format-'.$template, ['company_name'=>$company_name,'data'=>$data,'title'=>$title,'body'=>$body,'footer_text'=>$footer_text,'copyright_text'=>$copyright_text,'url'=>$url]);
    }
}
