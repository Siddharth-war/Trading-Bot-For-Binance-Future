<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerAddRequest extends FormRequest
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
            "f_name"=>["required"],
            "l_name"=>["required"],
            'email' => ['required', 'email', 'unique:users,email,' . $this->id],
            "password"=>["nullable"],
            "confirm_password"=>["nullable","same:password"],
            "phone"=>["required","digits:10"],
            "contact_person_number"=>["required","digits:10"],
            "floor"=>["required"],
            "house"=>["required"],
            "road"=>["required"],
            "address"=>["required"],
            "address_type"=>["required"],
            'group_id'=>['required'],
            "branch_option"=>['required'],
            "credit_limit"=>['required'],
            "day_id"=>['required'],
            "business_name"=>['required'],
            "vat_business"=>['required'],
            "business_address"=>['required'],


        ];
    }
}
