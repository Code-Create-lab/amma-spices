<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Orders;

class SalesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;
    protected $paymentMethod;

    public function __construct($startDate = null, $endDate = null, $paymentMethod = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->paymentMethod = $paymentMethod;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Orders::with(['user', 'address'])
            ->where('order_status', '!=', 'cancelled')
            ->where('payment_status', '!=', 'failed')
            ->orderBy('order_date', 'DESC');

        // Apply date filters
        if ($this->startDate && $this->endDate) {
            $query->where('order_date', '>=', $this->startDate)
                  ->where('order_date', '<=', $this->endDate);
        }

        // Apply payment method filter
        if ($this->paymentMethod) {
            if ($this->paymentMethod == "COD") {
                $query->where('payment_method', 'COD');
            } elseif ($this->paymentMethod == "wallet") {
                $query->where('payment_method', 'wallet');
            } elseif ($this->paymentMethod == "online" || $this->paymentMethod == "Online") {
                $query->where('payment_method', '!=', 'COD')
                      ->where('payment_method', '!=', 'wallet')
                      ->where('payment_method', '!=', NULL);
            }
        }

        return $query->get();
    }

    /**
     * Define the headings for the Excel sheet
     */
    public function headings(): array
    {
        return [
            'Cart ID',
            'Cart Price',
            'User Name',
            'User Phone',
            'Order Date',
            'Order Status',
            'Payment Status',
            'Payment Method'
        ];
    }

    /**
     * Map data for each row
     */
    public function map($order): array
    {
        $orderStatus = $order->order_status ?? 'NOT PLACED';
        $paymentStatus = $order->payment_status ?? 'Pending';

        // Map payment status to readable format
        $paymentStatusReadable = match(strtolower($paymentStatus)) {
            'success', 'paid', 'successful' => 'Paid',
            'cod' => 'COD',
            'pending' => 'Pending',
            'failed' => 'Failed',
            default => ucfirst($paymentStatus)
        };

        return [
            $order->cart_id,
            $order->total_price,
            $order->user?->name ?? 'N/A',
            $order->user?->user_phone ?? 'N/A',
            $order->order_date,
            ucfirst($orderStatus),
            $paymentStatusReadable,
            $order->payment_method ?? 'N/A'
        ];
    }
}
