<?php

namespace mmerlijn\followUpInvite\Classes\Excel;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class VisitPerLocationStartSheet implements FromArray, WithTitle, WithColumnWidths, WithStyles
{
    private array $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $this->data[] = ['', '=SUM(B3:B' . (count($this->data) - 1) . ')'];
        return $this->data;
    }

    public function title(): string
    {
        return 'Overzicht';
    }


    public function columnWidths(): array
    {
        return [
            'A' => 64,
            'B' => 16
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
            3 => ['borders' => ['top' => ['borderStyle' => Border::BORDER_THIN]]]
        ];
    }
}