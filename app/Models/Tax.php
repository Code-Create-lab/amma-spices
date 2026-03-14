<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    protected $table = 'tax_types';

    protected $guarded = [];

        public function product()
    {
        return $this->hasOne(Product::class, 'product_id', 'product_id');
    }
}
