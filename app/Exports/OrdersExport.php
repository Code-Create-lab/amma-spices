<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Orders;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $search;
    protected $statusFilter;
    protected $fromDate;
    protected $toDate;

    public function __construct($search = null, $statusFilter = null, $fromDate = null, $toDate = null)
    {
        $this->search = $search;
        $this->statusFilter = $statusFilter;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Orders::with(['user', 'address'])
            ->orderBy('order_id', 'DESC');

        // Add status filter
        if ($this->statusFilter) {
            $query->where(function($q) {
                $q->where('order_status', $this->statusFilter)
                  ->orWhere('order_status', ucfirst($this->statusFilter))
                  ->orWhere('order_status', strtoupper($this->statusFilter));
            });
        }

        // Add date range filter
        if ($this->fromDate) {
            $query->where('order_date', '>=', $this->fromDate);
        }
        if ($this->toDate) {
            $query->where('order_date', '<=', $this->toDate);
        }

        // Add search filter
        if ($this->search) {
            $search = strtolower($this->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(cart_id) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(order_status) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(payment_method) LIKE ?", ["%{$search}%"])
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->whereRaw("LOWER(name) LIKE ?", ["%{$search}%"])
                                ->orWhereRaw("LOWER(user_phone) LIKE ?", ["%{$search}%"]);
                  });
            });
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
            'Status',
            'Payment Status',
            'Payment Method'
        ];
    }

    /**
     * Map data for each row
     */
    public function map($order): array
    {
        $status = $order->order_status ?? 'NOT PLACED';

        return [
            $order->cart_id,
            $order->total_price,
            $order->user?->name ?? 'N/A',
            $order->user?->user_phone ?? 'N/A',
            $order->order_date,
            ucfirst($status),
            $order->payment_status ?? 'N/A',
            $order->payment_method ?? 'N/A'
        ];
    }
}
