<?php

namespace mmerlijn\followUpInvite\Classes\Excel;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use mmerlijn\followUpInvite\Models\FollowUpPatient;
use mmerlijn\followUpInvite\Models\MijnSaltLocation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VisitPerLocationSheet implements FromArray, WithTitle, WithHeadings, WithColumnWidths, WithStyles
{
    private $month;
    private $year;
    private $location;
    private $provider;
    private $rows;

    public function __construct(MijnSaltLocation $location, int $year, int $month, string $provider)
    {
        $this->month = $month;
        $this->year = $year;
        $this->location = $location;
        $this->provider = $provider;
        $this->rows = FollowUpPatient::whereLastVisitLocation($this->location->locatieId)
            ->whereProvider($this->provider)
            ->whereYear('last_appointment_at', $this->year)
            ->whereMonth('last_appointment_at', $this->month)
            ->count();
    }

    public function array(): array
    {
        $result = [];
        foreach (FollowUpPatient::whereLastVisitLocation($this->location->locatieId)
                     ->whereProvider($this->provider)
                     ->whereYear('last_appointment_at', $this->year)
                     ->whereMonth('last_appointment_at', $this->month)
                     ->with(['mijnsaltpatient', 'aanvrager'])->cursor()
                 as $item) {
            $row = [];
            $row[] = $item->mijnsaltpatient->contactId;
            $row[] = $item->mijnsaltpatient->naam;
            $row[] = $item->mijnsaltpatient->gbdatum->format('d-m-Y');
            $row[] = $item->mijnsaltpatient->bsn;
            $row[] = $item->aanvrager->achternaam . ", " . $item->aanvrager->voorletters;
            $row[] = $item->aanvrager->agbcode;
            $row[] = $item->last_appointment_at->format('d-m-Y');
            $row[] = ($item->last_appointment_at != $item->last_test_at) ? 'NO SHOW' : 'DONE';
            $result[] = $row;

        }
        return $result;
    }


    public function headings(): array
    {
        return [
            'ID', 'Naam', 'Geb datum', 'BSN', 'Aanvrager', 'Agbcode', 'Afspraak datum', 'Type'
        ];
    }

    public function title(): string
    {
        return $this->location->naam . " " . $this->year . "-" . $this->month;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 40,
            'C' => 12,
            'D' => 12,
            'E' => 48,
            'F' => 10,
            'G' => 14,
            'H' => 32
        ];
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],

        ];
    }
}