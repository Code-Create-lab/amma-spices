<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $variation_attributes = [];

        $this->variations->each(function ($variation) use (&$variation_attributes) {
            foreach ($variation->variation_attributes as $attribute) {
                $attributeName = $attribute->attribute_options->attribute->name;
                $optionId = $attribute->attribute_options->id;
                $optionName = $attribute->attribute_options->name;
                $optionValue = $attribute->attribute_options->value;

                if (!isset($variation_attributes[$attributeName])) {
                    $variation_attributes[$attributeName] = [];
                }
                $optionExists = false;
                foreach ($variation_attributes[$attributeName] as &$option) {
                    if ($option['id'] == $optionId) {
                        $optionExists = true;
                        break;
                    }
                }
                if (!$optionExists) {
                    $variation_attributes[$attributeName][] = [
                        'id' => $optionId,
                        'name' => $optionName,
                        'value' => $optionValue,
                    ];
                }
            }
        });
        // dd($this->product_image);
        return [
            'id' => $this->product_id,
            'uuid' => $this->uuid,
            'name' => $this->product_name,
            'product_type' => $this->type,
            'base_price' => $this->base_price,
            'base_mrp' => $this->base_mrp,
            'description' => $this->description,
            'info' => $this->info,
            'shipping' => $this->shipping,
            'category' => $this->category,
            'slug' => $this->slug,
            // 'status' => $this->is_status,
            'image' => $this->product_image, // for testing
            'size_guide_images' => $this->size_guide_images, // for testing
            'images' => $this->images->toArray(),
            'product_video' => $this->product_video,
            'variations' => (ProductVariationsResource::collection($this->variations))->toArray(request()),
            'variation_attributes' => $variation_attributes
        ];
    }
}
