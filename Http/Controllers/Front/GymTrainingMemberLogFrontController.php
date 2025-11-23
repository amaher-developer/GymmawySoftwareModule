<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Classes\TypeConstants;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymTrainingMemberLog;
use Modules\Software\Models\GymTrainingAssessment;
use Modules\Software\Models\GymTrainingPlan;
use Modules\Software\Models\GymTrainingMedicine;
use Modules\Software\Models\GymTrainingFile;
use Modules\Software\Models\GymTrainingTrack;
use Modules\Software\Models\GymAiRecommendation;
use Modules\Software\Models\GymPaymentType;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Http\Controllers\Front\GymMoneyBoxFrontController;
use Modules\Software\Repositories\GymTrainingMemberLogRepository;
use Modules\Software\Repositories\GymTrainingAssessmentRepository;
use Modules\Software\Http\Requests\GymTrainingAssessmentRequest;
use Illuminate\Container\Container as Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Mpdf\Mpdf;

class GymTrainingMemberLogFrontController extends GymGenericFrontController
{
    public $MemberLogRepository;
    public $AssessmentRepository;

    public function __construct()
    {
        parent::__construct();
        $this->MemberLogRepository = new GymTrainingMemberLogRepository(new Application);
        $this->AssessmentRepository = new GymTrainingAssessmentRepository(new Application);
    }

    /**
     * Display main member logs page
     */
    public function index(Request $request)
    {
        $title = trans('sw.training_member_logs');
        
        // Get all members for selection
        $query = GymMember::where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1);

        // Search filter
        if ($request->has('q') && $request->q) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Gender filter (if column exists)
        if ($request->has('gender') && $request->gender && \Schema::hasColumn('sw_gym_members', 'gender')) {
            $query->where('gender', $request->gender);
        }

        $members = $query->orderBy('name', 'asc')->paginate(20)->appends($request->except('page'));
        $total = GymMember::where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1)->count();

        return view('software::Front.training_member_log_list', compact('title', 'members', 'total'));
    }

    /**
     * Show member training management page
     */
    public function show($memberId)
    {
        $title = trans('sw.training_member_management');
        
        $member = GymMember::where('id', $memberId)
            ->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1)
            ->firstOrFail();

        // Get logs with related data
        $logs = GymTrainingMemberLog::where('member_id', $memberId)
            ->with(['creator'])
            ->latest()
            ->paginate(20);
        
        // Enrich logs with detailed data
        foreach ($logs as $log) {
            $log->details = $this->getLogDetails($log);
        }

        // Get latest assessment
        $latestAssessment = GymTrainingAssessment::where('member_id', $memberId)
            ->latest()
            ->first();

        // Get active plans
        $activePlans = \DB::table('sw_gym_training_members')
            ->where('member_id', $memberId)
            ->where('status', 1)
            ->get();

        // Get all plans for selection
        $allPlans = GymTrainingPlan::where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1)
            ->select('id', 'title', 'type')
            ->orderBy('title')
            ->get();

        // Get all medicines for selection
        $allMedicines = GymTrainingMedicine::where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1)
            ->where('status', 1) // Only active medicines
            ->select('id', 'name_ar', 'name_en')
            ->orderBy('name_en')
            ->get();

        // Get member files
        $files = GymTrainingFile::where('member_id', $memberId)->latest()->get();

        // Get member tracks
        $tracks = GymTrainingTrack::where('member_id', $memberId)->latest()->take(10)->get();
        
        // Get VAT percentage from system settings
        $vatPercentage = 0;
        if ($this->mainSettings && isset($this->mainSettings->vat_details) && is_array($this->mainSettings->vat_details)) {
            $vatPercentage = $this->mainSettings->vat_details['vat_percentage'] ?? 0;
        }
        
        // Get payment types from database
        $paymentTypes = GymPaymentType::orderBy('payment_id')->get();
        
        // Get all member logs for count badges in AI modal
        $memberLogs = GymTrainingMemberLog::where('member_id', $memberId)->get();

        return view('software::Front.training_member_log_manage', compact(
            'title', 'member', 'logs', 'memberLogs', 'latestAssessment', 'activePlans', 
            'allPlans', 'allMedicines', 'files', 'tracks', 'vatPercentage', 'paymentTypes'
        ));
    }

    /**
     * Add assessment for member
     */
    public function addAssessment(GymTrainingAssessmentRequest $request, $memberId)
    {
        $inputs = $request->all();
        $inputs['member_id'] = $memberId;
        $inputs['trainer_id'] = $this->user_sw->id;
        $inputs['branch_setting_id'] = $this->user_sw->branch_setting_id ?? 1;

        $assessment = $this->AssessmentRepository->create($inputs);

        // Log the action
        $this->logMemberAction($memberId, 'assessment', 'added', trans('sw.assessment_added'), ['assessment_id' => $assessment->id]);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('sw.assessment_added_successfully'),
            'type' => 'success'
        ]);

        return redirect()->back();
    }

    /**
     * Add plan to member
     */
    public function addPlan(Request $request, $memberId)
    {
        $request->validate([
            'plan_id' => 'required|exists:sw_gym_training_plans,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'vat_percentage' => 'nullable|numeric|min:0',
            'vat' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_type' => 'nullable|in:0,1,2',
        ]);

        $plan = GymTrainingPlan::findOrFail($request->plan_id);
        $member = GymMember::findOrFail($memberId);

        // Check what columns exist in the table
        $columns = \Schema::getColumnListing('sw_gym_training_members');
        
        // Prepare insert data with only existing columns
        $insertData = [
            'member_id' => $memberId,
            'user_id' => $this->user_sw->id,
            'from_date' => $request->from_date ?? now(),
            'to_date' => $request->to_date,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Add optional columns if they exist
        if (in_array('branch_setting_id', $columns)) {
            $insertData['branch_setting_id'] = $this->user_sw->branch_setting_id ?? 1;
        }
        
        if (in_array('weight', $columns)) {
            $insertData['weight'] = $member->weight ?? 0;
        }
        
        if (in_array('height', $columns)) {
            $insertData['height'] = $member->height ?? 0;
        }
        
        if (in_array('notes', $columns)) {
            $insertData['notes'] = $request->notes ?? '';
        }
        
        if (in_array('diseases', $columns)) {
            $insertData['diseases'] = '';
        }
        
        if (in_array('status', $columns)) {
            $insertData['status'] = 1;
        }

        // Set plan ID based on available columns
        if ($plan->type == 1) {
            // Training plan
            if (in_array('training_plan_id', $columns)) {
                $insertData['training_plan_id'] = $plan->id;
            }
            if (in_array('diet_plan_id', $columns)) {
                $insertData['diet_plan_id'] = 0;
            }
            if (in_array('training_plan_details', $columns)) {
                $insertData['training_plan_details'] = $plan->content ?? '';
            }
            if (in_array('diet_plan_details', $columns)) {
                $insertData['diet_plan_details'] = '';
            }
            if (in_array('plan_details', $columns)) {
                $insertData['plan_details'] = $plan->content ?? '';
            }
            if (in_array('plan_id', $columns)) {
                $insertData['plan_id'] = $plan->id;
            }
            if (in_array('title', $columns)) {
                $insertData['title'] = $plan->title;
            }
            if (in_array('type', $columns)) {
                $insertData['type'] = $plan->type;
            }
        } else {
            // Diet plan
            if (in_array('training_plan_id', $columns)) {
                $insertData['training_plan_id'] = 0;
            }
            if (in_array('diet_plan_id', $columns)) {
                $insertData['diet_plan_id'] = $plan->id;
            }
            if (in_array('training_plan_details', $columns)) {
                $insertData['training_plan_details'] = '';
            }
            if (in_array('diet_plan_details', $columns)) {
                $insertData['diet_plan_details'] = $plan->content ?? '';
            }
            if (in_array('plan_details', $columns)) {
                $insertData['plan_details'] = $plan->content ?? '';
            }
            if (in_array('plan_id', $columns)) {
                $insertData['plan_id'] = $plan->id;
            }
            if (in_array('title', $columns)) {
                $insertData['title'] = $plan->title;
            }
            if (in_array('type', $columns)) {
                $insertData['type'] = $plan->type;
            }
        }
        
        // Add payment information if provided
        if ($request->amount_paid && $request->amount_paid > 0) {
            if (in_array('price', $columns)) {
                $insertData['price'] = $request->amount_paid; // price = amount_paid
            }
            if (in_array('amount_paid', $columns)) {
                $insertData['amount_paid'] = $request->amount_paid;
            }
            if (in_array('discount', $columns)) {
                $insertData['discount'] = $request->discount ?? 0;
            }
            if (in_array('vat', $columns)) {
                $insertData['vat'] = $request->vat ?? 0;
            }
            if (in_array('vat_percentage', $columns)) {
                $insertData['vat_percentage'] = $request->vat_percentage ?? 0;
            }
            if (in_array('total', $columns)) {
                $insertData['total'] = $request->amount_paid; // total = amount_paid
            }
            if (in_array('payment_type', $columns)) {
                $insertData['payment_type'] = $request->payment_type ?? 0;
            }
        }

        // Insert into sw_gym_training_members table
        $memberPlanId = \DB::table('sw_gym_training_members')->insertGetId($insertData);

        // Handle payment if provided (optional)
        $moneyBoxId = null;
        $paymentMeta = [];
        
        if ($request->amount_paid && $request->amount_paid > 0) {
            $price = $request->price ?? 0;
            $discount = $request->discount ?? 0;
            $vat = $request->vat ?? 0;
            $total = $request->total ?? $price;
            $amountPaid = $request->amount_paid;
            
            $gymMoneyBox = GymMoneyBox::branch()->orderBy('created_at','desc')->first();
            $amount_before = GymMoneyBoxFrontController::amountAfter((float)@$gymMoneyBox->amount, (float)@$gymMoneyBox->amount_before, @$gymMoneyBox->operation);
         
            $moneyBoxData = [
                'branch_setting_id' => $this->user_sw->branch_setting_id ?? 1,
                'user_id' => $this->user_sw->id,
                'member_id' => $memberId,
                'training_plan_id' => $memberPlanId, // FK to sw_gym_training_members
                'operation' => 0, // 0 = addition (income)
                'amount' => $amountPaid,
                'amount_before' => $amount_before, // Starting balance (for income operations)
                'price' => $price,
                'discount' => $discount,
                'vat' => $vat,
                'total' => $total,
                'payment_type' => $request->payment_type ?? 0,
                'notes' => trans('sw.training_plan_payment') . ': ' . $plan->title,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Check for optional money box columns
            $moneyBoxColumns = \Schema::getColumnListing('sw_gym_money_boxes');
            $finalMoneyBoxData = [];
            foreach ($moneyBoxData as $key => $value) {
                if (in_array($key, $moneyBoxColumns)) {
                    $finalMoneyBoxData[$key] = $value;
                }
            }
            
            $moneyBoxId = \DB::table('sw_gym_money_boxes')->insertGetId($finalMoneyBoxData);
            

            // Store payment details in meta
            $paymentMeta = [
                'price' => $price,
                'discount' => $discount,
                'vat' => $vat,
                'total' => $total,
                'amount_paid' => $amountPaid,
                'payment_type' => $request->payment_type,
                'money_box_id' => $moneyBoxId,
            ];
        }

        // Log the action with or without payment details
        $this->logMemberAction($memberId, 'plan', 'assigned', trans('sw.plan_assigned') . ': ' . $plan->title, array_merge([
            'member_plan_id' => $memberPlanId,
            'plan_id' => $plan->id,
            'plan_title' => $plan->title,
            'plan_type' => $plan->type,
        ], $paymentMeta));

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('sw.plan_assigned_and_paid_successfully'),
            'type' => 'success'
        ]);

        return redirect()->back();
    }

    /**
     * Add medicine to member
     */
    public function addMedicine(Request $request, $memberId)
    {
        $request->validate([
            'medicine_id' => 'required|exists:sw_gym_training_medicines,id',
            'dose' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $medicine = GymTrainingMedicine::findOrFail($request->medicine_id);

        // Log the action
        $this->logMemberAction($memberId, 'medicine', 'added', $medicine->name_en . ' - ' . ($request->dose ?? trans('sw.no_dose_specified')), [
            'medicine_id' => $medicine->id,
            'dose' => $request->dose,
            'notes' => $request->notes,
        ]);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('sw.medicine_added_successfully'),
            'type' => 'success'
        ]);

        return redirect()->back();
    }

    /**
     * Upload file for member
     */
    public function addFile(Request $request, $memberId)
    {
        $request->validate([
            'file' => 'required|file|max:2048|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,txt', // 2MB in KB
            'title' => 'nullable|string',
            'type' => 'nullable|string',
        ], [
            'file.mimes' => trans('sw.invalid_file_type') . ': ' . trans('sw.allowed_file_types') . ': PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, GIF, TXT',
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        
        // Create directory if it doesn't exist (C:\wamp64\www\gym\demo_v2\uploads\training_files)
        $uploadPath = base_path('uploads/training_files');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        // Move file to uploads/training_files
        $file->move($uploadPath, $filename);
        $path = 'uploads/training_files/' . $filename;
        
        $fileId = \DB::table('sw_gym_training_files')->insertGetId([
            'branch_setting_id' => $this->user_sw->branch_setting_id ?? 1,
            'user_id' => $this->user_sw->id,
            'member_id' => $memberId,
            'title' => $request->title ?? $filename,
            'file_name' => $filename,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Log the action
        $this->logMemberAction($memberId, 'file', 'uploaded', $request->title ?? $filename, ['file_id' => $fileId, 'path' => $path, 'file_name' => $filename]);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('sw.file_uploaded_successfully'),
            'type' => 'success'
        ]);

        return redirect()->back();
    }

    /**
     * Add track (measurement) for member
     */
    public function addTrack(Request $request, $memberId)
    {
        $request->validate([
            'date' => 'required|date',
            'weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'bmi' => 'nullable|numeric',
            'fat_percentage' => 'nullable|numeric',
            'muscle_mass' => 'nullable|numeric',
            'neck_circumference' => 'nullable|numeric',
            'chest_circumference' => 'nullable|numeric',
            'arm_circumference' => 'nullable|numeric',
            'abdominal_circumference' => 'nullable|numeric',
            'pelvic_circumference' => 'nullable|numeric',
            'thigh_circumference' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        // Check what columns exist in the table
        $columns = \Schema::getColumnListing('sw_gym_training_tracks');
        
        // Prepare insert data with only existing columns
        $insertData = [
            'member_id' => $memberId,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Add optional columns if they exist
        if (in_array('branch_setting_id', $columns)) {
            $insertData['branch_setting_id'] = $this->user_sw->branch_setting_id ?? 1;
        }
        
        if (in_array('user_id', $columns)) {
            $insertData['user_id'] = $this->user_sw->id;
        }
        
        if (in_array('date', $columns) && $request->date) {
            $insertData['date'] = $request->date;
        }
        
        if (in_array('weight', $columns) && $request->weight) {
            $insertData['weight'] = $request->weight;
        }
        
        if (in_array('height', $columns) && $request->height) {
            $insertData['height'] = $request->height;
        }
        
        if (in_array('bmi', $columns) && $request->bmi) {
            $insertData['bmi'] = $request->bmi;
        }
        
        if (in_array('fat_percentage', $columns) && $request->fat_percentage) {
            $insertData['fat_percentage'] = $request->fat_percentage;
        }
        
        if (in_array('muscle_mass', $columns) && $request->muscle_mass) {
            $insertData['muscle_mass'] = $request->muscle_mass;
        }
        
        // Body circumferences
        if (in_array('neck_circumference', $columns) && $request->neck_circumference) {
            $insertData['neck_circumference'] = $request->neck_circumference;
        }
        
        if (in_array('chest_circumference', $columns) && $request->chest_circumference) {
            $insertData['chest_circumference'] = $request->chest_circumference;
        }
        
        if (in_array('arm_circumference', $columns) && $request->arm_circumference) {
            $insertData['arm_circumference'] = $request->arm_circumference;
        }
        
        if (in_array('abdominal_circumference', $columns) && $request->abdominal_circumference) {
            $insertData['abdominal_circumference'] = $request->abdominal_circumference;
        }
        
        if (in_array('pelvic_circumference', $columns) && $request->pelvic_circumference) {
            $insertData['pelvic_circumference'] = $request->pelvic_circumference;
        }
        
        if (in_array('thigh_circumference', $columns) && $request->thigh_circumference) {
            $insertData['thigh_circumference'] = $request->thigh_circumference;
        }
        
        if (in_array('notes', $columns) && $request->notes) {
            $insertData['notes'] = $request->notes;
        }

        $trackId = \DB::table('sw_gym_training_tracks')->insertGetId($insertData);

        // Log the action with measurements in meta
        $this->logMemberAction($memberId, 'track', 'added', trans('sw.progress_measurement_added'), [
            'track_id' => $trackId,
            'date' => $request->date,
            'weight' => $request->weight,
            'height' => $request->height,
            'bmi' => $request->bmi,
            'fat_percentage' => $request->fat_percentage,
            'muscle_mass' => $request->muscle_mass,
            'neck_circumference' => $request->neck_circumference,
            'chest_circumference' => $request->chest_circumference,
            'arm_circumference' => $request->arm_circumference,
            'abdominal_circumference' => $request->abdominal_circumference,
            'pelvic_circumference' => $request->pelvic_circumference,
            'thigh_circumference' => $request->thigh_circumference,
        ]);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('sw.track_added_successfully'),
            'type' => 'success'
        ]);

        return redirect()->back();
    }

    /**
     * Add note for member
     */
    public function addNote(Request $request, $memberId)
    {
        $request->validate([
            'note' => 'required|string',
        ]);

        // Log the action
        $this->logMemberAction($memberId, 'note', 'added', $request->note);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('sw.note_added_successfully'),
            'type' => 'success'
        ]);

        return redirect()->back();
    }

    /**
     * Generate AI recommendation
     */
    public function generateAi(Request $request, $memberId)
    {
        $request->validate([
            'type' => 'required|in:training,diet',
            'goal' => 'nullable|string',
        ]);

        // Get member assessment
        $assessment = GymTrainingAssessment::where('member_id', $memberId)->latest()->first();

        $context = [
            'assessment' => $assessment ? $assessment->answers : null,
            'goal' => $request->goal,
        ];

        // Placeholder AI response (replace with actual AI integration)
        $aiResponse = json_encode([
            'plan_type' => $request->type,
            'summary' => 'AI-generated ' . $request->type . ' plan based on member data',
            'recommendations' => [
                'Replace this with actual AI API integration (OpenAI, etc.)',
                'Use member assessment data to generate personalized recommendations',
            ],
        ], JSON_UNESCAPED_UNICODE);

        $recommendation = GymAiRecommendation::create([
            'member_id' => $memberId,
            'trainer_id' => $this->user_sw->id,
            'type' => $request->type,
            'context_data' => $context,
            'ai_response' => $aiResponse,
            'status' => 'pending',
        ]);

        // Log the action
        $this->logMemberAction($memberId, 'ai_plan', 'generated', trans('sw.ai_recommendation_generated_log'), ['ai_id' => $recommendation->id]);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('sw.ai_recommendation_generated'),
            'type' => 'success'
        ]);

        return redirect()->back();
    }

    /**
     * Helper: Log member action
     */
    private function logMemberAction($memberId, $trainingType, $action, $notes, $meta = [])
    {
        try {
            // Extract reference ID from meta
            $referenceId = null;
            if (isset($meta['assessment_id'])) {
                $referenceId = $meta['assessment_id'];
            } elseif (isset($meta['member_plan_id'])) {
                $referenceId = $meta['member_plan_id'];
            } elseif (isset($meta['medicine_id'])) {
                $referenceId = $meta['medicine_id'];
            } elseif (isset($meta['track_id'])) {
                $referenceId = $meta['track_id'];
            } elseif (isset($meta['file_id'])) {
                $referenceId = $meta['file_id'];
            } elseif (isset($meta['ai_id'])) {
                $referenceId = $meta['ai_id'];
            }

            $log = GymTrainingMemberLog::create([
                'branch_setting_id' => $this->user_sw->branch_setting_id ?? 1,
                'member_id' => $memberId,
                'training_type' => $trainingType,
                'action' => $action,
                'notes' => $notes,
                'reference_id' => $referenceId,
                'meta' => json_encode($meta),
                'created_by' => $this->user_sw->id,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to log member action: ' . $e->getMessage(), [
                'exception' => $e,
                'meta' => $meta,
                'reference_id' => $referenceId ?? 'not set'
            ]);
        }
    }

    /**
     * Download plan as PDF
     */
    public function downloadPlanPDF($memberId, $logId)
    {
        try {
            // Get the log entry
            $log = GymTrainingMemberLog::where('member_id', $memberId)
                ->where('id', $logId)
                ->where('training_type', 'plan')
                ->firstOrFail();

            // Get member
            $member = GymMember::findOrFail($memberId);

            // Get plan details
            $plan = $this->getLogDetails($log);
            
            if (!$plan) {
                abort(404, 'Plan not found');
            }

            // Ensure tasks are loaded
            if (!isset($plan->tasks) || !$plan->tasks) {
                $plan = GymTrainingPlan::with(['tasks' => function($q) {
                    $q->orderBy('order', 'asc')->orderBy('id', 'asc');
                }])->find($plan->id ?? $plan->plan_id ?? null);
            }

            // Prepare data for PDF
            $data = [
                'plan' => $plan,
                'member' => $member,
                'log' => $log,
                'lang' => app()->getLocale(),
            ];

            // Try mPDF for better Arabic support
            if (app()->getLocale() == 'ar') {
                try {
                    // Render view to HTML
                    $html = view('software::Front.training_plan_pdf', $data)->render();
                    
                    // Configure mPDF for Arabic
                    $mpdf = new Mpdf([
                        'mode' => 'utf-8',
                        'format' => 'A4',
                        'orientation' => 'P',
                        'margin_left' => 15,
                        'margin_right' => 15,
                        'margin_top' => 15,
                        'margin_bottom' => 15,
                        'margin_header' => 9,
                        'margin_footer' => 9,
                        'directionality' => 'rtl',
                        'default_font' => 'dejavusans',
                        'default_font_size' => 12,
                        'autoScriptToLang' => true,
                        'autoLangToFont' => true,
                        'allow_charset_conversion' => false,
                        'tempDir' => storage_path('app/temp'),
                    ]);
                    
                    // Set RTL for Arabic - must be called after instantiation
                    $mpdf->SetDirectionality('rtl');
                    
                    // Write HTML - mPDF handles RTL automatically when directionality is set
                    $mpdf->WriteHTML($html);
                    
                    // Generate filename
                    $filename = 'plan_' . ($plan->title ?? 'plan') . '_' . $member->code . '_' . date('Y-m-d') . '.pdf';
                    $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
                    
                    return response($mpdf->Output($filename, 'D'), 200, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '"'
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error('mPDF failed for plan PDF: ' . $e->getMessage());
                    // Fallback to DomPDF
                }
            }
            
            // Use DomPDF as fallback or for non-Arabic
            $pdf = Pdf::loadView('software::Front.training_plan_pdf', $data);
            
            // Set paper size and orientation
            $pdf->setPaper('A4', 'portrait');
            
            // Set options for better Arabic support
            $options = [
                'enable-local-file-access' => true,
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isFontSubsettingEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'isPhpEnabled' => true,
                'isJavascriptEnabled' => false,
                'chroot' => public_path(),
            ];
            
            // Configure for Arabic text
            if (app()->getLocale() == 'ar') {
                $options['defaultFont'] = 'DejaVu Sans';
                $options['fontDir'] = storage_path('fonts/');
                $options['fontCache'] = storage_path('fonts/');
            }
            
            foreach ($options as $key => $value) {
                $pdf->setOption($key, $value);
            }
            
            // Generate filename
            $filename = 'plan_' . ($plan->title ?? 'plan') . '_' . $member->code . '_' . date('Y-m-d') . '.pdf';
            $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);

            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            \Log::error('Failed to generate plan PDF: ' . $e->getMessage(), [
                'exception' => $e,
                'member_id' => $memberId,
                'log_id' => $logId
            ]);
            
            session()->flash('sweet_flash_message', [
                'title' => trans('admin.error'),
                'message' => trans('sw.failed_to_generate_pdf') . ': ' . $e->getMessage(),
                'type' => 'error'
            ]);
            
            return redirect()->back();
        }
    }

    /**
     * Get detailed data for a log entry
     */
    private function getLogDetails($log)
    {
        $meta = is_string($log->meta) ? json_decode($log->meta, true) : (array)$log->meta;

        try {
            switch ($log->training_type) {
                case 'assessment':
                    if ($log->reference_id) {
                        $assessment = GymTrainingAssessment::find($log->reference_id);
                        if ($assessment) {
                            return $assessment;
                        }
                    }
                    return null;
                
                case 'plan':
                    // Try reference_id first, then fallback to meta
                    $memberPlanId = $log->reference_id ?? $meta['member_plan_id'] ?? null;
                    $planId = $meta['plan_id'] ?? null;
                    
                    if ($memberPlanId) {
                        $planAssignment = \DB::table('sw_gym_training_members')->find($memberPlanId);
                        if ($planAssignment) {
                            // Get plan ID based on type (training_plan_id or diet_plan_id or plan_id)
                            if (!$planId) {
                                $planId = $planAssignment->training_plan_id ?? $planAssignment->diet_plan_id ?? $planAssignment->plan_id;
                            }
                            
                            if ($planId) {
                                $plan = GymTrainingPlan::with(['tasks' => function($q) {
                                    $q->orderBy('order', 'asc')->orderBy('id', 'asc');
                                }])->find($planId);
                                
                                if ($plan) {
                                    $plan->from_date = $planAssignment->from_date ?? null;
                                    $plan->to_date = $planAssignment->to_date ?? null;
                                    $plan->assignment_weight = $planAssignment->weight ?? null;
                                    $plan->assignment_height = $planAssignment->height ?? null;
                                    $plan->assignment_notes = $planAssignment->notes ?? null;
                                    return $plan;
                                }
                            }
                        }
                    } elseif ($planId) {
                        // If we have plan_id but no member_plan_id, still try to load the plan
                        $plan = GymTrainingPlan::with(['tasks' => function($q) {
                            $q->orderBy('order', 'asc')->orderBy('id', 'asc');
                        }])->find($planId);
                        
                        if ($plan) {
                            // Try to get assignment data from meta if available
                            $plan->from_date = isset($meta['from_date']) ? $meta['from_date'] : null;
                            $plan->to_date = isset($meta['to_date']) ? $meta['to_date'] : null;
                            $plan->assignment_weight = isset($meta['assignment_weight']) ? $meta['assignment_weight'] : null;
                            $plan->assignment_height = isset($meta['assignment_height']) ? $meta['assignment_height'] : null;
                            $plan->assignment_notes = isset($meta['assignment_notes']) ? $meta['assignment_notes'] : null;
                            return $plan;
                        }
                    }
                    
                    return null;
                
                case 'medicine':
                    // Medicine uses reference_id to store medicine_id
                    $medicineId = $log->reference_id ?? $meta['medicine_id'] ?? null;
                    if ($medicineId) {
                        $medicine = GymTrainingMedicine::find($medicineId);
                        if ($medicine) {
                            $medicine->dose_instructions = $meta['dose'] ?? '';
                            $medicine->log_notes = $meta['notes'] ?? '';
                            return $medicine;
                        }
                    }
                    return null;
                
                case 'track':
                    if ($log->reference_id) {
                        $track = GymTrainingTrack::find($log->reference_id);
                        if ($track) {
                            // Get member for calculations
                            $member = GymMember::find($track->member_id);
                            $gender = $member->gender ?? 'male';
                            $age = $member->age ?? ($member->birth_date ? \Carbon\Carbon::parse($member->birth_date)->age : 30);
                            
                            // Calculate additional metrics
                            $calculations = [];
                            $weight = $track->weight ?? 0;
                            $height = $track->height ?? 0;
                            $heightInMeters = $height / 100;
                            $fatPercentage = $track->fat_percentage ?? 0;
                            
                            if ($weight > 0 && $height > 0) {
                                // BMR (Basal Metabolic Rate) - Mifflin-St Jeor Equation
                                if ($gender == 'female') {
                                    $bmr = 10 * $weight + 6.25 * $height - 5 * $age - 161;
                                } else {
                                    $bmr = 10 * $weight + 6.25 * $height - 5 * $age + 5;
                                }
                                $calculations['bmr'] = round($bmr, 2) . ' kcal/day';
                                
                                // TDEE (Total Daily Energy Expenditure) - approximate using BMR * 1.55 (moderately active)
                                $tdee = $bmr * 1.55;
                                $calculations['tdee'] = round($tdee, 2) . ' kcal/day';
                                
                                // Ideal Weight Range (BMI 18.5-24.9)
                                $idealWeightMin = 18.5 * ($heightInMeters * $heightInMeters);
                                $idealWeightMax = 24.9 * ($heightInMeters * $heightInMeters);
                                $calculations['ideal_weight'] = round($idealWeightMin, 1) . ' - ' . round($idealWeightMax, 1) . ' kg';
                                
                                // Weight to Lose/Gain (based on ideal weight)
                                $currentBMI = $weight / ($heightInMeters * $heightInMeters);
                                if ($currentBMI > 24.9) {
                                    $weightToLose = $weight - $idealWeightMax;
                                    $calculations['weight_to_lose'] = round($weightToLose, 1) . ' kg';
                                } elseif ($currentBMI < 18.5) {
                                    $weightToGain = $idealWeightMin - $weight;
                                    $calculations['weight_to_gain'] = round($weightToGain, 1) . ' kg';
                                }
                                
                                // Waist-to-Height Ratio (if waist/abdominal circumference available)
                                if ($track->abdominal_circumference) {
                                    $whtr = ($track->abdominal_circumference / $height) * 100;
                                    $calculations['waist_to_height_ratio'] = round($whtr, 1) . '%';
                                    if ($whtr >= 50) {
                                        $calculations['whtr_status'] = trans('sw.high_risk');
                                    } elseif ($whtr >= 45) {
                                        $calculations['whtr_status'] = trans('sw.moderate_risk');
                                    } else {
                                        $calculations['whtr_status'] = trans('sw.low_risk');
                                    }
                                }
                            }
                            
                            if ($weight > 0 && $fatPercentage > 0) {
                                // Body Fat Mass
                                $bodyFatMass = ($weight * $fatPercentage) / 100;
                                $calculations['body_fat_mass'] = round($bodyFatMass, 2) . ' kg';
                                
                                // Lean Body Mass
                                $leanBodyMass = $weight - $bodyFatMass;
                                $calculations['lean_body_mass'] = round($leanBodyMass, 2) . ' kg';
                            }
                            
                            // Structure measurements for display
                            $track->measurements = [
                                'date' => $track->date ? \Carbon\Carbon::parse($track->date)->format('Y-m-d') : ($track->created_at ? $track->created_at->format('Y-m-d') : null),
                                'weight' => $track->weight ? $track->weight . ' kg' : null,
                                'height' => $track->height ? $track->height . ' cm' : null,
                                'bmi' => $track->bmi ?? (($weight > 0 && $height > 0) ? round($weight / (($height / 100) * ($height / 100)), 2) : null),
                                'fat_percentage' => $track->fat_percentage ? $track->fat_percentage . '%' : null,
                                'muscle_mass' => $track->muscle_mass ? $track->muscle_mass . ' kg' : null,
                                'neck_circumference' => $track->neck_circumference ? $track->neck_circumference . ' cm' : null,
                                'chest_circumference' => $track->chest_circumference ? $track->chest_circumference . ' cm' : null,
                                'arm_circumference' => $track->arm_circumference ? $track->arm_circumference . ' cm' : null,
                                'abdominal_circumference' => $track->abdominal_circumference ? $track->abdominal_circumference . ' cm' : null,
                                'pelvic_circumference' => $track->pelvic_circumference ? $track->pelvic_circumference . ' cm' : null,
                                'thigh_circumference' => $track->thigh_circumference ? $track->thigh_circumference . ' cm' : null,
                            ];
                            
                            // Add calculations
                            $track->calculations = $calculations;
                            
                            // Remove null values from measurements
                            $track->measurements = array_filter($track->measurements);
                            return $track;
                        }
                    }
                    return null;
                
                case 'file':
                    if ($log->reference_id) {
                        $file = GymTrainingFile::find($log->reference_id);
                        if ($file) {
                            // Construct the full path: training_files/filename
                            $filename = $file->file_name ?? $file->file_path ?? null;
                            $file->file_path = $filename ? 'training_files/' . $filename : null;
                            $file->file_name_only = $filename;
                            return $file;
                        }
                    }
                    return null;
                
                case 'note':
                    // Notes don't have reference ID, just display the note text
                    return (object) ['note_text' => $log->notes];
                
                case 'ai':
                case 'ai_plan':
                    if ($log->reference_id) {
                        $ai = GymAiRecommendation::with(['member', 'trainer'])
                            ->find($log->reference_id);
                        if ($ai) {
                            return $ai;
                        }
                    }
                    return null;
                
                default:
                    return null;
            }
        } catch (\Exception $e) {
            \Log::error('Error getting log details: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate AI plan based on member assessment
     */
    public function generateAiPlan(Request $request, $memberId)
    {
        $request->validate([
            'type' => 'required|in:1,2',
            'focus' => 'nullable|string',
            'language' => 'required|in:ar,en',
            'custom_notes' => 'nullable|string',
            'include_assessment' => 'nullable|boolean',
            'include_tracks' => 'nullable|boolean',
            'include_medicines' => 'nullable|boolean',
            'include_previous_plans' => 'nullable|boolean',
        ]);

        // Get member
        $member = GymMember::findOrFail($memberId);

        // Build comprehensive AI context
        $context = [
            'language' => $request->language,
            'plan_type' => $request->type, // 1 = Training, 2 = Diet
            'focus' => $request->focus,
            'custom_notes' => $request->custom_notes,
            'member' => [
                'id' => $member->id,
                'name' => $member->name,
                'phone' => $member->phone ?? null,
                'email' => $member->email ?? null,
                'birth_date' => $member->birth_date ?? null,
                'gender' => $member->gender ?? null,
            ],
        ];

        // Include assessment if requested
        if ($request->include_assessment) {
            $assessment = GymTrainingAssessment::where('member_id', $memberId)
                ->latest()
                ->first();
            
            if ($assessment) {
                $context['assessment'] = [
                    'date' => $assessment->created_at->format('Y-m-d'),
                    'data' => $assessment->answers,
                ];
            }
        }

        // Include tracking history if requested
        if ($request->include_tracks) {
            // Check which columns exist in sw_gym_training_tracks
            $trackColumns = \Schema::getColumnListing('sw_gym_training_tracks');
            $desiredColumns = ['weight', 'height', 'fat_percentage', 'muscle_mass', 'bmi', 'neck_circumference', 'chest_circumference', 'arm_circumference', 'abdominal_circumference', 'pelvic_circumference', 'thigh_circumference', 'notes', 'created_at'];
            $selectColumns = array_intersect($desiredColumns, $trackColumns);
            
            if (empty($selectColumns)) {
                $selectColumns = ['*']; // Fallback to all columns
            }
            
            $tracks = GymTrainingTrack::where('member_id', $memberId)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get($selectColumns);
            
            if ($tracks->count() > 0) {
                $context['tracking_history'] = $tracks->map(function($track) use ($trackColumns) {
                    $data = [
                        'date' => $track->created_at->format('Y-m-d'),
                    ];
                    
                    // Only include columns that exist
                    if (in_array('weight', $trackColumns)) $data['weight'] = $track->weight;
                    if (in_array('height', $trackColumns)) $data['height'] = $track->height;
                    if (in_array('bmi', $trackColumns)) $data['bmi'] = $track->bmi;
                    if (in_array('fat_percentage', $trackColumns)) $data['fat_percentage'] = $track->fat_percentage;
                    if (in_array('muscle_mass', $trackColumns)) $data['muscle_mass'] = $track->muscle_mass;
                    if (in_array('notes', $trackColumns)) $data['notes'] = $track->notes;
                    
                    // Measurements
                    $measurements = [];
                    if (in_array('neck_circumference', $trackColumns)) $measurements['neck'] = $track->neck_circumference;
                    if (in_array('chest_circumference', $trackColumns)) $measurements['chest'] = $track->chest_circumference;
                    if (in_array('arm_circumference', $trackColumns)) $measurements['arm'] = $track->arm_circumference;
                    if (in_array('abdominal_circumference', $trackColumns)) $measurements['abdominal'] = $track->abdominal_circumference;
                    if (in_array('pelvic_circumference', $trackColumns)) $measurements['pelvic'] = $track->pelvic_circumference;
                    if (in_array('thigh_circumference', $trackColumns)) $measurements['thigh'] = $track->thigh_circumference;
                    
                    if (!empty($measurements)) {
                        $data['measurements'] = $measurements;
                    }
                    
                    return $data;
                })->toArray();
                
                // Add latest measurements summary
                $latestTrack = $tracks->first();
                $currentStats = [];
                if (in_array('weight', $trackColumns)) $currentStats['weight'] = $latestTrack->weight;
                if (in_array('height', $trackColumns)) $currentStats['height'] = $latestTrack->height;
                if (in_array('bmi', $trackColumns)) $currentStats['bmi'] = $latestTrack->bmi;
                if (in_array('fat_percentage', $trackColumns)) $currentStats['fat_percentage'] = $latestTrack->fat_percentage;
                if (in_array('muscle_mass', $trackColumns)) $currentStats['muscle_mass'] = $latestTrack->muscle_mass;
                
                if (!empty($currentStats)) {
                    $context['current_stats'] = $currentStats;
                }
            }
        }

        // Include medicines if requested
        if ($request->include_medicines) {
            $medicineLogs = GymTrainingMemberLog::where('member_id', $memberId)
                ->where('training_type', 'medicine')
                ->with('medicine')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            if ($medicineLogs->count() > 0) {
                $context['current_medicines'] = $medicineLogs->map(function($log) use ($request) {
                    $meta = is_string($log->meta) ? json_decode($log->meta, true) : $log->meta;
                    return [
                        'name' => $log->medicine ? ($log->medicine->{'name_' . $request->language} ?? $log->medicine->name_en) : 'Unknown',
                        'dose' => $meta['dose'] ?? null,
                        'notes' => $meta['notes'] ?? null,
                        'started_date' => $log->created_at->format('Y-m-d'),
                    ];
                })->toArray();
            }
        }

        // Include previous plans if requested
        if ($request->include_previous_plans) {
            $previousPlans = \DB::table('sw_gym_training_members')
                ->where('member_id', $memberId)
                ->whereNotNull('plan_id')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(['plan_id', 'title', 'type', 'plan_details', 'from_date', 'to_date', 'created_at']);
            
            if ($previousPlans->count() > 0) {
                $context['previous_plans'] = $previousPlans->map(function($plan) {
                    return [
                        'title' => $plan->title,
                        'type' => $plan->type == 1 ? 'Training' : 'Diet',
                        'details' => strip_tags($plan->plan_details),
                        'from_date' => $plan->from_date,
                        'to_date' => $plan->to_date,
                        'duration_days' => $plan->from_date && $plan->to_date ? 
                            \Carbon\Carbon::parse($plan->from_date)->diffInDays(\Carbon\Carbon::parse($plan->to_date)) : null,
                    ];
                })->toArray();
            }
        }

        // Generate plan using AI (placeholder - integrate with OpenAI/Claude API)
        $generatedPlan = $this->mockAiPlanGeneration($request->type, $context);

        return response()->json([
            'success' => true,
            'plan' => $generatedPlan,
            'context_summary' => [
                'language' => $request->language,
                'has_assessment' => isset($context['assessment']),
                'tracks_count' => isset($context['tracking_history']) ? count($context['tracking_history']) : 0,
                'medicines_count' => isset($context['current_medicines']) ? count($context['current_medicines']) : 0,
                'previous_plans_count' => isset($context['previous_plans']) ? count($context['previous_plans']) : 0,
                'custom_notes' => !empty($request->custom_notes),
            ],
        ]);
    }

    /**
     * Mock AI plan generation (replace with actual AI API)
     */
    private function mockAiPlanGeneration($type, $context)
    {
        $focus = $context['focus'] ?? 'general';
        $isTraining = ($type == 1);

        if ($isTraining) {
            return [
                'title' => $this->getTrainingPlanTitle($focus),
                'description' => 'AI-generated training plan based on member assessment',
                'duration' => 30,
                'type' => 1,
                'tasks' => $this->generateTrainingTasks($focus),
            ];
        } else {
            return [
                'title' => $this->getDietPlanTitle($focus),
                'description' => 'AI-generated diet plan based on member assessment',
                'duration' => 30,
                'type' => 2,
                'tasks' => $this->generateDietTasks($focus),
            ];
        }
    }

    private function getTrainingPlanTitle($focus)
    {
        $titles = [
            'general_fitness' => trans('sw.general_fitness') . ' ' . trans('sw.training_plan'),
            'weight_loss' => trans('sw.weight_loss') . ' ' . trans('sw.training_plan'),
            'muscle_building' => trans('sw.muscle_building') . ' ' . trans('sw.training_plan'),
            'endurance' => trans('sw.endurance') . ' ' . trans('sw.training_plan'),
            'flexibility' => trans('sw.flexibility') . ' ' . trans('sw.training_plan'),
        ];
        return $titles[$focus] ?? trans('sw.training_plan');
    }

    private function getDietPlanTitle($focus)
    {
        $titles = [
            'balanced' => trans('sw.balanced_nutrition') . ' ' . trans('sw.diet_plan'),
            'low_carb' => trans('sw.low_carb') . ' ' . trans('sw.diet_plan'),
            'high_protein' => trans('sw.high_protein') . ' ' . trans('sw.diet_plan'),
            'vegetarian' => trans('sw.vegetarian') . ' ' . trans('sw.diet_plan'),
            'keto' => trans('sw.keto') . ' ' . trans('sw.diet_plan'),
        ];
        return $titles[$focus] ?? trans('sw.diet_plan');
    }

    private function generateTrainingTasks($focus)
    {
        // Sample training tasks
        return [
            [
                'day_name' => trans('sw.day') . ' 1',
                'title' => 'Warm-up & Cardio',
                'description' => '10 minutes jogging, 5 minutes stretching',
                't_group' => 3,
                't_repeats' => 10,
                't_rest' => '60s',
            ],
            [
                'day_name' => trans('sw.day') . ' 1',
                'title' => 'Push-ups',
                'description' => 'Standard push-ups with proper form',
                't_group' => 3,
                't_repeats' => 15,
                't_rest' => '60s',
            ],
            [
                'day_name' => trans('sw.day') . ' 2',
                'title' => 'Squats',
                'description' => 'Bodyweight squats',
                't_group' => 4,
                't_repeats' => 20,
                't_rest' => '45s',
            ],
        ];
    }

    private function generateDietTasks($focus)
    {
        // Sample diet tasks
        return [
            [
                'day_name' => trans('sw.day') . ' 1',
                'title' => 'Breakfast',
                'description' => 'Oatmeal with fruits and nuts',
                'd_calories' => '350',
                'd_protein' => '12g',
                'd_carb' => '55g',
                'd_fats' => '10g',
            ],
            [
                'day_name' => trans('sw.day') . ' 1',
                'title' => 'Lunch',
                'description' => 'Grilled chicken with brown rice and vegetables',
                'd_calories' => '500',
                'd_protein' => '40g',
                'd_carb' => '50g',
                'd_fats' => '15g',
            ],
            [
                'day_name' => trans('sw.day') . ' 1',
                'title' => 'Dinner',
                'description' => 'Salmon with quinoa and salad',
                'd_calories' => '450',
                'd_protein' => '35g',
                'd_carb' => '40g',
                'd_fats' => '18g',
            ],
        ];
    }

    /**
     * Save AI-generated plan as template
     */
    public function saveAiPlanTemplate(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:1,2',
            'duration' => 'required|integer',
            'tasks' => 'required|array',
        ]);

        try {
            // Prepare plan data
            $planData = [
                'branch_setting_id' => $this->user_sw->branch_setting_id ?? 1,
                'title' => $request->title,
                'type' => $request->type,
                'duration' => $request->duration,
                'status' => $request->status ?? 1,
                'user_id' => $this->user_sw->id,
            ];
            
            // Check which columns exist and add content/details
            $planColumns = \Schema::getColumnListing('sw_gym_training_plans');
            if (in_array('content', $planColumns)) {
                $planData['content'] = $request->description ?? '';
            }
            if (in_array('details', $planColumns)) {
                $planData['details'] = $request->description ?? '';
            }
            
            // Create plan
            $plan = GymTrainingPlan::create($planData);

            // Create tasks
            foreach ($request->tasks as $index => $taskData) {
                $plan->tasks()->create([
                    'branch_setting_id' => $this->user_sw->branch_setting_id ?? 1,
                    'day_name' => $taskData['day_name'] ?? '',
                    'title' => $taskData['title'] ?? '',
                    'description' => $taskData['description'] ?? '',
                    'name_ar' => $taskData['title'] ?? '',
                    'name_en' => $taskData['title'] ?? '',
                    't_group' => $taskData['t_group'] ?? null,
                    't_repeats' => $taskData['t_repeats'] ?? null,
                    't_rest' => $taskData['t_rest'] ?? null,
                    'd_calories' => $taskData['d_calories'] ?? null,
                    'd_protein' => $taskData['d_protein'] ?? null,
                    'd_carb' => $taskData['d_carb'] ?? null,
                    'd_fats' => $taskData['d_fats'] ?? null,
                    'status' => 1,
                    'order' => $index + 1,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => trans('sw.ai_plan_saved_successfully'),
                'plan_id' => $plan->id,
                'plan' => [
                    'id' => $plan->id,
                    'title' => $plan->title,
                    'type' => $plan->type,
                    'type_name' => $plan->type == 1 ? trans('sw.training') : trans('sw.diet'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Assign AI-generated plan directly to member
     */
    public function assignAiPlanToMember(Request $request, $memberId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:1,2',
            'duration' => 'required|integer',
            'tasks' => 'required|array',
        ]);

        try {
            // First, save as template
            $saveResponse = $this->saveAiPlanTemplate($request);
            $responseData = json_decode($saveResponse->getContent(), true);

            if (!$responseData['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $responseData['message'],
                ], 500);
            }

            $planId = $responseData['plan_id'];
            $plan = GymTrainingPlan::find($planId);

            // Then, assign to member
            $member = GymMember::findOrFail($memberId);
            $weight = $member->weight ?? 0;
            $height = $member->height ?? 0;

            $trainingMemberData = [
                'member_id' => $memberId,
                'user_id' => $this->user_sw->id,
                'from_date' => now()->toDateString(),
                'to_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'branch_setting_id' => $this->user_sw->branch_setting_id ?? 1,
                'weight' => $weight,
                'height' => $height,
                'notes' => '',
                'diseases' => '',
                'status' => 1,
                'plan_details' => $plan->details ?? '',
                'plan_id' => $planId,
                'title' => $plan->title,
                'type' => $plan->type,
            ];

            // Check for optional columns and insert
            $trainingMemberColumns = \Schema::getColumnListing('sw_gym_training_members');
            $finalData = [];
            foreach ($trainingMemberData as $key => $value) {
                if (in_array($key, $trainingMemberColumns)) {
                    $finalData[$key] = $value;
                }
            }

            $memberPlanId = \DB::table('sw_gym_training_members')->insertGetId($finalData);

            // Log the action
            $this->logMemberAction($memberId, 'plan', 'assigned', trans('sw.ai_plan_assigned_log') . ': ' . $plan->title, [
                'plan_id' => $planId,
                'member_plan_id' => $memberPlanId,
            ]);

            return response()->json([
                'success' => true,
                'message' => trans('sw.ai_plan_assigned_successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

