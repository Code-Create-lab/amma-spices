<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariationAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'variation_id',
        'attribute_id',
    ];

    public function attribute()
    {
        return $this->belongsTo(AttributeOption::class,'attribute_id');
    }
    public function attribute_options(){
        return $this->belongsTo(AttributeOption::class,'attribute_id','id');
    }
}
