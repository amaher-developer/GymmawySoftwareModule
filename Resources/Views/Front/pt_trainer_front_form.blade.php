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
            <a href="{{ route('sw.listPTTrainer') }}" class="text-muted text-hover-primary">{{ trans('sw.pt_trainers')}}</a>
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
@section('styles')
    <link href="{{asset('/')}}resources/assets/admin/global/scripts/css/fileupload.css" rel="stylesheet"
          type="text/css"/>
   <style>
        .tag-orange {
            background-color: #fd7e14 !important;
            color: #fff;
        }
        .tag {
            color: #14112d;
            background-color: #ecf0fa;
            border-radius: 3px;
            padding: 0 .5rem;
            line-height: 2em;
            display: -ms-inline-flexbox;
            display: inline-flex;
            cursor: default;
            font-weight: 400;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;

            margin: 0 15px;
        }
        .ckbox input{
             width: 20px;
             height: 20px;
         }
        .ckbox span{
            vertical-align: text-top;
        }
        .sch-day-name {
            color: black;
            font-weight: bold;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="{{asset('resources/assets/admin/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('resources/assets/admin/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css')}}"/>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
@endsection
@section('page_body')
    <!--begin::PT Trainer Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Aside column-->
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
            <!--begin::Thumbnail settings-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2>{{ trans('sw.the_image')}}</h2>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body text-center pt-0">
                    <!--begin::Image input-->
                    <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url({{asset('uploads/settings/default.jpg')}})">
                        <!--begin::Preview existing avatar-->
                        <div class="image-input-wrapper w-125px h-125px" style="background-image: url({{$trainer->image ?? asset('uploads/settings/default.jpg')}})"></div>
                        <!--end::Preview existing avatar-->
                        <!--begin::Label-->
                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="{{ trans('sw.change_image')}}">
                            <i class="ki-outline ki-pencil fs-7"></i>
                            <!--begin::Inputs-->
                            <input type="file" name="image" accept=".png, .jpg, .jpeg" />
                            <input type="hidden" name="avatar_remove" />
                            <!--end::Inputs-->
                        </label>
                        <!--end::Label-->
                        <!--begin::Cancel-->
                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="{{ trans('sw.cancel')}}">
                            <i class="ki-outline ki-cross fs-2"></i>
                        </span>
                        <!--end::Cancel-->
                        <!--begin::Remove-->
                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="{{ trans('sw.remove_image')}}">
                            <i class="ki-outline ki-cross fs-2"></i>
                        </span>
                        <!--end::Remove-->
                    </div>
                    <!--end::Image input-->
                    <!--begin::Hint-->
                    <div class="text-muted fs-7">{{ trans('sw.set_the_trainer_image')}}</div>
                    <!--end::Hint-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Thumbnail settings-->
        </div>
        <!--end::Aside column-->
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::Trainer Details-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{$title}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-lg-6">
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.name')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="name" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_name')}}" 
                               value="{{ old('name', $trainer->name) }}" 
                                       required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.phone')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                    <input id="phone" value="{{ old('phone', $trainer->phone) }}"
                           placeholder="{{ trans('sw.enter_phone')}}"
                           name="phone" type="text" class="form-control" required>
                                <!--end::Input-->
                </div>
                            <!--end::Input group-->
            </div>
                        <div class="col-lg-6">
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="form-label">{{ trans('sw.bonus_amount')}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                    <input id="price" value="{{ old('price', $trainer->price) }}"
                           placeholder="{{ trans('sw.enter_price')}}"
                           name="price" type="number" step="0.01" min="0"  class="form-control" >
                                <!--end::Input-->
                </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="required form-label">{{ trans('sw.trainer_percentage_for_member')}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                    <input id="percentage" value="{{ old('percentage', $trainer->percentage) ?? 0 }}"
                           placeholder="{{ trans('sw.enter_percentage')}}"
                           name="percentage" type="number" max="100" min="0" class="form-control" required>
                                <!--end::Input-->
                            </div>
                            <!--end::Input group-->
                        </div>
                </div>
            </div>
                <!--end::Card body-->
            </div>
            <!--end::Trainer Details-->

            <!--begin::Class Days-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ trans('sw.class_days')}}</h2>
                    </div>
                    <div class="card-toolbar">
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modelAddSubscriptionToTrainer" class="btn btn-sm btn-light-primary">
                            <i class="ki-outline ki-plus fs-6"></i>
                            {{ trans('sw.add_class_days')}}
                        </a>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table id="AddSubscriptionToTrainerTable" class="table table-bordered">
                    <thead>
                                <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                    <th><i class="ki-outline ki-notepad fs-6 me-2"></i>{{ trans('sw.pt_subscriptions')}}</th>
                                    <th><i class="ki-outline ki-notepad fs-6 me-2"></i>{{ trans('sw.pt_classes')}}</th>
                                    <th><i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.class_days')}}</th>
                                    <th class="text-end"><i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(@$trainer->pt_subscription_trainer)
                        @foreach(@$trainer->pt_subscription_trainer as $pt_subscription_trainer)
                            @php
                                $reservation_details = @$pt_subscription_trainer['reservation_details'];
                                $schedule = '';
                                $schedule_input = '';
                                if(@$reservation_details['work_days']){
                                    foreach ($reservation_details['work_days'] as $key => $reservation_detail){
                                                    $schedule .= '<span class="badge badge-light-primary fs-7 me-1">'.week_name($key, @$lang).'</span> '. $reservation_detail['start'] . ' - ' . $reservation_detail['end'] . '<br>';
                                        $schedule_input .= $key . ',,' . $reservation_detail['start'] . ',,' .$reservation_detail['end']. '@@';
                                    }
                                }
                            @endphp
                            <tr id='subscriptionToMembershipTrId_{{$pt_subscription_trainer->pt_class_id}}' value="{{$pt_subscription_trainer->pt_class_id}}">
                                <input type='hidden' name='reservation_details[]' value='{{@$schedule_input}}' />
                                <input type='hidden' name='class_ids[]' value='{{@$pt_subscription_trainer->pt_class_id}}' />
                                <td> {{@$pt_subscription_trainer->pt_class->pt_subscription->name}} </td>
                                            <td> {{@$pt_subscription_trainer->pt_class->name}} </td>
                                            <td> {!! $schedule !!} </td>
                                            <td class='text-end'>
                                                <a class='btn btn-icon btn-sm btn-light-danger' onclick='deleteSubscriptionToTrainer($(this))'>
                                                    <i class='ki-outline ki-trash fs-3'></i>
                                                </a>
                                            </td>
                                        </tr>
                        @endforeach
                    @endif

                    <tr id="noRecordFound" style="{{(count(@$trainer->pt_subscription_trainer) > 0 && @$trainer) ? 'display:none': ''}}">
                        <td colspan="4" class="text-center">{{ trans('sw.no_record_found')}}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
                    </div>
                <!--end::Card body-->
                </div>
            <!--end::Class Days-->

            <!--begin::Form actions-->
            <div class="d-flex justify-content-end">
                <button type="reset" class="btn btn-light me-5">{{ trans('admin.reset')}}</button>
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-check fs-2"></i>
                    {{ trans('global.save')}}
                </button>
            </div>
            <!--end::Form actions-->
        </div>
    </form>


    <!-- begin::Modal - Add Subscription -->
    <div class="modal fade" tabindex="-1" id="modelAddSubscriptionToTrainer">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('sw.add_class_days')}}</h5>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-outline ki-cross fs-2x"></i>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="mb-10 fv-row">
                        <label class="required form-label">{{ trans('sw.pt_subscription')}}</label>
                        <select id="AddSubscriptionToTrainerSelect" class="form-select select2" data-dropdown-parent="#modelAddSubscriptionToTrainer" data-placeholder="{{ trans('admin.choose')}}...">
                            <option></option>
                                @foreach($subscriptions as $subscription)
                                    <optgroup label="{{$subscription->name}} @if(count($subscription->pt_classes) == 0) ({{ trans('sw.no_data_found')}}) @endif">
                                        @foreach($subscription->pt_classes as $class)
                                            <option value="{{$class->id}}" subscription="{{$subscription->name}}">{{$class->name}}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                    <th class="w-100px">{{ trans('sw.status')}}</th>
                                    <th>{{ trans('sw.day')}}</th>
                                    <th>{{ trans('sw.time_from')}}</th>
                                    <th>{{ trans('sw.time_to')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                @php
                                    $days = [
                                        6 => 'sat', 0 => 'sun', 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thurs', 5 => 'fri'
                                    ];
                                @endphp
                                @foreach($days as $dayNum => $dayKey)
                                        <tr>
                                            <td>
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input id="reservation_details_work_days_{{$dayNum}}_status"
                                                   value="1" type="checkbox" class="form-check-input">
                                                </div>
                                            </td>
                                    <td>{{ trans('sw.' . $dayKey)}}</td>
                                            <td>
                                                <div class="input-group">
                                            <input id="reservation_details_work_days_{{$dayNum}}_start"
                                                   type="text" class="form-control timepicker timepicker-24">
                                            <span class="input-group-text"><i class="ki-outline ki-time"></i></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                            <input id="reservation_details_work_days_{{$dayNum}}_end"
                                                   type="text" class="form-control timepicker timepicker-24">
                                            <span class="input-group-text"><i class="ki-outline ki-time"></i></span>
                                                </div>
                                            </td>
                                        </tr>
                                @endforeach
                                        </tbody>
                                    </table>
                                </div>

                    <input type="hidden" id="selectedSubscriptions" value="{{@count(@$trainer->pt_subscription_trainer) > 0 ? "@@" . implode("@@@@" , @collect(@$trainer->pt_subscription_trainer)->pluck('pt_class_id')->toArray()) . "@@": ''}}">
                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('sw.exist')}}</button>
                    <button id="btn_edit_membership" class="btn btn-primary"
                            onclick="submitSubscriptionToTrainerData()"
                            type="button">{{ trans('sw.add')}}</button>
                </div>
            </div>
        </div>
    </div>
    <!-- end::Modal - Add Subscription -->
@endsection

@section('scripts')
    @parent
    <script type="text/javascript" src="{{asset('resources/assets/admin/global/scripts/js/fileupload.js')}}"></script>
    <script type="text/javascript" src="{{asset('resources/assets/admin/global/scripts/js/file-upload.js')}}"></script>
<script type="text/javascript" src="{{asset('resources/assets/admin/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js')}}"></script>
<script type="text/javascript" src="{{asset('resources/assets/admin/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js')}}"></script>

<script>
        $(document).ready(function() {
    $('.timepicker-24').timepicker({
        autoclose: true,
        minuteStep: 5,
        showSeconds: false,
        showMeridian: false
    });

            KTImageInput.createInstances();
        });

        function submitSubscriptionToTrainerData(){
            let subscription_id = $("#AddSubscriptionToTrainerSelect option:selected").val();
            let subscription_name = $("#AddSubscriptionToTrainerSelect option:selected").text();
            let pt_subscription_name = $("#AddSubscriptionToTrainerSelect option:selected").attr('subscription');
            let selectedSubscriptions = $('#selectedSubscriptions').val();

            if(subscription_id) {
                if(selectedSubscriptions.indexOf("@@"+subscription_id+"@@") >= 0){
                    Swal.fire({
                        text: '{{ trans('sw.error_reservation_in_subscription_exist')}}',
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "{{ trans('sw.ok_got_it')}}",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                } else {
                    let schedule = '';
                    let schedule_input = '';
                    let days = { 6: 'sat', 0: 'sun', 1: 'mon', 2: 'tue', 3: 'wed', 4: 'thurs', 5: 'fri' };
                    
                    for (const dayNum in days) {
                        if ($('#reservation_details_work_days_' + dayNum + '_status').is(":checked")) {
                            let start = $('#reservation_details_work_days_' + dayNum + '_start').val();
                            let end = $('#reservation_details_work_days_' + dayNum + '_end').val();
                            if (start && end) {
                                schedule += '<span class="badge badge-light-primary fs-7 me-1">' + '{{ trans('sw.' . $dayKey) }}' + '</span> ' + start + ' - ' + end + '<br>';
                                schedule_input += dayNum + ',,' + start + ',,' + end + '@@';
                            }
                        }
                    }

                    if(!schedule){
                        Swal.fire({
                            text: '{{ trans('sw.error_reservation_work_days_in_subscription_not_exist')}}',
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "{{ trans('sw.ok_got_it')}}",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    } else {
                        $('#AddSubscriptionToTrainerTable tbody').append(
                            `<tr id='subscriptionToMembershipTrId_${subscription_id}' value='${subscription_id}'>
                                <input type='hidden' name='reservation_details[]' value='${schedule_input}' />
                                <input type='hidden' name='class_ids[]' value='${subscription_id}' />
                                <td>${pt_subscription_name}</td>
                                <td>${subscription_name}</td>
                                <td>${schedule}</td>
                                <td class='text-end'>
                                    <a class='btn btn-icon btn-sm btn-light-danger' onclick='deleteSubscriptionToTrainer($(this))'>
                                        <i class='ki-outline ki-trash fs-3'></i>
                                    </a>
                                </td>
                            </tr>`
                        );

                        $('#modelAddSubscriptionToTrainer').modal('hide');
                        $('#selectedSubscriptions').val($("#selectedSubscriptions").val() + '@@' + subscription_id + '@@');
                        $('#noRecordFound').hide();
                    }
                }
            }
        }

        function deleteSubscriptionToTrainer(row){
            let class_id = row.closest("tr").attr('value');
            row.closest("tr").remove();
            let selectedSubscriptions = $('#selectedSubscriptions').val();
            selectedSubscriptions = selectedSubscriptions.replace('@@'+class_id+'@@','');
            $('#selectedSubscriptions').val(selectedSubscriptions);
            if(!selectedSubscriptions){
                $('#noRecordFound').show();
            }
        }
    </script>
@endsection
