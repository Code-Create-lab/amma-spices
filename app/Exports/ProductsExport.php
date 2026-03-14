<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use DB;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $categoryFilter;
    protected $typeFilter;
    protected $search;

    public function __construct($categoryFilter = null, $typeFilter = null, $search = null)
    {
        $this->categoryFilter = $categoryFilter;
        $this->typeFilter = $typeFilter;
        $this->search = $search;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = DB::table('products')
            ->join('categories', 'products.cat_id', '=', 'categories.cat_id')
            ->select(
                'products.product_name',
                'products.product_id',
                'categories.title as category_title',
                'products.type'
            )
            ->where('products.approved', 1)
            ->where('products.is_deleted', 0);

        // Add category filter
        if ($this->categoryFilter) {
            $query->where('products.cat_id', $this->categoryFilter);
        }

        // Add type filter
        if ($this->typeFilter) {
            $query->where('products.type', $this->typeFilter);
        }

        // Add search filter
        if ($this->search) {
            $search = strtolower($this->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(products.product_name) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(products.product_id) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(categories.title) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(products.type) LIKE ?", ["%{$search}%"]);
            });
        }

        $query->orderBy('products.product_id', 'desc');

        return $query->get();
    }

    /**
     * Define the headings for the Excel sheet
     */
    public function headings(): array
    {
        return [
            'Product Name',
            'Product ID',
            'Category',
            'Type'
        ];
    }

    /**
     * Map data for each row
     */
    public function map($product): array
    {
        return [
            $product->product_name,
            $product->product_id,
            $product->category_title,
            $product->type
        ];
    }
}
