@extends('software::layouts.list')
@section('list_title') {{ @$title }} @endsection
@section('breadcrumb')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home') }}</a>
        </li>
        <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.listTrainingMemberLog') }}" class="text-muted text-hover-primary">{{ trans('sw.training_member_logs') }}</a>
        </li>
        <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
        <li class="breadcrumb-item text-gray-900">{{ $member->name }}</li>
    </ul>
@endsection

@section('list_add_button')
    
@endsection

@section('page_body')
<!-- Member Info Card -->
<div class="card card-flush mb-5">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="symbol symbol-circle symbol-75px overflow-hidden me-5">
                <div class="symbol-label fs-2 bg-light-primary text-primary">
                    {{ substr($member->name, 0, 2) }}
                </div>
            </div>
            <div class="flex-grow-1">
                <h3 class="mb-1">{{ $member->name }}</h3>
                <div class="text-muted">
                    <i class="ki-outline ki-phone fs-5 me-1"></i> {{ $member->phone }} &nbsp;&nbsp;
                    <i class="ki-outline ki-sms fs-5 me-1"></i> {{ $member->email }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card card-flush mb-5">
    <div class="card-header">
        <h3 class="card-title">{{ trans('sw.quick_actions') }}</h3>
    </div>
    <div class="card-body pt-0">
        <div class="row g-3">
            <div class="col-md-3 col-sm-6">
                <button type="button" class="btn btn-light-primary w-100 py-4" data-bs-toggle="modal" data-bs-target="#assessmentModal">
                    <i class="ki-outline ki-document fs-2x mb-2"></i>
                    <div class="fw-bold">{{ trans('sw.add_assessment') }}</div>
                    <div class="fs-7 text-muted">{{ trans('sw.add_assessment_desc') }}</div>
                </button>
            </div>
            <div class="col-md-3 col-sm-6">
                <button type="button" class="btn btn-light-success w-100 py-4" data-bs-toggle="modal" data-bs-target="#planModal">
                    <i class="ki-outline ki-notepad fs-2x mb-2"></i>
                    <div class="fw-bold">{{ trans('sw.assign_plan') }}</div>
                    <div class="fs-7 text-muted">{{ trans('sw.assign_plan_desc') }}</div>
                </button>
            </div>
            <div class="col-md-3 col-sm-6">
                <button type="button" class="btn btn-light-warning w-100 py-4" data-bs-toggle="modal" data-bs-target="#medicineModal">
                    <i class="ki-outline ki-capsule fs-2x mb-2"></i>
                    <div class="fw-bold">{{ trans('sw.add_medicine') }}</div>
                    <div class="fs-7 text-muted">{{ trans('sw.add_medicine_desc') }}</div>
                </button>
            </div>
            <div class="col-md-3 col-sm-6">
                <button type="button" class="btn btn-light-info w-100 py-4" data-bs-toggle="modal" data-bs-target="#trackModal">
                    <i class="ki-outline ki-chart-simple fs-2x mb-2"></i>
                    <div class="fw-bold">{{ trans('sw.add_track') }}</div>
                    <div class="fs-7 text-muted">{{ trans('sw.add_track_desc') }}</div>
                </button>
            </div>
            <div class="col-md-3 col-sm-6">
                <button type="button" class="btn btn-light-danger w-100 py-4" data-bs-toggle="modal" data-bs-target="#fileModal">
                    <i class="ki-outline ki-file-up fs-2x mb-2"></i>
                    <div class="fw-bold">{{ trans('sw.upload_file') }}</div>
                    <div class="fs-7 text-muted">{{ trans('sw.upload_file_desc') }}</div>
                </button>
            </div>
            <div class="col-md-3 col-sm-6">
                <button type="button" class="btn btn-light-dark w-100 py-4" data-bs-toggle="modal" data-bs-target="#noteModal">
                    <i class="ki-outline ki-note fs-2x mb-2"></i>
                    <div class="fw-bold">{{ trans('sw.add_note') }}</div>
                    <div class="fs-7 text-muted">{{ trans('sw.add_note_desc') }}</div>
                </button>
            </div>
            <div class="col-md-3 col-sm-6">
                <button type="button" class="btn btn-light-primary w-100 py-4" data-bs-toggle="modal" data-bs-target="#aiModal">
                    <i class="ki-outline ki-robot fs-2x mb-2"></i>
                    <div class="fw-bold">{{ trans('sw.ai_generate_plan') }}</div>
                    <div class="fs-7 text-muted">{{ trans('sw.ai_generate_plan_desc') }}</div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Activity Timeline -->
<div class="card card-flush">
    <div class="card-header">
        <h3 class="card-title">{{ trans('sw.activity_timeline') }}</h3>
        <div class="card-toolbar">
            <span class="badge badge-light-primary">{{ $logs->total() }} {{ trans('sw.total_activities') }}</span>
        </div>
    </div>
    <div class="card-body">
        @forelse($logs as $log)
        <div class="card card-flush mb-5 border">
            <div class="card-header min-h-50px cursor-pointer" data-bs-toggle="collapse" data-bs-target="#log_{{ $log->id }}">
                <div class="card-title">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-circle symbol-40px me-3">
                            <div class="symbol-label 
                                @if($log->training_type == 'assessment') bg-light-primary
                                @elseif($log->training_type == 'plan') bg-light-success
                                @elseif($log->training_type == 'medicine') bg-light-warning
                                @elseif($log->training_type == 'track') bg-light-info
                                @elseif($log->training_type == 'file') bg-light-danger
                                @elseif($log->training_type == 'note') bg-light-dark
                                @else bg-light-primary
                                @endif">
                                <i class="ki-outline 
                                    @if($log->training_type == 'assessment') ki-document
                                    @elseif($log->training_type == 'plan') ki-notepad
                                    @elseif($log->training_type == 'medicine') ki-capsule
                                    @elseif($log->training_type == 'track') ki-chart-simple
                                    @elseif($log->training_type == 'file') ki-file-up
                                    @elseif($log->training_type == 'note') ki-note
                                    @else ki-robot
                                    @endif
                                fs-2 
                                    @if($log->training_type == 'assessment') text-primary
                                    @elseif($log->training_type == 'plan') text-success
                                    @elseif($log->training_type == 'medicine') text-warning
                                    @elseif($log->training_type == 'track') text-info
                                    @elseif($log->training_type == 'file') text-danger
                                    @elseif($log->training_type == 'note') text-dark
                                    @else text-primary
                                    @endif"></i>
                            </div>
                        </div>
                        <div>
                            <div class="fs-6 fw-bold text-gray-800">{{ $log->notes }}</div>
                            <div class="text-muted fs-7">
                                <i class="ki-outline ki-calendar fs-7"></i> {{ $log->created_at->format('Y-m-d H:i') }}
                                @if($log->creator)
                                <span class="mx-2">·</span>
                                <i class="ki-outline ki-user fs-7"></i> {{ $log->creator->name }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-toolbar">
                    <span class="badge 
                        @if($log->training_type == 'assessment') badge-light-primary
                        @elseif($log->training_type == 'plan') badge-light-success
                        @elseif($log->training_type == 'medicine') badge-light-warning
                        @elseif($log->training_type == 'track') badge-light-info
                        @elseif($log->training_type == 'file') badge-light-danger
                        @elseif($log->training_type == 'note') badge-light-dark
                        @else badge-light-primary
                        @endif me-2">
                        {{ ucfirst($log->training_type) }}
                    </span>
                    <i class="ki-outline ki-down fs-3"></i>
                </div>
            </div>
            
            <div id="log_{{ $log->id }}" class="collapse">
                <div class="card-body border-top">
                    @if($log->details)
                        {{-- Assessment Details --}}
                        @if($log->training_type == 'assessment' && isset($log->details->answers))
                            @php
                                // Translation mapping for assessment fields
                                $fieldTranslations = [
                                    'age' => trans('sw.age'),
                                    'gender' => trans('sw.gender'),
                                    'weight' => trans('sw.weight'),
                                    'height' => trans('sw.height'),
                                    'bmi' => trans('sw.bmi'),
                                    'waist_circumference' => trans('sw.waist_circumference'),
                                    'hip_circumference' => trans('sw.hip_circumference'),
                                    'chest_circumference' => trans('sw.chest_circumference'),
                                    'arm_circumference' => trans('sw.arm_circumference'),
                                    'thigh_circumference' => trans('sw.thigh_circumference'),
                                    'fat_percentage' => trans('sw.fat_percentage'),
                                    'muscle_mass' => trans('sw.muscle_mass'),
                                    'water_percentage' => trans('sw.water_percentage'),
                                    'bone_mass' => trans('sw.bone_mass'),
                                    'primary_goal' => trans('sw.primary_goal'),
                                    'target_weight' => trans('sw.target_weight'),
                                    'target_date' => trans('sw.target_date'),
                                    'goals' => trans('sw.goal_details'),
                                    'training_experience' => trans('sw.training_experience'),
                                    'injuries' => trans('sw.injuries'),
                                    'diseases' => trans('sw.diseases'),
                                    'medications' => trans('sw.medications'),
                                    'allergies' => trans('sw.allergies'),
                                    'activity_level' => trans('sw.activity_level'),
                                    'sleep_hours' => trans('sw.sleep_hours'),
                                    'stress_level' => trans('sw.stress_level'),
                                    'diet_type' => trans('sw.diet_type'),
                                    'water_intake' => trans('sw.water_intake'),
                                ];
                                
                                // Value translations for dropdown options
                                $valueTranslations = [
                                    'male' => trans('sw.male'),
                                    'female' => trans('sw.female'),
                                    'weight_loss' => trans('sw.weight_loss'),
                                    'muscle_gain' => trans('sw.muscle_gain'),
                                    'body_toning' => trans('sw.body_toning'),
                                    'strength_increase' => trans('sw.strength_increase'),
                                    'endurance_improvement' => trans('sw.endurance_improvement'),
                                    'flexibility' => trans('sw.flexibility'),
                                    'general_fitness' => trans('sw.general_fitness'),
                                    'beginner' => trans('sw.beginner'),
                                    'intermediate' => trans('sw.intermediate'),
                                    'advanced' => trans('sw.advanced'),
                                    'sedentary' => trans('sw.sedentary'),
                                    'light' => trans('sw.light'),
                                    'moderate' => trans('sw.moderate'),
                                    'active' => trans('sw.active'),
                                    'very_active' => trans('sw.very_active'),
                                    'low' => trans('sw.low'),
                                    'medium' => trans('sw.medium'),
                                    'high' => trans('sw.high'),
                                    'regular' => trans('sw.regular'),
                                    'vegetarian' => trans('sw.vegetarian'),
                                    'vegan' => trans('sw.vegan'),
                                    'keto' => trans('sw.keto'),
                                    'low_carb' => trans('sw.low_carb'),
                                ];
                            @endphp
                            
                            <div class="mb-3">
                                <div class="fw-bold text-gray-700 mb-3">{{ trans('sw.assessment_answers') }}:</div>
                                <div class="row g-3">
                                    @foreach((array)$log->details->answers as $key => $value)
                                    @if($value) {{-- Only show non-empty values --}}
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between p-3 bg-light rounded">
                                            <span class="text-gray-700 fw-semibold">
                                                {{ $fieldTranslations[$key] ?? ucfirst(str_replace('_', ' ', $key)) }}:
                                            </span>
                                            <span class="text-gray-900 fw-bold">
                                                @if(is_numeric($value))
                                                    {{ $value }}
                                                    @if(strpos($key, 'percentage') !== false) % @endif
                                                    @if(strpos($key, 'circumference') !== false || $key == 'height') cm @endif
                                                    @if($key == 'weight' || $key == 'target_weight' || strpos($key, 'mass') !== false) kg @endif
                                                    @if($key == 'water_intake') {{ trans('sw.liters') }} @endif
                                                @else
                                                    {{ $valueTranslations[$value] ?? $value }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                            @if($log->details->notes)
                            <div class="alert alert-light-info">
                                <i class="ki-outline ki-information-5 fs-5 me-2"></i>
                                <strong>{{ trans('sw.notes') }}:</strong> {{ $log->details->notes }}
                            </div>
                            @endif
                        @endif

                        {{-- Plan Details --}}
                        @if($log->training_type == 'plan')
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light-success rounded">
                                        <i class="{{ $log->details->type == 1 ? 'la la-dumbbell text-success' : 'la la-apple text-warning' }} fs-2x me-3"></i>
                                        <div>
                                            <div class="text-muted fs-7">{{ trans('sw.plan_title') }}</div>
                                            <div class="fw-bold text-gray-900">{{ $log->details->title }}</div>
                                            <div class="text-muted fs-8">{{ $log->details->type == 1 ? trans('sw.training_plan') : trans('sw.diet_plan') }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded">
                                        <div class="text-muted fs-7">{{ trans('sw.from') }}</div>
                                        <div class="fw-bold">{{ \Carbon\Carbon::parse($log->details->from_date)->format('Y-m-d') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 bg-light rounded">
                                        <div class="text-muted fs-7">{{ trans('sw.to') }}</div>
                                        <div class="fw-bold">{{ $log->details->to_date ? \Carbon\Carbon::parse($log->details->to_date)->format('Y-m-d') : '--' }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            @if(isset($log->details->assignment_weight) || isset($log->details->assignment_height))
                            <div class="row g-3 mb-3">
                                @if(isset($log->details->assignment_weight) && $log->details->assignment_weight > 0)
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded">
                                        <div class="text-muted fs-7">{{ trans('sw.weight_at_assignment') }}</div>
                                        <div class="fw-bold">{{ $log->details->assignment_weight }} kg</div>
                                    </div>
                                </div>
                                @endif
                                @if(isset($log->details->assignment_height) && $log->details->assignment_height > 0)
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded">
                                        <div class="text-muted fs-7">{{ trans('sw.height_at_assignment') }}</div>
                                        <div class="fw-bold">{{ $log->details->assignment_height }} cm</div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endif
                            
                            <div class="alert alert-light-success">
                                <strong>{{ trans('sw.description') }}:</strong> {{ $log->details->content }}
                            </div>
                            
                            {{-- Payment Information --}}
                            @php
                                // Try to get payment from plan details first, then from meta
                                $paymentData = null;
                                if (isset($log->details->amount_paid) && $log->details->amount_paid > 0) {
                                    $paymentData = $log->details;
                                } else {
                                    $meta = json_decode($log->meta, true);
                                    if (isset($meta['amount_paid']) && $meta['amount_paid'] > 0) {
                                        $paymentData = (object) $meta;
                                    }
                                }
                            @endphp
                            
                            @if($paymentData && isset($paymentData->amount_paid) && $paymentData->amount_paid > 0)
                            <div class="separator separator-dashed my-4"></div>
                            <h5 class="fw-bold mb-3 text-gray-700">
                                <i class="ki-outline ki-dollar fs-3 me-2 text-primary"></i>
                                {{ trans('sw.payment_details') }}
                            </h5>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <div class="p-3 bg-light-primary rounded">
                                        <div class="text-muted fs-7">{{ trans('sw.amount_paid') }}</div>
                                        <div class="fw-bold text-primary fs-3">{{ number_format($paymentData->amount_paid, 2) }} {{ trans('sw.currency') }}</div>
                                    </div>
                                </div>
                                @if(isset($paymentData->discount) && $paymentData->discount > 0)
                                <div class="col-md-4">
                                    <div class="p-3 bg-light-warning rounded">
                                        <div class="text-muted fs-7">{{ trans('sw.discount') }}</div>
                                        <div class="fw-bold text-warning">-{{ number_format($paymentData->discount, 2) }} {{ trans('sw.currency') }}</div>
                                    </div>
                                </div>
                                @endif
                                @if(isset($paymentData->vat) && $paymentData->vat > 0)
                                <div class="col-md-4">
                                    <div class="p-3 bg-light-info rounded">
                                        <div class="text-muted fs-7">{{ trans('sw.vat') }}</div>
                                        <div class="fw-bold text-gray-900">{{ number_format($paymentData->vat, 2) }} {{ trans('sw.currency') }}</div>
                                        @if(isset($paymentData->vat_percentage))
                                        <div class="text-muted fs-8">({{ $paymentData->vat_percentage }}%)</div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                @if(isset($paymentData->payment_type))
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded">
                                        <div class="text-muted fs-7">{{ trans('sw.payment_method') }}</div>
                                        <div class="fw-bold text-gray-900">
                                            @php
                                                // Try to get payment type name from database
                                                $paymentTypeName = '';
                                                $paymentTypeIcon = '';
                                                
                                                $paymentTypeRecord = \Modules\Software\Models\GymPaymentType::where('payment_id', $paymentData->payment_type)->first();
                                                if ($paymentTypeRecord) {
                                                    $paymentTypeName = $paymentTypeRecord->{'name_'.app()->getLocale()} ?? $paymentTypeRecord->name_en;
                                                } else {
                                                    // Fallback to default translations
                                                    if($paymentData->payment_type == 0) {
                                                        $paymentTypeName = trans('sw.payment_cash');
                                                        $paymentTypeIcon = '💵';
                                                    } elseif($paymentData->payment_type == 1) {
                                                        $paymentTypeName = trans('sw.payment_online');
                                                        $paymentTypeIcon = '💳';
                                                    } else {
                                                        $paymentTypeName = trans('sw.payment_bank_transfer');
                                                        $paymentTypeIcon = '🏦';
                                                    }
                                                }
                                            @endphp
                                            {{ $paymentTypeIcon }} {{ $paymentTypeName }}
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endif
                            
                            @if(isset($log->details->assignment_notes) && $log->details->assignment_notes)
                            <div class="alert alert-light-info mt-3">
                                <i class="ki-outline ki-information-5 fs-5 me-2"></i>
                                <strong>{{ trans('sw.assignment_notes') }}:</strong> {{ $log->details->assignment_notes }}
                            </div>
                            @endif
                        @endif

                        {{-- Medicine Details --}}
                        @if($log->training_type == 'medicine')
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <i class="ki-outline ki-pill fs-2x text-warning me-3"></i>
                                        <div>
                                            <div class="text-muted fs-7">{{ trans('sw.medicine_name') }}</div>
                                            <div class="fw-bold text-gray-900">{{ $log->details->{'name_'.app()->getLocale()} ?? $log->details->name_en }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if($log->details->dose_instructions)
                            <div class="alert alert-light-warning mb-3">
                                <i class="ki-outline ki-flask fs-5 me-2"></i>
                                <strong>{{ trans('sw.dosage_instructions') }}:</strong> {{ $log->details->dose_instructions }}
                            </div>
                            @endif
                            @if($log->details->log_notes)
                            <div class="alert alert-light-info">
                                <i class="ki-outline ki-information-5 fs-5 me-2"></i>
                                <strong>{{ trans('sw.notes') }}:</strong> {{ $log->details->log_notes }}
                            </div>
                            @endif
                        @endif

                        {{-- Track/Measurement Details --}}
                        @if($log->training_type == 'track' && isset($log->details->measurements))
                            {{-- Basic Measurements --}}
                            @if(isset($log->details->measurements['weight']) || isset($log->details->measurements['height']) || isset($log->details->measurements['bmi']))
                            <div class="mb-4">
                                <h5 class="fw-bold text-primary mb-3">
                                    <i class="ki-outline ki-weight fs-3 me-2"></i>
                                    {{ trans('sw.basic_measurements') }}
                                </h5>
                                <div class="row g-3">
                                    @if(isset($log->details->measurements['date']))
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light-primary rounded">
                                            <div class="text-muted fs-7">{{ trans('sw.measurement_date') }}</div>
                                            <div class="text-gray-900 fw-bold fs-4">{{ $log->details->measurements['date'] }}</div>
                                        </div>
                                    </div>
                                    @endif
                                    @if(isset($log->details->measurements['weight']))
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light-primary rounded">
                                            <div class="text-muted fs-7">{{ trans('sw.weight') }}</div>
                                            <div class="text-gray-900 fw-bold fs-4">{{ $log->details->measurements['weight'] }} kg</div>
                                        </div>
                                    </div>
                                    @endif
                                    @if(isset($log->details->measurements['height']))
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light-primary rounded">
                                            <div class="text-muted fs-7">{{ trans('sw.height') }}</div>
                                            <div class="text-gray-900 fw-bold fs-4">{{ $log->details->measurements['height'] }} cm</div>
                                        </div>
                                    </div>
                                    @endif
                                    @if(isset($log->details->measurements['bmi']))
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light-primary rounded">
                                            <div class="text-muted fs-7">{{ trans('sw.bmi') }}</div>
                                            <div class="text-gray-900 fw-bold fs-4">{{ $log->details->measurements['bmi'] }}</div>
                                            <div class="text-muted fs-8">
                                                @php
                                                    $bmi = $log->details->measurements['bmi'];
                                                    $category = '';
                                                    $color = '';
                                                    if ($bmi < 18.5) {
                                                        $category = trans('sw.bmi_underweight');
                                                        $color = 'warning';
                                                    } elseif ($bmi >= 18.5 && $bmi < 25) {
                                                        $category = trans('sw.bmi_normal');
                                                        $color = 'success';
                                                    } elseif ($bmi >= 25 && $bmi < 30) {
                                                        $category = trans('sw.bmi_overweight');
                                                        $color = 'warning';
                                                    } else {
                                                        $category = trans('sw.bmi_obese');
                                                        $color = 'danger';
                                                    }
                                                @endphp
                                                <span class="badge badge-light-{{ $color }}">{{ $category }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            {{-- Body Composition --}}
                            @if(isset($log->details->measurements['fat_percentage']) || isset($log->details->measurements['muscle_mass']))
                            <div class="mb-4">
                                <h5 class="fw-bold text-success mb-3">
                                    <i class="ki-outline ki-chart-simple-3 fs-3 me-2"></i>
                                    {{ trans('sw.body_composition') }}
                                </h5>
                                <div class="row g-3">
                                    @if(isset($log->details->measurements['fat_percentage']))
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light-success rounded">
                                            <div class="text-muted fs-7">{{ trans('sw.fat_percentage') }}</div>
                                            <div class="text-gray-900 fw-bold fs-4">{{ $log->details->measurements['fat_percentage'] }} %</div>
                                        </div>
                                    </div>
                                    @endif
                                    @if(isset($log->details->measurements['muscle_mass']))
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light-success rounded">
                                            <div class="text-muted fs-7">{{ trans('sw.muscle_mass') }}</div>
                                            <div class="text-gray-900 fw-bold fs-4">{{ $log->details->measurements['muscle_mass'] }} kg</div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            {{-- Body Circumferences --}}
                            @php
                                $hasCircumferences = false;
                                $circumferences = ['neck_circumference', 'chest_circumference', 'arm_circumference', 'abdominal_circumference', 'pelvic_circumference', 'thigh_circumference'];
                                foreach ($circumferences as $circ) {
                                    if (isset($log->details->measurements[$circ])) {
                                        $hasCircumferences = true;
                                        break;
                                    }
                                }
                            @endphp
                            @if($hasCircumferences)
                            <div class="mb-4">
                                <h5 class="fw-bold text-info mb-3">
                                    <i class="ki-outline ki-abstract-26 fs-3 me-2"></i>
                                    {{ trans('sw.body_circumferences') }}
                                </h5>
                                <div class="row g-3">
                                    @if(isset($log->details->measurements['neck_circumference']))
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light-info rounded">
                                            <div class="text-muted fs-7">{{ trans('sw.neck_circumference') }}</div>
                                            <div class="text-gray-900 fw-bold fs-5">{{ $log->details->measurements['neck_circumference'] }} cm</div>
                                        </div>
                                    </div>
                                    @endif
                                    @if(isset($log->details->measurements['chest_circumference']))
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light-info rounded">
                                            <div class="text-muted fs-7">{{ trans('sw.chest_circumference') }}</div>
                                            <div class="text-gray-900 fw-bold fs-5">{{ $log->details->measurements['chest_circumference'] }} cm</div>
                                        </div>
                                    </div>
                                    @endif
                                    @if(isset($log->details->measurements['arm_circumference']))
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light-info rounded">
                                            <div class="text-muted fs-7">{{ trans('sw.arm_circumference') }}</div>
                                            <div class="text-gray-900 fw-bold fs-5">{{ $log->details->measurements['arm_circumference'] }} cm</div>
                                        </div>
                                    </div>
                                    @endif
                                    @if(isset($log->details->measurements['abdominal_circumference']))
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light-info rounded">
                                            <div class="text-muted fs-7">{{ trans('sw.abdominal_circumference') }}</div>
                                            <div class="text-gray-900 fw-bold fs-5">{{ $log->details->measurements['abdominal_circumference'] }} cm</div>
                                        </div>
                                    </div>
                                    @endif
                                    @if(isset($log->details->measurements['pelvic_circumference']))
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light-info rounded">
                                            <div class="text-muted fs-7">{{ trans('sw.pelvic_circumference') }}</div>
                                            <div class="text-gray-900 fw-bold fs-5">{{ $log->details->measurements['pelvic_circumference'] }} cm</div>
                                        </div>
                                    </div>
                                    @endif
                                    @if(isset($log->details->measurements['thigh_circumference']))
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light-info rounded">
                                            <div class="text-muted fs-7">{{ trans('sw.thigh_circumference') }}</div>
                                            <div class="text-gray-900 fw-bold fs-5">{{ $log->details->measurements['thigh_circumference'] }} cm</div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            {{-- Notes --}}
                            @if($log->details->notes)
                            <div class="alert alert-light-info">
                                <i class="ki-outline ki-information-5 fs-5 me-2"></i>
                                <strong>{{ trans('sw.notes') }}:</strong> {{ $log->details->notes }}
                            </div>
                            @endif
                        @endif

                        {{-- File Details --}}
                        @if($log->training_type == 'file')
                            <div class="d-flex align-items-center p-4 bg-light rounded">
                                <i class="ki-outline ki-file fs-3x text-danger me-4"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-gray-900 mb-1">{{ $log->details->title ?? trans('sw.uploaded_file') }}</div>
                                    <div class="text-muted fs-7">{{ $log->details->file_path }}</div>
                                </div>
                                @if($log->details->file_path)
                                <a href="{{ asset('uploads/'.$log->details->file_path) }}" target="_blank" class="btn btn-sm btn-light-danger">
                                    <i class="ki-outline ki-down fs-5"></i> {{ trans('sw.download') }}
                                </a>
                                @endif
                            </div>
                        @endif

                        {{-- Note Details --}}
                        @if($log->training_type == 'note')
                            <div class="alert alert-light-dark">
                                <i class="ki-outline ki-note fs-2x me-3"></i>
                                <div class="fw-semibold">{{ $log->details->note_text }}</div>
                            </div>
                        @endif

                        {{-- AI Plan Details --}}
                        @if(($log->training_type == 'ai' || $log->training_type == 'ai_plan') && $log->details)
                            <div class="card bg-light-primary border-primary border-dashed mb-4">
                                <div class="card-header bg-primary">
                                    <h5 class="card-title text-white mb-0">
                                        <i class="ki-outline ki-robot fs-2 me-2"></i>
                                        {{ trans('sw.ai_generated_plan') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    {{-- Plan Type --}}
                                    @if(isset($log->details->type))
                                    <div class="mb-3">
                                        <span class="badge badge-lg {{ $log->details->type == 'training' ? 'badge-success' : 'badge-info' }}">
                                            <i class="ki-outline ki-{{ $log->details->type == 'training' ? 'barbell' : 'apple' }} fs-4 me-1"></i>
                                            {{ $log->details->type == 'training' ? trans('sw.training_plan') : trans('sw.diet_plan') }}
                                        </span>
                                    </div>
                                    @endif

                                    {{-- AI Response Data --}}
                                    @if(isset($log->details->ai_response))
                                        @php
                                            $aiResponse = is_array($log->details->ai_response) ? $log->details->ai_response : json_decode($log->details->ai_response, true);
                                        @endphp
                                        
                                        <div class="mb-4">
                                            {{-- Plan Summary --}}
                                            @if(isset($aiResponse['summary']) || isset($aiResponse['description']) || isset($aiResponse['title']))
                                            <div class="alert alert-light-primary border border-primary border-dashed mb-3">
                                                {{-- Plan Type Badge --}}
                                                @if(isset($aiResponse['plan_type']))
                                                <div class="mb-2">
                                                    <span class="badge badge-primary">
                                                        <i class="ki-outline ki-{{ $aiResponse['plan_type'] == 'training' ? 'barbell' : 'apple' }} me-1"></i>
                                                        {{ ucfirst($aiResponse['plan_type']) }} {{ trans('sw.plan') }}
                                                    </span>
                                                </div>
                                                @endif
                                                
                                                {{-- Title --}}
                                                @if(isset($aiResponse['title']))
                                                <h5 class="fw-bold text-dark mb-2">
                                                    <i class="ki-outline ki-note-2 fs-3 me-2 text-primary"></i>
                                                    {{ $aiResponse['title'] }}
                                                </h5>
                                                @endif
                                                
                                                {{-- Summary/Description --}}
                                                @if(isset($aiResponse['summary']))
                                                <div class="text-gray-700 mb-2">{{ $aiResponse['summary'] }}</div>
                                                @elseif(isset($aiResponse['description']))
                                                <div class="text-gray-700 mb-2">{{ $aiResponse['description'] }}</div>
                                                @endif
                                                
                                                {{-- Duration --}}
                                                @if(isset($aiResponse['duration']))
                                                <div class="mt-2">
                                                    <span class="badge badge-info">
                                                        <i class="ki-outline ki-calendar me-1"></i>
                                                        {{ $aiResponse['duration'] }} {{ trans('sw.days') }}
                                                    </span>
                                                </div>
                                                @endif
                                            </div>
                                            @endif

                                            {{-- Recommendations List (Simple Array) --}}
                                            @if(isset($aiResponse['recommendations']) && is_array($aiResponse['recommendations']) && count($aiResponse['recommendations']) > 0)
                                            <div class="mb-3">
                                                <h6 class="fw-bold text-gray-800 mb-3">
                                                    <i class="ki-outline ki-abstract-26 fs-4 me-2 text-success"></i>
                                                    {{ trans('sw.ai_recommendations') }}
                                                </h6>
                                                
                                                @foreach($aiResponse['recommendations'] as $index => $recommendation)
                                                    @if(is_string($recommendation))
                                                    <div class="d-flex align-items-start mb-2 p-3 bg-light rounded">
                                                        <span class="badge badge-circle badge-success me-3 mt-1">{{ $index + 1 }}</span>
                                                        <div class="text-gray-800">{{ $recommendation }}</div>
                                                    </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                            @endif

                                            {{-- Tasks List (Structured) --}}
                                            @if(isset($aiResponse['tasks']) && is_array($aiResponse['tasks']) && count($aiResponse['tasks']) > 0)
                                            <div>
                                                <h6 class="fw-bold text-gray-800 mb-3">
                                                    <i class="ki-outline ki-check-square fs-4 me-2 text-primary"></i>
                                                    {{ trans('sw.plan_tasks') }} ({{ count($aiResponse['tasks']) }})
                                                </h6>
                                                
                                                @foreach($aiResponse['tasks'] as $index => $task)
                                                <div class="d-flex align-items-start mb-2 p-3 bg-light rounded border border-gray-300">
                                                    <span class="badge badge-circle badge-primary me-3 mt-1">{{ $index + 1 }}</span>
                                                    <div class="flex-grow-1">
                                                        {{-- Task Title --}}
                                                        @if(isset($task['title']))
                                                        <div class="fw-semibold text-dark mb-1">{{ $task['title'] }}</div>
                                                        @endif
                                                        
                                                        {{-- Task Description --}}
                                                        @if(isset($task['description']))
                                                        <div class="text-gray-600 fs-7 mb-2">{{ $task['description'] }}</div>
                                                        @endif
                                                        
                                                        {{-- Training Details --}}
                                                        @if(isset($task['sets']) || isset($task['reps']) || isset($task['duration']))
                                                        <div class="d-flex flex-wrap gap-1">
                                                            @if(isset($task['sets']))
                                                            <span class="badge badge-success fs-8">{{ trans('sw.sets') }}: {{ $task['sets'] }}</span>
                                                            @endif
                                                            @if(isset($task['reps']))
                                                            <span class="badge badge-success fs-8">{{ trans('sw.reps') }}: {{ $task['reps'] }}</span>
                                                            @endif
                                                            @if(isset($task['duration']))
                                                            <span class="badge badge-success fs-8">{{ $task['duration'] }}</span>
                                                            @endif
                                                        </div>
                                                        @endif
                                                        
                                                        {{-- Nutrition Details --}}
                                                        @if(isset($task['calories']) || isset($task['protein']) || isset($task['carbs']) || isset($task['fats']))
                                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                                            @if(isset($task['calories']))
                                                            <span class="badge badge-info fs-8">{{ $task['calories'] }} cal</span>
                                                            @endif
                                                            @if(isset($task['protein']))
                                                            <span class="badge badge-info fs-8">P: {{ $task['protein'] }}g</span>
                                                            @endif
                                                            @if(isset($task['carbs']))
                                                            <span class="badge badge-info fs-8">C: {{ $task['carbs'] }}g</span>
                                                            @endif
                                                            @if(isset($task['fats']))
                                                            <span class="badge badge-info fs-8">F: {{ $task['fats'] }}g</span>
                                                            @endif
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                        
                                        {{-- Debug: Show raw ai_response structure --}}
                                        <div class="mt-3">
                                            <button class="btn btn-sm btn-light-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#ai_debug_{{ $log->id }}">
                                                <i class="ki-outline ki-code fs-6 me-1"></i>
                                                {{ trans('sw.view_raw_data') }}
                                            </button>
                                            <div class="collapse mt-2" id="ai_debug_{{ $log->id }}">
                                                <div class="card bg-light-secondary">
                                                    <div class="card-body">
                                                        <pre class="mb-0 text-gray-800" style="font-size: 11px; max-height: 400px; overflow-y: auto;">{{ json_encode($aiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Context Data --}}
                                    @if(isset($log->details->context_data) && !empty($log->details->context_data))
                                        @php
                                            $contextData = is_array($log->details->context_data) ? $log->details->context_data : json_decode($log->details->context_data, true);
                                        @endphp
                                        
                                        {{-- Member Assessment Data --}}
                                        @if(isset($contextData['assessment']) && !empty($contextData['assessment']))
                                        <div class="mt-4">
                                            <div class="card border border-gray-300">
                                                <div class="card-header bg-light-info">
                                                    <h6 class="card-title mb-0">
                                                        <i class="ki-outline ki-profile-circle fs-4 me-2"></i>
                                                        {{ trans('sw.member_assessment_data') }}
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row g-3">
                                                        @php
                                                            $assessment = $contextData['assessment'];
                                                            $displayFields = [
                                                                'primary_goal' => trans('sw.primary_goal'),
                                                                'age' => trans('sw.age'),
                                                                'gender' => trans('sw.gender'),
                                                                'weight' => trans('sw.weight') . ' (kg)',
                                                                'height' => trans('sw.height') . ' (cm)',
                                                                'bmi' => trans('sw.bmi'),
                                                                'target_weight' => trans('sw.target_weight') . ' (kg)',
                                                                'activity_level' => trans('sw.activity_level'),
                                                                'training_experience' => trans('sw.training_experience'),
                                                                'diet_type' => trans('sw.diet_type'),
                                                                'sleep_hours' => trans('sw.sleep_hours'),
                                                                'water_intake' => trans('sw.water_intake') . ' (L)',
                                                                'stress_level' => trans('sw.stress_level'),
                                                                'fat_percentage' => trans('sw.fat_percentage') . ' (%)',
                                                                'muscle_mass' => trans('sw.muscle_mass') . ' (kg)',
                                                                'chest_circumference' => trans('sw.chest_circumference') . ' (cm)',
                                                                'waist_circumference' => trans('sw.waist_circumference') . ' (cm)',
                                                                'arm_circumference' => trans('sw.arm_circumference') . ' (cm)',
                                                                'thigh_circumference' => trans('sw.thigh_circumference') . ' (cm)',
                                                                'diseases' => trans('sw.diseases'),
                                                                'injuries' => trans('sw.injuries'),
                                                                'allergies' => trans('sw.allergies'),
                                                                'medications' => trans('sw.medications'),
                                                            ];
                                                        @endphp
                                                        
                                                        @foreach($displayFields as $key => $label)
                                                            @if(isset($assessment[$key]) && $assessment[$key] !== null && $assessment[$key] !== '')
                                                            <div class="col-md-6 col-lg-4">
                                                                <div class="d-flex align-items-center p-3 bg-light rounded">
                                                                    <div>
                                                                        <div class="text-muted fs-7 fw-semibold">{{ $label }}</div>
                                                                        <div class="fs-6 fw-bold text-dark">{{ ucfirst($assessment[$key]) }}</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        
                                        {{-- Goal Data --}}
                                        @if(isset($contextData['goal']) && !empty($contextData['goal']) && $contextData['goal'] !== null)
                                        <div class="mt-3">
                                            <div class="alert alert-light-success d-flex align-items-center">
                                                <i class="ki-outline ki-flag fs-2x me-3 text-success"></i>
                                                <div>
                                                    <div class="fw-semibold text-gray-800">{{ trans('sw.plan_goal') }}</div>
                                                    <div class="text-gray-700">{{ $contextData['goal'] }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        
                                        {{-- Tracking History --}}
                                        @if(isset($contextData['tracking_history']) && !empty($contextData['tracking_history']))
                                        <div class="mt-3">
                                            <div class="card border border-gray-300">
                                                <div class="card-header bg-light-warning">
                                                    <h6 class="card-title mb-0">
                                                        <i class="ki-outline ki-chart-line fs-4 me-2"></i>
                                                        {{ trans('sw.tracking_history') }}
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="text-muted">{{ trans('sw.records_found') }}: <strong>{{ count($contextData['tracking_history']) }}</strong></div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        
                                        {{-- Medicines --}}
                                        @if(isset($contextData['medicines']) && !empty($contextData['medicines']))
                                        <div class="mt-3">
                                            <div class="card border border-gray-300">
                                                <div class="card-header bg-light-danger">
                                                    <h6 class="card-title mb-0">
                                                        <i class="ki-outline ki-pill fs-4 me-2"></i>
                                                        {{ trans('sw.medicines') }}
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="text-muted">{{ trans('sw.medicines_count') }}: <strong>{{ count($contextData['medicines']) }}</strong></div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        
                                        {{-- Previous Plans --}}
                                        @if(isset($contextData['previous_plans']) && !empty($contextData['previous_plans']))
                                        <div class="mt-3">
                                            <div class="card border border-gray-300">
                                                <div class="card-header bg-light-primary">
                                                    <h6 class="card-title mb-0">
                                                        <i class="ki-outline ki-notepad fs-4 me-2"></i>
                                                        {{ trans('sw.previous_plans') }}
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="text-muted">{{ trans('sw.plans_count') }}: <strong>{{ count($contextData['previous_plans']) }}</strong></div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        
                                        {{-- Custom Notes --}}
                                        @if(isset($contextData['custom_notes']) && !empty($contextData['custom_notes']))
                                        <div class="mt-3">
                                            <div class="alert alert-light-info d-flex align-items-start">
                                                <i class="ki-outline ki-notepad-edit fs-2x me-3 text-info"></i>
                                                <div>
                                                    <div class="fw-semibold text-gray-800">{{ trans('sw.custom_notes') }}</div>
                                                    <div class="text-gray-700 mt-2">{{ $contextData['custom_notes'] }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    @endif

                                    {{-- Status --}}
                                    @if(isset($log->details->status))
                                    <div class="mt-3">
                                        <span class="badge {{ $log->details->status == 'completed' ? 'badge-success' : ($log->details->status == 'pending' ? 'badge-warning' : 'badge-secondary') }}">
                                            {{ trans('sw.status') }}: {{ ucfirst($log->details->status) }}
                                        </span>
                                    </div>
                                    @endif

                                    {{-- Trainer Info --}}
                                    @if(isset($log->details->trainer))
                                    <div class="mt-3 pt-3 border-top">
                                        <small class="text-muted">
                                            <i class="ki-outline ki-user fs-5 me-1"></i>
                                            {{ trans('sw.generated_by') }}: {{ $log->details->trainer->name ?? trans('sw.system') }}
                                        </small>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-light-secondary">
                            <div class="text-muted fst-italic mb-2">{{ trans('sw.no_details_available') }}</div>
                            @if($log->meta)
                            <div class="text-muted fs-7">
                                <strong>{{ trans('sw.meta_data') }}:</strong>
                                <pre class="mb-0 mt-2 p-3 bg-light rounded">{{ is_string($log->meta) ? $log->meta : json_encode($log->meta, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                            @endif
                            @if($log->reference_id)
                            <div class="text-muted fs-7 mt-2">
                                <strong>{{ trans('sw.reference_id') }}:</strong> {{ $log->reference_id }}
                            </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-10">
            <div class="symbol symbol-100px mb-5">
                <div class="symbol-label fs-2x fw-semibold text-primary bg-light-primary">
                    <i class="ki-outline ki-information fs-2x"></i>
                </div>
            </div>
            <h4 class="text-gray-800 fw-bold">{{ trans('sw.no_activity_yet') }}</h4>
            <p class="text-muted">{{ trans('sw.no_activity_yet_desc') }}</p>
        </div>
        @endforelse

        @if($logs->hasPages())
        <div class="d-flex flex-stack flex-wrap pt-10">
            <div class="fs-6 fw-semibold text-gray-700">
                {{ trans('sw.showing_entries', [
                    'from' => $logs->firstItem() ?? 0,
                    'to' => $logs->lastItem() ?? 0,
                    'total' => $logs->total()
                ]) }}
            </div>
            <ul class="pagination">
                {!! $logs->render() !!}
            </ul>
        </div>
        @endif
    </div>
</div>

@include('software::Front.training_member_log_modals', [
    'member' => $member,
    'latestAssessment' => $latestAssessment,
    'allPlans' => $allPlans,
    'allMedicines' => $allMedicines
])

@endsection

@push('scripts')
<script>
    // Handle sweet flash message
    @if(session('sweet_flash_message'))
        @php
            $flash = session('sweet_flash_message');
        @endphp
        Swal.fire({
            title: '{{ $flash["title"] ?? trans("admin.done") }}',
            text: '{{ $flash["message"] ?? "" }}',
            icon: '{{ $flash["type"] ?? "success" }}',
            confirmButtonText: '{{ trans("admin.ok") }}',
            buttonsStyling: false,
            customClass: {
                confirmButton: "btn btn-primary"
            }
        });
    @endif
</script>
@endpush

