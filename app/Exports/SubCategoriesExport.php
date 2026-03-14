<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use DB;

class SubCategoriesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = DB::table('categories as c')
            ->leftJoin('categories as p', 'c.parent', '=', 'p.cat_id')
            ->select(
                'c.title',
                DB::raw("IFNULL(p.title, '-') as parent_name"),
                'c.cat_id'
            )
            ->where('c.is_deleted', 0)
            ->where(function ($q) {
                $q->whereNull('p.is_deleted')
                  ->orWhere('p.is_deleted', 0);
            })
            ->orderBy('c.parent', 'asc')
            ->orderBy('c.level', 'asc')
            ->orderBy('c.cat_id', 'desc');

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereRaw("LOWER(c.title) LIKE ?", ["%{$this->search}%"])
                  ->orWhereRaw("LOWER(p.title) LIKE ?", ["%{$this->search}%"])
                  ->orWhere('c.cat_id', 'like', "%{$this->search}%");
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
            'Title',
            'Parent Category',
            'Cat Id'
        ];
    }

    /**
     * Map data for each row
     */
    public function map($category): array
    {
        return [
            $category->title,
            $category->parent_name,
            $category->cat_id
        ];
    }
}
