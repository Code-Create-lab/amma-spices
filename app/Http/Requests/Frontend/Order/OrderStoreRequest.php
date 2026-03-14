<?php

namespace App\Http\Requests\Frontend\Order;

use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
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
            'address_id' => ['required','exists:address,uuid']
        ];
    }
    public function messages(){
        return [
            'address_id.required' => 'Address Is Required'
        ];
    }
}
