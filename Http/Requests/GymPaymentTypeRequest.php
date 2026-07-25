<?php

namespace Modules\Software\Http\Requests;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Models\GymPaymentType;

class GymPaymentTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
//            'name_ar' => 'required',
//            'name_en' => 'required',
//            'price' => 'required|numeric',
//            'content_ar' => 'max:250',
//            'content_en' => 'max:250',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $selectedPaymentMethods = $this->selectedPaymentMethods();
            if (empty($selectedPaymentMethods)) {
                return;
            }

            $currentPaymentTypeId = $this->currentPaymentTypeId();
            $conflicts = $this->findConflictingPaymentMethods($selectedPaymentMethods, $currentPaymentTypeId);

            if (empty($conflicts)) {
                return;
            }

            $validator->errors()->add('payment_methods', $this->buildConflictMessage($conflicts));
        });
    }

    private function selectedPaymentMethods(): array
    {
        $paymentMethods = array_values(array_unique(array_filter(array_map('intval', (array) $this->input('payment_methods', [])))));

        if (empty($paymentMethods) && $this->filled('payment_method')) {
            $paymentMethods = [(int) $this->input('payment_method')];
        }

        return $paymentMethods;
    }

    private function findConflictingPaymentMethods(array $paymentMethods, int $currentPaymentTypeId): array
    {
        $conflicts = [];

        if (Schema::hasTable('sw_gym_payment_type_methods')) {
            $pivotConflicts = DB::table('sw_gym_payment_type_methods as pivot')
                ->join('sw_gym_payment_types as payment_types', 'payment_types.id', '=', 'pivot.payment_type_id')
                ->whereIn('pivot.payment_method', $paymentMethods)
                ->where('pivot.payment_type_id', '!=', $currentPaymentTypeId)
                ->whereNull('payment_types.deleted_at')
                ->select('pivot.payment_method', 'payment_types.name_ar', 'payment_types.name_en')
                ->get();

            foreach ($pivotConflicts as $row) {
                $conflicts[(int) $row->payment_method] = $this->paymentTypeDisplayName($row);
            }
        }

        $legacyConflicts = GymPaymentType::query()
            ->whereIn('payment_method', $paymentMethods)
            ->when($currentPaymentTypeId > 0, function ($query) use ($currentPaymentTypeId) {
                $query->where('id', '!=', $currentPaymentTypeId);
            })
            ->get(['payment_method', 'name_ar', 'name_en']);

        foreach ($legacyConflicts as $paymentType) {
            $methodId = (int) $paymentType->payment_method;
            if (!isset($conflicts[$methodId])) {
                $conflicts[$methodId] = $this->paymentTypeDisplayName($paymentType);
            }
        }

        return $conflicts;
    }

    private function buildConflictMessage(array $conflicts): string
    {
        $gatewayLabels = $this->paymentMethodLabels();
        $messages = [];

        foreach ($conflicts as $methodId => $paymentTypeName) {
            $methodName = $gatewayLabels[$methodId] ?? ('#' . $methodId);
            $messages[] = str_replace(
                [':payment_method', ':payment_type'],
                [$methodName, $paymentTypeName],
                trans('sw.payment_method_already_assigned')
            );
        }

        return implode(' ', $messages);
    }

    private function paymentMethodLabels(): array
    {
        return [
            TypeConstants::TABBY_TRANSACTION => 'Tabby',
            TypeConstants::PAYMOB_TRANSACTION => 'Paymob',
            TypeConstants::TAMARA_TRANSACTION => 'Tamara',
            TypeConstants::PAYTABS_TRANSACTION => 'PayTabs',
        ];
    }

    private function paymentTypeDisplayName($paymentType): string
    {
        $locale = app()->getLocale();
        if ($locale === 'ar' && !empty($paymentType->name_ar)) {
            return (string) $paymentType->name_ar;
        }

        if (!empty($paymentType->name_en)) {
            return (string) $paymentType->name_en;
        }

        return (string) ($paymentType->name_ar ?? trans('sw.payment_type'));
    }

    private function currentPaymentTypeId(): int
    {
        $paymentType = $this->route('payment_type');

        if (is_object($paymentType) && isset($paymentType->id)) {
            return (int) $paymentType->id;
        }

        return (int) $paymentType;
    }
}

