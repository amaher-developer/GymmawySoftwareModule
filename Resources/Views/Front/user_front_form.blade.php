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
        <li class="breadcrumb-item text-gray-900">{{ $title }}</li>
        <!--end::Item-->
    </ul>
    <!--end::Breadcrumb-->
@endsection
@section('form_title') {{ @$title }} @endsection
@section('styles')
    {{-- Required CSS Libraries --}}
    <link rel="stylesheet" type="text/css" href="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
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
        }
        .tab-content>.tab-pane {
            padding-top: 20px;
        }
        .ckbox input{
            width: 20px;
            height: 20px;
        }
        .ckbox span{
            vertical-align: text-top;
        }
    </style>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
@endsection
@section('page_body')




    <!--begin::User Form-->
    <form method="post" action="" class="form" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::User Details-->
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
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.the_image')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input id="SubscribedClientsInputPhoto"
                               data-default-file="{{asset('uploads/settings')}}/default.jpg"
                               name="image" type="file" class="dropify mb-2" data-height="200"
                               accept=".jpg, .png, image/jpeg, image/png"/>
                        <input type="hidden" name="image" id="photo_camera">
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.name')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="name" class="form-control mb-2" 
                               placeholder="{{ trans('sw.name')}}" 
                               value="{{ old('name', $user->name) }}" 
                               required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.job_title')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="title" class="form-control mb-2" 
                               placeholder="{{ trans('sw.job_title')}}" 
                               value="{{ old('title', $user->title) }}" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
{{--            <div class="form-group col-md-12">--}}
{{--                <label class="col-md-3  control-label">{{ trans('sw.branch')}} <span class="required">*</span></label>--}}
{{--                <div class="col-md-9">--}}
{{--                    <select id="branch_setting_id" name="branch_setting_id" class="form-control select2" required>--}}
{{--                        <option value="">{{ trans('sw.choose')}}...</option>--}}
{{--                        @foreach($branches as $branch)--}}
{{--                        <option value="{{$branch->id}}" @if($branch->id == old('branch_setting_id', @$user->branch_setting_id)) selected="" @endif>{{$branch->name}}</option>--}}
{{--                        @endforeach--}}
{{--                    </select>--}}
{{--                </div>--}}
{{--            </div>--}}
                    <!--begin::Section: Basic Information-->
                    <div class="separator separator-dashed my-10"></div>
                    <div class="mb-10">
                        <h4 class="text-dark fw-bold mb-5">{{ trans('sw.basic_information') ?? 'Basic Information' }}</h4>
                    </div>
                    <!--end::Section: Basic Information-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.email')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input name="email" value="{{ old('email', $user->email) }}" type="email" class="form-control"
                           placeholder="{{ trans('sw.enter_email')}}" required>
                        <!--end::Input-->
                </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.password')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input name="password" value="" type="password" class="form-control" autocomplete="new-password"
                           placeholder="{{ trans('sw.enter_password')}}">
                        <!--end::Input-->
                        <!--begin::Helper text-->
                        <!-- <div class="form-text">{{ trans('sw.leave_empty_to_keep_current_password') ?? 'Leave empty to keep current password' }}</div> -->
                        <!--end::Helper text-->
                </div>
                    <!--end::Input group-->
                    
                    <!--begin::Row-->
                    <div class="row mb-10">
                        <div class="col-md-6 fv-row">
                            <!--begin::Label-->
                            <label class="required form-label">{{ trans('sw.phone')}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                    <input name="phone" value="{{ old('phone', $user->phone) }}" type="text" class="form-control"
                           placeholder="{{ trans('sw.enter_phone')}}" required>
                            <!--end::Input-->
                </div>
                        <div class="col-md-6 fv-row">
                            <!--begin::Label-->
                            <label class="form-label">{{ trans('sw.address')}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                    <input name="address" value="{{ old('address', $user->address) }}" type="text" class="form-control"
                                   placeholder="{{ trans('sw.enter_address')}}">
                            <!--end::Input-->
                </div>
            </div>
                    <!--end::Row-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.salary')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input name="salary" value="{{ old('salary', $user->salary) }}" type="number" class="form-control"
                               placeholder="{{ trans('sw.enter_salary')}}" step="0.01">
                        <!--end::Input-->
                </div>
                    <!--end::Input group-->

                    <!--begin::Section: Additional Information-->
                    <div class="separator separator-dashed my-10"></div>
                    <div class="mb-10">
                        <h4 class="text-dark fw-bold mb-5">{{ trans('sw.additional_information') ?? 'Additional Information' }}</h4>
            </div>
                    <!--end::Section: Additional Information-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.fingerprint')}}</label>
                        <!--end::Label-->
                        <!--begin::Input group-->
                        <div class="input-group">
                            <input class="form-control" placeholder="{{ trans('sw.fingerprint_id_data')}}"
                               name="fp_id" min="0"
                               value="{{ old('fp_id', $user->fp_id) }}"
                                   type="number">
                            <span class="input-group-text">
                                <i class="material-icons">fingerprint</i>
                        </span>
                    </div>
                        <!--end::Input group-->
                </div>
                    <!--end::Input group-->
                    
                    <!--begin::Row-->
                    <div class="row mb-10">
                        <div class="col-md-6 fv-row">
                            <!--begin::Label-->
                            <label class="required form-label">{{ trans('sw.start_time_work')}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input class="form-control timepicker timepicker-no-seconds" autocomplete="off" 
                                   placeholder="{{ trans('sw.start_time_work')}}"
                           name="start_time_work"
                           value="{{ old('start_time_work', $user->start_time_work) }}"
                                   type="text" required>
                            <!--end::Input-->
                    </div>
                        <div class="col-md-6 fv-row">
                            <!--begin::Label-->
                            <label class="required form-label">{{ trans('sw.end_time_work')}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input class="form-control timepicker timepicker-no-seconds" autocomplete="off" 
                                   placeholder="{{ trans('sw.end_time_work')}}"
                           name="end_time_work"
                           value="{{ old('end_time_work', $user->end_time_work) }}"
                                   type="text" required>
                            <!--end::Input-->
                </div>
            </div>
                    <!--end::Row-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::User Details-->



            <!--begin::Permissions-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa fa-gift me-2"></i>
                        <h2>{{ trans('sw.permissions')}}</h2>
                                </div>
                            </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Permission Mode Selection-->
                    <div class="mb-10">
                        <div class="row g-5">
                            <div class="col-md-6">
                                <label class="form-label">{{ trans('sw.use_permission_group')}}</label>
                                <select name="permission_group_id" id="permission_group_id" class="form-select form-select-solid" data-control="select2" data-placeholder="{{ trans('sw.select_permission_group')}}">
                                    <option value="">{{ trans('sw.custom_permissions')}}</option>
                                    @foreach(\Modules\Software\Models\GymUserPermission::branch()->get() as $group)
                                        <option value="{{ $group->id }}" 
                                            data-permissions="{{ json_encode($group->permissions) }}"
                                            @if(old('permission_group_id', $user->permission_group_id) == $group->id) selected @endif>
                                            {{ $group->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">{{ trans('sw.leave_empty_for_custom')}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="separator my-5"></div>
                    <!--end::Permission Mode Selection-->
                    
                    <!--begin::Tab navigation-->
                    <ul class="nav nav-line-tabs nav-stretch fs-6 fw-semibold">
                        <li class="nav-item">
                            <a class="nav-link " href="#admins" data-toggle="tab">
                                {{ trans('sw.users')}}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#memberships" data-toggle="tab">
                                {{ trans('sw.memberships')}}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#activities" data-toggle="tab">
                                {{ trans('sw.activities')}}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#unsubscribedClients" data-toggle="tab">
                                {{ trans('sw.daily_clients')}}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#subscribedClients" data-toggle="tab">
                                {{ trans('sw.subscribed_clients')}}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#moneybox" data-toggle="tab">
                                {{ trans('sw.moneybox')}}
                            </a>
                        </li>
                                    @if($mainSettings->active_pt)
                        <li class="nav-item">
                            <a class="nav-link" href="#pt" data-toggle="tab">
                                {{ trans('sw.pt')}}
                            </a>
                        </li>
                                    @endif
                                    @if($mainSettings->active_training)
                        <li class="nav-item">
                            <a class="nav-link" href="#training" data-toggle="tab">
                                {{ trans('sw.training')}}
                            </a>
                        </li>
                                    @endif
                                    @if($mainSettings->active_store)
                        <li class="nav-item">
                            <a class="nav-link" href="#store" data-toggle="tab">
                                {{ trans('sw.store')}}
                            </a>
                        </li>
                                    @endif
                        <li class="nav-item">
                            <a class="nav-link" href="#potentialMembers" data-toggle="tab">
                                {{ trans('sw.potential_clients')}}
                            </a>
                        </li>
                                    @if($mainSettings->active_website || $mainSettings->active_mobile)
                        <li class="nav-item">
                            <a class="nav-link" href="#reservationMembers" data-toggle="tab">
                                {{ trans('sw.reservation_clients')}}
                            </a>
                        </li>
                                    @endif
                                    @if($mainSettings->active_mobile)
                        <li class="nav-item">
                            <a class="nav-link" href="#banners" data-toggle="tab">
                                {{ trans('sw.banners')}}
                            </a>
                        </li>
                                        @endif
                                </ul>
                    <!--end::Tab navigation-->

                    <!--begin::Tab content-->
                                <div class="tab-content">
                                    <div class="tab-pane active" id="admins">
                                        <div class="row pt-2 pb-2">
                                            <div class="col-lg-2 ">
                                                <label class="ckbox">
                                                    <input name="permissions[]"
                                                                            value="listUser"
                                                                            @if(is_array($user->permissions) && in_array('listUser', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span> </label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createUser"
                                                                            @if(is_array($user->permissions) && in_array('createUser', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editUser"
                                                                            @if(is_array($user->permissions) && in_array('editUser', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteUser"
                                                                            @if(is_array($user->permissions) && in_array('deleteUser', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportUserExcel"
                                                                            @if(is_array($user->permissions) && in_array('exportUserExcel', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportUserPDF"
                                                                            @if(is_array($user->permissions) && in_array('exportUserPDF', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pdf_download')}}</span></label>
                                            </div>


                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportUserAttendeesList"
                                                                            @if(is_array($user->permissions) && in_array('reportUserAttendeesList', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.attendees_report')}}</span></label>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="tab-pane " id="memberships">
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-2 ">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listSubscription"
                                                                            @if(is_array($user->permissions) && in_array('listSubscription', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createSubscription"
                                                                            @if(is_array($user->permissions) && in_array('createSubscription', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editSubscription"
                                                                            @if(is_array($user->permissions) && in_array('editSubscription', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteSubscription"
                                                                            @if(is_array($user->permissions) && in_array('deleteSubscription', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportSubscriptionExcel"
                                                                            @if(is_array($user->permissions) && in_array('exportSubscriptionExcel', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportSubscriptionPDF"
                                                                            @if(is_array($user->permissions) && in_array('exportSubscriptionPDF', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pdf_download')}}</span></label>
                                            </div>


                                        </div>
                                    </div>
                                    <div class="tab-pane " id="activities">
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-2 ">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listActivity"
                                                                            @if(is_array($user->permissions) && in_array('listActivity', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createActivity"
                                                                            @if(is_array($user->permissions) && in_array('createActivity', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editActivity"
                                                                            @if(is_array($user->permissions) && in_array('editActivity', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteActivity"
                                                                            @if(is_array($user->permissions) && in_array('deleteActivity', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportActivityExcel"
                                                                            @if(is_array($user->permissions) && in_array('exportActivityExcel', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportActivityPDF"
                                                                            @if(is_array($user->permissions) && in_array('exportActivityPDF', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pdf_download')}}</span></label>
                                            </div>

                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportTodayNonMemberList"
                                                                            @if(is_array($user->permissions) && in_array('reportTodayNonMemberList', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.non_client_attendees_today')}}</span></label>
                                            </div>


                                        </div>
                                    </div>
                                    <div class="tab-pane " id="unsubscribedClients">
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listNonMember"
                                                                            @if(is_array($user->permissions) && in_array('listNonMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createNonMember"
                                                                            @if(is_array($user->permissions) && in_array('createNonMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editNonMember"
                                                                            @if(is_array($user->permissions) && in_array('editNonMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteNonMember"
                                                                            @if(is_array($user->permissions) && in_array('deleteNonMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editNonMemberDiscount"
                                                                            @if(is_array($user->permissions) && in_array('editNonMemberDiscount', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_discount')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editNonMemberDiscountGroup"
                                                                            @if(is_array($user->permissions) && in_array('editNonMemberDiscountGroup', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.group_discount_add')}}</span></label>
                                            </div>

                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listNonMemberReport"
                                                                            @if(is_array($user->permissions) && in_array('listNonMemberReport', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.activities_calender')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportNonMemberExcel"
                                                                            @if(is_array($user->permissions) && in_array('exportNonMemberExcel', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportNonMemberPDF"
                                                                            @if(is_array($user->permissions) && in_array('exportNonMemberPDF', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pdf_download')}}</span></label>
                                            </div>


                                        </div>
                                    </div>
                                    <div class="tab-pane " id="subscribedClients">
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-2 ">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listMember"
                                                                            @if(is_array($user->permissions) && in_array('listMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createMember"
                                                                            @if(is_array($user->permissions) && in_array('createMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editMember"
                                                                            @if(is_array($user->permissions) && in_array('editMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteMember"
                                                                            @if(is_array($user->permissions) && in_array('deleteMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="memberSubscriptionEdit"
                                                                            @if(is_array($user->permissions) && in_array('memberSubscriptionEdit', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit_accounts')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="memberSubscriptionRenewStore"
                                                                            @if(is_array($user->permissions) && in_array('memberSubscriptionRenewStore', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.renew_accounts')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteMemberSubscription"
                                                                            @if(is_array($user->permissions) && in_array('deleteMemberSubscription', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete_accounts')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="freezeMember"
                                                                            @if(is_array($user->permissions) && in_array('freezeMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.freeze_accounts')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="unfreezeMember"
                                                                            @if(is_array($user->permissions) && in_array('unfreezeMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.unfreeze_accounts')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createMemberPayAmountRemainingForm"
                                                                            @if(is_array($user->permissions) && in_array('createMemberPayAmountRemainingForm', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pay_remaining')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editMemberDiscount"
                                                                            @if(is_array($user->permissions) && in_array('editMemberDiscount', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_discount')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editMemberDiscountGroup"
                                                                            @if(is_array($user->permissions) && in_array('editMemberDiscountGroup', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.group_discount_add')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="creditMemberBalanceAdd"
                                                                            @if(is_array($user->permissions) && in_array('creditMemberBalanceAdd', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_credit')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportMemberExcel"
                                                                            @if(is_array($user->permissions) && in_array('exportMemberExcel', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportMemberPDF"
                                                                            @if(is_array($user->permissions) && in_array('exportMemberPDF', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pdf_download')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportRenewMemberList"
                                                                            @if(is_array($user->permissions) && in_array('reportRenewMemberList', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.memberships_renewal_report')}}</span></label>
                                            </div>

                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportDetailMemberList"
                                                                            @if(is_array($user->permissions) && in_array('reportDetailMemberList', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.memberships_detail_report')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportSubscriptionMemberList"
                                                                            @if(is_array($user->permissions) && in_array('reportSubscriptionMemberList', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.report_subscriptions')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportTodayMemberList"
                                                                            @if(is_array($user->permissions) && in_array('reportTodayMemberList', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.client_attendees_today')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportExpireMemberList"
                                                                            @if(is_array($user->permissions) && in_array('reportExpireMemberList', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.memberships_expire_report')}}</span></label>
                                            </div>


                                        </div>
                                    </div>
                                    <div class="tab-pane " id="moneybox">
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-2 ">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createMoneyBoxAdd"
                                                                            @if(is_array($user->permissions) && in_array('createMoneyBoxAdd', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_to_money_box')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="showMoneyBox"
                                                                            @if(is_array($user->permissions) && in_array('showMoneyBox', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.moneybox_show')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createMoneyBoxWithdraw"
                                                                            @if(is_array($user->permissions) && in_array('createMoneyBoxWithdraw', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.withdraw_from_money_box')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createMoneyBoxWithdrawEarnings"
                                                                            @if(is_array($user->permissions) && in_array('createMoneyBoxWithdrawEarnings', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.withdraw_earning')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listMoneyBox"
                                                                            @if(is_array($user->permissions) && in_array('listMoneyBox', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.money_report')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listMoneyBoxDaily"
                                                                            @if(is_array($user->permissions) && in_array('listMoneyBoxDaily', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.money_daily_report')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editPaymentTypeOrderMoneybox"
                                                                            @if(is_array($user->permissions) && in_array('editPaymentTypeOrderMoneybox', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.payment_type_edit')}}</span></label>
                                            </div>

                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportMoneyBoxExcel"
                                                                            @if(is_array($user->permissions) && in_array('exportMoneyBoxExcel', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportMoneyBoxPDF"
                                                                            @if(is_array($user->permissions) && in_array('exportMoneyBoxPDF', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pdf_download')}}</span></label>
                                            </div>

                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportMoneyboxTax"
                                                                            @if(is_array($user->permissions) && in_array('reportMoneyboxTax', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.moneybox_tax')}}</span></label>
                                            </div>

                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportMoneyBoxTaxExcel"
                                                                            @if(is_array($user->permissions) && in_array('exportMoneyBoxTaxExcel', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download')}} {{ trans('sw.moneybox_tax')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportMoneyBoxTaxPDF"
                                                                            @if(is_array($user->permissions) && in_array('exportMoneyBoxTaxPDF', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pdf_download')}} {{ trans('sw.moneybox_tax')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="uploadContractGymOrder"
                                                                            @if(is_array($user->permissions) && in_array('uploadContractGymOrder', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.upload_subscription_contract')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="signOrderSubscription"
                                                                            @if(is_array($user->permissions) && in_array('signOrderSubscription', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.signature_contract')}}</span></label>
                                            </div>

                                        </div>
                                    </div>


                                    <div class="tab-pane " id="pt">
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-3 ">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listPTSubscription"
                                                                            @if(is_array($user->permissions) && in_array('listPTSubscription', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pt_subscriptions')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createPTSubscription"
                                                                            @if(is_array($user->permissions) && in_array('createPTSubscription', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_pt_subscriptions')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editPTSubscription"
                                                                            @if(is_array($user->permissions) && in_array('editPTSubscription', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit_pt_subscriptions')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deletePTSubscription"
                                                                            @if(is_array($user->permissions) && in_array('deletePTSubscription', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete_pt_subscriptions')}}</span></label>
                                            </div>


                                            <div class="clearfix"></div>


                                            <div class="col-lg-3 ">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listPTClass"
                                                                            @if(is_array($user->permissions) && in_array('listPTClass', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pt_classes')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createPTClass"
                                                                            @if(is_array($user->permissions) && in_array('createPTClass', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_pt_classes')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editPTClass"
                                                                            @if(is_array($user->permissions) && in_array('editPTClass', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit_pt_classes')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deletePTClass"
                                                                            @if(is_array($user->permissions) && in_array('deletePTClass', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete_pt_classes')}}</span></label>
                                            </div>

                                            <div class="clearfix"></div>


                                            <div class="col-lg-3 ">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listPTTrainer"
                                                                            @if(is_array($user->permissions) && in_array('listPTTrainer', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pt_trainers')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createPTTrainer"
                                                                            @if(is_array($user->permissions) && in_array('createPTTrainer', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_pt_trainers')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editPTTrainer"
                                                                            @if(is_array($user->permissions) && in_array('editPTTrainer', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit_pt_trainers')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deletePTTrainer"
                                                                            @if(is_array($user->permissions) && in_array('deletePTTrainer', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete_pt_trainers')}}</span></label>
                                            </div>

                                            <div class="clearfix"></div>


                                            <div class="col-lg-3 ">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listPTMember"
                                                                            @if(is_array($user->permissions) && in_array('listPTMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pt_members')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createPTMember"
                                                                            @if(is_array($user->permissions) && in_array('createPTMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_pt_members')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editPTMember"
                                                                            @if(is_array($user->permissions) && in_array('editPTMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit_pt_members')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deletePTMember"
                                                                            @if(is_array($user->permissions) && in_array('deletePTMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete_pt_members')}}</span></label>
                                            </div>

                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editPTMemberDiscount"
                                                                            @if(is_array($user->permissions) && in_array('editPTMemberDiscount', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_discount')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editPTMemberDiscountGroup"
                                                                            @if(is_array($user->permissions) && in_array('editPTMemberDiscountGroup', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.group_discount_add')}}</span></label>
                                            </div>

                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createPTMemberPayAmountRemainingForm"
                                                                            @if(is_array($user->permissions) && in_array('createPTMemberPayAmountRemainingForm', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pay_remaining')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createTrainerPayPercentageAmountForm"
                                                                            @if(is_array($user->permissions) && in_array('createTrainerPayPercentageAmountForm', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pay_to_trainer')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportPTSubscriptionMemberList"
                                                                            @if(is_array($user->permissions) && in_array('reportPTSubscriptionMemberList', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.report_pt_subscriptions')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listPTTrainerReport"
                                                                            @if(is_array($user->permissions) && in_array('listPTTrainerReport', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pt_training_calender')}}</span></label>
                                            </div>

                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportTodayPTMemberList"
                                                                            @if(is_array($user->permissions) && in_array('reportTodayPTMemberList', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.client_pt_attendees_today')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editPTTrainerSubscription"
                                                                            @if(is_array($user->permissions) && in_array('editPTTrainerSubscription', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit_pt_trainers_schedule')}}</span></label>
                                            </div>

                                        </div>
                                    </div>


                                    <div class="tab-pane " id="training">
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listTrainingPlan"
                                                                            @if(is_array($user->permissions) && in_array('listTrainingPlan', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_plans')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createTrainingPlan"
                                                                            @if(is_array($user->permissions) && in_array('createTrainingPlan', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_plan_add')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editTrainingPlan"
                                                                            @if(is_array($user->permissions) && in_array('editTrainingPlan', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_plan_edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteTrainingPlan"
                                                                            @if(is_array($user->permissions) && in_array('deleteTrainingPlan', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_plan_delete')}}</span></label>
                                            </div>




                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listTrainingMember"
                                                                            @if(is_array($user->permissions) && in_array('listTrainingMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_members')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createTrainingTrainMember"
                                                                            @if(is_array($user->permissions) && in_array('createTrainingTrainMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_plan_training')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createTrainingDietMember"
                                                                            @if(is_array($user->permissions) && in_array('createTrainingDietMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_plan_diet')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editTrainingMember"
                                                                            @if(is_array($user->permissions) && in_array('editTrainingMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_member_edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteTrainingMember"
                                                                            @if(is_array($user->permissions) && in_array('deleteTrainingMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_member_delete')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editTrainingMemberDiscount"
                                                                            @if(is_array($user->permissions) && in_array('editTrainingMemberDiscount', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_discount')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editTrainingMemberDiscountGroup"
                                                                            @if(is_array($user->permissions) && in_array('editTrainingMemberDiscountGroup', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.group_discount_add')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportTrainingMemberExcel"
                                                                            @if(is_array($user->permissions) && in_array('exportTrainingMemberExcel', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download')}} {{ trans('sw.training_plans')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportTrainingMemberPDF"
                                                                            @if(is_array($user->permissions) && in_array('exportTrainingMemberPDF', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pdf_download')}} {{ trans('sw.training_plans')}}</span></label>
                                            </div>


                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listTrainingTrack"
                                                                            @if(is_array($user->permissions) && in_array('listTrainingTrack', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_tracks')}}</span></label>
                                            </div>

                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createTrainingTrack"
                                                                            @if(is_array($user->permissions) && in_array('createTrainingTrack', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_track_add')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editTrainingTrack"
                                                                            @if(is_array($user->permissions) && in_array('editTrainingTrack', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_track_edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteTrainingTrack"
                                                                            @if(is_array($user->permissions) && in_array('deleteTrainingTrack', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_track_delete')}}</span></label>
                                            </div>



                                        </div>
                                    </div>


                                    <div class="tab-pane " id="store">
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listStoreProducts"
                                                                            @if(is_array($user->permissions) && in_array('listStoreProducts', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createStoreProduct"
                                                                            @if(is_array($user->permissions) && in_array('createStoreProduct', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add')}}</span></label>
                                            </div>

                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editStoreProduct"
                                                                            @if(is_array($user->permissions) && in_array('editStoreProduct', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteStoreProduct"
                                                                            @if(is_array($user->permissions) && in_array('deleteStoreProduct', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.store_product_delete')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editStoreDiscount"
                                                                            @if(is_array($user->permissions) && in_array('editStoreDiscount', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_discount')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editStoreDiscountGroup"
                                                                            @if(is_array($user->permissions) && in_array('editStoreDiscountGroup', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.group_discount_add')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createStoreOrder"
                                                                            @if(is_array($user->permissions) && in_array('createStoreOrder', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.sell_products')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listStoreOrders"
                                                                            @if(is_array($user->permissions) && in_array('listStoreOrders', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.sales_invoices')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteStoreOrder"
                                                                            @if(is_array($user->permissions) && in_array('deleteStoreOrder', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.store_orders_refund')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listStoreOrderVendor"
                                                                            @if(is_array($user->permissions) && in_array('listStoreOrderVendor', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.purchase_invoices')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="storePurchasesBill"
                                                                            @if(is_array($user->permissions) && in_array('storePurchasesBill', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.store_order_vendor_add')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteStoreOrderVendor"
                                                                            @if(is_array($user->permissions) && in_array('deleteStoreOrderVendor', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.store_order_vendor_delete')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportStoreList"
                                                                            @if(is_array($user->permissions) && in_array('reportStoreList', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.store_report')}}</span></label>
                                            </div>


                                        </div>
                                    </div>


                                    <div class="tab-pane " id="potentialMembers">
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listPotentialMember"
                                                                            @if(is_array($user->permissions) && in_array('listPotentialMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createPotentialMember"
                                                                            @if(is_array($user->permissions) && in_array('createPotentialMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editPotentialMember"
                                                                            @if(is_array($user->permissions) && in_array('editPotentialMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deletePotentialMember"
                                                                            @if(is_array($user->permissions) && in_array('deletePotentialMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportPotentialMemberExcel"
                                                                            @if(is_array($user->permissions) && in_array('exportPotentialMemberExcel', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportPotentialMemberPDF"
                                                                            @if(is_array($user->permissions) && in_array('exportPotentialMemberPDF', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pdf_download')}}</span></label>
                                            </div>


                                        </div>
                                    </div>

                                    <div class="tab-pane " id="reservationMembers">
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listReservationMember"
                                                                            @if(is_array($user->permissions) && in_array('listReservationMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteReservationMember"
                                                                            @if(is_array($user->permissions) && in_array('deleteReservationMember', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete')}}</span></label>
                                            </div>


                                        </div>
                                    </div>


                                    <div class="tab-pane " id="banners">
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listBanner"
                                                                            @if(is_array($user->permissions) && in_array('listBanner', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createBanner"
                                                                            @if(is_array($user->permissions) && in_array('createBanner', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editBanner"
                                                                            @if(is_array($user->permissions) && in_array('editBanner', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteBanner"
                                                                            @if(is_array($user->permissions) && in_array('deleteBanner', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportBannerExcel"
                                                                            @if(is_array($user->permissions) && in_array('exportBannerExcel', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportBannerPDF"
                                                                            @if(is_array($user->permissions) && in_array('exportBannerPDF', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pdf_download')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listGallery"
                                                                            @if(is_array($user->permissions) && in_array('listGallery', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.gallery')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editSettingUploadImage"
                                                                            @if(is_array($user->permissions) && in_array('editSettingUploadImage', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.gallery_add')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editSettingDeleteUploadImage"
                                                                            @if(is_array($user->permissions) && in_array('editSettingDeleteUploadImage', $user->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.gallery_delete')}}</span></label>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                </div>
                    <!--end::Tab content-->
                            </div>
                <!--end::Card body-->
                        </div>
            <!--end::Permissions-->
       <div class="clearfix"><br/></div>
            <div class=" card-flush py-4">
<!--begin::Form Actions-->
<div class="d-flex justify-content-end mt-5">
            <!--begin::Button-->
            <button type="reset" class="btn btn-light me-5">{{ trans('admin.reset')}}</button>
            <!--end::Button-->
            <!--begin::Button-->
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">{{ trans('global.save')}}</span>
                <span class="indicator-progress">Please wait... 
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                </span>
            </button>
            <!--end::Button-->
        </div>
        <!--end::Form Actions-->        
            </div>
        </div>
        </div>
        

        

        
        
    </form>




    <!-- Modal Camera with effects -->
    <div class="modal" id="modalCamera">
        <div class="modal-dialog modal-dialog-scrollable " role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">{{ trans('sw.camera_snapshot')}}</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                                aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body text-center">
                    <div class="img-fluid" id="my_camera"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-primary" onclick="take_snapshot()" data-dismiss="modal" type="button">
                        {{ trans('sw.camera_snapshot')}}
                    </button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal"
                            type="button">  {{ trans('sw.exist')}}</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Camera with effects-->
@endsection


@section('sub_scripts')
    {{-- Required JavaScript Libraries --}}
    <script src="{{asset('/')}}resources/assets/admin/global/scripts/metronic.js" type="text/javascript"></script>
    <script src="{{asset('/')}}resources/assets/admin/pages/scripts/components-pickers.js"></script>
    <script type="text/javascript" src="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
    
    {{-- File Upload Scripts --}}
    <script src="{{asset('/')}}resources/assets/admin/global/scripts/js/fileupload.js"></script>
    <script src="{{asset('/')}}resources/assets/admin/global/scripts/js/file-upload.js"></script>

    {{-- Form Initialization --}}
    <script>
        $(document).ready(function() {
            // Initialize time pickers and components
            ComponentsPickers.init();
            
            // Initialize dropify file upload
        $('.dropify-infos-message').html("{{ trans('sw.upload_image')}}");
        $('.dropify-message p:first').html("{{ trans('sw.upload_image')}}");
        $('.dropify-clear').html("{{ trans('sw.remove')}}");
            
            // Initialize bootstrap tabs
            $('.nav-tabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                // Tab has been activated, any additional logic can go here
            });
        });

        // Camera modal effect handler
        $('.modal-effect').on('click', function (e) {
            e.preventDefault();
            var effect = $(this).attr('data-effect');
            $('#modalCamera').addClass(effect);
        });
        
        // Permission group selector handler
        $(document).ready(function() {
            $('#permission_group_id').select2({
                placeholder: "{{ trans('sw.select_permission_group')}}",
                allowClear: true,
                width: '100%'
            });
            
            $('#permission_group_id').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var permissions = selectedOption.data('permissions');
                
                if (permissions && permissions.length > 0) {
                    // Uncheck all permissions first
                    $('input[name="permissions[]"]').prop('checked', false);
                    
                    // Check permissions from the group
                    permissions.forEach(function(permission) {
                        $('input[name="permissions[]"][value="' + permission + '"]').prop('checked', true);
                    });
                } else {
                    // If "Custom Permissions" is selected, keep current selections
                    // User can manually check/uncheck as needed
                }
            });
        });
    </script>

@endsection

