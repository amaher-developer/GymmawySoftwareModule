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


class MembersAttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithTitle
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
                $arr[] = $data['member']['code'];
            else if($key == 'name')
                $arr[] = $data['member']['name'];
            else if($key == 'phone')
                $arr[] = $data['member']['phone'];
            else if($key == 'membership')
                $arr[] = $data['member']['member_subscription_info']['subscription']['name'];
            else if($key == 'subscription')
                $arr[] = $data['subscription']['name'];
            else if($key == 'dob')
                $arr[] = $data['member']['dob'];
            else if($key == 'national_id')
                $arr[] = $data['member']['national_id'];
            else if($key == 'workouts')
                $arr[] = $data['member']['member_subscription_info']['workouts'];
            else if($key == 'number_of_visits')
                $arr[] = $data['member']['member_subscription_info']['visits'];
            else if($key == 'amount_remaining')
                $arr[] = $data['member']['member_subscription_info']['amount_remaining'];
            else if($key == 'joining_date')
                $arr[] = Carbon::parse($data['member']['member_subscription_info']['joining_date'])->toDateString();
            else if($key == 'expire_date')
                $arr[] = Carbon::parse($data['member']['member_subscription_info']['expire_date'])->toDateString();
            else if($key == 'status')
                $arr[] = $data['member']['member_subscription_info']['status_name'];
            else if($key == 'created_at')
                $arr[] =  Carbon::parse($data['created_at'])->toDateTimeString();
            else if($key == 'pt_membership')
                $arr[] =  $data['pt_member_subscription']['pt_subscription']['name'];
            else if($key == 'pt_subscription')
                $arr[] = $data['pt_subscription']['name'];
            else if($key == 'pt_classes')
                $arr[] =  $data['pt_member_subscription']['sessions_total'] ?? $data['pt_member_subscription']['classes'];
            else if($key == 'pt_sessions_used')
                $arr[] =  $data['pt_member_subscription']['sessions_used'] ?? $data['pt_member_subscription']['visits'];
            else if($key == 'pt_visits')
                $arr[] =  $data['pt_member_subscription']['sessions_used'] ?? $data['pt_member_subscription']['visits'];
            else if($key == 'pt_amount_remaining')
                $arr[] =  $data['pt_member_subscription']['amount_remaining'];
            else if($key == 'pt_joining_date')
                $arr[] = Carbon::parse($data['pt_member_subscription']['joining_date'])->toDateString();
            else if($key == 'pt_expire_date')
                $arr[] = Carbon::parse($data['pt_member_subscription']['expire_date'])->toDateString();
            else if($key == 'non_created_at')
                $arr[] = Carbon::parse($data['date'])->toDateTimeString();
            else if($key == 'non_name')
                $arr[] =  @$data['member']['name'] ?? @$data['non_member']['name'];
            else if($key == 'non_phone')
                $arr[] =  @$data['member']['phone'] ?? @$data['non_member']['phone'];
            else if($key == 'non_membership')
                $arr[] =  $data['activity']['name'];
            else
                $arr[] = $data[$key];
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
