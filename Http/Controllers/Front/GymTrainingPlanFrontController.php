<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Classes\TypeConstants;
use Modules\Software\Models\GymTrainingPlan;
use Modules\Software\Models\GymTrainingTask;
use Modules\Software\Models\GymSubscriptionCategory;
use Modules\Software\Http\Requests\GymTrainingPlanRequest;
use Illuminate\Http\Request;

class GymTrainingPlanFrontController extends GymGenericFrontController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display training plans list
     */
    public function index(Request $request)
    {
        $title = trans('sw.training_plans');
        
        $query = GymTrainingPlan::where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1);

        // Search filter
        if ($request->has('q') && $request->q) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Type filter
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        $plans = $query->latest()->paginate(20)->appends($request->except('page'));
        $total = GymTrainingPlan::where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1)->count();

        return view('software::Front.training_plan_list', compact('title', 'plans', 'total'));
    }

    /**
     * Show form to create new plan
     */
    public function create()
    {
        $title = trans('sw.add_training_plan');
        $plan = new GymTrainingPlan();
        $categories = GymSubscriptionCategory::where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1)->get();
        
        return view('software::Front.training_plan_form', compact('title', 'plan', 'categories'));
    }

    /**
     * Store new plan
     */
    public function store(GymTrainingPlanRequest $request)
    {
        $inputs = $request->all();
        $inputs['branch_setting_id'] = $this->user_sw->branch_setting_id ?? 1;
        $inputs['user_id'] = $this->user_sw->id;

        $plan = GymTrainingPlan::create($inputs);

        // Store tasks if provided
        if ($request->has('tasks') && is_array($request->tasks)) {
            foreach ($request->tasks as $taskData) {
                if (!empty($taskData['title']) || !empty($taskData['name_ar']) || !empty($taskData['name_en'])) {
                    GymTrainingTask::create([
                        'branch_setting_id' => $this->user_sw->branch_setting_id ?? 1,
                        'plan_id' => $plan->id,
                        'day_name' => $taskData['day_name'] ?? null,
                        'title' => $taskData['title'] ?? null,
                        'name_ar' => $taskData['name_ar'] ?? '',
                        'name_en' => $taskData['name_en'] ?? '',
                        'description' => $taskData['description'] ?? null,
                        'image_url' => $taskData['image_url'] ?? null,
                        'youtube_link' => $taskData['youtube_link'] ?? null,
                        't_group' => $taskData['t_group'] ?? null,
                        't_repeats' => $taskData['t_repeats'] ?? null,
                        't_rest' => $taskData['t_rest'] ?? null,
                        'd_calories' => $taskData['d_calories'] ?? null,
                        'd_protein' => $taskData['d_protein'] ?? null,
                        'd_carb' => $taskData['d_carb'] ?? null,
                        'd_fats' => $taskData['d_fats'] ?? null,
                        'details' => $taskData['details'] ?? null,
                        'status' => isset($taskData['status']) ? 1 : 0,
                        'order' => $taskData['order'] ?? 0,
                    ]);
                }
            }
        }

        $notes = trans('sw.plan_added', ['name' => $inputs['title']]);
        $this->userLog($notes, TypeConstants::CreateTrainingPlan);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);

        return redirect()->route('sw.listTrainingPlan');
    }

    /**
     * Show form to edit plan
     */
    public function edit($id)
    {
        $title = trans('sw.edit_training_plan');
        $plan = GymTrainingPlan::where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1)
            ->findOrFail($id);
        $categories = GymSubscriptionCategory::where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1)->get();
        $tasks = GymTrainingTask::where('plan_id', $id)->orderBy('order')->get();
        
        return view('software::Front.training_plan_form', compact('title', 'plan', 'categories', 'tasks'));
    }

    /**
     * Update plan
     */
    public function update(GymTrainingPlanRequest $request, $id)
    {
        $plan = GymTrainingPlan::where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1)
            ->findOrFail($id);
        
        $inputs = $request->all();
        $plan->update($inputs);

        // Update tasks
        if ($request->has('tasks') && is_array($request->tasks)) {
            // Delete old tasks
            GymTrainingTask::where('plan_id', $id)->delete();
            
            // Create new tasks
            foreach ($request->tasks as $taskData) {
                if (!empty($taskData['title']) || !empty($taskData['name_ar']) || !empty($taskData['name_en'])) {
                    GymTrainingTask::create([
                        'branch_setting_id' => $this->user_sw->branch_setting_id ?? 1,
                        'plan_id' => $plan->id,
                        'day_name' => $taskData['day_name'] ?? null,
                        'title' => $taskData['title'] ?? null,
                        'name_ar' => $taskData['name_ar'] ?? '',
                        'name_en' => $taskData['name_en'] ?? '',
                        'description' => $taskData['description'] ?? null,
                        'image_url' => $taskData['image_url'] ?? null,
                        'youtube_link' => $taskData['youtube_link'] ?? null,
                        't_group' => $taskData['t_group'] ?? null,
                        't_repeats' => $taskData['t_repeats'] ?? null,
                        't_rest' => $taskData['t_rest'] ?? null,
                        'd_calories' => $taskData['d_calories'] ?? null,
                        'd_protein' => $taskData['d_protein'] ?? null,
                        'd_carb' => $taskData['d_carb'] ?? null,
                        'd_fats' => $taskData['d_fats'] ?? null,
                        'details' => $taskData['details'] ?? null,
                        'status' => isset($taskData['status']) ? 1 : 0,
                        'order' => $taskData['order'] ?? 0,
                    ]);
                }
            }
        }

        $notes = trans('sw.plan_updated', ['name' => $inputs['title']]);
        $this->userLog($notes, TypeConstants::EditTrainingPlan);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_updated'),
            'type' => 'success'
        ]);

        return redirect()->route('sw.listTrainingPlan');
    }

    /**
     * Delete plan
     */
    public function destroy($id)
    {
        $plan = GymTrainingPlan::where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1)
            ->findOrFail($id);
        
        $name = $plan->title;
        
        // Delete associated tasks
        GymTrainingTask::where('plan_id', $id)->delete();
        
        $plan->delete();

        $notes = trans('sw.plan_deleted', ['name' => $name]);
        $this->userLog($notes, TypeConstants::DeleteTrainingPlan);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);

        return redirect()->route('sw.listTrainingPlan');
    }
}


