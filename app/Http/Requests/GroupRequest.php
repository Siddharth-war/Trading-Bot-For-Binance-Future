<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GroupRequest extends FormRequest
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
            'name' => ['required', 'unique:groups,name,'.$this->group->id.','.'id'],
            // 'price_modifier' => 'required|numeric',
            // "category_id"=>'required',
            "credit_limit"=>'required|numeric',
        ];
    }

    public function messages(){
        return [
            'category_id.required'=>"The category field  is required",
        ];
    }
}
