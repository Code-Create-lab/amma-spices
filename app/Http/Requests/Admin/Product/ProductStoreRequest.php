<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'parent_cat' => 'required',
            'product_name' => 'required|string|max:255',
            'type' => 'required',
            'hsn_number' => 'required|unique:products,hsn_number', // Fixed typo and added table
            'quantity' => 'required_if:type,Simple',
            // 'quantity' => 'required|integer|min:1',
            'ean' => 'nullable|string|max:50',
            'mrp' => 'required|numeric|min:1',
            // 'price' => 'lte:mrp',
            'product_image' => 'required',
            'images' => 'required|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
            'description' => 'required|string',
            'variations' => 'required_if:type,Variable|array',
            'variations.*.mrp' => 'required_if:type,Variable',
            'variations.*.price' => 'required_if:type,Variable',
            'variations.*.stock' => 'required_if:type,Variable',
            'variations.*.image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ];
    }


    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'parent_cat.required' => 'The category field is required.',
            'hsn_number.required' => 'SKU code is required',
            'hsn_number.unique' => 'SKU code should be unique',
            // 'cat_id.exists'         => 'The selected category is invalid.',
            'product_name.required' => 'The product name is required.',
            'quantity.required' => 'The quantity is required.',
            'quantity.integer' => 'The quantity must be a valid number.',
            // 'ean.unique'            => 'This EAN code is already used for another product.',
            'mrp.required' => 'The MRP field is required.',
            'images.max' => 'You can upload a maximum of 10 images.',
            'price.required' => 'The price field is required.',
            'price.lte' => 'The price must be less than or equal to the MRP.',
            'product_image.required' => 'A main product image is required.',
            'product_image.image' => 'The main product image must be an image file.',
            'product_image.max' => 'The main product image must not exceed 10 MB.',
            'description.required' => 'The description field is required.',
            'variations.*.price.required' => 'The price for each variation is required.',
            'variations.*.stock.required' => 'The stock quantity for each variation is required.',
        ];
    }
}
