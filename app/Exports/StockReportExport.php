<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use DB;

class StockReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $categoryId;
    protected $index = 0;

    public function __construct($categoryId = null)
    {
        $this->categoryId = $categoryId;
    }

    public function collection()
    {
        $query = DB::table('variations')
            ->join('products', 'variations.product_id', '=', 'products.product_id')
            ->join('categories', 'products.cat_id', '=', 'categories.cat_id')
            ->leftJoin(DB::raw('(SELECT varient_id, SUM(qty) as total_ordered
                FROM store_orders
                INNER JOIN orders ON store_orders.order_cart_id = orders.cart_id
                WHERE orders.order_status != "cancelled"
                GROUP BY varient_id) as ordered'), 'variations.id', '=', 'ordered.varient_id')
            ->where('products.is_deleted', 0)
            ->where('variations.is_deleted', 0)
            ->select(
                'variations.id',
                'products.product_name',
                'variations.uuid',
                'categories.title as cat_name',
                'variations.stock',
                DB::raw('COALESCE(ordered.total_ordered, 0) as total_ordered')
            );

        if ($this->categoryId) {
            $query->where('products.cat_id', $this->categoryId);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'Product Name',
            'Variation',
            'Category',
            'Stock Qty',
            'Ordered Qty',
            'Available Qty',
        ];
    }

    public function map($row): array
    {
        $this->index++;

        return [
            $this->index,
            $row->product_name,
            $row->uuid,
            $row->cat_name,
            $row->stock,
            $row->total_ordered,
            $row->stock,
        ];
    }
}
