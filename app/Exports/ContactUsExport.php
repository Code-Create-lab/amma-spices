<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\ContactUs;

class ContactUsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $index = 0;

    public function collection()
    {
        return ContactUs::orderBy('id', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'Full Name',
            'Phone',
            'Email',
            'Subject',
            'Message',
            'Date',
        ];
    }

    public function map($enquiry): array
    {
        $this->index++;

        return [
            $this->index,
            $enquiry->full_name,
            $enquiry->phone_number,
            $enquiry->email,
            $enquiry->subject ?? '',
            $enquiry->message,
            $enquiry->created_at ? $enquiry->created_at->format('d-m-Y h:i A') : '',
        ];
    }
}
