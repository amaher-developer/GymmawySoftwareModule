{{-- Assessment Modal --}}
<div class="modal fade" id="assessmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <form action="{{ route('sw.addTrainingAssessment', $member->id) }}" method="POST" id="assessmentForm">
                @csrf
                <div class="modal-header">
                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-1">{{ trans('sw.add_assessment') }}</h2>
                        <div class="text-muted fs-7">{{ trans('sw.complete_member_profile') }}</div>
                    </div>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                
                {{-- Progress Steps --}}
                <div class="modal-header border-0 pb-0">
                    <ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0 fw-bold w-100" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" data-bs-toggle="tab" href="#assess_basics" role="tab" aria-selected="true">
                                <span class="badge badge-circle badge-primary me-2">1</span>
                                {{ trans('sw.basics') }}
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#assess_measurements" role="tab" aria-selected="false">
                                <span class="badge badge-circle badge-secondary me-2">2</span>
                                {{ trans('sw.measurements') }}
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#assess_goals" role="tab" aria-selected="false">
                                <span class="badge badge-circle badge-secondary me-2">3</span>
                                {{ trans('sw.goals') }}
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#assess_health" role="tab" aria-selected="false">
                                <span class="badge badge-circle badge-secondary me-2">4</span>
                                {{ trans('sw.health') }}
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#assess_lifestyle" role="tab" aria-selected="false">
                                <span class="badge badge-circle badge-secondary me-2">5</span>
                                {{ trans('sw.lifestyle') }}
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="modal-body scroll-y mx-5 mx-xl-10 my-5">
                    <div class="tab-content" id="assessmentTabContent">
                        
                        {{-- Tab 1: Basics --}}
                        <div class="tab-pane fade show active" id="assess_basics" role="tabpanel">
                            <div class="text-center mb-7">
                                <i class="ki-outline ki-user fs-3x text-primary mb-3"></i>
                                <h3 class="fw-bold">{{ trans('sw.basic_info') }}</h3>
                                <p class="text-muted">{{ trans('sw.basic_info_desc') }}</p>
                            </div>

                            <div class="row g-5">
                                <div class="col-md-3">
                                    <label class="form-label">{{ trans('sw.age') }}</label>
                                    <input type="number" name="answers[age]" class="form-control form-control-lg" placeholder="25" />
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ trans('sw.gender') }}</label>
                                    <select name="answers[gender]" class="form-select form-select-lg">
                                        <option value="">{{ trans('sw.select') }}</option>
                                        <option value="male">{{ trans('sw.male') }}</option>
                                        <option value="female">{{ trans('sw.female') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label required">{{ trans('sw.weight') }} (kg)</label>
                                    <input type="number" step="0.1" name="answers[weight]" id="assess_weight" class="form-control form-control-lg" placeholder="75" required />
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label required">{{ trans('sw.height') }} (cm)</label>
                                    <input type="number" step="0.1" name="answers[height]" id="assess_height" class="form-control form-control-lg" placeholder="175" required />
                                </div>
                            </div>

                            {{-- BMI Display --}}
                            <div class="mt-7 p-5 bg-light-primary rounded text-center">
                                <div class="mb-2 text-muted">{{ trans('sw.your_bmi') }}</div>
                                <div class="fs-2hx fw-bold text-primary" id="assess_bmi_display">--</div>
                                <div id="assess_bmi_category" class="fs-4 fw-semibold"></div>
                                <input type="hidden" name="answers[bmi]" id="assess_bmi" />
                            </div>
                        </div>

                        {{-- Tab 2: Measurements --}}
                        <div class="tab-pane fade" id="assess_measurements" role="tabpanel">
                            <div class="text-center mb-7">
                                <i class="ki-outline ki-abstract-26 fs-3x text-success mb-3"></i>
                                <h3 class="fw-bold">{{ trans('sw.body_measurements') }}</h3>
                                <p class="text-muted">{{ trans('sw.measurements_desc') }}</p>
                            </div>

                            <div class="row g-5">
                                <div class="col-md-3">
                                    <label class="form-label">{{ trans('sw.fat_percentage') }}</label>
                                    <div class="input-group input-group-lg">
                                        <input type="number" step="0.1" name="answers[fat_percentage]" class="form-control" placeholder="15" />
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ trans('sw.muscle_mass') }}</label>
                                    <div class="input-group input-group-lg">
                                        <input type="number" step="0.1" name="answers[muscle_mass]" class="form-control" placeholder="55" />
                                        <span class="input-group-text">kg</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ trans('sw.waist_circumference') }}</label>
                                    <div class="input-group input-group-lg">
                                        <input type="number" step="0.1" name="answers[waist_circumference]" class="form-control" placeholder="80" />
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ trans('sw.chest_circumference') }}</label>
                                    <div class="input-group input-group-lg">
                                        <input type="number" step="0.1" name="answers[chest_circumference]" class="form-control" placeholder="95" />
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ trans('sw.arm_circumference') }}</label>
                                    <div class="input-group input-group-lg">
                                        <input type="number" step="0.1" name="answers[arm_circumference]" class="form-control" placeholder="35" />
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ trans('sw.thigh_circumference') }}</label>
                                    <div class="input-group input-group-lg">
                                        <input type="number" step="0.1" name="answers[thigh_circumference]" class="form-control" placeholder="55" />
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-light-info mt-5">
                                <i class="ki-outline ki-information fs-5 me-2"></i>
                                {{ trans('sw.measurements_optional') }}
                            </div>
                        </div>

                        {{-- Tab 3: Goals --}}
                        <div class="tab-pane fade" id="assess_goals" role="tabpanel">
                            <div class="text-center mb-7">
                                <i class="ki-outline ki-trophy fs-3x text-warning mb-3"></i>
                                <h3 class="fw-bold">{{ trans('sw.fitness_goals') }}</h3>
                                <p class="text-muted">{{ trans('sw.goals_desc') }}</p>
                            </div>

                            <div class="row g-5">
                                <div class="col-md-6">
                                    <label class="form-label required fs-5">{{ trans('sw.primary_goal') }}</label>
                                    <select name="answers[primary_goal]" class="form-select form-select-lg" required>
                                        <option value="">{{ trans('sw.select_goal') }}</option>
                                        <option value="weight_loss">🔥 {{ trans('sw.weight_loss') }}</option>
                                        <option value="muscle_gain">💪 {{ trans('sw.muscle_gain') }}</option>
                                        <option value="body_toning">✨ {{ trans('sw.body_toning') }}</option>
                                        <option value="strength_increase">⚡ {{ trans('sw.strength_increase') }}</option>
                                        <option value="endurance_improvement">🏃 {{ trans('sw.endurance_improvement') }}</option>
                                        <option value="flexibility">🧘 {{ trans('sw.flexibility') }}</option>
                                        <option value="general_fitness">🎯 {{ trans('sw.general_fitness') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fs-5">{{ trans('sw.target_weight') }}</label>
                                    <div class="input-group input-group-lg">
                                        <input type="number" step="0.1" name="answers[target_weight]" class="form-control" placeholder="70" />
                                        <span class="input-group-text">kg</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fs-5">{{ trans('sw.target_date') }}</label>
                                    <input type="date" name="answers[target_date]" class="form-control form-control-lg" />
                                </div>
                                <div class="col-12">
                                    <label class="form-label fs-5">{{ trans('sw.goal_details') }}</label>
                                    <textarea name="answers[goals]" class="form-control form-control-lg" rows="4" placeholder="{{ trans('sw.goal_details_placeholder') }}"></textarea>
                                </div>
                            </div>

                            {{-- Training Experience --}}
                            <div class="mt-7">
                                <label class="form-label fs-5">{{ trans('sw.training_experience') }}</label>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <input type="radio" class="btn-check" name="answers[training_experience]" value="beginner" id="exp_beginner" />
                                        <label class="btn btn-outline btn-outline-dashed btn-active-light-primary p-5 d-flex align-items-center" for="exp_beginner">
                                            <span class="badge badge-circle badge-light-primary me-3 fs-1">1</span>
                                            <div class="text-start">
                                                <div class="fw-bold">{{ trans('sw.beginner') }}</div>
                                                <div class="fs-7 text-muted">{{ trans('sw.less_than_year') }}</div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="radio" class="btn-check" name="answers[training_experience]" value="intermediate" id="exp_intermediate" checked />
                                        <label class="btn btn-outline btn-outline-dashed btn-active-light-primary p-5 d-flex align-items-center" for="exp_intermediate">
                                            <span class="badge badge-circle badge-light-primary me-3 fs-1">2</span>
                                            <div class="text-start">
                                                <div class="fw-bold">{{ trans('sw.intermediate') }}</div>
                                                <div class="fs-7 text-muted">{{ trans('sw.one_to_three_years') }}</div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="radio" class="btn-check" name="answers[training_experience]" value="advanced" id="exp_advanced" />
                                        <label class="btn btn-outline btn-outline-dashed btn-active-light-primary p-5 d-flex align-items-center" for="exp_advanced">
                                            <span class="badge badge-circle badge-light-primary me-3 fs-1">3</span>
                                            <div class="text-start">
                                                <div class="fw-bold">{{ trans('sw.advanced') }}</div>
                                                <div class="fs-7 text-muted">{{ trans('sw.three_plus_years') }}</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tab 4: Health --}}
                        <div class="tab-pane fade" id="assess_health" role="tabpanel">
                            <div class="text-center mb-7">
                                <i class="ki-outline ki-cross-circle fs-3x text-danger mb-3"></i>
                                <h3 class="fw-bold">{{ trans('sw.health_info') }}</h3>
                                <p class="text-muted">{{ trans('sw.health_desc') }}</p>
                            </div>

                            <div class="row g-5">
                                <div class="col-md-6">
                                    <label class="form-label fs-5">
                                        <i class="ki-outline ki-bandage text-danger me-2"></i>
                                        {{ trans('sw.injuries') }}
                                    </label>
                                    <textarea name="answers[injuries]" class="form-control form-control-lg" rows="3" placeholder="{{ trans('sw.injuries_placeholder') }}"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-5">
                                        <i class="ki-outline ki-medical-heart text-danger me-2"></i>
                                        {{ trans('sw.diseases') }}
                                    </label>
                                    <textarea name="answers[diseases]" class="form-control form-control-lg" rows="3" placeholder="{{ trans('sw.diseases_placeholder') }}"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-5">
                                        <i class="ki-outline ki-pill text-danger me-2"></i>
                                        {{ trans('sw.medications') }}
                                    </label>
                                    <textarea name="answers[medications]" class="form-control form-control-lg" rows="3" placeholder="{{ trans('sw.medications_placeholder') }}"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-5">
                                        <i class="ki-outline ki-shield-cross text-danger me-2"></i>
                                        {{ trans('sw.allergies') }}
                                    </label>
                                    <textarea name="answers[allergies]" class="form-control form-control-lg" rows="3" placeholder="{{ trans('sw.allergies_placeholder') }}"></textarea>
                                </div>
                            </div>

                            <div class="alert alert-light-warning mt-5">
                                <i class="ki-outline ki-information-5 fs-3 me-2"></i>
                                <strong>{{ trans('sw.important') }}:</strong> {{ trans('sw.health_info_important') }}
                            </div>
                        </div>

                        {{-- Tab 5: Lifestyle --}}
                        <div class="tab-pane fade" id="assess_lifestyle" role="tabpanel">
                            <div class="text-center mb-7">
                                <i class="ki-outline ki-profile-user fs-3x text-info mb-3"></i>
                                <h3 class="fw-bold">{{ trans('sw.lifestyle') }}</h3>
                                <p class="text-muted">{{ trans('sw.lifestyle_desc') }}</p>
                            </div>

                            <div class="row g-5">
                                <div class="col-md-4">
                                    <label class="form-label fs-5">{{ trans('sw.activity_level') }}</label>
                                    <select name="answers[activity_level]" class="form-select form-select-lg">
                                        <option value="sedentary">{{ trans('sw.sedentary') }}</option>
                                        <option value="light" selected>{{ trans('sw.light') }}</option>
                                        <option value="moderate">{{ trans('sw.moderate') }}</option>
                                        <option value="active">{{ trans('sw.active') }}</option>
                                        <option value="very_active">{{ trans('sw.very_active') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-5">{{ trans('sw.sleep_hours') }}</label>
                                    <div class="input-group input-group-lg">
                                        <input type="number" step="0.5" name="answers[sleep_hours]" class="form-control" placeholder="7-8" />
                                        <span class="input-group-text">{{ trans('sw.hours') }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-5">{{ trans('sw.stress_level') }}</label>
                                    <select name="answers[stress_level]" class="form-select form-select-lg">
                                        <option value="low">😊 {{ trans('sw.low') }}</option>
                                        <option value="medium">😐 {{ trans('sw.medium') }}</option>
                                        <option value="high">😰 {{ trans('sw.high') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-5">{{ trans('sw.diet_type') }}</label>
                                    <select name="answers[diet_type]" class="form-select form-select-lg">
                                        <option value="regular">{{ trans('sw.regular') }}</option>
                                        <option value="vegetarian">🥗 {{ trans('sw.vegetarian') }}</option>
                                        <option value="vegan">🌱 {{ trans('sw.vegan') }}</option>
                                        <option value="keto">🥩 {{ trans('sw.keto') }}</option>
                                        <option value="low_carb">🍖 {{ trans('sw.low_carb') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-5">{{ trans('sw.water_intake') }}</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text">💧</span>
                                        <input type="number" step="0.5" name="answers[water_intake]" class="form-control" placeholder="2-3" />
                                        <span class="input-group-text">{{ trans('sw.liters') }}</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fs-5">{{ trans('sw.additional_notes') }}</label>
                                    <textarea name="notes" class="form-control form-control-lg" rows="3" placeholder="{{ trans('sw.additional_notes_placeholder') }}"></textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-lg" data-bs-dismiss="modal">
                        {{ trans('sw.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="ki-outline ki-check fs-2"></i> 
                        {{ trans('sw.save_assessment') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- BMI Auto-Calculation for Assessment --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const weightInput = document.getElementById('assess_weight');
    const heightInput = document.getElementById('assess_height');
    const bmiInput = document.getElementById('assess_bmi');
    const bmiDisplay = document.getElementById('assess_bmi_display');
    const bmiCategory = document.getElementById('assess_bmi_category');

    function calculateAssessmentBMI() {
        const weight = parseFloat(weightInput.value);
        const height = parseFloat(heightInput.value);

        if (weight && height && height > 0) {
            const heightInMeters = height / 100;
            const bmi = weight / (heightInMeters * heightInMeters);
            const bmiValue = bmi.toFixed(1);
            
            bmiInput.value = bmiValue;
            bmiDisplay.textContent = bmiValue;

            let category = '';
            let categoryClass = '';
            let emoji = '';
            
            if (bmi < 18.5) {
                category = '{{ trans("sw.bmi_underweight") }}';
                categoryClass = 'text-warning';
                emoji = '⚠️';
            } else if (bmi >= 18.5 && bmi < 25) {
                category = '{{ trans("sw.bmi_normal") }}';
                categoryClass = 'text-success';
                emoji = '✅';
            } else if (bmi >= 25 && bmi < 30) {
                category = '{{ trans("sw.bmi_overweight") }}';
                categoryClass = 'text-warning';
                emoji = '⚠️';
            } else {
                category = '{{ trans("sw.bmi_obese") }}';
                categoryClass = 'text-danger';
                emoji = '🔴';
            }

            bmiCategory.innerHTML = `<span class="${categoryClass}">${emoji} ${category}</span>`;
        } else {
            bmiInput.value = '';
            bmiDisplay.textContent = '--';
            bmiCategory.innerHTML = '';
        }
    }

    if (weightInput && heightInput) {
        weightInput.addEventListener('input', calculateAssessmentBMI);
        heightInput.addEventListener('input', calculateAssessmentBMI);
    }
    
    // Tab completion indicators
    const tabs = document.querySelectorAll('[data-bs-toggle="tab"]');
    tabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (event) {
            // Update badge colors
            const currentBadge = event.target.querySelector('.badge');
            if (currentBadge) {
                // Remove primary from all
                document.querySelectorAll('.nav-link .badge').forEach(b => {
                    b.classList.remove('badge-primary');
                    b.classList.add('badge-secondary');
                });
                // Add primary to current
                currentBadge.classList.remove('badge-secondary');
                currentBadge.classList.add('badge-primary');
            }
        });
    });
});
</script>

@php
    // Check if training AI feature is enabled (check once at the top if not already defined)
    if (!isset($active_training_ai)) {
        $features = is_array($mainSettings->features ?? null) 
            ? $mainSettings->features 
            : (is_string($mainSettings->features ?? null) 
                ? json_decode($mainSettings->features, true) 
                : []);
        $active_training_ai = isset($features['active_training_ai']) && $features['active_training_ai'];
    }
@endphp

{{-- Plan Modal --}}
<div class="modal fade" id="planModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('sw.addMemberTrainingPlan', $member->id) }}" method="POST" id="planAssignForm">
                @csrf
                <div class="modal-header">
                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-1">{{ trans('sw.assign_plan') }}</h2>
                        <div class="text-muted fs-7">{{ trans('sw.plan_info') }}</div>
                    </div>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    {{-- Plan Selection --}}
                    <div class="mb-7">
                        <h4 class="fw-bold mb-4 text-gray-800">
                            <i class="ki-outline ki-notepad fs-2 me-2 text-success"></i>
                            {{ trans('sw.plan_selection') }}
                        </h4>
                        <div class="mb-5">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label required mb-0">{{ trans('sw.select_plan') }}</label>
                                @if($active_training_ai)
                                <button type="button" class="btn btn-sm btn-light-primary" onclick="openAiPlanGenerator()">
                                    <i class="ki-outline ki-abstract-26 fs-4 me-1"></i>
                                    {{ trans('sw.generate_with_ai') }}
                                </button>
                                @endif
                            </div>
                            <select name="plan_id" id="plan_id_select" class="form-select form-select-lg" required>
                                <option value="">-- {{ trans('sw.choose') }} --</option>
                                @foreach($allPlans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->title }} ({{ $plan->type == 1 ? trans('sw.training') : trans('sw.diet') }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ trans('sw.start_date') }}</label>
                                <input type="date" name="from_date" class="form-control" value="{{ date('Y-m-d') }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ trans('sw.end_date') }}</label>
                                <input type="date" name="to_date" class="form-control" />
                            </div>
                        </div>
                    </div>

                    {{-- Payment Information (Optional) --}}
                    <div class="separator separator-dashed my-7"></div>
                    <div class="mb-7">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h4 class="fw-bold text-gray-800 mb-0">
                                <i class="ki-outline ki-dollar fs-2 me-2 text-primary"></i>
                                {{ trans('sw.payment_information') }}
                                <span class="badge badge-light-primary ms-2">{{ trans('sw.optional') }}</span>
                            </h4>
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="enable_payment" />
                                <label class="form-check-label fw-semibold text-gray-700" for="enable_payment">
                                    {{ trans('sw.enable_payment') }}
                                </label>
                            </div>
                        </div>

                        <div id="payment_section" style="display: none;">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label required">{{ trans('sw.amount_paid') }}</label>
                                    <div class="input-group input-group-lg">
                                        <input type="number" step="0.01" name="amount_paid" id="plan_amount_paid" class="form-control" placeholder="500.00" />
                                        <span class="input-group-text">{{ trans('sw.currency') }}</span>
                                    </div>
                                    <div class="form-text">{{ trans('sw.amount_paid_hint') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">{{ trans('sw.payment_type') }}</label>
                                    <select name="payment_type" class="form-select form-select-lg">
                                        @if(isset($paymentTypes) && $paymentTypes->count() > 0)
                                            @foreach($paymentTypes as $paymentType)
                                            <option value="{{ $paymentType->payment_id }}">
                                                {{ $paymentType->{'name_'.app()->getLocale()} ?? $paymentType->name_en }}
                                            </option>
                                            @endforeach
                                        @else
                                            <option value="0">💵 {{ trans('sw.payment_cash') }}</option>
                                            <option value="1">💳 {{ trans('sw.payment_online') }}</option>
                                            <option value="2">🏦 {{ trans('sw.payment_bank_transfer') }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="row g-4 mt-4">
                                <div class="col-md-6">
                                    <label class="form-label">{{ trans('sw.discount') }}</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" name="discount" id="plan_discount" class="form-control" placeholder="0.00" value="0" />
                                        <span class="input-group-text">{{ trans('sw.currency') }}</span>
                                    </div>
                                    <div class="form-text">{{ trans('sw.discount_hint') }}</div>
                                </div>
                                @if(isset($vatPercentage) && $vatPercentage > 0)
                                <div class="col-md-6">
                                    <div class="alert alert-light-success mb-0 p-4">
                                        <div class="d-flex align-items-center">
                                            <i class="ki-outline ki-check-circle fs-2x text-success me-3"></i>
                                            <div>
                                                <div class="fw-bold text-gray-900">{{ trans('sw.vat_applied') }}</div>
                                                <div class="text-muted fs-7">{{ trans('sw.vat_percentage_value', ['value' => $vatPercentage]) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="vat_percentage" id="plan_vat_percent" value="{{ $vatPercentage }}" />
                                </div>
                                @else
                                <input type="hidden" name="vat_percentage" id="plan_vat_percent" value="0" />
                                @endif
                            </div>

                            {{-- Payment Summary --}}
                            <div class="mt-5 p-5 bg-light-primary rounded" id="payment_summary" style="display: none;">
                                <h5 class="fw-bold text-gray-800 mb-4">
                                    <i class="ki-outline ki-calculator fs-3 me-2"></i>
                                    {{ trans('sw.payment_summary') }}
                                </h5>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-gray-700">{{ trans('sw.amount') }}:</span>
                                    <span class="fw-bold fs-5" id="summary_amount">0.00 {{ trans('sw.currency') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3" id="summary_discount_row" style="display: none;">
                                    <span class="text-gray-700">{{ trans('sw.discount') }}:</span>
                                    <span class="fw-bold text-warning fs-5" id="summary_discount">- 0.00 {{ trans('sw.currency') }}</span>
                                </div>
                                <div class="separator separator-dashed my-3"></div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-gray-700 fw-bold">{{ trans('sw.subtotal') }}:</span>
                                    <span class="fw-bold text-dark fs-4" id="summary_subtotal">0.00 {{ trans('sw.currency') }}</span>
                                </div>
                                @if(isset($vatPercentage) && $vatPercentage > 0)
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-gray-700">{{ trans('sw.vat') }} ({{ $vatPercentage }}%):</span>
                                    <span class="fw-bold text-info fs-5" id="summary_vat">+ 0.00 {{ trans('sw.currency') }}</span>
                                </div>
                                <div class="separator separator-dashed my-3"></div>
                                @endif
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-gray-900 fw-bold fs-3">{{ trans('sw.total') }}:</span>
                                    <span class="fw-bold text-success fs-2" id="summary_total">0.00 {{ trans('sw.currency') }}</span>
                                </div>
                            </div>

                            <input type="hidden" name="price" id="plan_price" />
                            <input type="hidden" name="vat" id="plan_vat_amount" />
                            <input type="hidden" name="total" id="plan_total" />

                            <div class="alert alert-light-info mt-5">
                                <i class="ki-outline ki-information-5 fs-3 me-2"></i>
                                {{ trans('sw.plan_payment_note') }}
                            </div>
                        </div>

                        <div id="payment_skip_notice" class="alert alert-light-warning">
                            <i class="ki-outline ki-information fs-3 me-2"></i>
                            {{ trans('sw.payment_skip_notice') }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-lg" data-bs-dismiss="modal">{{ trans('sw.cancel') }}</button>
                    <button type="submit" class="btn btn-success btn-lg" id="plan_submit_btn">
                        <i class="ki-outline ki-check fs-2"></i> 
                        <span id="submit_text">{{ trans('sw.assign') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Plan Payment & Toggle Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const enablePaymentCheckbox = document.getElementById('enable_payment');
    const paymentSection = document.getElementById('payment_section');
    const paymentSkipNotice = document.getElementById('payment_skip_notice');
    const submitBtn = document.getElementById('plan_submit_btn');
    const submitText = document.getElementById('submit_text');
    
    const amountPaidInput = document.getElementById('plan_amount_paid');
    const discountInput = document.getElementById('plan_discount');
    const vatPercentInput = document.getElementById('plan_vat_percent');
    const priceInput = document.getElementById('plan_price');
    const vatAmountInput = document.getElementById('plan_vat_amount');
    const totalInput = document.getElementById('plan_total');

    // Toggle payment section
    if (enablePaymentCheckbox) {
        enablePaymentCheckbox.addEventListener('change', function() {
            if (this.checked) {
                paymentSection.style.display = 'block';
                paymentSkipNotice.style.display = 'none';
                submitBtn.classList.remove('btn-success');
                submitBtn.classList.add('btn-primary');
                submitText.textContent = '{{ trans("sw.assign_and_pay") }}';
            } else {
                paymentSection.style.display = 'none';
                paymentSkipNotice.style.display = 'block';
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-success');
                submitText.textContent = '{{ trans("sw.assign") }}';
                // Clear payment fields
                if (amountPaidInput) amountPaidInput.value = '';
                if (discountInput) discountInput.value = '0';
            }
        });
    }

    // Payment calculation with summary display
    function calculatePayment() {
        if (!enablePaymentCheckbox || !enablePaymentCheckbox.checked) return;
        
        const amountPaid = parseFloat(amountPaidInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;
        const vatPercent = parseFloat(vatPercentInput.value) || 0;

        // Show summary if amount is entered
        const paymentSummary = document.getElementById('payment_summary');
        if (amountPaid > 0 && paymentSummary) {
            paymentSummary.style.display = 'block';
            
            // Update summary display
            document.getElementById('summary_amount').textContent = amountPaid.toFixed(2) + ' {{ trans("sw.currency") }}';
            
            // Show/hide discount row
            const discountRow = document.getElementById('summary_discount_row');
            if (discount > 0) {
                discountRow.style.display = 'flex';
                document.getElementById('summary_discount').textContent = '- ' + discount.toFixed(2) + ' {{ trans("sw.currency") }}';
            } else {
                discountRow.style.display = 'none';
            }
            
            // Calculate subtotal after discount
            const subtotal = amountPaid - discount;
            document.getElementById('summary_subtotal').textContent = subtotal.toFixed(2) + ' {{ trans("sw.currency") }}';
            
            // Calculate VAT if system has it configured
            let vatAmount = 0;
            if (vatPercent > 0) {
                vatAmount = (subtotal * vatPercent) / 100;
                const summaryVat = document.getElementById('summary_vat');
                if (summaryVat) {
                    summaryVat.textContent = '+ ' + vatAmount.toFixed(2) + ' {{ trans("sw.currency") }}';
                }
            }
            
            // Calculate final total
            const total = subtotal + vatAmount;
            document.getElementById('summary_total').textContent = total.toFixed(2) + ' {{ trans("sw.currency") }}';
            
            // Update hidden fields
            priceInput.value = amountPaid;
            vatAmountInput.value = vatAmount.toFixed(2);
            totalInput.value = total.toFixed(2);
        } else if (paymentSummary) {
            paymentSummary.style.display = 'none';
        }
    }

    if (amountPaidInput) {
        amountPaidInput.addEventListener('input', calculatePayment);
        discountInput.addEventListener('input', calculatePayment);
    }
});
</script>

{{-- Medicine Modal --}}
<div class="modal fade" id="medicineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('sw.addMemberTrainingMedicine', $member->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h2 class="fw-bold">{{ trans('sw.add_medicine') }}</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <div class="alert alert-info d-flex align-items-center mb-7">
                        <i class="ki-outline ki-information-5 fs-2x me-3"></i>
                        <div class="flex-grow-1 fs-7">
                            {{ trans('sw.medicine_assignment_note') }}
                        </div>
                    </div>

                    <div class="mb-7">
                        <label class="form-label required">{{ trans('sw.select_medicine') }}</label>
                        <select name="medicine_id" class="form-select form-select-lg" required>
                            <option value="">-- {{ trans('sw.choose_medicine') }} --</option>
                            @foreach($allMedicines as $medicine)
                            <option value="{{ $medicine->id }}">
                                {{ $medicine->{'name_'.app()->getLocale()} ?? $medicine->name_en }}
                            </option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            <i class="ki-outline ki-information-5 fs-6"></i>
                            {{ trans('sw.select_medicine_hint') }}
                        </div>
                    </div>

                    <div class="mb-7">
                        <label class="form-label required">{{ trans('sw.dosage_instructions') }}</label>
                        <textarea name="dose" class="form-control form-control-lg" rows="3" 
                                  placeholder="{{ trans('sw.dosage_placeholder') }}" required></textarea>
                        <div class="form-text">
                            <i class="ki-outline ki-information-5 fs-6"></i>
                            {{ trans('sw.dosage_instructions_hint') }}
                        </div>
                        <div class="alert alert-light-primary d-flex align-items-center mt-3 p-3">
                            <i class="ki-outline ki-notepad fs-2x me-3 text-primary"></i>
                            <div class="flex-grow-1 fs-7">
                                <strong>{{ trans('sw.examples') }}:</strong> 500mg {{ trans('sw.twice_daily') }}, 1 {{ trans('sw.tablet') }} {{ trans('sw.before_workout') }}, 2 {{ trans('sw.capsules') }} {{ trans('sw.after_meals') }}
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label">{{ trans('sw.additional_notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2" 
                                  placeholder="{{ trans('sw.additional_notes_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('sw.cancel') }}</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="ki-outline ki-check fs-2"></i> {{ trans('sw.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Track Modal --}}
<div class="modal fade" id="trackModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('sw.addMemberTrainingTrack', $member->id) }}" method="POST" id="trackForm">
                @csrf
                <div class="modal-header">
                    <h2 class="fw-bold">{{ trans('sw.add_track') }}</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    {{-- Date --}}
                    <div class="mb-5">
                        <label class="form-label required">{{ trans('sw.measurement_date') }}</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required />
                    </div>

                    {{-- Basic Measurements --}}
                    <div class="separator separator-dashed my-5"></div>
                    <h4 class="fw-bold mb-4 text-gray-800">
                        <i class="ki-outline ki-weight fs-2 me-2 text-primary"></i>
                        {{ trans('sw.basic_measurements') }}
                    </h4>
                    <div class="row g-4 mb-5">
                        <div class="col-md-4">
                            <label class="form-label">{{ trans('sw.weight') }} (kg)</label>
                            <input type="number" step="0.1" name="weight" id="track_weight" class="form-control" placeholder="70.5" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ trans('sw.height') }} (cm)</label>
                            <input type="number" step="0.1" name="height" id="track_height" class="form-control" placeholder="175" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ trans('sw.bmi') }}</label>
                            <input type="number" step="0.01" name="bmi" id="track_bmi" class="form-control bg-light" readonly placeholder="Auto" />
                            <div id="bmi_category" class="form-text"></div>
                        </div>
                    </div>

                    {{-- Body Composition --}}
                    <div class="separator separator-dashed my-5"></div>
                    <h4 class="fw-bold mb-4 text-gray-800">
                        <i class="ki-outline ki-chart-simple-3 fs-2 me-2 text-success"></i>
                        {{ trans('sw.body_composition') }}
                    </h4>
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label class="form-label">{{ trans('sw.fat_percentage') }} (%)</label>
                            <input type="number" step="0.1" name="fat_percentage" class="form-control" placeholder="15.5" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ trans('sw.muscle_mass') }} (kg)</label>
                            <input type="number" step="0.1" name="muscle_mass" class="form-control" placeholder="55.2" />
                        </div>
                    </div>

                    {{-- Body Circumferences --}}
                    <div class="separator separator-dashed my-5"></div>
                    <h4 class="fw-bold mb-4 text-gray-800">
                        <i class="ki-outline ki-abstract-26 fs-2 me-2 text-info"></i>
                        {{ trans('sw.body_circumferences') }}
                    </h4>
                    <div class="row g-4 mb-5">
                        <div class="col-md-4">
                            <label class="form-label">{{ trans('sw.neck_circumference') }} (cm)</label>
                            <input type="number" step="0.1" name="neck_circumference" class="form-control" placeholder="38" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ trans('sw.chest_circumference') }} (cm)</label>
                            <input type="number" step="0.1" name="chest_circumference" class="form-control" placeholder="95" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ trans('sw.arm_circumference') }} (cm)</label>
                            <input type="number" step="0.1" name="arm_circumference" class="form-control" placeholder="35" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ trans('sw.abdominal_circumference') }} (cm)</label>
                            <input type="number" step="0.1" name="abdominal_circumference" class="form-control" placeholder="80" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ trans('sw.pelvic_circumference') }} (cm)</label>
                            <input type="number" step="0.1" name="pelvic_circumference" class="form-control" placeholder="90" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ trans('sw.thigh_circumference') }} (cm)</label>
                            <input type="number" step="0.1" name="thigh_circumference" class="form-control" placeholder="55" />
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="separator separator-dashed my-5"></div>
                    <div class="mb-3">
                        <label class="form-label">{{ trans('sw.notes') }}</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="{{ trans('sw.track_notes_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('sw.cancel') }}</button>
                    <button type="submit" class="btn btn-info">
                        <i class="ki-outline ki-check fs-2"></i> {{ trans('sw.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- BMI Auto-Calculation Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const weightInput = document.getElementById('track_weight');
    const heightInput = document.getElementById('track_height');
    const bmiInput = document.getElementById('track_bmi');
    const bmiCategory = document.getElementById('bmi_category');

    function calculateBMI() {
        const weight = parseFloat(weightInput.value);
        const height = parseFloat(heightInput.value);

        if (weight && height && height > 0) {
            // Convert height from cm to meters
            const heightInMeters = height / 100;
            // Calculate BMI
            const bmi = weight / (heightInMeters * heightInMeters);
            bmiInput.value = bmi.toFixed(2);

            // Set BMI category with color
            let category = '';
            let categoryClass = '';
            
            if (bmi < 18.5) {
                category = '{{ trans("sw.bmi_underweight") }}';
                categoryClass = 'text-warning';
            } else if (bmi >= 18.5 && bmi < 25) {
                category = '{{ trans("sw.bmi_normal") }}';
                categoryClass = 'text-success';
            } else if (bmi >= 25 && bmi < 30) {
                category = '{{ trans("sw.bmi_overweight") }}';
                categoryClass = 'text-warning';
            } else {
                category = '{{ trans("sw.bmi_obese") }}';
                categoryClass = 'text-danger';
            }

            bmiCategory.innerHTML = `<span class="${categoryClass} fw-bold">${category}</span>`;
        } else {
            bmiInput.value = '';
            bmiCategory.innerHTML = '';
        }
    }

    weightInput.addEventListener('input', calculateBMI);
    heightInput.addEventListener('input', calculateBMI);
});
</script>

{{-- File Modal --}}
<div class="modal fade" id="fileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('sw.addMemberTrainingFile', $member->id) }}" method="POST" enctype="multipart/form-data" id="fileUploadForm">
                @csrf
                <div class="modal-header">
                    <h2 class="fw-bold">{{ trans('sw.upload_file') }}</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <div class="mb-5">
                        <label class="form-label required">{{ trans('sw.file') }}</label>
                        <input type="file" name="file" id="file_input" class="form-control" required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.txt" />
                        <div class="form-text">
                            {{ trans('sw.max_file_size_2mb') ?? 'Maximum file size: 2MB' }}
                            <br>
                            {{ trans('sw.allowed_file_types') ?? 'Allowed file types' }}: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, GIF, TXT
                        </div>
                        <div id="file_error" class="text-danger mt-2" style="display: none;"></div>
                        <div id="file_info" class="text-muted mt-2" style="display: none;"></div>
                    </div>
                    <div class="mb-5">
                        <label class="form-label">{{ trans('sw.file_title') }}</label>
                        <input type="text" name="title" class="form-control" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('sw.cancel') }}</button>
                    <button type="submit" class="btn btn-danger" id="file_submit_btn">
                        <i class="ki-outline ki-check fs-2"></i> {{ trans('sw.upload') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    // Maximum file size in bytes (2MB = 2 * 1024 * 1024)
    const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2097152 bytes
    
    // Allowed file types/extensions
    const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'txt'];
    const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'text/plain'
    ];
    
    // Format bytes to human readable format
    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
    
    // Get file extension from filename
    function getFileExtension(filename) {
        return filename.split('.').pop().toLowerCase();
    }
    
    // Check if file type is allowed
    function validateFileType(file) {
        if (!file) return false;
        
        const fileName = file.name;
        const fileExtension = getFileExtension(fileName);
        const fileType = file.type;
        
        // Check extension
        const isValidExtension = ALLOWED_EXTENSIONS.includes(fileExtension);
        
        // Check MIME type (if available)
        const isValidMimeType = !fileType || ALLOWED_MIME_TYPES.includes(fileType);
        
        return isValidExtension && isValidMimeType;
    }
    
    // Check if file size is valid
    function validateFileSize(file) {
        if (!file) return false;
        
        const fileSize = file.size;
        return fileSize <= MAX_FILE_SIZE;
    }
    
    // Validate file (both size and type)
    function validateFile(file) {
        if (!file) return { valid: false, message: '' };
        
        const fileSize = file.size;
        const fileName = file.name;
        
        // Clear previous errors
        $('#file_error').hide().text('');
        $('#file_info').hide().text('');
        
        // Check file type
        if (!validateFileType(file)) {
            const fileExtension = getFileExtension(fileName);
            const allowedTypesStr = ALLOWED_EXTENSIONS.join(', ').toUpperCase();
            $('#file_error')
                .html('<i class="ki-outline ki-information-5 fs-5 me-2"></i>' + 
                      '{{ trans('sw.invalid_file_type') ?? "Invalid file type" }}: .' + fileExtension + 
                      '<br><small>{{ trans('sw.allowed_file_types') ?? "Allowed file types" }}: ' + allowedTypesStr + '</small>')
                .show();
            $('#file_submit_btn').prop('disabled', true).addClass('btn-secondary').removeClass('btn-danger');
            return { valid: false, message: 'invalid_type' };
        }
        
        // Check file size
        if (!validateFileSize(file)) {
            const fileSizeFormatted = formatBytes(fileSize);
            const maxSizeFormatted = formatBytes(MAX_FILE_SIZE);
            $('#file_error')
                .html('<i class="ki-outline ki-information-5 fs-5 me-2"></i>' + 
                      '{{ trans('sw.file_size_exceeded') ?? "File size exceeded" }}: ' + 
                      fileSizeFormatted + ' ({{ trans('sw.max') ?? "Max" }}: ' + maxSizeFormatted + ')')
                .show();
            $('#file_submit_btn').prop('disabled', true).addClass('btn-secondary').removeClass('btn-danger');
            return { valid: false, message: 'invalid_size' };
        }
        
        // File is valid
        const fileSizeFormatted = formatBytes(fileSize);
        $('#file_info')
            .html('<i class="ki-outline ki-check-circle fs-5 me-2 text-success"></i>' + 
                  '{{ trans('sw.file_selected') ?? "File selected" }}: ' + fileName + ' (' + fileSizeFormatted + ')')
            .show();
        $('#file_submit_btn').prop('disabled', false).removeClass('btn-secondary').addClass('btn-danger');
        return { valid: true, message: '' };
    }
    
    // Initialize when modal is shown
    $('#fileModal').on('shown.bs.modal', function() {
        const fileInput = $('#file_input');
        const fileForm = $('#fileUploadForm');
        
        // Clear previous values
        fileInput.val('');
        $('#file_error').hide().text('');
        $('#file_info').hide().text('');
        $('#file_submit_btn').prop('disabled', false).removeClass('btn-secondary').addClass('btn-danger');
        
        // Handle file selection
        fileInput.on('change', function() {
            const file = this.files[0];
            validateFile(file);
        });
        
        // Validate before form submission
        fileForm.on('submit', function(e) {
            const fileInput = document.getElementById('file_input');
            const file = fileInput.files[0];
            
            if (!file) {
                e.preventDefault();
                $('#file_error')
                    .html('<i class="ki-outline ki-information-5 fs-5 me-2"></i>' + 
                          '{{ trans('sw.please_select_file') ?? "Please select a file" }}')
                    .show();
                return false;
            }
            
            const validation = validateFile(file);
            if (!validation.valid) {
                e.preventDefault();
                return false;
            }
            
            return true;
        });
    });
    
    // Reset when modal is hidden
    $('#fileModal').on('hidden.bs.modal', function() {
        $('#file_input').off('change');
        $('#fileUploadForm').off('submit');
        $('#file_input').val('');
        $('#file_error').hide().text('');
        $('#file_info').hide().text('');
        $('#file_submit_btn').prop('disabled', false).removeClass('btn-secondary').addClass('btn-danger');
    });
})();
</script>

{{-- Note Modal --}}
<div class="modal fade" id="noteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('sw.addMemberTrainingNote', $member->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h2 class="fw-bold">{{ trans('sw.add_note') }}</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <div class="mb-5">
                        <label class="form-label required">{{ trans('sw.note') }}</label>
                        <textarea name="note" class="form-control" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('sw.cancel') }}</button>
                    <button type="submit" class="btn btn-dark">
                        <i class="ki-outline ki-check fs-2"></i> {{ trans('sw.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- AI Modal --}}
@php
    // Check if training AI feature is enabled (check once at the top if not already defined)
    if (!isset($active_training_ai)) {
        $features = is_array($mainSettings->features ?? null) 
            ? $mainSettings->features 
            : (is_string($mainSettings->features ?? null) 
                ? json_decode($mainSettings->features, true) 
                : []);
        $active_training_ai = isset($features['active_training_ai']) && $features['active_training_ai'];
    }
@endphp
@if($active_training_ai)
<div class="modal fade" id="aiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('sw.generateMemberAiPlan', $member->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h2 class="fw-bold">{{ trans('sw.ai_generate_plan') }}</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    @if($latestAssessment)
                    <div class="alert alert-success d-flex align-items-center mb-5">
                        <i class="ki-outline ki-shield-tick fs-2x me-3"></i>
                        <div class="flex-grow-1">
                            {{ trans('sw.assessment_found_will_use_for_ai') }}
                            <br><small>{{ $latestAssessment->created_at->format('Y-m-d') }}</small>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-warning d-flex align-items-center mb-5">
                        <i class="ki-outline ki-information fs-2x me-3"></i>
                        <div class="flex-grow-1">{{ trans('sw.no_assessment_ai_warning') }}</div>
                    </div>
                    @endif

                    <div class="mb-5">
                        <label class="form-label required">{{ trans('sw.plan_type') }}</label>
                        <select name="type" class="form-select" required>
                            <option value="training">{{ trans('sw.training_plan') }}</option>
                            <option value="diet">{{ trans('sw.diet_plan') }}</option>
                        </select>
                    </div>
                    <div class="mb-5">
                        <label class="form-label">{{ trans('sw.specific_goal') }}</label>
                        <textarea name="goal" class="form-control" rows="3" placeholder="{{ trans('sw.ai_goal_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('sw.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ki-outline ki-robot fs-2"></i> {{ trans('sw.generate') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- AI Plan Generator Modal --}}
@if($active_training_ai)
<div class="modal fade" id="aiPlanGeneratorModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div class="flex-grow-1">
                    <h2 class="fw-bold mb-1">
                        <i class="ki-outline ki-abstract-26 fs-1 me-2 text-primary"></i>
                        {{ trans('sw.ai_plan_generator') }}
                    </h2>
                    <div class="text-muted fs-7">{{ trans('sw.ai_generating_message') }}</div>
                </div>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" onclick="closeAiModal()">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                {{-- Step 1: Configuration --}}
                <div id="ai_step_config">
                    {{-- Assessment Check --}}
                    @if($latestAssessment)
                    <div class="alert alert-success d-flex align-items-center mb-7">
                        <i class="ki-outline ki-check-circle fs-2x me-3"></i>
                        <div class="flex-grow-1">
                            <div class="fw-bold">{{ trans('sw.assessment_found_will_use_for_ai') }}</div>
                            <div class="fs-7 text-gray-700">{{ trans('sw.last_update') }}: {{ $latestAssessment->created_at->format('Y-m-d') }}</div>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-warning d-flex align-items-center mb-7">
                        <i class="ki-outline ki-information-5 fs-2x me-3"></i>
                        <div class="flex-grow-1">
                            <div class="fw-bold">{{ trans('sw.no_assessment_for_ai') }}</div>
                            <div class="fs-7">{{ trans('sw.ai_needs_assessment') }}</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-light-warning" onclick="closeAiModal(); $('#assessmentModal').modal('show')">
                            {{ trans('sw.add_assessment') }}
                        </button>
                    </div>
                    @endif

                    <div class="row g-7">
                        <div class="col-md-6">
                            <label class="form-label required fs-5 fw-bold">{{ trans('sw.select_plan_type_for_ai') }}</label>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="form-check form-check-custom form-check-solid form-check-lg border border-2 border-gray-300 rounded p-5 cursor-pointer hover-elevate-up" onclick="selectAiPlanType(1)">
                                        <input class="form-check-input" type="radio" name="ai_plan_type" value="1" id="ai_type_training" />
                                        <label class="form-check-label fw-bold fs-4 d-flex flex-column align-items-center w-100" for="ai_type_training">
                                            <i class="ki-outline ki-award fs-3x text-primary mb-3"></i>
                                            {{ trans('sw.training_plan') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check form-check-custom form-check-solid form-check-lg border border-2 border-gray-300 rounded p-5 cursor-pointer hover-elevate-up" onclick="selectAiPlanType(2)">
                                        <input class="form-check-input" type="radio" name="ai_plan_type" value="2" id="ai_type_diet" />
                                        <label class="form-check-label fw-bold fs-4 d-flex flex-column align-items-center w-100" for="ai_type_diet">
                                            <i class="ki-outline ki-apple fs-3x text-success mb-3"></i>
                                            {{ trans('sw.diet_plan') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fs-5 fw-bold">{{ trans('sw.member_goals') }}</label>
                            <div id="training_goals" style="display: none;">
                                <select id="training_focus" class="form-select form-select-lg">
                                    <option value="">{{ trans('sw.choose') }}...</option>
                                    <option value="general_fitness">💪 {{ trans('sw.general_fitness') }}</option>
                                    <option value="weight_loss">🔥 {{ trans('sw.weight_loss') }}</option>
                                    <option value="muscle_building">💎 {{ trans('sw.muscle_building') }}</option>
                                    <option value="endurance">🏃 {{ trans('sw.endurance') }}</option>
                                    <option value="flexibility">🧘 {{ trans('sw.flexibility') }}</option>
                                </select>
                            </div>
                            <div id="diet_goals" style="display: none;">
                                <select id="diet_focus" class="form-select form-select-lg">
                                    <option value="">{{ trans('sw.choose') }}...</option>
                                    <option value="balanced">⚖️ {{ trans('sw.balanced_nutrition') }}</option>
                                    <option value="low_carb">🥗 {{ trans('sw.low_carb') }}</option>
                                    <option value="high_protein">🥩 {{ trans('sw.high_protein') }}</option>
                                    <option value="vegetarian">🥬 {{ trans('sw.vegetarian') }}</option>
                                    <option value="keto">🧈 {{ trans('sw.keto') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="separator separator-dashed my-7"></div>

                    {{-- Additional AI Context --}}
                    <div class="row g-5">
                        <div class="col-md-6">
                            <label class="form-label fs-5 fw-bold">{{ trans('sw.language') }}</label>
                            <select id="ai_language" class="form-select form-select-lg">
                                <option value="ar" {{ app()->getLocale() == 'ar' ? 'selected' : '' }}>🇸🇦 {{ trans('sw.arabic') }}</option>
                                <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>🇬🇧 {{ trans('sw.english') }}</option>
                            </select>
                            <div class="form-text">{{ trans('sw.ai_plan_language_hint') }}</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fs-5 fw-bold">{{ trans('sw.use_member_data') }}</label>
                            <div class="d-flex flex-column gap-2 mt-2">
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" value="1" id="include_assessment" checked />
                                    <label class="form-check-label" for="include_assessment">
                                        📋 {{ trans('sw.include_assessment') }}
                                        @if($latestAssessment)
                                        <span class="badge badge-light-success ms-2">{{ trans('sw.available') }}</span>
                                        @else
                                        <span class="badge badge-light-warning ms-2">{{ trans('sw.none') }}</span>
                                        @endif
                                    </label>
                                </div>
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" value="1" id="include_tracks" checked />
                                    <label class="form-check-label" for="include_tracks">
                                        📊 {{ trans('sw.include_tracking_history') }}
                                        <span class="badge badge-light-info ms-2">{{ $memberLogs->where('training_type', 'track')->count() }}</span>
                                    </label>
                                </div>
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" value="1" id="include_medicines" checked />
                                    <label class="form-check-label" for="include_medicines">
                                        💊 {{ trans('sw.include_medicines') }}
                                        <span class="badge badge-light-primary ms-2">{{ $memberLogs->where('training_type', 'medicine')->count() }}</span>
                                    </label>
                                </div>
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" value="1" id="include_previous_plans" checked />
                                    <label class="form-check-label" for="include_previous_plans">
                                        📝 {{ trans('sw.include_previous_plans') }}
                                        <span class="badge badge-light-success ms-2">{{ $memberLogs->where('training_type', 'plan')->count() }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="form-label fs-5 fw-bold">{{ trans('sw.additional_instructions') }}</label>
                        <textarea id="ai_custom_notes" class="form-control form-control-lg" rows="4" 
                                  placeholder="{{ trans('sw.ai_custom_notes_placeholder') }}"></textarea>
                        <div class="form-text">
                            <i class="ki-outline ki-information-5 fs-6"></i>
                            {{ trans('sw.ai_custom_notes_hint') }}
                        </div>
                    </div>

                    <div class="separator separator-dashed my-7"></div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-light btn-lg" onclick="closeAiModal()">
                            {{ trans('sw.cancel') }}
                        </button>
                        <button type="button" class="btn btn-primary btn-lg" id="generateAiBtn" onclick="generateAiPlan()" disabled>
                            <i class="ki-outline ki-abstract-26 fs-2"></i>
                            {{ trans('sw.generate') }}
                        </button>
                    </div>
                </div>

                {{-- Step 2: Generating (Loading) --}}
                <div id="ai_step_generating" style="display: none;">
                    <div class="text-center py-20">
                        <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">
                            <span class="visually-hidden">{{ trans('sw.loading') }}...</span>
                        </div>
                        <h3 class="fw-bold text-gray-800 mt-7">{{ trans('sw.generating_plan') }}</h3>
                        <p class="text-muted fs-5">{{ trans('sw.ai_generating_please_wait') }}</p>
                        <p class="text-gray-600 fs-6">{{ trans('sw.this_may_take_moment') }}</p>
                    </div>
                </div>

                {{-- Step 3: Preview & Edit --}}
                <div id="ai_step_preview" style="display: none;">
                    <div class="alert alert-success d-flex align-items-center mb-7">
                        <i class="ki-outline ki-check-circle fs-2x me-3"></i>
                        <div class="flex-grow-1">
                            <div class="fw-bold">{{ trans('sw.generated_plan_preview') }}</div>
                            <div class="fs-7">{{ trans('sw.edit_before_save') }}</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-light-primary" onclick="regenerateAiPlan()">
                            <i class="ki-outline ki-arrows-circle fs-5 me-1"></i>
                            {{ trans('sw.regenerate') }}
                        </button>
                    </div>

                    <form id="aiPlanForm">
                        {{-- Plan Details --}}
                        <div class="mb-7">
                            <label class="form-label required">{{ trans('sw.plan_title') }}</label>
                            <input type="text" id="ai_plan_title" class="form-control form-control-lg" required />
                        </div>

                        <div class="mb-7">
                            <label class="form-label">{{ trans('sw.plan_description') }}</label>
                            <textarea id="ai_plan_description" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="row g-5 mb-7">
                            <div class="col-md-4">
                                <label class="form-label required">{{ trans('sw.plan_duration') }}</label>
                                <input type="number" id="ai_plan_duration" class="form-control" value="30" required />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required">{{ trans('sw.type') }}</label>
                                <input type="text" id="ai_plan_type_display" class="form-control" readonly />
                                <input type="hidden" id="ai_plan_type_value" />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required">{{ trans('sw.status') }}</label>
                                <select id="ai_plan_status" class="form-select">
                                    <option value="1">{{ trans('sw.active') }}</option>
                                    <option value="0">{{ trans('sw.inactive') }}</option>
                                </select>
                            </div>
                        </div>

                        {{-- Tasks --}}
                        <div class="separator separator-dashed my-7"></div>
                        <div class="d-flex justify-content-between align-items-center mb-5">
                            <h4 class="fw-bold text-gray-800 mb-0">
                                <i class="ki-outline ki-notepad fs-2 me-2"></i>
                                {{ trans('sw.plan_tasks') }}
                            </h4>
                            <button type="button" class="btn btn-sm btn-light-success" onclick="addAiTask()">
                                <i class="ki-outline ki-plus fs-3"></i>
                                {{ trans('sw.add_task') }}
                            </button>
                        </div>

                        <div id="ai_tasks_container" class="mb-7">
                            {{-- Tasks will be populated by JS --}}
                        </div>

                        <div class="separator separator-dashed my-7"></div>

                        {{-- Action Buttons --}}
                        <div class="d-flex justify-content-between gap-3">
                            <button type="button" class="btn btn-light btn-lg" onclick="closeAiModal()">
                                {{ trans('sw.cancel') }}
                            </button>
                            <div class="d-flex gap-3">
                                <button type="button" class="btn btn-success btn-lg" onclick="saveAiPlanAsTemplate()">
                                    <i class="ki-outline ki-folder-added fs-2"></i>
                                    {{ trans('sw.save_as_template') }}
                                </button>
                                <button type="button" class="btn btn-primary btn-lg" onclick="assignAiPlanToMember()">
                                    <i class="ki-outline ki-check-circle fs-2"></i>
                                    {{ trans('sw.assign_directly') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<script>
// AI Plan Generator Functions
@if($active_training_ai)
let aiPlanTaskCounter = 0;

function openAiPlanGenerator() {
    // Hide plan modal
    const planModal = bootstrap.Modal.getInstance(document.getElementById('planModal'));
    if (planModal) planModal.hide();
    
    // Reset AI modal to config step
    document.getElementById('ai_step_config').style.display = 'block';
    document.getElementById('ai_step_generating').style.display = 'none';
    document.getElementById('ai_step_preview').style.display = 'none';
    
    // Show AI modal
    const aiModal = new bootstrap.Modal(document.getElementById('aiPlanGeneratorModal'));
    aiModal.show();
}

function closeAiModal() {
    const aiModal = bootstrap.Modal.getInstance(document.getElementById('aiPlanGeneratorModal'));
    if (aiModal) aiModal.hide();
}

function selectAiPlanType(type) {
    // Check radio button
    if (type === 1) {
        document.getElementById('ai_type_training').checked = true;
        document.getElementById('training_goals').style.display = 'block';
        document.getElementById('diet_goals').style.display = 'none';
    } else {
        document.getElementById('ai_type_diet').checked = true;
        document.getElementById('training_goals').style.display = 'none';
        document.getElementById('diet_goals').style.display = 'block';
    }
    
    // Enable generate button
    document.getElementById('generateAiBtn').disabled = false;
}

async function generateAiPlan() {
    // Get selected options
    const planType = document.querySelector('input[name="ai_plan_type"]:checked')?.value;
    const trainingFocus = document.getElementById('training_focus')?.value;
    const dietFocus = document.getElementById('diet_focus')?.value;
    
    if (!planType) {
        alert('{{ trans("sw.select_plan_type_for_ai") }}');
        return;
    }
    
    const focus = planType === '1' ? trainingFocus : dietFocus;
    
    // Collect all AI context data
    const aiContext = {
        type: planType,
        focus: focus,
        member_id: {{ $member->id }},
        language: document.getElementById('ai_language').value,
        custom_notes: document.getElementById('ai_custom_notes').value,
        include_assessment: document.getElementById('include_assessment').checked,
        include_tracks: document.getElementById('include_tracks').checked,
        include_medicines: document.getElementById('include_medicines').checked,
        include_previous_plans: document.getElementById('include_previous_plans').checked
    };
    
    // Show generating step
    document.getElementById('ai_step_config').style.display = 'none';
    document.getElementById('ai_step_generating').style.display = 'block';
    
    // Call backend API
    try {
        const response = await fetch('{{ route("sw.generateAiPlan", $member->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(aiContext)
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Populate preview
            populateAiPlanPreview(data.plan);
            
            // Show preview step
            document.getElementById('ai_step_generating').style.display = 'none';
            document.getElementById('ai_step_preview').style.display = 'block';
        } else {
            alert(data.message || 'Error generating plan');
            document.getElementById('ai_step_generating').style.display = 'none';
            document.getElementById('ai_step_config').style.display = 'block';
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to generate plan. Please try again.');
        document.getElementById('ai_step_generating').style.display = 'none';
        document.getElementById('ai_step_config').style.display = 'block';
    }
}

function populateAiPlanPreview(plan) {
    document.getElementById('ai_plan_title').value = plan.title || '';
    document.getElementById('ai_plan_description').value = plan.description || '';
    document.getElementById('ai_plan_duration').value = plan.duration || 30;
    document.getElementById('ai_plan_type_value').value = plan.type;
    document.getElementById('ai_plan_type_display').value = plan.type == 1 ? '{{ trans("sw.training_plan") }}' : '{{ trans("sw.diet_plan") }}';
    document.getElementById('ai_plan_status').value = '1';
    
    // Populate tasks
    const tasksContainer = document.getElementById('ai_tasks_container');
    tasksContainer.innerHTML = '';
    aiPlanTaskCounter = 0;
    
    if (plan.tasks && plan.tasks.length > 0) {
        plan.tasks.forEach(task => {
            addAiTask(task);
        });
    }
}

function addAiTask(taskData = null) {
    const taskId = ++aiPlanTaskCounter;
    const planType = document.getElementById('ai_plan_type_value').value;
    
    const taskHtml = `
        <div class="card card-bordered mb-5" id="ai_task_${taskId}">
            <div class="card-header">
                <h3 class="card-title">{{ trans('sw.task') }} #${taskId}</h3>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-sm btn-icon btn-light-danger" onclick="removeAiTask(${taskId})">
                        <i class="ki-outline ki-trash fs-4"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label required">{{ trans('sw.day_name') }}</label>
                        <input type="text" class="form-control ai-task-field" data-field="day_name" value="${taskData?.day_name || ''}" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">${planType == 1 ? '{{ trans("sw.exercise_name") }}' : '{{ trans("sw.meal_name") }}'}</label>
                        <input type="text" class="form-control ai-task-field" data-field="title" value="${taskData?.title || ''}" />
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ trans('sw.description') }}</label>
                        <textarea class="form-control ai-task-field" data-field="description" rows="2">${taskData?.description || ''}</textarea>
                    </div>
                    ${planType == 1 ? `
                    <div class="col-md-4">
                        <label class="form-label">{{ trans('sw.t_group') }}</label>
                        <input type="number" class="form-control ai-task-field" data-field="t_group" value="${taskData?.t_group || ''}" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ trans('sw.t_repeats') }}</label>
                        <input type="number" class="form-control ai-task-field" data-field="t_repeats" value="${taskData?.t_repeats || ''}" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ trans('sw.t_rest') }}</label>
                        <input type="text" class="form-control ai-task-field" data-field="t_rest" value="${taskData?.t_rest || ''}" placeholder="30s" />
                    </div>
                    ` : `
                    <div class="col-md-3">
                        <label class="form-label">{{ trans('sw.d_calories') }}</label>
                        <input type="text" class="form-control ai-task-field" data-field="d_calories" value="${taskData?.d_calories || ''}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ trans('sw.d_protein') }}</label>
                        <input type="text" class="form-control ai-task-field" data-field="d_protein" value="${taskData?.d_protein || ''}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ trans('sw.d_carb') }}</label>
                        <input type="text" class="form-control ai-task-field" data-field="d_carb" value="${taskData?.d_carb || ''}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ trans('sw.d_fats') }}</label>
                        <input type="text" class="form-control ai-task-field" data-field="d_fats" value="${taskData?.d_fats || ''}" />
                    </div>
                    `}
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('ai_tasks_container').insertAdjacentHTML('beforeend', taskHtml);
}

function removeAiTask(taskId) {
    document.getElementById(`ai_task_${taskId}`).remove();
}

function regenerateAiPlan() {
    document.getElementById('ai_step_preview').style.display = 'none';
    document.getElementById('ai_step_config').style.display = 'block';
}

async function saveAiPlanAsTemplate() {
    const planData = collectAiPlanData();
    
    if (!planData) return;
    
    try {
        const response = await fetch('{{ route("sw.saveAiPlanTemplate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(planData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Add the new plan to the dropdown
            const planSelect = document.getElementById('plan_id_select');
            if (planSelect && data.plan) {
                const option = document.createElement('option');
                option.value = data.plan.id;
                option.text = `${data.plan.title} (${data.plan.type_name})`;
                option.selected = true; // Auto-select the new plan
                planSelect.appendChild(option);
            }
            
            // Close AI modal and show plan modal with the new plan selected
            closeAiModal();
            
            // Show success message
            Swal.fire({
                title: '{{ trans("admin.done") }}',
                text: '{{ trans("sw.ai_plan_saved_successfully") }}',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
            
            // Open the plan assignment modal with the new plan already selected
            const planModal = new bootstrap.Modal(document.getElementById('planModal'));
            planModal.show();
        } else {
            alert(data.message || 'Error saving plan');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to save plan. Please try again.');
    }
}

async function assignAiPlanToMember() {
    const planData = collectAiPlanData();
    planData.member_id = {{ $member->id }};
    
    if (!planData) return;
    
    try {
        const response = await fetch('{{ route("sw.assignAiPlanToMember", $member->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(planData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire({
                title: '{{ trans("admin.done") }}',
                text: '{{ trans("sw.ai_plan_assigned_successfully") }}',
                icon: 'success'
            }).then(() => {
                closeAiModal();
                location.reload();
            });
        } else {
            alert(data.message || 'Error assigning plan');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to assign plan. Please try again.');
    }
}

function collectAiPlanData() {
    const title = document.getElementById('ai_plan_title').value;
    const description = document.getElementById('ai_plan_description').value;
    const duration = document.getElementById('ai_plan_duration').value;
    const type = document.getElementById('ai_plan_type_value').value;
    const status = document.getElementById('ai_plan_status').value;
    
    if (!title) {
        alert('{{ trans("sw.plan_title") }} {{ trans("sw.required") }}');
        return null;
    }
    
    // Collect tasks
    const tasks = [];
    document.querySelectorAll('[id^="ai_task_"]').forEach(taskCard => {
        const taskData = {};
        taskCard.querySelectorAll('.ai-task-field').forEach(field => {
            taskData[field.dataset.field] = field.value;
        });
        tasks.push(taskData);
    });
    
    return {
        title,
        description,
        duration,
        type,
        status,
        tasks
    };
}
@endif
</script>


