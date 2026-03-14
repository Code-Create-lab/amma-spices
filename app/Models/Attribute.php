<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Attribute extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'uuid',
        'is_deleted',
        'value'
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            $product->uuid = (string) Str::uuid();
        });
    }
    public function values(){
        return $this->hasMany(AttributeOption::class,'attribute_id')->where('is_deleted',0);
    }
}
