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


class MembersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithTitle
{

    private $lang;
    private $data;
    private $keys;
    public function __construct($data)
    {
        $this->lang = $data['lang'];
        $this->data = $data['records'];
        $this->keys = $data['keys'];
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
        return $this->data;
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
        foreach($this->keys as $key) {
            if($key == 'barcode')
                $arr[] = $data['code'];
            else if($key == 'membership')
                $arr[] = @$data['member_subscription_info']['subscription']['name'];
            else if($key == 'dob')
                $arr[] = $data['dob'];
            else if($key == 'national_id')
                $arr[] = $data['national_id'];
            else if($key == 'workouts')
                $arr[] = @$data['member_subscription_info']['workouts'];
            else if($key == 'number_of_visits')
                $arr[] = @$data['member_subscription_info']['visits'];
            else if($key == 'amount_remaining')
                $arr[] = @$data['member_subscription_info']['amount_remaining'];
            else if($key == 'store_balance')
                $arr[] = $data['store_balance'];
            else if($key == 'joining_date')
                $arr[] = Carbon::parse(@$data['member_subscription_info']['joining_date'])->toDateString();
            else if($key == 'expire_date')
                $arr[] = Carbon::parse(@$data['member_subscription_info']['expire_date'])->toDateString();
            else if($key == 'status')
                $arr[] = @$data['member_subscription_info']['status_name'];
            else if($key == 'created_at')
                $arr[] =  Carbon::parse($data['created_at'])->toDateString();
            else
                $arr[] = @$data[$key];
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
            AfterSheet::class    => function(AfterSheet $event) {
                if ($this->lang == 'ar') $rtl = true; else $rtl = false;
                $event->sheet->getDelegate()
                    ->setRightToLeft($rtl);
            }
        ];
    }
}
