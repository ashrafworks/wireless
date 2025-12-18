<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NumberExport implements FromCollection, WithProperties, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
{
    
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $data = [];

    public function __construct($data)
    {
        $this->data = $data;
    }
    /**
     * @return string
     */
    public function title(): string
    {
        return 'Number Exports';
    }

    public function collection()
    {
        return $this->data;
    }

    public function styles(Worksheet $sheet)
    {

        return [

            // Style the first row as bold text.
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Phone Number',
            'Account Number',
            'Pin',
            'Expired At',
        ];
    }

    public function properties(): array
    {
        return [
            'creator' => 'Number',
            'lastModifiedBy' => 'Number',
            'title' => 'Number Exports '. Carbon::today()->toDateString(),
            'description' => 'Number Exports '. Carbon::today()->toDateString(),
            'subject' => 'Exports',
            'keywords' => 'Exports',
            'category' => 'Exports',
            'manager' => 'Number',
            'company' => 'Number',
        ];
    }
}