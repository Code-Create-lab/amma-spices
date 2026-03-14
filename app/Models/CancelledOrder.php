<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CancelledOrder extends Model
{
    protected $table = 'canceled_orders';

    protected $fillable = [
        'order_id',
        'inv_id',
        'user_id',
        'comment',
        'method_choosen',
        'amount',
        'is_refunded',
        'bank_id',
        'transaction_id',
        'txn_fee',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Orders::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
