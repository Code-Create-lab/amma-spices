<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_gateway',
        'gid',
        'merchant_txn_id',
        'merchant_unique_id',
        'amount',
        'currency',
        'payment_status',
        'payment_method',
        'gateway_response',
        'billing_data',
        'transaction_reference',
        'bank_reference',
        'payment_date',
        'processed_at',
        'failure_reason',
        'retry_count',
        'is_refunded',
        'refunded_amount',
        'refunded_at',
        'notes'
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'billing_data' => 'array',
        'processed_at' => 'datetime',
        'payment_date' => 'datetime',
        'refunded_at' => 'datetime',
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'is_refunded' => 'boolean',
        'retry_count' => 'integer'
    ];

    protected $dates = [
        'payment_date',
        'processed_at',
        'refunded_at',
        'created_at',
        'updated_at'
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get the order that owns the payment
     */
    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id', 'id');
    }

    /**
     * Get the user through the order
     */
    public function user()
    {
        return $this->hasOneThrough(User::class, Orders::class, 'id', 'id', 'order_id', 'user_id');
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope for successful payments
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('payment_status', ['SUCCESS', 'COMPLETED', 'SENT_FOR_CAPTURE']);
    }

    /**
     * Scope for failed payments
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('payment_status', [
            'FAILED', 
            'FAILURE', 
            'CUSTOMER_CANCELLED', 
            'AUTHENTICATION_TIMEOUT', 
            'ISSUER_DECLINE', 
            'GENERAL_DECLINE'
        ]);
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->whereIn('payment_status', ['PENDING', 'PROCESSING']);
    }

    /**
     * Scope for PayGlocal payments
     */
    public function scopePayGlocal($query)
    {
        return $query->where('payment_gateway', 'payglocal');
    }

    /**
     * Scope for refunded payments
     */
    public function scopeRefunded($query)
    {
        return $query->where('is_refunded', true);
    }

    /**
     * Scope for payments within date range
     */
    public function scopeWithinDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Scope for payments by amount range
     */
    public function scopeByAmountRange($query, $minAmount, $maxAmount)
    {
        return $query->whereBetween('amount', [$minAmount, $maxAmount]);
    }

    /**
     * Scope for recent payments
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('payment_date', '>=', Carbon::now()->subDays($days));
    }

    // ========================================
    // STATUS CHECK METHODS
    // ========================================

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return in_array($this->payment_status, ['SUCCESS', 'COMPLETED', 'SENT_FOR_CAPTURE']);
    }

    /**
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return in_array($this->payment_status, [
            'FAILED', 
            'FAILURE', 
            'CUSTOMER_CANCELLED', 
            'AUTHENTICATION_TIMEOUT', 
            'ISSUER_DECLINE', 
            'GENERAL_DECLINE'
        ]);
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return in_array($this->payment_status, ['PENDING', 'PROCESSING']);
    }

    /**
     * Check if payment can be refunded
     */
    public function canBeRefunded(): bool
    {
        return $this->isSuccessful() && !$this->is_refunded && $this->amount > 0;
    }

    /**
     * Check if payment is partially refunded
     */
    public function isPartiallyRefunded(): bool
    {
        return $this->is_refunded && $this->refunded_amount > 0 && $this->refunded_amount < $this->amount;
    }

    /**
     * Check if payment is fully refunded
     */
    public function isFullyRefunded(): bool
    {
        return $this->is_refunded && $this->refunded_amount >= $this->amount;
    }

    // ========================================
    // ACCESSOR ATTRIBUTES
    // ========================================

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return '₹' . number_format($this->amount, 2);
    }

    /**
     * Get formatted refunded amount
     */
    public function getFormattedRefundedAmountAttribute(): string
    {
        return '₹' . number_format($this->refunded_amount, 2);
    }

    /**
     * Get remaining refundable amount
     */
    public function getRemainingRefundableAmountAttribute(): float
    {
        return max(0, $this->amount - $this->refunded_amount);
    }

    /**
     * Get formatted remaining refundable amount
     */
    public function getFormattedRemainingRefundableAmountAttribute(): string
    {
        return '₹' . number_format($this->remaining_refundable_amount, 2);
    }

    /**
     * Get payment status badge class for UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->payment_status) {
            'SUCCESS', 'COMPLETED', 'SENT_FOR_CAPTURE' => 'badge-success',
            'PENDING', 'PROCESSING' => 'badge-warning',
            'FAILED', 'FAILURE', 'CUSTOMER_CANCELLED', 'AUTHENTICATION_TIMEOUT', 'ISSUER_DECLINE', 'GENERAL_DECLINE' => 'badge-danger',
            default => 'badge-secondary'
        };
    }

    /**
     * Get human readable payment status
     */
    public function getReadableStatusAttribute(): string
    {
        return match($this->payment_status) {
            'SUCCESS', 'COMPLETED', 'SENT_FOR_CAPTURE' => 'Successful',
            'PENDING' => 'Pending',
            'PROCESSING' => 'Processing',
            'CUSTOMER_CANCELLED' => 'Cancelled by Customer',
            'AUTHENTICATION_TIMEOUT' => 'Authentication Timeout',
            'ISSUER_DECLINE' => 'Declined by Bank',
            'GENERAL_DECLINE' => 'Payment Declined',
            'FAILED', 'FAILURE' => 'Failed',
            default => ucfirst(strtolower($this->payment_status ?? 'Unknown'))
        };
    }

    /**
     * Get payment gateway display name
     */
    public function getGatewayDisplayNameAttribute(): string
    {
        return match($this->payment_gateway) {
            'payglocal' => 'PayGlocal',
            'razorpay' => 'Razorpay',
            'payu' => 'PayU',
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
            default => ucfirst($this->payment_gateway ?? 'Unknown')
        };
    }

    /**
     * Get short transaction reference
     */
    public function getShortReferenceAttribute(): string
    {
        if ($this->gid) {
            return substr($this->gid, -8);
        }
        if ($this->transaction_reference) {
            return substr($this->transaction_reference, -8);
        }
        return substr($this->id, -4);
    }

    /**
     * Get payment age in human readable format
     */
    public function getPaymentAgeAttribute(): string
    {
        return $this->payment_date ? $this->payment_date->diffForHumans() : 'Unknown';
    }

    // ========================================
    // ACTION METHODS
    // ========================================

    /**
     * Mark payment as refunded
     */
    public function markAsRefunded(float $amount = null, string $reason = null): bool
    {
        $refundAmount = $amount ?? $this->amount;
        
        // Validate refund amount
        if ($refundAmount > $this->remaining_refundable_amount) {
            throw new \InvalidArgumentException('Refund amount cannot exceed remaining refundable amount');
        }

        $updateData = [
            'is_refunded' => true,
            'refunded_amount' => $this->refunded_amount + $refundAmount,
            'refunded_at' => now(),
        ];

        // If fully refunded, update status
        if (($this->refunded_amount + $refundAmount) >= $this->amount) {
            $updateData['payment_status'] = 'REFUNDED';
        }

        // Add refund note
        if ($reason) {
            $existingNotes = $this->notes ? $this->notes . "\n" : '';
            $updateData['notes'] = $existingNotes . 'Refund: ₹' . number_format($refundAmount, 2) . ' - ' . $reason . ' (' . now()->format('Y-m-d H:i:s') . ')';
        }

        return $this->update($updateData);
    }

    /**
     * Increment retry count
     */
    public function incrementRetryCount(): bool
    {
        return $this->increment('retry_count');
    }

    /**
     * Add note to payment
     */
    public function addNote(string $note): bool
    {
        $existingNotes = $this->notes ? $this->notes . "\n" : '';
        $timestamp = now()->format('Y-m-d H:i:s');
        $newNote = "[{$timestamp}] {$note}";
        
        return $this->update([
            'notes' => $existingNotes . $newNote
        ]);
    }

    /**
     * Update payment status
     */
    public function updateStatus(string $status, string $reason = null): bool
    {
        $updateData = [
            'payment_status' => strtoupper($status),
            'processed_at' => now()
        ];

        if ($reason) {
            $updateData['failure_reason'] = $reason;
        }

        return $this->update($updateData);
    }

    // ========================================
    // STATIC METHODS
    // ========================================

    /**
     * Get payment statistics
     */
    public static function getStatistics(array $filters = []): array
    {
        $query = self::query();

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->where('payment_date', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->where('payment_date', '<=', $filters['date_to']);
        }
        if (isset($filters['gateway'])) {
            $query->where('payment_gateway', $filters['gateway']);
        }

        return [
            'total_payments' => $query->count(),
            'successful_payments' => $query->clone()->successful()->count(),
            'failed_payments' => $query->clone()->failed()->count(),
            'pending_payments' => $query->clone()->pending()->count(),
            'total_amount' => $query->clone()->successful()->sum('amount'),
            'refunded_amount' => $query->clone()->where('is_refunded', true)->sum('refunded_amount'),
            'average_amount' => $query->clone()->successful()->avg('amount'),
        ];
    }

    /**
     * Find payment by GID
     */
    public static function findByGid(string $gid): ?self
    {
        return self::where('gid', $gid)->first();
    }

    /**
     * Find payments by order
     */
    public static function findByOrder(int $orderId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('order_id', $orderId)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get recent failed payments for monitoring
     */
    public static function getRecentFailures(int $hours = 24): \Illuminate\Database\Eloquent\Collection
    {
        return self::failed()
            ->where('payment_date', '>=', Carbon::now()->subHours($hours))
            ->orderBy('payment_date', 'desc')
            ->get();
    }
}