<?php

namespace App\Http\Requests\Admin\Banner;

use Illuminate\Foundation\Http\FormRequest;

class BannerUpdateRequest extends FormRequest
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
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'mobile_banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages()
    {
        return [
            'image.image' => 'Desktop banner must be a valid image file.',
            'image.mimes' => 'Desktop banner must be a JPG, JPEG, PNG, or WEBP file.',
            'image.max' => 'Desktop banner must not be greater than 2 MB.',
            'image.uploaded' => 'Desktop banner could not be uploaded. Please choose a JPG, JPEG, PNG, or WEBP file under 2 MB.',
            'mobile_banner.image' => 'Mobile banner must be a valid image file.',
            'mobile_banner.mimes' => 'Mobile banner must be a JPG, JPEG, PNG, or WEBP file.',
            'mobile_banner.max' => 'Mobile banner must not be greater than 2 MB.',
            'mobile_banner.uploaded' => 'Mobile banner could not be uploaded. Please choose a JPG, JPEG, PNG, or WEBP file under 2 MB.',
        ];
    }
}
