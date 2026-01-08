<?php

namespace Modules\Software\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymSubscription;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;


class MembersSubscriptionsImport implements ToCollection, WithHeadingRow
{
    public $successfulRows = 0;
    public $failedRows = 0;
    public $errors = [];
    public $totalRows = 0;

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        $this->totalRows = $rows->count();
        $branchSettingId = Auth::guard('sw')->user()->branch_setting_id;

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because index starts at 0 and header is row 1

            try {
                DB::beginTransaction();

                // Validate row data
                $row['phone'] = (string)$row['phone'];
                $row['joining_date'] = $this->parseExcelDate($row['joining_date'] ?? null);
                $row['expire_date'] = $this->parseExcelDate($row['expire_date'] ?? null);
                if (isset($row['dob'])) {
                    $row['dob'] = $this->parseExcelDate($row['dob']);
                }
                $row['image'] = $row['image'].'.jpg';
                $validator = $this->validateRow($row, $rowNumber);

                if ($validator->fails()) {
                    $this->failedRows++;
                    $this->errors[] = [
                        'row_number' => $rowNumber,
                        'member_phone' => $row['phone'] ?? 'N/A',
                        'error_message' => implode(', ', $validator->errors()->all())
                    ];
                    DB::rollBack();
                    continue;
                }

                // Find or create member
                $member = $this->findOrCreateMember($row, $branchSettingId);

                if (!$member) {
                    $this->failedRows++;
                    $this->errors[] = [
                        'row_number' => $rowNumber,
                        'member_phone' => $row['phone'] ?? 'N/A',
                        'error_message' => 'Failed to create or find member'
                    ];
                    DB::rollBack();
                    continue;
                }

                // Find subscription by code
                $subscription = GymSubscription::where('branch_setting_id', $branchSettingId)
                    ->where(function($query) use ($row) {
                        $query->where('id', $row['subscription_code'])
                              ->orWhere('name_ar', $row['subscription_code'])
                              ->orWhere('name_en', $row['subscription_code']);
                    })
                    ->first();

                if (!$subscription) {
                    $this->failedRows++;
                    $this->errors[] = [
                        'row_number' => $rowNumber,
                        'member_phone' => $row['phone'] ?? 'N/A',
                        'error_message' => 'Subscription not found with code: ' . $row['subscription_code']
                    ];
                    DB::rollBack();
                    continue;
                }

                // Create member subscription
                $this->createMemberSubscription($row, $member, $subscription, $branchSettingId);

                $this->successfulRows++;
                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                $this->failedRows++;
                $this->errors[] = [
                    'row_number' => $rowNumber,
                    'member_phone' => $row['phone'] ?? 'N/A',
                    'error_message' => $e->getMessage()
                ];
            }
        }
    }

    /**
     * Validate row data
     */
    protected function validateRow($row, $rowNumber)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'gender' => 'nullable|in:male,female',
            'dob' => 'nullable|date_format:Y-m-d',
            'fp_id' => 'nullable|numeric',
            'fp_uid' => 'nullable|numeric',
            'subscription_code' => 'required',
            'joining_date' => 'required|date_format:Y-m-d',
            'expire_date' => 'required|date_format:Y-m-d|after:joining_date',
            'amount_paid' => 'nullable|numeric|min:0',
            'amount_remaining' => 'nullable|numeric|min:0',
            'vat_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'status' => 'nullable|in:active,expired',
            'workouts' => 'nullable|integer|min:0',
            'visits' => 'nullable|integer|min:0',
        ];

        $messages = [
            'name.required' => 'Member name is required',
            'phone.required' => 'Phone number is required',
            'gender.in' => 'Gender must be male or female',
            'dob.date_format' => 'Date of birth must be in Y-m-d format',
            'subscription_code.required' => 'Subscription code is required',
            'joining_date.required' => 'Joining date is required',
            'joining_date.date_format' => 'Joining date must be in Y-m-d format',
            'expire_date.required' => 'Expire date is required',
            'expire_date.date_format' => 'Expire date must be in Y-m-d format',
            'expire_date.after' => 'Expire date must be after joining date',
            'amount_paid.numeric' => 'Amount paid must be a number',
            'amount_paid.min' => 'Amount paid cannot be negative',
            'discount_type.in' => 'Discount type must be fixed or percentage',
            'status.in' => 'Status must be active or expired',
        ];

        return Validator::make($row->toArray(), $rules, $messages);
    }

    /**
     * Find or create member
     */
    protected function findOrCreateMember($row, $branchSettingId)
    {
        // Try to find existing member by phone and branch
        $member = GymMember::where('branch_setting_id', $branchSettingId)
            ->where('phone', $row['phone'])
            ->first();

        if ($member) {
            return $member;
        }

        // Generate member code if not provided
        $memberCode = $row['member_code'] ?? null;
        if (!$memberCode) {
            $maxCode = GymMember::withTrashed()->max('code');
            $memberCode = str_pad(((int)$maxCode + 1), 12, '0', STR_PAD_LEFT);
        }

        // Create new member
        $memberData = [
            'code' => $memberCode,
            'image' => $row['image'] ?? null,
            'name' => $row['name'],
            'phone' => $row['phone'],
            'email' => $row['email'] ?? null,
            'gender' => $this->getGenderValue($row['gender'] ?? null),
            'dob' => $row['dob'] ?? null,
            'national_id' => $row['national_id'] ?? null,
            'address' => $row['address'] ?? null,
            'sale_channel' => $row['sale_channel'] ?? null,
            'fp_id' => $row['fp_id'] ?? null,
            'fp_uid' => $row['fp_uid'] ?? null,
            'branch_setting_id' => $branchSettingId,
            'user_id' => Auth::guard('sw')->user()->id,
            'on_app' => 1,
            'sms_new_member' => 0,
            'sms_renew_member' => 0,
            'sms_before_expire_member' => 0,
            'sms_expire_member' => 0,
            'is_blocked' => 0,
        ];

        return GymMember::create($memberData);
    }

    /**
     * Create member subscription
     */
    protected function createMemberSubscription($row, $member, $subscription, $branchSettingId)
    {
        // Calculate VAT
        $vatPercentage = $row['vat_percentage'] ?? 0;
        $amountPaid = $row['amount_paid'] ?? 0;
        $vat = ($amountPaid * $vatPercentage) / 100;

        // Calculate amount remaining
        $amountRemaining = $row['amount_remaining'] ?? 0;
        if (!isset($row['amount_remaining']) && $subscription->price) {
            $amountRemaining = max(0, $subscription->price - $amountPaid);
        }

        // Determine status
        $status = 0; // Default: expired
        if (isset($row['status'])) {
            $status = $row['status'] === 'active' ? 1 : 0;
        } else {
            // Auto-determine based on expire date
            $expireDate = Carbon::parse($row['expire_date']);
            if ($expireDate->isFuture()) {
                $status = 1; // active
            }
        }

        $subscriptionData = [
            'member_id' => $member->id,
            'branch_setting_id' => $branchSettingId,
            'subscription_id' => $subscription->id,
            'joining_date' => $row['joining_date'],
            'expire_date' => $row['expire_date'],
            'workouts' => $row['workouts'] ?? 0,
            'visits' => $row['visits'] ?? 0,
            'amount_paid' => $amountPaid,
            'amount_remaining' => $amountRemaining,
            'vat' => $vat,
            'vat_percentage' => $vatPercentage,
            'discount_value' => $row['discount_value'] ?? 0,
            'discount_type' => $this->getDiscountType($row['discount_type'] ?? null),
            'payment_type' => $this->getPaymentType($row['payment_type'] ?? null),
            'status' => $status,
            'freeze_limit' => 0,
            'number_times_freeze' => 0,
            'amount_before_discount' => 0,
        ];

        return GymMemberSubscription::create($subscriptionData);
    }

    /**
     * Parse Excel date value (handles both numeric and string formats)
     */
    protected function parseExcelDate($value)
    {
        if (empty($value)) {
            return null;
        }

        // If it's already a valid date string, return it
        if (is_string($value)) {
            try {
                $date = Carbon::parse($value);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                // If parsing as string fails, try as Excel serial number
            }
        }

        // If it's a numeric value (Excel serial date)
        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Convert gender string to numeric value
     */
    protected function getGenderValue($gender)
    {
        if (!$gender) {
            return null;
        }

        return strtolower($gender) === 'male' ? 1 : 0;
    }

    /**
     * Convert discount type to numeric value
     */
    protected function getDiscountType($discountType)
    {
        if (!$discountType) {
            return 0;
        }

        return strtolower($discountType) === 'percentage' ? 1 : 0;
    }

    /**
     * Convert payment type to numeric value
     */
    protected function getPaymentType($paymentType)
    {
        if (!$paymentType) {
            return 0;
        }

        // This mapping should match your payment types table
        // Adjust as needed based on your actual payment types
        $typeMap = [
            'cash' => 0,
            'card' => 1,
            'bank' => 2,
            'online' => 3,
        ];

        return $typeMap[strtolower($paymentType)] ?? 0;
    }

    /**
     * Get import statistics
     */
    public function getStats()
    {
        return [
            'total_rows' => $this->totalRows,
            'successful_rows' => $this->successfulRows,
            'failed_rows' => $this->failedRows,
            'errors' => $this->errors
        ];
    }
}
