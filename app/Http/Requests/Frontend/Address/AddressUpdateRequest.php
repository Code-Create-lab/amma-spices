<?php

namespace App\Http\Requests\Frontend\Address;

use Illuminate\Foundation\Http\FormRequest;

class AddressUpdateRequest extends FormRequest
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
            'address_id' => ['required','exists:address,uuid'],
            'receiver_name' => ['required','max:255'],
            'receiver_phone' => ['required'],
            'receiver_email' => ['required','email:rfc,dns'],
            'type' => ['required'],
            'house_no' => ['required'],
            'society' => ['required'],
            'city' => ['required'],
            'state' => ['required'],
            'pincode' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'receiver_name.required' => 'Name Is Required',
            'receiver_phone.required' => 'Contact Is Required',
            'receiver_email.required' => 'Email Is Required',
            'type.required' => 'Type Is Required',
            'house_no.required' => 'House/Flat/Office No Is Required',
            'society.required' => 'Society Is Required',
            'city.required' => 'City Is Required',
            'state.required' => 'State Is Required',
            'pincode.required' => 'Pincode Is Required'
        ];
    }
}
