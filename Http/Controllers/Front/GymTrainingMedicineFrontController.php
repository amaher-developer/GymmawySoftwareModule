<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Classes\TypeConstants;
use Modules\Software\Models\GymTrainingMedicine;
use Modules\Software\Http\Requests\GymTrainingMedicineRequest;
use Illuminate\Http\Request;

class GymTrainingMedicineFrontController extends GymGenericFrontController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display medicines list
     */
    public function index(Request $request)
    {
        $title = trans('sw.training_medicines');
        
        $query = GymTrainingMedicine::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1);

        // Search filter
        if ($request->has('q') && $request->q) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $medicines = $query->orderBy('name_en')->paginate(20)->appends($request->except('page'));
        $total = GymTrainingMedicine::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1)->count();

        return view('software::Front.training_medicine_list', compact('title', 'medicines', 'total'));
    }

    /**
     * Show form to create new medicine
     */
    public function create()
    {
        $title = trans('sw.add_training_medicine');
        $medicine = new GymTrainingMedicine();
        
        return view('software::Front.training_medicine_form', compact('title', 'medicine'));
    }

    /**
     * Store new medicine
     */
    public function store(GymTrainingMedicineRequest $request)
    {
        $inputs = $request->all();
        $inputs['branch_setting_id'] = $this->user_sw->branch_setting_id ?? 1;
        $inputs['status'] = $request->has('status') ? 1 : 0;

        GymTrainingMedicine::create($inputs);

        $notes = trans('sw.medicine_added', ['name' => $inputs['name_en'] ?? $inputs['name_ar']]);
        $this->userLog($notes, TypeConstants::CreateTrainingMedicine);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);

        return redirect()->route('sw.listTrainingMedicine');
    }

    /**
     * Show form to edit medicine
     */
    public function edit($id)
    {
        $title = trans('sw.edit_training_medicine');
        $medicine = GymTrainingMedicine::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1)
            ->findOrFail($id);
        
        return view('software::Front.training_medicine_form', compact('title', 'medicine'));
    }

    /**
     * Update medicine
     */
    public function update(GymTrainingMedicineRequest $request, $id)
    {
        $medicine = GymTrainingMedicine::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1)
            ->findOrFail($id);

        $inputs = $request->all();
        $inputs['status'] = $request->has('status') ? 1 : 0;

        $medicine->update($inputs);

        $notes = trans('sw.medicine_updated', ['name' => $inputs['name_en'] ?? $inputs['name_ar']]);
        $this->userLog($notes, TypeConstants::EditTrainingMedicine);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);

        return redirect()->route('sw.listTrainingMedicine');
    }

    /**
     * Delete medicine
     */
    public function destroy($id)
    {
        $medicine = GymTrainingMedicine::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1)
            ->findOrFail($id);

        $name = $medicine->name_en ?? $medicine->name_ar;
            $medicine->delete();

        $notes = trans('sw.medicine_deleted', ['name' => $name]);
        $this->userLog($notes, TypeConstants::DeleteTrainingMedicine);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);

        return redirect()->route('sw.listTrainingMedicine');
    }
}

