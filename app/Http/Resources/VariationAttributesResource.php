<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VariationAttributesResource extends JsonResource
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
        return [
           'id'     => $this->attribute_options->id,
           'name'   => $this->attribute_options->attribute->name,
           'value'  => $this->attribute_options->value
        ];
    }
}
