<?php

namespace App\Http\Requests\Frontend\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SignUpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => ['required','max:255'],
            'email' => ['required','email:rfc,dns','unique:users,email'],
            'user_phone' => ['required','max:10']
        ];
    }

    public function messages(){
        return [
            'user_phone.required' => 'Phone Number Is Required',
            'user_phone.max' => 'Phone Number Cannot Exceed 10 Digits'
        ];
    }
}
