<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use DB;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $fromDate;
    protected $toDate;
    protected $search;

    public function __construct($fromDate = null, $toDate = null, $search = null)
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->search = $search;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = DB::table('users')
            ->select([
                'users.id',
                'users.name',
                'users.user_phone',
                'users.email',
                'users.reg_date',
                'users.block'
            ])
            ->whereNotNull('users.email')
            ->whereNotNull('users.user_phone')
            ->orderBy('users.reg_date', 'desc');

        // Apply date filter
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween(
                DB::raw('DATE(users.reg_date)'),
                [$this->fromDate, $this->toDate]
            );
        }

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('users.name', 'like', "%{$this->search}%")
                  ->orWhere('users.user_phone', 'like', "%{$this->search}%")
                  ->orWhere('users.email', 'like', "%{$this->search}%");
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
            'User Name',
            'User Phone',
            'User Email',
            'Registration Date',
            'Status'
        ];
    }

    /**
     * Map data for each row
     */
    public function map($user): array
    {
        return [
            $user->name,
            $user->user_phone,
            $user->email,
            $user->reg_date,
            $user->block == 1 ? 'Blocked' : 'Active'
        ];
    }
}
