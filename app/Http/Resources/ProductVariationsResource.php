<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $options = [];
        foreach ($this->variation_attributes as $option) {
            if ($option && $option->attribute_options && $option->attribute_options->attribute) {
                $options[$option->attribute_options->attribute->name] = [
                    'id'    => $option->attribute_options->id,
                    'name'  => $option->attribute_options->name,
                    'value' => $option->attribute_options->value,
                ];
            }
        }
        return [
            'id'                 => $this->id,
            'price'              => (int) $this->price ?? '',
            'mrp'                => (int) $this->mrp ??  '',
            'stock'              => (int) $this->stock ?? '',
            'in_stock'           => $this->stock > 0 ? true : false,
            'image'              => $this->image,
            'attributes_options' => $options,
            'attributes'         => (VariationAttributesResource::collection($this->variation_attributes))->toArray(request())
        ];
    }
}
