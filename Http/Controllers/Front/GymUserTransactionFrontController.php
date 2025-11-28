<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Http\Controllers\Front\GenericFrontController;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Http\Requests\GymUserTransactionRequest;
use Modules\Software\Models\GymUserTransaction;
use Modules\Software\Models\GymUser;
use Modules\Software\Models\GymPaymentType;
use Modules\Software\Repositories\GymUserTransactionRepository;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;

class GymUserTransactionFrontController extends GymGenericFrontController
{
    public $TransactionRepository;

    public function __construct()
    {
        parent::__construct();
        $this->TransactionRepository = new GymUserTransactionRepository(new Application);
        $this->TransactionRepository = $this->TransactionRepository->branch();
    }

    public function index()
    {
        $title = trans('sw.employee_transactions');
        $this->request_array = ['search', 'employee_id', 'transaction_type', 'financial_month'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        
        if(request('trashed'))
        {
            $transactions = $this->TransactionRepository->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $transactions = $this->TransactionRepository->orderBy('id', 'DESC');
        }

        // Apply filters
        $transactions->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', $search);
                $query->orWhere('notes', 'like', "%" . $search . "%");
                $query->orWhere('amount', 'like', "%" . $search . "%");
            });
        });

        $transactions->when($employee_id, function ($query) use ($employee_id) {
            $query->where('employee_id', $employee_id);
        });

        $transactions->when($transaction_type, function ($query) use ($transaction_type) {
            $query->where('transaction_type', $transaction_type);
        });

        $transactions->when($financial_month, function ($query) use ($financial_month) {
            $query->where('financial_month', $financial_month);
        });

        // Eager load relationships
        $transactions->with(['employee', 'creator']);

        $search_query = request()->query();

        if ($this->limit) {
            $transactions = $transactions->paginate($this->limit);
            $total = $transactions->total();
        } else {
            $transactions = $transactions->get();
            $total = $transactions->count();
        }

        // Get employees for filter
        $employees = GymUser::branch()->orderBy('name', 'ASC')->get();

        return view('software::Front.user_transaction_front_list', compact('transactions','title', 'total', 'search_query', 'employees'));
    }

    public function create()
    {
        $title = trans('sw.employee_transaction_add');
        $employees = GymUser::branch()->orderBy('name', 'ASC')->get();
        $payment_types = GymPaymentType::branch()->orderBy('id')->get();
        return view('software::Front.user_transaction_front_form', [
            'transaction' => new GymUserTransaction(),
            'title' => $title,
            'employees' => $employees,
            'payment_types' => $payment_types,
        ]);
    }

    public function store(GymUserTransactionRequest $request)
    {
        $inputs = $this->prepare_inputs($request->except(['_token']));
        
        if(@$this->user_sw->id){
            $inputs['user_id'] = @$this->user_sw->id;
        }
        
        $transaction = $this->TransactionRepository->create($inputs);
        $transaction->payment_type = $inputs['payment_type'];
        // Create money box entry for this transaction
        $this->createMoneyBoxEntry($transaction);
        
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);

        if (!empty($inputs['employee_id'])) {
            $employee = GymUser::find($inputs['employee_id']);
            if ($employee) {
                $notes = str_replace([':employee', ':type', ':amount'], 
                    [$employee->name, trans('sw.' . $inputs['transaction_type']), $inputs['amount']], 
                    trans('sw.add_employee_transaction'));
                $this->userLog($notes, TypeConstants::CreateEmployeeTransaction);
            }
        }
        
        return redirect(route('sw.listUserTransaction'));
    }

    public function edit($id)
    {
        $transaction = $this->TransactionRepository->withTrashed()->find($id);
        $title = trans('sw.employee_transaction_edit');
        $employees = GymUser::branch()->orderBy('name', 'ASC')->get();
        $payment_types = GymPaymentType::branch()->orderBy('id')->get();
        return view('software::Front.user_transaction_front_form', [
            'transaction' => $transaction,
            'title' => $title,
            'employees' => $employees,
            'payment_types' => $payment_types,
        ]);
    }

    public function update(GymUserTransactionRequest $request, $id)
    {
        $transaction = $this->TransactionRepository->withTrashed()->find($id);
        $inputs = array_filter($this->prepare_inputs($request->except(['_token'])));
             
        $transaction->update($inputs);

        // Update money box entry (delete old and create new)
        $this->createMoneyBoxEntry($transaction->fresh(), true);

        if (!empty($transaction->employee_id)) {
            $employee = GymUser::find($transaction->employee_id);
            if ($employee) {
                $notes = str_replace([':employee', ':type', ':amount'], 
                    [$employee->name, trans('sw.' . $transaction->transaction_type), $transaction->amount], 
                    trans('sw.edit_employee_transaction'));
                $this->userLog($notes, TypeConstants::EditEmployeeTransaction);
            }
        }

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listUserTransaction'));
    }

    public function destroy($id)
    {
        $transaction = $this->TransactionRepository->withTrashed()->find($id);
        
        if($transaction->trashed())
        {
            // Restore transaction - recreate money box entries
            $transaction->restore();
            $this->createMoneyBoxEntry($transaction->fresh());
            
            session()->flash('sweet_flash_message', [
                'title' => trans('admin.done'),
                'message' => trans('admin.successfully_restored'),
                'type' => 'success'
            ]);
        }
        else
        {
            $employee = GymUser::find($transaction->employee_id);
            $employeeName = $employee ? $employee->name : '';
            
            // IMPORTANT: Get ALL money box entries BEFORE deleting transaction
            // (excluding previous cancellations to avoid double-reversing)
            $allEntries = \Modules\Software\Models\GymMoneyBox::where('user_transaction_id', $transaction->id)
                ->where('notes', 'not like', '%' . trans('sw.cancellation') . '%')
                ->orderBy('id', 'ASC')
                ->get();
            
            // Calculate the cumulative net amount from all entries
            if ($allEntries->isNotEmpty()) {
                $cumulativeNet = 0;
                foreach ($allEntries as $entry) {
                    // Calculate net: addition (+), withdraw (-)
                    if ($entry->operation == 0) {
                        $cumulativeNet += $entry->amount;
                    } else {
                        $cumulativeNet -= $entry->amount;
                    }
                }
                
                // If cumulative is zero, nothing to reverse
                if ($cumulativeNet != 0) {
                    // Get the last money box balance
                    $lastMoneyBox = \Modules\Software\Models\GymMoneyBox::branch()
                        ->orderBy('id', 'DESC')
                        ->first();
                    
                    $amount_before = $lastMoneyBox ? $lastMoneyBox->amount_before + $lastMoneyBox->amount * ($lastMoneyBox->operation == 0 ? 1 : -1) : 0;

                    // Create ONE reversal entry to reverse the cumulative net
                    // If cumulative is negative (money out), reversal is positive (add back)
                    // If cumulative is positive (money in), reversal is negative (withdraw back)
                    $reversalNotes = trans('sw.cancellation') . ' - ' . trans('sw.' . $transaction->transaction_type) . ' - ' . $employeeName . ' - ' . $transaction->financial_month;
                    
                    \Modules\Software\Models\GymMoneyBox::create([
                        'user_transaction_id' => $transaction->id,
                        'user_id' => $transaction->user_id,
                        'branch_setting_id' => $transaction->branch_setting_id,
                        'amount' => abs($cumulativeNet),
                        'amount_before' => $amount_before,
                        'operation' => $cumulativeNet > 0 ? 1 : 0, // Reverse the cumulative
                        'notes' => $reversalNotes,
                        'payment_type' => 0,
                    ]);
                }
            }
            
            // Now soft delete the transaction
            $transaction->delete();

            // Log the deletion
            if (!empty($transaction->employee_id) && $employee) {
                $notes = str_replace([':employee', ':type', ':amount'], 
                    [$employee->name, trans('sw.' . $transaction->transaction_type), $transaction->amount], 
                    trans('sw.delete_employee_transaction'));
                $this->userLog($notes, TypeConstants::DeleteEmployeeTransaction);
            }
            
            session()->flash('sweet_flash_message', [
                'title' => trans('admin.done'),
                'message' => trans('admin.successfully_deleted'),
                'type' => 'success'
            ]);
        }
        
        return redirect(route('sw.listUserTransaction'));
    }

    private function prepare_inputs($inputs)
    {
        // Handle text fields - convert null/empty values to empty strings to avoid null constraint violations
        if (isset($inputs['content_ar'])) {
            $inputs['content_ar'] = $inputs['content_ar'] !== null ? $inputs['content_ar'] : '';
        } else {
            $inputs['content_ar'] = '';
        }
        if (isset($inputs['content_en'])) {
            $inputs['content_en'] = $inputs['content_en'] !== null ? $inputs['content_en'] : '';
        } else {
            $inputs['content_en'] = '';
        }

        if(@$this->user_sw->branch_setting_id){
            $inputs['branch_setting_id'] = @$this->user_sw->branch_setting_id;
        }

        // Extract financial_year from financial_month (format: YYYY-MM)
        if (isset($inputs['financial_month'])) {
            $dateParts = explode('-', $inputs['financial_month']);
            $inputs['financial_year'] = $dateParts[0];
        }

        // Map deduction_month to advance_discount_month (form field vs database column)
        if (isset($inputs['deduction_month'])) {
            $inputs['advance_discount_month'] = $inputs['deduction_month'];
            unset($inputs['deduction_month']);
            
            // Extract advance_discount_year from advance_discount_month
            $dateParts = explode('-', $inputs['advance_discount_month']);
            $inputs['advance_discount_year'] = $dateParts[0];
        }

        // Set advance_discount_month to null if transaction_type is not advance
        if(isset($inputs['transaction_type']) && $inputs['transaction_type'] != 'advance'){
            $inputs['advance_discount_month'] = null;
            $inputs['advance_discount_year'] = null;
        }

        if (isset($inputs['payment_type'])) {
            $inputs['payment_type'] = (int)$inputs['payment_type'];
        }

        return $inputs;
    }

    /**
     * Create or update money box entry for employee transaction
     * IMPORTANT: Money box entries are like invoices - never edit/delete, only add corrective entries
     */
    private function createMoneyBoxEntry($transaction, $isUpdate = false)
    {
        if (empty($transaction->employee_id) || empty($transaction->amount)) {
            return;
        }

        $employee = GymUser::find($transaction->employee_id);
        $employeeName = $employee ? $employee->name : '';

        // If updating, create corrective entries instead of deleting
        if ($isUpdate) {
            $this->createCorrectiveMoneyBoxEntries($transaction, $employeeName);
            return;
        }

        // Handle Advance transactions specially (2 entries: giving advance + future deduction)
        if ($transaction->transaction_type === 'advance' && !empty($transaction->advance_discount_month)) {
            $this->createAdvanceMoneyBoxEntries($transaction, $employeeName);
            return;
        }

        // Determine operation type for regular transactions
        // penalty_deduction = money coming back to gym (addition = 0)
        // All other types (salary, commission, bonus) = money going out (withdraw = 1)
        $operation = $transaction->transaction_type === 'penalty_deduction' ? 0 : 1;

        // Get the last money box balance
        $lastMoneyBox = \Modules\Software\Models\GymMoneyBox::branch()
            ->orderBy('id', 'DESC')
            ->first();
        
        $amount_before = $lastMoneyBox ? $lastMoneyBox->amount_before + $lastMoneyBox->amount * ($lastMoneyBox->operation == 0 ? 1 : -1) : 0;

        // Prepare notes
        $notes = trans('sw.' . $transaction->transaction_type) . ' - ' . $employeeName . ' - ' . $transaction->financial_month;
        if ($transaction->notes) {
            $notes .= ' - ' . $transaction->notes;
        }

        // Create new money box entry
        \Modules\Software\Models\GymMoneyBox::create([
            'user_transaction_id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'branch_setting_id' => $transaction->branch_setting_id,
            'amount' => abs($transaction->amount),
            'amount_before' => $amount_before,
            'operation' => $operation,
            'notes' => $notes,
            'payment_type' => $transaction->payment_type ?? 0,
        ]);
    }

    /**
     * Create corrective money box entries when editing a transaction
     * Creates ONLY ONE correction entry with the difference (minimal approach)
     */
    private function createCorrectiveMoneyBoxEntries($transaction, $employeeName)
    {
        // Get ALL money box entries for this transaction (excluding cancellations only)
        // We MUST include corrections to calculate the cumulative net properly
        $oldEntries = \Modules\Software\Models\GymMoneyBox::where('user_transaction_id', $transaction->id)
            ->where('notes', 'not like', '%' . trans('sw.cancellation') . '%')
            ->orderBy('id', 'ASC')
            ->get();

        if ($oldEntries->isEmpty()) {
            // No old entries, create new ones
            if ($transaction->transaction_type === 'advance' && !empty($transaction->advance_discount_month)) {
                $this->createAdvanceMoneyBoxEntries($transaction, $employeeName, true);
            } else {
                $this->createRegularMoneyBoxEntry($transaction, $employeeName);
            }
            return;
        }

        // Calculate the CUMULATIVE net amount from all entries for this transaction
        $cumulativeNet = 0;
        $firstOperation = null;
        
        foreach ($oldEntries as $entry) {
            if ($firstOperation === null) {
                $firstOperation = $entry->operation;
            }
            // Calculate net: addition (+), withdraw (-)
            if ($entry->operation == 0) {
                $cumulativeNet += $entry->amount;
            } else {
                $cumulativeNet -= $entry->amount;
            }
        }

        // New amount (with proper sign based on transaction type)
        $newAmount = abs($transaction->amount);
        $isWithdrawType = $transaction->transaction_type !== 'penalty_deduction';
        $newNet = $isWithdrawType ? -$newAmount : $newAmount;

        // Calculate what correction is needed
        $correctionAmount = $newNet - $cumulativeNet;

        // If no difference, no correction needed
        if ($correctionAmount == 0) {
            return;
        }

        // Get the last money box balance
        $lastMoneyBox = \Modules\Software\Models\GymMoneyBox::branch()
            ->orderBy('id', 'DESC')
            ->first();
        
        $amount_before = $lastMoneyBox ? $lastMoneyBox->amount_before + $lastMoneyBox->amount * ($lastMoneyBox->operation == 0 ? 1 : -1) : 0;

        // Determine operation based on correction sign
        // Positive correction = need to add money (operation 0)
        // Negative correction = need to withdraw money (operation 1)
        if ($correctionAmount > 0) {
            $operation = 0; // Addition
        } else {
            $operation = 1; // Withdraw
        }

        $correctionNotes = trans('sw.correction') . ' - ' . trans('sw.' . $transaction->transaction_type) . ' - ' . $employeeName . ' - ' . $transaction->financial_month;
        
        // Create ONE correction entry with the difference
        \Modules\Software\Models\GymMoneyBox::create([
            'user_transaction_id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'branch_setting_id' => $transaction->branch_setting_id,
            'amount' => abs($correctionAmount),
            'amount_before' => $amount_before,
            'operation' => $operation,
            'notes' => $correctionNotes,
            'payment_type' => $transaction->payment_type ?? 0,
        ]);
    }

    /**
     * Create a regular (non-advance) money box entry
     */
    private function createRegularMoneyBoxEntry($transaction, $employeeName)
    {
        $operation = $transaction->transaction_type === 'penalty_deduction' ? 0 : 1;
        
        $lastMoneyBox = \Modules\Software\Models\GymMoneyBox::branch()
            ->orderBy('id', 'DESC')
            ->first();
        
        $amount_before = $lastMoneyBox ? $lastMoneyBox->amount_before + $lastMoneyBox->amount * ($lastMoneyBox->operation == 0 ? 1 : -1) : 0;

        $notes = trans('sw.' . $transaction->transaction_type) . ' - ' . $employeeName . ' - ' . $transaction->financial_month;
        if ($transaction->notes) {
            $notes .= ' - ' . $transaction->notes;
        }

        \Modules\Software\Models\GymMoneyBox::create([
            'user_transaction_id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'branch_setting_id' => $transaction->branch_setting_id,
            'amount' => abs($transaction->amount),
            'amount_before' => $amount_before,
            'operation' => $operation,
            'notes' => $notes,
            'payment_type' => $transaction->payment_type ?? 0,
        ]);
    }

    /**
     * Create two money box entries for advance:
     * 1. Advance given (Financial Month - money out)
     * 2. Advance deduction scheduled (Deduction Month - money recovered)
     */
    private function createAdvanceMoneyBoxEntries($transaction, $employeeName, $skipDelete = false)
    {
        // Get the last money box balance
        $lastMoneyBox = \Modules\Software\Models\GymMoneyBox::branch()
            ->orderBy('id', 'DESC')
            ->first();
        
        $amount_before = $lastMoneyBox ? $lastMoneyBox->amount_before + $lastMoneyBox->amount * ($lastMoneyBox->operation == 0 ? 1 : -1) : 0;

        // 1. Create money box entry for ADVANCE GIVEN (Financial Month - withdraw)
        $advanceGivenNotes = trans('sw.advance') . ' ' . trans('sw.given') . ' - ' . $employeeName . ' - ' . $transaction->financial_month;
        if ($transaction->notes) {
            $advanceGivenNotes .= ' - ' . $transaction->notes;
        }
        
        $moneyBoxGiven = \Modules\Software\Models\GymMoneyBox::create([
            'user_transaction_id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'branch_setting_id' => $transaction->branch_setting_id,
            'amount' => abs($transaction->amount),
            'amount_before' => $amount_before,
            'operation' => 1, // Withdraw - money going out
            'notes' => $advanceGivenNotes,
            'payment_type' => $transaction->payment_type ?? 0,
        ]);

        // Update amount_before for the deduction entry
        $amount_before = $moneyBoxGiven->amount_before + $moneyBoxGiven->amount * ($moneyBoxGiven->operation == 0 ? 1 : -1);

        // 2. Create money box entry for ADVANCE DEDUCTION (Deduction Month - addition back)
        $advanceDeductionNotes = trans('sw.advance') . ' ' . trans('sw.deduction') . ' - ' . $employeeName . ' - ' . $transaction->advance_discount_month;
        if ($transaction->notes) {
            $advanceDeductionNotes .= ' - ' . $transaction->notes;
        }

        \Modules\Software\Models\GymMoneyBox::create([
            'user_transaction_id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'branch_setting_id' => $transaction->branch_setting_id,
            'amount' => abs($transaction->amount),
            'amount_before' => $amount_before,
            'operation' => 0, // Addition - money coming back
            'notes' => $advanceDeductionNotes,
            'payment_type' => $transaction->payment_type ?? 0,
        ]);
    }
}


