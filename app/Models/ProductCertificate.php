<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCertificate extends Model
{
    use HasFactory;

    protected $table = 'product_certificates';

    protected $fillable = [
        'product_id',
        'title',
        'file',
        'file_type',
        'sort_order',
        'is_deleted',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->file_type === 'pdf';
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file);
    }
}
