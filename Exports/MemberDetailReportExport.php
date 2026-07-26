<?php

namespace Modules\Software\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\Software\Exports\Traits\HasReportHeader;

class MemberDetailReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithTitle
{
    use HasReportHeader;

    private $lang;
    private $data;
    private $keys;
    private $settings;

    public function __construct($data)
    {
        $this->lang = $data['lang'];
        $this->data = $data['records'];
        $this->keys = $data['keys'];
        $this->settings = $data['settings'] ?? null;
    }

    public function headings(): array
    {
        $arr = [];
        foreach ($this->keys as $key) {
            $arr[] = trans('sw.' . $key);
        }
        return [$arr];
    }

    public function collection()
    {
        return $this->data;
    }

    public function map($record): array
    {
        $arr = [];
        foreach ($this->keys as $key) {
            $arr[] = $record[$key] ?? null;
        }
        return [$arr];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return trans('sw.records_data');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $rtl = ($this->lang == 'ar');
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft($rtl);

                if ($this->settings) {
                    $this->applyReportHeader($event, count($this->keys));
                }
            }
        ];
    }
}
