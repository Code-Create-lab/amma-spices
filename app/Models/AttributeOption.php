<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeOption extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'value',
        'attribute_id',
        'is_deleted'
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class,'attribute_id');
    }
}
