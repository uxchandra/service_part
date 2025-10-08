<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BarangTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        return [
            [
                'QR001',
                'PN-12345',
                'Customer A',
                'Part Name Example',
                '100x50',
                'Blue',
                'keypoint/AR.ADL.0001.jpeg' // Contoh path keypoint
            ],
            [
                'QR002',
                'PN-67890',
                'Customer B',
                'Part Name Example 2',
                '200x100',
                'Red',
                'keypoint/AR.ADL.0002.jpeg'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'qr_label',
            'part_no',
            'customer',
            'part_name',
            'size_plastic',
            'part_color',
            'keypoint' // Tambahkan header keypoint
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // qr_label
            'B' => 15, // part_no
            'C' => 20, // customer
            'D' => 25, // part_name
            'E' => 15, // size_plastic
            'F' => 15, // part_color
            'G' => 30, // keypoint (lebih lebar untuk path)
        ];
    }
}