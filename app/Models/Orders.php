<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\OrderPayments;
use App\Models\OrderRefund;


class Orders extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';
    protected $primaryKey = 'order_id';

    public $timestamps = false;

    protected $fillable = [
        'product_name',
        'varient_image',
        'quantity',
        'unit',
        'varient_id',
        'qty',
        'cart_id',
        'order_date',
        'order_time',
        'store_approval',
        'store_id',
        'user_id',
        'address_id',
        'total_products_mrp',
        'price_without_delivery',
        'total_price',
        'delivery_date',
        'time_slot',
        'payment_method',
        'payment_status',
        'order_status',
        'rem_price',
        'coupon_discount',
        'delivery_charge',
        'coupon_id',
        'razorpay_order_id',
        'razorpay_refund_id	',
        'payment_mode',
        'paid_at',
        'length' ,
        'weight' ,
        'breadth'   ,
        'height',
        'is_view',

    ];

    /**
     * If the last week's value is greater than zero, then return the difference between this week's
     * value and last week's value divided by last week's value divided by 100. Otherwise, return this
     * week's value.
     * 
     * @param float last_week the number of orders from last week
     * @param float this_week the number of orders this week
     * 
     * @return The percentage change in orders from last week to this week.
     */
    public static function getOrdersIndex(float $last_week, float $this_week)
    {
        if ($last_week > 0) {
            $reference = $last_week / 100;
            return ($this_week - $last_week) / $reference;
        } else {
            return $this_week;
        }
    }

    public function getOrderByCart(string $cart_id, bool $only_exists = false)
    {
        if ($only_exists) {
            return Orders::where('cart_id', $cart_id)->first();
        } else {
            $result = Orders::where('cart_id', $cart_id)->get();
            return ($result->count() > 0) ? $result : false;
        }
    }

    /**
     * A function to fetch all orders for a specific User. It can return first result or all records related.
     * 
     * @param int user_id The user id of the user you want to get the orders for.
     * @param bool only_exists if true, it will return the first result, if false, it will return all
     * results.
     * 
     * @return An array with orders related for a specific user, if .
     */
    public function getUserOrders(int $user_id, bool $only_exists = false)
    {
        if ($only_exists) {
            return Orders::where('user_id', $user_id)->first();
        } else {
            $result = Orders::where('user_id', $user_id)->get();
            return ($result->count() > 0) ? $result : false;
        }
    }

    public function getUserOrderStore(int $user_id, int $store_id, bool $only_exists = false)
    {
        if ($only_exists) {
        } else {
        }
    }

    // This function will fetch Order info using a particular coupon code
    public function getOrdersUsingCoupon(string $cart_id, string $coupon_code, bool $only_exists = false) {}


    public function getOrderByStore(int $store_id, bool $only_exists = false)
    {
        if ($only_exists) {
            return Orders::where('store_id', $store_id)->first();
        } else {
            $result = Orders::where('store_id', $store_id)->get();
            return ($result->count() > 0) ? $result : false;
        }
    }

    public function getOrderByID(int $order_id)
    {
        return Orders::where('order_id', $order_id)->first();
    }

    /*
        - Check Min Cart Value
        - Coupon Max Uses
        - Use User_Id only when there are no previous orders. Otherwise, it will be checked by default
    */
    public function couponApply2Cart(string $cart_id, array $coupon_info, int $user_id = null)
    {
        if (!isset($coupon_info['id'], $coupon_info['store_id']))
            return -1;
        $order = $this->getOrderByCart($cart_id, true);
        if ($order->order_status == 'Cancelled')
            return -1;
        if ($order->store_id !== $coupon_info['store_id'])
            return -1;   // If coupon doesn't belongs to store, fail

        // If User_id is set, it means there are no orders within the system for that user
        if (!is_null($user_id)) {
            if ($user_id !== $order->user_id)
                return -1;      // We need to double check cart belongs to user_id, otherwise fail
        } else {
            $user_id = $order->user_id; // First order condition met, user_id needs to be assigned directly from cart_id
            $user_orders_from_store = $this->getUserOrderStore($user_id, $coupon_info['store_id']);  // Get Orders from User to a particular store
            $max_uses_cnt = 0;
            // We must check if user_orders_from_store have applied this coupon and count results, if max_exceed return false
            foreach ($user_orders_from_store as $order) {
                if ($order->coupon_id == $coupon_info['id'])
                    ++$max_uses_cnt;
            }
            if ($max_uses_cnt > $coupon_info['max_uses'])
                return 1;
        }
        if (isset($coupon_info['min_cart']) && ($coupon_info['min_cart'] > 0) && ($order->total_price < $coupon_info['min_cart']))
            return false;
        // Create list of fields to update once coupon can be applied
        $total_price = $order->total_price;
        $per = ($coupon_info['type_discount'] == 'percent') ? ($total_price * $coupon_info['amount']) / 100 : $coupon_info['amount'];
        $per = round($per, 2, PHP_ROUND_HALF_UP);
        if ($per > $coupon_info['max_discount'])
            $per = $coupon_info['max_discount'];    // Checking max_discount is not exceeded. If it exceeds, will set max_discount value
        $rem_price = $total_price - $per;
        // Collecting fields to update and then call UpdateOrderInfo
        $fields = [
            'rem_price' => $rem_price,
            'coupon_discount' => $per,
            'coupon_id' => $coupon_info['id']
        ];
        $updateResults = $this->updateOrderInfo($cart_id, $fields, false);
        if ($updateResults) {
            $order = $this->getOrderByCart($cart_id, true);
            $order->discountonmrp = round($order->total_products_mrp - $order->price_without_delivery, 2, PHP_ROUND_HALF_UP);
            return $order;
        } else {
            return false;
        }
    }

    private function updateOrderInfo(string $cart_id, array $fields, bool $createIfNotExists = false)
    {
        if (!isset($cart_id) || is_null($cart_id) || empty($fields))
            return false;   // There is nothing to be updated. Return false
        if ($createIfNotExists) {
            return Orders::updateOrCreate(['cart_id' => $cart_id], $fields);
        } else {
            return Orders::where('cart_id', $cart_id)->update($fields);
        }
    }

    public function orderItems()
    {
        return $this->hasMany(StoreOrders::class, 'order_cart_id', 'cart_id');
    }
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'address_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function coupon()
    {
        return $this->hasOne(Coupon::class, 'coupon_id', 'coupon_id');
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class, 'order_id', 'order_id');
    }

    public function shipmentTracking()
    {
        return $this->hasOne(ShipmentTracking::class, 'order_id', 'order_id');
    }

    public function payment()
    {
        return $this->hasOne(OrderPayments::class, 'order_id', 'order_id');
    }

    public function refund()
    {
        return $this->hasOne(OrderRefund::class, 'order_id', 'order_id');
    }

    /**
     * Get the tracking information for the order
     */
    public function tracking()
    {
        return $this->hasOne(ShipmentTracking::class, 'order_id', 'order_id');
    }

    /**
     * Get all tracking updates for the order
     */
    public function trackingHistory()
    {
        return $this->hasMany(ShipmentTracking::class, 'order_id', 'order_id')
            ->orderBy('latest_scan_time', 'desc');
    }

    /**
     * Get the latest tracking update
     */
    public function latestTracking()
    {
        return $this->hasOne(ShipmentTracking::class, 'order_id', 'order_id')
            ->latest('latest_scan_time');
    }
}
