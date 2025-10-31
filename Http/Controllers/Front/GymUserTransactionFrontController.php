<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Http\Controllers\Front\GenericFrontController;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Http\Requests\GymUserTransactionRequest;
use Modules\Software\Models\GymUserTransaction;
use Modules\Software\Models\GymUser;
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
        return view('software::Front.user_transaction_front_form', [
            'transaction' => new GymUserTransaction(),
            'title' => $title,
            'employees' => $employees
        ]);
    }

    public function store(GymUserTransactionRequest $request)
    {
        $inputs = $this->prepare_inputs($request->except(['_token']));
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
        if(@$this->user_sw->id){
            $inputs['user_id'] = @$this->user_sw->id;
        }
        
        $transaction = $this->TransactionRepository->create($inputs);

        // If transaction type is advance and deduction_month is set, create a reminder/placeholder
        // This can be expanded later for automatic deduction handling
        
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);

        $employee = GymUser::find($inputs['employee_id']);
        $notes = str_replace([':employee', ':type', ':amount'], 
            [$employee->name, trans('sw.' . $inputs['transaction_type']), $inputs['amount']], 
            trans('sw.add_employee_transaction'));
        $this->userLog($notes, TypeConstants::CreateEmployeeTransaction);
        
        return redirect(route('sw.listUserTransaction'));
    }

    public function edit($id)
    {
        $transaction = $this->TransactionRepository->withTrashed()->find($id);
        $title = trans('sw.employee_transaction_edit');
        $employees = GymUser::branch()->orderBy('name', 'ASC')->get();
        return view('software::Front.user_transaction_front_form', [
            'transaction' => $transaction,
            'title' => $title,
            'employees' => $employees
        ]);
    }

    public function update(GymUserTransactionRequest $request, $id)
    {
        $transaction = $this->TransactionRepository->withTrashed()->find($id);
        $inputs = array_filter($this->prepare_inputs($request->except(['_token'])));
             
        $transaction->update($inputs);

        $employee = GymUser::find($transaction->employee_id);
        $notes = str_replace([':employee', ':type', ':amount'], 
            [$employee->name, trans('sw.' . $transaction->transaction_type), $transaction->amount], 
            trans('sw.edit_employee_transaction'));
        $this->userLog($notes, TypeConstants::EditEmployeeTransaction);

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
            $transaction->restore();
        }
        else
        {
            $transaction->delete();

            $employee = GymUser::find($transaction->employee_id);
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

        // Set deduction_month to null if transaction_type is not advance
        if(isset($inputs['transaction_type']) && $inputs['transaction_type'] != 'advance'){
            $inputs['deduction_month'] = null;
        }

        return $inputs;
    }
}

