@extends('software::layouts.form')
@section('breadcrumb')
    <!--begin::Breadcrumb-->
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <!--begin::Item-->
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home') }}</a>
        </li>
        <!--end::Item-->
        <!--begin::Item-->
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-300 w-5px h-2px"></span>
        </li>
        <!--end::Item-->
        <!--begin::Item-->
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.listUser') }}" class="text-muted text-hover-primary">{{ trans('sw.users')}}</a>
        </li>
        <!--end::Item-->
        <!--begin::Item-->
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-300 w-5px h-2px"></span>
        </li>
        <!--end::Item-->
        <!--begin::Item-->
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.listUserTransaction') }}" class="text-muted text-hover-primary">{{ trans('sw.employee_transactions')}}</a>
        </li>
        <!--end::Item-->
        <!--begin::Item-->
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-300 w-5px h-2px"></span>
        </li>
        <!--end::Item-->
        <!--begin::Item-->
        <li class="breadcrumb-item text-gray-900">{{ $title }}</li>
        <!--end::Item-->
    </ul>
    <!--end::Breadcrumb-->
@endsection

@section('form_title') {{ @$title }} @endsection

@section('page_body')
    <!--begin::Form-->
    <form method="post" action="{{ $transaction->id ? route('sw.updateUserTransaction', $transaction->id) : route('sw.storeUserTransaction') }}" class="form">
        {{csrf_field()}}
        
        <div class="row g-7">
            <!--begin::Transaction Info-->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ trans('sw.transaction_information')}}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!--begin::Employee-->
                            <div class="col-md-12 mb-10">
                                <div class="fv-row">
                                    <label class="required form-label">{{ trans('sw.employee')}}</label>
                                    <select name="employee_id" id="employee_id" class="form-select" data-control="select2" 
                                            data-placeholder="{{ trans('sw.select_employee')}}" required>
                                        <option value="">{{ trans('sw.select_employee')}}</option>
                                        @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" 
                                            {{ old('employee_id', $transaction->employee_id) == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->name }} - {{ $employee->email }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <!--end::Employee-->

                            <!--begin::Transaction Type-->
                            <div class="col-md-6 mb-10">
                                <div class="fv-row">
                                    <label class="required form-label">{{ trans('sw.transaction_type')}}</label>
                                    <select name="transaction_type" id="transaction_type" class="form-select" required>
                                        <option value="">{{ trans('sw.select_transaction_type')}}</option>
                                        <option value="monthly_salary" {{ old('transaction_type', $transaction->transaction_type) == 'monthly_salary' ? 'selected' : '' }}>
                                            {{ trans('sw.monthly_salary')}}
                                        </option>
                                        <option value="commission_private_training" {{ old('transaction_type', $transaction->transaction_type) == 'commission_private_training' ? 'selected' : '' }}>
                                            {{ trans('sw.commission_private_training')}}
                                        </option>
                                        <option value="commission_subscription_sales" {{ old('transaction_type', $transaction->transaction_type) == 'commission_subscription_sales' ? 'selected' : '' }}>
                                            {{ trans('sw.commission_subscription_sales')}}
                                        </option>
                                        <option value="bonus" {{ old('transaction_type', $transaction->transaction_type) == 'bonus' ? 'selected' : '' }}>
                                            {{ trans('sw.bonus')}}
                                        </option>
                                        <option value="advance" {{ old('transaction_type', $transaction->transaction_type) == 'advance' ? 'selected' : '' }}>
                                            {{ trans('sw.advance')}}
                                        </option>
                                        <option value="penalty_deduction" {{ old('transaction_type', $transaction->transaction_type) == 'penalty_deduction' ? 'selected' : '' }}>
                                            {{ trans('sw.penalty_deduction')}}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <!--end::Transaction Type-->

                            <!--begin::Amount-->
                            <div class="col-md-6 mb-10">
                                <div class="fv-row">
                                    <label class="required form-label">{{ trans('sw.amount')}}</label>
                                    <input type="number" name="amount" id="amount" class="form-control" 
                                           placeholder="{{ trans('sw.enter_amount')}}" 
                                           value="{{ old('amount', $transaction->amount) }}" 
                                           step="0.01" min="0" required />
                                </div>
                            </div>
                            <!--end::Amount-->

                            <!--begin::Financial Month-->
                            <div class="col-md-6 mb-10">
                                <div class="fv-row">
                                    <label class="required form-label">{{ trans('sw.financial_month')}}</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="ki-outline ki-calendar fs-2"></i>
                                        </span>
                                        <input type="text" name="financial_month" id="financial_month" class="form-control" 
                                               placeholder="{{ trans('sw.select_month_year')}}"
                                               value="{{ old('financial_month', $transaction->financial_month) }}" 
                                               readonly required />
                                    </div>
                                </div>
                            </div>
                            <!--end::Financial Month-->

                            <!--begin::Deduction Month-->
                            <div class="col-md-6 mb-10" id="deduction_month_container" style="display: none;">
                                <div class="fv-row">
                                    <label class="form-label">{{ trans('sw.deduction_month')}}</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="ki-outline ki-calendar fs-2"></i>
                                        </span>
                                        <input type="text" name="deduction_month" id="deduction_month" class="form-control" 
                                               placeholder="{{ trans('sw.select_month_year')}}"
                                               value="{{ old('deduction_month', $transaction->deduction_month) }}" 
                                               readonly />
                                    </div>
                                    <div class="form-text text-muted">{{ trans('sw.deduction_month_help')}}</div>
                                </div>
                            </div>
                            <!--end::Deduction Month-->

                            <!--begin::Notes-->
                            <div class="col-md-12 mb-10">
                                <div class="fv-row">
                                    <label class="form-label">{{ trans('sw.notes')}}</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3" 
                                              placeholder="{{ trans('sw.enter_notes')}}">{{ old('notes', $transaction->notes) }}</textarea>
                                </div>
                            </div>
                            <!--end::Notes-->
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Transaction Info-->
        </div>

        <!--begin::Actions-->
        <div class="card card-flush py-4 mt-7">
            <div class="card-body text-end">
                <a href="{{ route('sw.listUserTransaction') }}" class="btn btn-light me-3">{{ trans('sw.cancel')}}</a>
                <button type="submit" class="btn btn-primary">
                    <span class="indicator-label">{{ trans('sw.save')}}</span>
                </button>
            </div>
        </div>
        <!--end::Actions-->
    </form>
    <!--end::Form-->
@endsection

@section('scripts')
<script>
    jQuery(document).ready(function() {
        // Initialize Select2
        $('#employee_id').select2();

        // Initialize Month/Year Pickers
        const monthYearFormat = 'YYYY-MM';
        const displayFormat = '{{ $lang == "ar" ? "MMMM YYYY" : "MMMM YYYY" }}';
        
        $('#financial_month').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            minYear: 2020,
            maxYear: parseInt(moment().format('YYYY'), 10) + 5,
            locale: {
                format: displayFormat,
                @if($lang == 'ar')
                applyLabel: '{{ trans('sw.apply') }}',
                cancelLabel: '{{ trans('sw.cancel') }}',
                monthNames: [
                    'يناير', 'فبراير', 'مارس', 'إبريل', 'مايو', 'يونيو',
                    'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
                ],
                @else
                applyLabel: '{{ trans('sw.apply') }}',
                cancelLabel: '{{ trans('sw.cancel') }}',
                @endif
            },
            @if(old('financial_month', $transaction->financial_month))
            startDate: moment('{{ old('financial_month', $transaction->financial_month) }}', monthYearFormat),
            @else
            startDate: moment(),
            @endif
        }).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format(displayFormat));
            // Store the YYYY-MM format in a hidden field for submission
            var monthYear = picker.startDate.format(monthYearFormat);
            if (!$('#financial_month_hidden').length) {
                $(this).after('<input type="hidden" name="financial_month" id="financial_month_hidden">');
                $(this).removeAttr('name');
            }
            $('#financial_month_hidden').val(monthYear);
        });

        $('#deduction_month').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            minYear: 2020,
            maxYear: parseInt(moment().format('YYYY'), 10) + 5,
            locale: {
                format: displayFormat,
                @if($lang == 'ar')
                applyLabel: '{{ trans('sw.apply') }}',
                cancelLabel: '{{ trans('sw.cancel') }}',
                monthNames: [
                    'يناير', 'فبراير', 'مارس', 'إبريل', 'مايو', 'يونيو',
                    'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
                ],
                @else
                applyLabel: '{{ trans('sw.apply') }}',
                cancelLabel: '{{ trans('sw.cancel') }}',
                @endif
            },
            @if(old('deduction_month', $transaction->deduction_month))
            startDate: moment('{{ old('deduction_month', $transaction->deduction_month) }}', monthYearFormat),
            @else
            startDate: moment(),
            @endif
        }).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format(displayFormat));
            // Store the YYYY-MM format in a hidden field for submission
            var monthYear = picker.startDate.format(monthYearFormat);
            if (!$('#deduction_month_hidden').length) {
                $(this).after('<input type="hidden" name="deduction_month" id="deduction_month_hidden">');
                $(this).removeAttr('name');
            }
            $('#deduction_month_hidden').val(monthYear);
        });

        // Set initial display values if data exists
        @if(old('financial_month', $transaction->financial_month))
        var financialDate = moment('{{ old('financial_month', $transaction->financial_month) }}', monthYearFormat);
        $('#financial_month').val(financialDate.format(displayFormat));
        $('#financial_month').after('<input type="hidden" name="financial_month" id="financial_month_hidden" value="{{ old('financial_month', $transaction->financial_month) }}">');
        $('#financial_month').removeAttr('name');
        @endif

        @if(old('deduction_month', $transaction->deduction_month))
        var deductionDate = moment('{{ old('deduction_month', $transaction->deduction_month) }}', monthYearFormat);
        $('#deduction_month').val(deductionDate.format(displayFormat));
        $('#deduction_month').after('<input type="hidden" name="deduction_month" id="deduction_month_hidden" value="{{ old('deduction_month', $transaction->deduction_month) }}">');
        $('#deduction_month').removeAttr('name');
        @endif

        // Show/Hide deduction month based on transaction type
        function toggleDeductionMonth() {
            const transactionType = $('#transaction_type').val();
            const deductionContainer = $('#deduction_month_container');
            const deductionInput = $('#deduction_month');
            const deductionHidden = $('#deduction_month_hidden');
            
            if (transactionType === 'advance') {
                deductionContainer.show();
                deductionInput.attr('required', true);
            } else {
                deductionContainer.hide();
                deductionInput.attr('required', false);
                deductionInput.val('');
                deductionHidden.val('');
            }
        }

        // Trigger on page load
        toggleDeductionMonth();

        // Trigger on change
        $('#transaction_type').on('change', function() {
            toggleDeductionMonth();
        });
    });
</script>
@endsection


