<?php

namespace App\Http\Requests\Frontend\ContactUs;

use Illuminate\Foundation\Http\FormRequest;

class ContactUsStoreRequest extends FormRequest
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
            'full_name' => ['required','max:255'],
            'email' => ['required','email:rfc,dns'],
            'phone_number' => ['required','max:10'],
            'message' => ['required']
        ];
    }
}
