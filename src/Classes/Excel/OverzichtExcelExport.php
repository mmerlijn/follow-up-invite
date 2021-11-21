<?php

namespace mmerlijn\followUpInvite\Classes\Excel;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OverzichtExcelExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    private $input;
    private $cols;

    public function __construct($input, array $cols)
    {
        $this->input = $input;
        $this->cols = $cols;
    }

    public function array(): array
    {
        $data = [];
        foreach ($this->input->get() as $item) {
            $row = [];
            if (in_array('id', $this->cols)) {
                $row[] = $item->mijnsalt_id;
            }
            if (in_array('naam', $this->cols)) {
                $row[] = $item->mijnsaltpatient->naam;
            }
            if (in_array('dob', $this->cols)) {
                $row[] = $item->mijnsaltpatient->gbdatum->format('d-m-Y');
            }
            if (in_array('bsn', $this->cols)) {
                $row[] = "BSN" . $item->mijnsaltpatient->bsn;
            }
            if (in_array('locatie', $this->cols)) {
                $row[] = $item->location ? $item->location->afkorting : '';
            }
            if (in_array('aanvrager', $this->cols)) {
                $row[] = $item->aanvrager ? $item->aanvrager->achternaam : $item->requester;
            }
            if (in_array('uitnodiging1', $this->cols)) {
                $row[] = $item->last_invitation_at ? $item->last_invitation_at->format('d-m-Y') : '-';
            }
            if (in_array('uitnodiging2', $this->cols)) {
                $row[] = $item->last_reminder_invitation_at ? $item->last_reminder_invitation_at->format('d-m-Y') : '-';
            }
            if (in_array('laatste_onderzoek', $this->cols)) {
                $row[] = $item->last_test_at ? $item->last_test_at->format('d-m-Y') : '-';
            }
            if (in_array('laatste_afspraak', $this->cols)) {
                $row[] = $item->last_appointment_at ? $item->last_appointment_at->format('d-m-Y') : '-';
            }
            if (in_array('volgende_onderzoek', $this->cols)) {
                $row[] = $item->next_test_at ? $item->next_test_at->format('d-m-Y') : '-';
            }
            if (in_array('wachten_tot', $this->cols)) {
                $row[] = $item->wait_until ? $item->wait_until->format('d-m-Y') : '-';
            }
            if (in_array('stop', $this->cols)) {
                $row[] = $item->stop ? $item->stop->format('d-m-Y') : '-';
            }
            if (in_array('reden', $this->cols)) {
                $row[] = implode(",", $item->reason ?? []);
            }
            if (in_array('test_result', $this->cols)) {
                $row[] = implode(",", $item->latest_test ?? []);
            }
            $data[] = $row;
        }
        return $data;

    }

    public function headings(): array
    {
        $heading = [];
        foreach (['id', 'naam', 'dob', 'bsn', 'locatie', 'aanvrager', 'uitnodiging1', 'uitnodiging2', 'laatste_onderzoek', 'laatste_afspraak', 'volgende_onderzoek', 'wachten_tot', 'stop', 'reden', 'test_result'] as $item) {
            if (in_array($item, $this->cols)) {
                $heading[] = $item;
            }
        }
        return $heading;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }

}