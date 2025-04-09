<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DayDeliveryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'day_id'=>'required',
            'location.*'=>'required',

        ];
    }

    public function messages(){
        return [
            "day_id"=>"The day field is required",
        ];
    }
}
