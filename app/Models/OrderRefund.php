<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderRefund extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_id',
        'refund_id',
        'amount',
        'currency',
        'status',
        'reason',
        'payment_method',
        'gateway_response',
        'refunded_at',
        'created_by',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'refunded_at' => 'datetime',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Orders::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
