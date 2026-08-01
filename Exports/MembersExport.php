<?php

namespace Modules\Software\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\Software\Exports\Traits\HasReportHeader;


class MembersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithTitle
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
        $data = $this->prepareForExcelHeader();
        return [
            $data
        ];
    }
    public function collection()
    {
        return collect($this->data);
    }

    public function map($record): array
    {
        $record = $this->prepareForExcelValue($record);
        return [
            $record
        ];
    }
    private function prepareForExcelHeader()
    {
        foreach($this->keys as $row) {
            $arr[] = trans('sw.'.$row);
        }
        return $arr;
    }
    private function prepareForExcelValue($data)
    {
        if (is_scalar($data) || is_null($data)) {
            return array_fill(0, count($this->keys), null);
        }
        foreach($this->keys as $key) {
            if($key == 'barcode')
                $arr[] = $data['code'] ?? null;
            else if($key == 'membership')
                $arr[] = $data['member_subscription_info']['subscription']['name'] ?? null;
            else if($key == 'dob')
                $arr[] = $data['dob'] ?? null;
            else if($key == 'national_id')
                $arr[] = $data['national_id'] ?? null;
            else if($key == 'workouts')
                $arr[] = $data['member_subscription_info']['workouts'] ?? null;
            else if($key == 'number_of_visits')
                $arr[] = $data['member_subscription_info']['visits'] ?? null;
            else if($key == 'amount_remaining')
                $arr[] = $data['member_subscription_info']['amount_remaining'] ?? null;
            else if($key == 'store_balance')
                $arr[] = $data['store_balance'] ?? null;
            else if($key == 'joining_date')
                $arr[] = isset($data['member_subscription_info']['joining_date']) ? Carbon::parse($data['member_subscription_info']['joining_date'])->toDateString() : null;
            else if($key == 'expire_date')
                $arr[] = isset($data['member_subscription_info']['expire_date']) ? Carbon::parse($data['member_subscription_info']['expire_date'])->toDateString() : null;
            else if($key == 'status')
                $arr[] = $data['member_subscription_info']['status_name'] ?? null;
            else if($key == 'created_at')
                $arr[] = isset($data['created_at']) ? Carbon::parse($data['created_at'])->toDateString() : null;
            else
                $arr[] = $data[$key] ?? null;
        }
        return $arr;
    }
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
    public function title(): string
    {
        return trans('sw.records_data');
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $rtl = ($this->lang == 'ar');
                $event->sheet->getDelegate()->setRightToLeft($rtl);

                if ($this->settings) {
                    $this->applyReportHeader($event, count($this->keys));
                }
            }
        ];
    }
}
