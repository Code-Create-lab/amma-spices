<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Orders;
use DB;

class UserSalesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $index = 0;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $users = DB::table('users')->get();

        foreach ($users as $u) {
            $order = Orders::join('address', 'orders.address_id', '=', 'address.address_id')
                ->select('address.*', 'orders.*', DB::raw('SUM(total_price) as amount'))
                ->where('orders.user_id', $u->id)
                ->where('orders.payment_method', '!=', NULL)
                ->where('orders.order_status', '!=', 'cancelled')
                ->selectRaw('count(orders.order_id) as cnt')
                ->selectRaw('max(orders.total_price) as highest')
                ->selectRaw('min(orders.total_price) as lowest')
                ->first();

            $u->cnt = $order->cnt;
            $u->highest = $order->highest;
            $u->lowest = $order->lowest;
            $u->amount = $order->amount;
            $u->city = $order->city;
            $u->state = $order->state;
            $u->house_no = $order->house_no;
            $u->society = $order->society;
            $u->landmark = $order->landmark;
            $u->pincode = $order->pincode;
        }

        return $users;
    }

    /**
     * Define the headings for the Excel sheet
     */
    public function headings(): array
    {
        return [
            '#',
            'Name',
            'Phone',
            'Email',
            'Total Orders',
            'Total Amount',
            'Highest Amount',
            'Lowest Amount',
            'Address',
        ];
    }

    /**
     * Map data for each row
     */
    public function map($user): array
    {
        $this->index++;

        $address = implode(', ', array_filter([
            $user->house_no ?? '',
            $user->society ?? '',
            $user->landmark ?? '',
            $user->pincode ?? '',
        ]));

        return [
            $this->index,
            $user->name ?? 'N/A',
            $user->user_phone ?? 'N/A',
            $user->email ?? 'N/A',
            $user->cnt ?? 0,
            round($user->amount ?? 0, 2),
            round($user->highest ?? 0, 2),
            round($user->lowest ?? 0, 2),
            $address,
        ];
    }
}
