<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentTracking extends Model
{
    use HasFactory;

    protected $table = 'shipment_trackings';

    protected $fillable = [
        'order_id',
        'awb_number',
        'logistics_name',
        'current_tracking_status',
        'status',
        'remark',
        'location',
        'latest_scan_time',
        'edd_date',
        'tracking_url',
        'raw_payload',
    ];

    protected $casts = [
        'latest_scan_time' => 'datetime',
        'edd_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the order that owns the tracking
     */
    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id', 'order_id');
    }

    /**
     * Get the shipment associated with this tracking
     */
    public function shipment()
    {
        return $this->hasOneThrough(
            Shipment::class,
            Orders::class,
            'order_id', // Foreign key on orders table
            'order_id', // Foreign key on shipments table
            'order_id', // Local key on shipment_tracking table
            'order_id'  // Local key on orders table
        );
    }

    /**
     * Scope to get latest tracking updates
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('latest_scan_time', 'desc');
    }

    /**
     * Scope to get tracking by AWB
     */
    public function scopeByAwb($query, $awb)
    {
        return $query->where('awb_number', $awb);
    }

    /**
     * Scope to get tracking by order ID
     */
    public function scopeByOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Get decoded raw payload
     */
    public function getDecodedPayloadAttribute()
    {
        return json_decode($this->raw_payload, true);
    }
}