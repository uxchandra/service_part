<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdersTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['no_transaksi', 'part_no', 'qty', 'delivery_date'];
    }

    public function array(): array
    {
        return [
            ['SO-001', 'PN-123', 100, '15/08/2025'],
            ['SO-001', 'PN-456', 50, '15/08/2025'],
            ['SO-002', 'PN-789', 75, '20/08/2025'],
        ];
    }
}