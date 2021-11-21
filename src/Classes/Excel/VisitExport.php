<?php

namespace mmerlijn\followUpInvite\Classes\Excel;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use mmerlijn\followUpInvite\Models\MijnSaltLocation;

class VisitExport implements WithMultipleSheets
{
    use Exportable;

    protected $month;
    protected $year;
    protected $provider;

    public function __construct(int $year, int $month, string $provider)
    {
        $this->month = $month;
        $this->year = $year;
        $this->provider = $provider;
    }

    public function sheets(): array
    {

        $data = [['Overzicht', 'Jaar', $this->year, 'Maand', $this->month], [], ['Locatie', 'Aantal']];

        $sheets = [];
        foreach (MijnSaltLocation::whereActief(1)->get() as $location) {
            $row = [];
            $row[] = $location->naam;
            $sheet = new VisitPerLocationSheet($location, $this->year, $this->month, $this->provider);
            if ($sheet->getRowCount()) {
                $sheets[] = $sheet;
            }
            $row[] = $sheet->getRowCount();
            $data[] = $row;
        }
        $sheets = array_merge([new VisitPerLocationStartSheet($data)], $sheets);
        return $sheets;
    }

}