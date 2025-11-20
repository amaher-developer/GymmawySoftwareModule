@php
if(!isset($permission_group)){
    $permission_group = new stdClass();
    if(isset($user)){
        $permission_group->permissions = $user->permissions ?? [];
    } else {
        $permission_group->permissions = [];
    }
}
// Ensure permissions is always set and is an array
if(!isset($permission_group->permissions)){
    $permission_group->permissions = [];
}
@endphp

<style>

 /* Enhanced permissions styling */
 .permission-toolbar{
        display:flex;
        gap:10px;
        align-items:center;
        flex-wrap:wrap;
    }
    .permission-search{
        max-width:320px;
    }
    .perm-grid .ckbox{
        display:flex;
        align-items:center;
        gap:8px;
        background:#f8f9fa;
        border:1px solid #eef2f7;
        border-radius:8px;
        padding:8px 12px;
        margin-bottom:10px;
        transition:all .15s ease-in-out;
    }
    .perm-grid .ckbox:hover{
        background:#f1f4f8;
        border-color:#e5ebf2;
    }
    .perm-section-title{
        display:flex;
        align-items:center;
        gap:8px;
        font-weight:600;
        color:#3f4254;
        margin:18px 0 8px;
    }
    .perm-badge{
        background:#eef6ff;
        color:#1d65d8;
        border-radius:999px;
        font-size:12px;
        padding:2px 8px;
    }
    .perm-hr{border:0;height:1px;background:#eef2f7;margin:14px 0;}
.permission-section-select{ min-width:220px; }
.permission-tabs{ overflow-x:auto; white-space:nowrap; scrollbar-width: thin; padding-bottom:4px; border-bottom:1px solid #eef2f7; margin-bottom:10px; }
.permission-tabs .nav-item{ display:inline-block; }
</style>

<!--begin::Permissions-->
<div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa fa-gift me-2"></i>
                        <h2>{{ trans('sw.permissions')}}</h2>
                    </div>
                    
                    <div class="card-toolbar">
                        <div class="permission-toolbar">
                            <input type="text" id="permission_search" class="form-control permission-search" placeholder="{{ trans('sw.search_on') }}" />
                            <select id="permission_section_select" class="form-select permission-section-select">
                                <option value="admins">{{ trans('sw.users') }}</option>
                                <option value="memberships">{{ trans('sw.memberships') }}</option>
                                @if(@$mainSettings->active_activity)
                                <option value="activities">{{ trans('sw.activities') }}</option>
                                @endif
                                <option value="unsubscribedClients">{{ trans('sw.daily_clients') }}</option>
                                <option value="subscribedClients">{{ trans('sw.subscribed_clients') }}</option>
                                <option value="moneybox">{{ trans('sw.moneybox') }}</option>
                                @if(@$mainSettings->active_pt)
                                <option value="pt">{{ trans('sw.pt') }}</option>
                                @endif
                                @if(@$mainSettings->active_training)
                                <option value="training">{{ trans('sw.training') }}</option>
                                @endif
                                @if(@$mainSettings->active_store)
                                <option value="store">{{ trans('sw.store') }}</option>
                                @endif
                                <option value="potentialMembers">{{ trans('sw.potential_clients') }}</option>
                                @if(@$mainSettings->active_website || @$mainSettings->active_mobile)
                                <option value="reservationMembers">{{ trans('sw.reservation_clients') }}</option>
                                @endif
                                @if(@$mainSettings->active_mobile)
                                <option value="banners">{{ trans('sw.media') }}</option>
                                @endif
                                @if(@$mainSettings->active_activity_reservation)
                                <option value="reservations">{{ trans('sw.reservations') }}</option>
                                @endif
                                @if(@$mainSettings->active_loyalty)
                                <option value="loyalty">{{ trans('sw.loyalty_points') }}</option>
                                @endif
                                <option value="statistics">{{ trans('sw.statistics') }}</option>
                                <option value="reports">{{ trans('sw.reports') }}</option>
                                @if(@$mainSettings->active_sms || @$mainSettings->active_telegram || @$mainSettings->active_wa || @$mainSettings->active_notification || @$mainSettings->active_mobile)
                                <option value="messages">{{ trans('sw.messages') }}</option>
                                @endif
                                <option value="settings">{{ trans('sw.settings') }}</option>
                                @if(@$mainSettings->active_ai)
                                <option value="aiReports">{{ trans('sw.ai_reports') }}</option>
                                @endif
                            </select>
                            <span class="perm-badge" id="permission_count_badge">0 selected</span>
                        </div>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0 perm-grid">
                    
                    
                    <!--begin::Tab navigation-->
                    <ul class="nav nav-line-tabs nav-stretch fs-6 fw-semibold permission-tabs">
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
                        @if(@$mainSettings->active_activity)
                        <li class="nav-item">
                            <a class="nav-link" href="#activities" data-toggle="tab">
                                {{ trans('sw.activities')}}
                            </a>
                        </li>
                        @endif
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
                        @if(@$mainSettings->active_activity_reservation)
                        <li class="nav-item">
                            <a class="nav-link" href="#reservations" data-toggle="tab">
                                {{ trans('sw.reservations') }}
                            </a>
                        </li>
                        @endif
                        @if(@$mainSettings->active_loyalty)
                        <li class="nav-item">
                            <a class="nav-link" href="#loyalty" data-toggle="tab">
                                {{ trans('sw.loyalty_points') }}
                            </a>
                        </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link" href="#statistics" data-toggle="tab">
                                {{ trans('sw.statistics') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#reports" data-toggle="tab">
                                {{ trans('sw.reports') }}
                            </a>
                        </li>
                        @if(@$mainSettings->active_sms || @$mainSettings->active_telegram || @$mainSettings->active_wa || @$mainSettings->active_notification || @$mainSettings->active_mobile)
                        <li class="nav-item">
                            <a class="nav-link" href="#messages" data-toggle="tab">
                                {{ trans('sw.messages') }}
                            </a>
                        </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link" href="#settings" data-toggle="tab">
                                {{ trans('sw.settings') }}
                            </a>
                        </li>
                        @if(@$mainSettings->active_ai)
                        <li class="nav-item">
                            <a class="nav-link" href="#aiReports" data-toggle="tab">
                                {{ trans('sw.ai_reports') }}
                            </a>
                        </li>
                        @endif
                                </ul>
                    <!--end::Tab navigation-->

                    <!--begin::Tab content-->
                                <div class="tab-content">
                                    <div class="tab-pane active" id="admins">
                                        <div class="perm-section-title">
                                            <i class="ki-outline ki-shield-tick"></i> {{ trans('sw.users') }}
                                        </div>
                                        <div class="row pt-2 pb-2">
                                            <div class="col-lg-2 ">
                                                <label class="ckbox">
                                                    <input name="permissions[]"
                                                                            value="listUser"
                                                                            @if(@in_array('listUser', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span> </label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createUser"
                                                                            @if(@in_array('createUser', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editUser"
                                                                            @if(@in_array('editUser', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteUser"
                                                                            @if(@in_array('deleteUser', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportUserExcel"
                                                                            @if(@in_array('exportUserExcel', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportUserPDF"
                                                                            @if(@in_array('exportUserPDF', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pdf_download')}}</span></label>
                                            </div>


                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportUserAttendeesList"
                                                                            @if(@in_array('reportUserAttendeesList', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.attendees_report')}}</span></label>
                                            </div>

                                            <div class="col-lg-12 mt-3">
                                                <hr class="perm-hr"/>
                                                <h6 class="mb-2">{{ trans('sw.employee_transactions') }}</h6>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listUserTransaction"
                                                                            @if(@in_array('listUserTransaction', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createUserTransaction"
                                                                            @if(@in_array('createUserTransaction', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editUserTransaction"
                                                                            @if(@in_array('editUserTransaction', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteUserTransaction"
                                                                            @if(@in_array('deleteUserTransaction', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete')}}</span></label>
                                            </div>

                                            <div class="col-lg-12 mt-3">
                                                <hr class="perm-hr"/>
                                                <h6 class="mb-2">{{ trans('sw.permission_groups') }}</h6>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listUserPermission"
                                                                            @if(@in_array('listUserPermission', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createUserPermission"
                                                                            @if(@in_array('createUserPermission', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editUserPermission"
                                                                            @if(@in_array('editUserPermission', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteUserPermission"
                                                                            @if(@in_array('deleteUserPermission', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete')}}</span></label>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="tab-pane " id="memberships">
                                        <hr class="perm-hr"/>
                                        <div class="perm-section-title">
                                            <i class="ki-outline ki-map"></i> {{ trans('sw.memberships') }}
                                        </div>
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-2 ">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listSubscription"
                                                                            @if(@in_array('listSubscription', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createSubscription"
                                                                            @if(@in_array('createSubscription', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editSubscription"
                                                                            @if(@in_array('editSubscription', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteSubscription"
                                                                            @if(@in_array('deleteSubscription', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportSubscriptionExcel"
                                                                            @if(@in_array('exportSubscriptionExcel', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportSubscriptionPDF"
                                                                            @if(@in_array('exportSubscriptionPDF', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pdf_download')}}</span></label>
                                            </div>


                                        </div>
                                    </div>
                                    @if(@$mainSettings->active_activity)
                                    <div class="tab-pane " id="activities">
                                        <hr class="perm-hr"/>
                                        <div class="perm-section-title">
                                            <i class="ki-outline ki-questionnaire-tablet"></i> {{ trans('sw.activities') }}
                                        </div>
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-2 ">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listActivity"
                                                                            @if(@in_array('listActivity', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createActivity"
                                                                            @if(@in_array('createActivity', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editActivity"
                                                                            @if(@in_array('editActivity', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteActivity"
                                                                            @if(@in_array('deleteActivity', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportActivityExcel"
                                                                            @if(@in_array('exportActivityExcel', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportActivityPDF"
                                                                            @if(@in_array('exportActivityPDF', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pdf_download')}}</span></label>
                                            </div>

                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportTodayNonMemberList"
                                                                            @if(@in_array('reportTodayNonMemberList', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.non_client_attendees_today')}}</span></label>
                                            </div>


                                        </div>
                                    </div>
                                    @endif
                                    <div class="tab-pane " id="unsubscribedClients">
                                        <hr class="perm-hr"/>
                                        <div class="perm-section-title">
                                            <i class="ki-outline ki-user"></i> {{ trans('sw.daily_clients') }}
                                        </div>
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listNonMember"
                                                                            @if(@in_array('listNonMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createNonMember"
                                                                            @if(@in_array('createNonMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editNonMember"
                                                                            @if(@in_array('editNonMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteNonMember"
                                                                            @if(@in_array('deleteNonMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editNonMemberDiscount"
                                                                            @if(@in_array('editNonMemberDiscount', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_discount')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editNonMemberDiscountGroup"
                                                                            @if(@in_array('editNonMemberDiscountGroup', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.group_discount_add')}}</span></label>
                                            </div>

                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listNonMemberReport"
                                                                            @if(@in_array('listNonMemberReport', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.activities_calender')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportNonMemberExcel"
                                                                            @if(@in_array('exportNonMemberExcel', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportNonMemberPDF"
                                                                            @if(@in_array('exportNonMemberPDF', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pdf_download')}}</span></label>
                                            </div>


                                        </div>
                                    </div>
                                    <div class="tab-pane " id="subscribedClients">
                                        <hr class="perm-hr"/>
                                        <div class="perm-section-title">
                                            <i class="ki-outline ki-people"></i> {{ trans('sw.subscribed_clients') }}
                                        </div>
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-2 ">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listMember"
                                                                            @if(@in_array('listMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createMember"
                                                                            @if(@in_array('createMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editMember"
                                                                            @if(@in_array('editMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteMember"
                                                                            @if(@in_array('deleteMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="memberSubscriptionEdit"
                                                                            @if(@in_array('memberSubscriptionEdit', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit_accounts')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="memberSubscriptionRenewStore"
                                                                            @if(@in_array('memberSubscriptionRenewStore', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.renew_accounts')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteMemberSubscription"
                                                                            @if(@in_array('deleteMemberSubscription', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete_accounts')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="freezeMember"
                                                                            @if(@in_array('freezeMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.freeze_accounts')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="unfreezeMember"
                                                                            @if(@in_array('unfreezeMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.unfreeze_accounts')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listBlockMember"
                                                                            @if(@in_array('listBlockMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span> <small>({{ trans('sw.block_list') }})</small></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createBlockMember"
                                                                            @if(@in_array('createBlockMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add')}}</span> <small>({{ trans('sw.block_list') }})</small></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editBlockMember"
                                                                            @if(@in_array('editBlockMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit')}}</span> <small>({{ trans('sw.block_list') }})</small></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteBlockMember"
                                                                            @if(@in_array('deleteBlockMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete')}}</span> <small>({{ trans('sw.block_list') }})</small></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createMemberPayAmountRemainingForm"
                                                                            @if(@in_array('createMemberPayAmountRemainingForm', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pay_remaining')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editMemberDiscount"
                                                                            @if(@in_array('editMemberDiscount', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_discount')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editMemberDiscountGroup"
                                                                            @if(@in_array('editMemberDiscountGroup', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.group_discount_add')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="creditMemberBalanceAdd"
                                                                            @if(@in_array('creditMemberBalanceAdd', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_credit')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportMemberExcel"
                                                                            @if(@in_array('exportMemberExcel', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportMemberPDF"
                                                                            @if(@in_array('exportMemberPDF', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pdf_download')}}</span></label>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="tab-pane " id="moneybox">
                                        <hr class="perm-hr"/>
                                        <div class="perm-section-title">
                                            <i class="ki-outline ki-wallet"></i> {{ trans('sw.moneybox') }}
                                        </div>
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-2 ">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createMoneyBoxAdd"
                                                                            @if(@in_array('createMoneyBoxAdd', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_to_money_box')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="showMoneyBox"
                                                                            @if(@in_array('showMoneyBox', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.moneybox_show')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createMoneyBoxWithdraw"
                                                                            @if(@in_array('createMoneyBoxWithdraw', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.withdraw_from_money_box')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createMoneyBoxWithdrawEarnings"
                                                                            @if(@in_array('createMoneyBoxWithdrawEarnings', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.withdraw_earning')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editPaymentTypeOrderMoneybox"
                                                                            @if(@in_array('editPaymentTypeOrderMoneybox', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.payment_type_edit')}}</span></label>
                                            </div>

                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportMoneyBoxExcel"
                                                                            @if(@in_array('exportMoneyBoxExcel', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportMoneyBoxPDF"
                                                                            @if(@in_array('exportMoneyBoxPDF', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pdf_download')}}</span></label>
                                            </div>

                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportMoneyboxTax"
                                                                            @if(@in_array('reportMoneyboxTax', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.moneybox_tax')}}</span></label>
                                            </div>
                                            @if(config('sw_billing.zatca_enabled'))
                                                <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                    <label class="ckbox"><input name="permissions[]"
                                                                                value="reportZatcaInvoices"
                                                                                @if(@in_array('reportZatcaInvoices', $permission_group->permissions)) checked @endif
                                                                                type="checkbox"> <span>{{ trans('sw.zatca_invoices_report')}}</span></label>
                                                </div>
                                            @endif

                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportMoneyBoxTaxExcel"
                                                                            @if(@in_array('exportMoneyBoxTaxExcel', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download')}} {{ trans('sw.moneybox_tax')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportMoneyBoxTaxPDF"
                                                                            @if(@in_array('exportMoneyBoxTaxPDF', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pdf_download')}} {{ trans('sw.moneybox_tax')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="uploadContractGymOrder"
                                                                            @if(@in_array('uploadContractGymOrder', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.upload_subscription_contract')}}</span></label>
                                            </div>
                                            <div class="col-lg-2 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="signOrderSubscription"
                                                                            @if(@in_array('signOrderSubscription', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.signature_contract')}}</span></label>
                                            </div>

                                        </div>
                                    </div>


                                    <div class="tab-pane " id="pt">
                                        <hr class="perm-hr"/>
                                        <div class="perm-section-title">
                                            <i class="ki-outline ki-security-user"></i> {{ trans('sw.pt') }}
                                        </div>
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-3 ">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listPTSubscription"
                                                                            @if(@in_array('listPTSubscription', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pt_subscriptions')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createPTSubscription"
                                                                            @if(@in_array('createPTSubscription', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_pt_subscriptions')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editPTSubscription"
                                                                            @if(@in_array('editPTSubscription', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit_pt_subscriptions')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deletePTSubscription"
                                                                            @if(@in_array('deletePTSubscription', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete_pt_subscriptions')}}</span></label>
                                            </div>


                                            <div class="clearfix"></div>


                                            <div class="col-lg-3 ">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listPTClass"
                                                                            @if(@in_array('listPTClass', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pt_classes')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createPTClass"
                                                                            @if(@in_array('createPTClass', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_pt_classes')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editPTClass"
                                                                            @if(@in_array('editPTClass', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit_pt_classes')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deletePTClass"
                                                                            @if(@in_array('deletePTClass', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete_pt_classes')}}</span></label>
                                            </div>

                                            <div class="clearfix"></div>


                                            <div class="col-lg-3 ">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listPTTrainer"
                                                                            @if(@in_array('listPTTrainer', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pt_trainers')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createPTTrainer"
                                                                            @if(@in_array('createPTTrainer', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_pt_trainers')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editPTTrainer"
                                                                            @if(@in_array('editPTTrainer', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit_pt_trainers')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deletePTTrainer"
                                                                            @if(@in_array('deletePTTrainer', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete_pt_trainers')}}</span></label>
                                            </div>

                                            <div class="clearfix"></div>


                                            <div class="col-lg-3 ">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listPTMember"
                                                                            @if(@in_array('listPTMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pt_members')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createPTMember"
                                                                            @if(@in_array('createPTMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_pt_members')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editPTMember"
                                                                            @if(@in_array('editPTMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit_pt_members')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deletePTMember"
                                                                            @if(@in_array('deletePTMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete_pt_members')}}</span></label>
                                            </div>

                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editPTMemberDiscount"
                                                                            @if(@in_array('editPTMemberDiscount', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_discount')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editPTMemberDiscountGroup"
                                                                            @if(@in_array('editPTMemberDiscountGroup', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.group_discount_add')}}</span></label>
                                            </div>

                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createPTMemberPayAmountRemainingForm"
                                                                            @if(@in_array('createPTMemberPayAmountRemainingForm', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pay_remaining')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createTrainerPayPercentageAmountForm"
                                                                            @if(@in_array('createTrainerPayPercentageAmountForm', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pay_to_trainer')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportPTSubscriptionMemberList"
                                                                            @if(@in_array('reportPTSubscriptionMemberList', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.report_pt_subscriptions')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listPTTrainerReport"
                                                                            @if(@in_array('listPTTrainerReport', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pt_training_calender')}}</span></label>
                                            </div>

                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportTodayPTMemberList"
                                                                            @if(@in_array('reportTodayPTMemberList', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.client_pt_attendees_today')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editPTTrainerSubscription"
                                                                            @if(@in_array('editPTTrainerSubscription', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit_pt_trainers_schedule')}}</span></label>
                                            </div>

                                            <div class="clearfix"></div>

                                            <!-- PT Sessions Permissions -->
                                            <div class="col-lg-12 mt-3">
                                                <hr class="perm-hr"/>
                                                <h6 class="mb-2">{{ trans('sw.pt_sessions') }}</h6>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listPTSessions"
                                                                            @if(@in_array('listPTSessions', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list') }} {{ trans('sw.pt_sessions') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="showPTSession"
                                                                            @if(@in_array('showPTSession', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.view') }} {{ trans('sw.pt_session') }}</span></label>
                                            </div>

                                        </div>
                                    </div>


                                    <div class="tab-pane " id="training">
                                        <hr class="perm-hr"/>
                                        <div class="perm-section-title">
                                            <i class="ki-outline ki-calendar-tick"></i> {{ trans('sw.training') }}
                                        </div>
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listTrainingPlan"
                                                                            @if(@in_array('listTrainingPlan', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_plans')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createTrainingPlan"
                                                                            @if(@in_array('createTrainingPlan', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_plan_add')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editTrainingPlan"
                                                                            @if(@in_array('editTrainingPlan', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_plan_edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteTrainingPlan"
                                                                            @if(@in_array('deleteTrainingPlan', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_plan_delete')}}</span></label>
                                            </div>




                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listTrainingMember"
                                                                            @if(@in_array('listTrainingMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_members')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createTrainingTrainMember"
                                                                            @if(@in_array('createTrainingTrainMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_plan_training')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createTrainingDietMember"
                                                                            @if(@in_array('createTrainingDietMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_plan_diet')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editTrainingMember"
                                                                            @if(@in_array('editTrainingMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_member_edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteTrainingMember"
                                                                            @if(@in_array('deleteTrainingMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_member_delete')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editTrainingMemberDiscount"
                                                                            @if(@in_array('editTrainingMemberDiscount', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_discount')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editTrainingMemberDiscountGroup"
                                                                            @if(@in_array('editTrainingMemberDiscountGroup', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.group_discount_add')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportTrainingMemberExcel"
                                                                            @if(@in_array('exportTrainingMemberExcel', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download')}} {{ trans('sw.training_plans')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportTrainingMemberPDF"
                                                                            @if(@in_array('exportTrainingMemberPDF', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pdf_download')}} {{ trans('sw.training_plans')}}</span></label>
                                            </div>


                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listTrainingTrack"
                                                                            @if(@in_array('listTrainingTrack', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_tracks')}}</span></label>
                                            </div>

                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createTrainingTrack"
                                                                            @if(@in_array('createTrainingTrack', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_track_add')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editTrainingTrack"
                                                                            @if(@in_array('editTrainingTrack', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_track_edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteTrainingTrack"
                                                                            @if(@in_array('deleteTrainingTrack', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.training_track_delete')}}</span></label>
                                            </div>

                                            <div class="clearfix"></div>

                                            <!-- Training Medicine Permissions -->
                                            <div class="col-lg-12 mt-3">
                                                <hr class="perm-hr"/>
                                                <h6 class="mb-2">{{ trans('sw.training_medicine') }}</h6>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listTrainingMedicine"
                                                                            @if(@in_array('listTrainingMedicine', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list') }} {{ trans('sw.training_medicine') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createTrainingMedicine"
                                                                            @if(@in_array('createTrainingMedicine', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add') }} {{ trans('sw.training_medicine') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editTrainingMedicine"
                                                                            @if(@in_array('editTrainingMedicine', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit') }} {{ trans('sw.training_medicine') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteTrainingMedicine"
                                                                            @if(@in_array('deleteTrainingMedicine', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete') }} {{ trans('sw.training_medicine') }}</span></label>
                                            </div>

                                            <div class="clearfix"></div>

                                            <!-- Training Member Log Permissions -->
                                            <div class="col-lg-12 mt-3">
                                                <hr class="perm-hr"/>
                                                <h6 class="mb-2">{{ trans('sw.training_member_log') }}</h6>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listTrainingMemberLog"
                                                                            @if(@in_array('listTrainingMemberLog', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list') }} {{ trans('sw.training_member_log') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="showTrainingMemberLog"
                                                                            @if(@in_array('showTrainingMemberLog', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.view') }} {{ trans('sw.training_member_log') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="addTrainingAssessment"
                                                                            @if(@in_array('addTrainingAssessment', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_assessment') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="addMemberTrainingPlan"
                                                                            @if(@in_array('addMemberTrainingPlan', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_training_plan') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="addMemberTrainingMedicine"
                                                                            @if(@in_array('addMemberTrainingMedicine', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_training_medicine') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="addMemberTrainingFile"
                                                                            @if(@in_array('addMemberTrainingFile', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_training_file') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="addMemberTrainingTrack"
                                                                            @if(@in_array('addMemberTrainingTrack', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_training_track') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="addMemberTrainingNote"
                                                                            @if(@in_array('addMemberTrainingNote', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_training_note') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="generateMemberAiPlan"
                                                                            @if(@in_array('generateMemberAiPlan', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.generate_ai_plan') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="generateAiPlan"
                                                                            @if(@in_array('generateAiPlan', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.generate_ai_plan_template') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="saveAiPlanTemplate"
                                                                            @if(@in_array('saveAiPlanTemplate', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.save_ai_plan_template') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="assignAiPlanToMember"
                                                                            @if(@in_array('assignAiPlanToMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.assign_ai_plan_to_member') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="downloadPlanPDF"
                                                                            @if(@in_array('downloadPlanPDF', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.download_plan_pdf') }}</span></label>
                                            </div>

                                        </div>
                                    </div>


                                    <div class="tab-pane " id="store">
                                        <hr class="perm-hr"/>
                                        <div class="perm-section-title">
                                            <i class="ki-outline ki-shop"></i> {{ trans('sw.store') }}
                                        </div>
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listStoreProducts"
                                                                            @if(@in_array('listStoreProducts', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createStoreProduct"
                                                                            @if(@in_array('createStoreProduct', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add')}}</span></label>
                                            </div>

                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editStoreProduct"
                                                                            @if(@in_array('editStoreProduct', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteStoreProduct"
                                                                            @if(@in_array('deleteStoreProduct', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.store_product_delete')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editStoreDiscount"
                                                                            @if(@in_array('editStoreDiscount', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add_discount')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editStoreDiscountGroup"
                                                                            @if(@in_array('editStoreDiscountGroup', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.group_discount_add')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createStoreOrderPOS"
                                                                            @if(@in_array('createStoreOrderPOS', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.sell_products')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listStoreOrders"
                                                                            @if(@in_array('listStoreOrders', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.sales_invoices')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteStoreOrder"
                                                                            @if(@in_array('deleteStoreOrder', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.store_orders_refund')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listStoreOrderVendor"
                                                                            @if(@in_array('listStoreOrderVendor', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.purchase_invoices')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="storePurchasesBill"
                                                                            @if(@in_array('storePurchasesBill', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.store_order_vendor_add')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteStoreOrderVendor"
                                                                            @if(@in_array('deleteStoreOrderVendor', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.store_order_vendor_delete')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportStoreList"
                                                                            @if(@in_array('reportStoreList', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.store_report')}}</span></label>
                                            </div>


                                        </div>
                                    </div>


                                    <div class="tab-pane " id="potentialMembers">
                                        <hr class="perm-hr"/>
                                        <div class="perm-section-title">
                                            <i class="ki-outline ki-user"></i> {{ trans('sw.potential_clients') }}
                                        </div>
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listPotentialMember"
                                                                            @if(@in_array('listPotentialMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createPotentialMember"
                                                                            @if(@in_array('createPotentialMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editPotentialMember"
                                                                            @if(@in_array('editPotentialMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deletePotentialMember"
                                                                            @if(@in_array('deletePotentialMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportPotentialMemberExcel"
                                                                            @if(@in_array('exportPotentialMemberExcel', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportPotentialMemberPDF"
                                                                            @if(@in_array('exportPotentialMemberPDF', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pdf_download')}}</span></label>
                                            </div>


                                        </div>
                                    </div>

                                    <div class="tab-pane " id="reservationMembers">
                                        <hr class="perm-hr"/>
                                        <div class="perm-section-title">
                                            <i class="ki-outline ki-calendar"></i> {{ trans('sw.reservation_clients') }}
                                        </div>
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listReservationMember"
                                                                            @if(@in_array('listReservationMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteReservationMember"
                                                                            @if(@in_array('deleteReservationMember', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete')}}</span></label>
                                            </div>


                                        </div>
                                    </div>

                                    <!--begin::Reservations Tab-->
                                    @if(@$mainSettings->active_activity_reservation)
                                    <div class="tab-pane" id="reservations">
                                        <hr class="perm-hr"/>
                                        <div class="perm-section-title">
                                            <i class="ki-outline ki-calendar-tick"></i> {{ trans('sw.reservations') }}
                                        </div>
                                        <div class="row pt-2 pb-2">
                                            <!-- Basic CRUD Permissions -->
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listReservation"
                                                                            @if(@in_array('listReservation', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createReservation"
                                                                            @if(@in_array('createReservation', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.create') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editReservation"
                                                                            @if(@in_array('editReservation', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('admin.edit') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteReservation"
                                                                            @if(@in_array('deleteReservation', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete') }}</span></label>
                                            </div>
                                            
                                            <!-- Status Change Permissions -->
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="changeReservationStatus"
                                                                            @if(@in_array('changeReservationStatus', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.change_status') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="confirmReservation"
                                                                            @if(@in_array('confirmReservation', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.confirm') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="cancelReservation"
                                                                            @if(@in_array('cancelReservation', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.cancel') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="attendReservation"
                                                                            @if(@in_array('attendReservation', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.attend') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="markMissedReservation"
                                                                            @if(@in_array('markMissedReservation', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.mark_missed') }}</span></label>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Reservations Tab-->
                                    @endif

                                    @if(@$mainSettings->active_loyalty)
                                    <div class="tab-pane " id="loyalty">
                                        <hr class="perm-hr"/>
                                        <div class="perm-section-title">
                                            <i class="ki-outline ki-star"></i> {{ trans('sw.loyalty_points') }}
                                        </div>
                                        <div class="row pt-2 pb-2">
                                            <div class="col-lg-12 mt-2">
                                                <h6 class="mb-2">{{ trans('sw.loyalty_point_rules') }}</h6>
                                            </div>
                                            <div class="col-lg-3">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listLoyaltyPointRule"
                                                                            @if(@in_array('listLoyaltyPointRule', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createLoyaltyPointRule"
                                                                            @if(@in_array('createLoyaltyPointRule', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editLoyaltyPointRule"
                                                                            @if(@in_array('editLoyaltyPointRule', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteLoyaltyPointRule"
                                                                            @if(@in_array('deleteLoyaltyPointRule', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete') }}</span></label>
                                            </div>

                       		        	<div class="col-lg-12 mt-4">
                                                <h6 class="mb-2">{{ trans('sw.loyalty_campaigns') }}</h6>
                                            </div>
                                            <div class="col-lg-3">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listLoyaltyCampaign"
                                                                            @if(@in_array('listLoyaltyCampaign', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createLoyaltyCampaign"
                                                                            @if(@in_array('createLoyaltyCampaign', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editLoyaltyCampaign"
                                                                            @if(@in_array('editLoyaltyCampaign', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteLoyaltyCampaign"
                                                                            @if(@in_array('deleteLoyaltyCampaign', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete') }}</span></label>
                                            </div>

                                            <div class="col-lg-12 mt-4">
                                                <h6 class="mb-2">{{ trans('sw.loyalty_transactions') }}</h6>
                                            </div>
                                            <div class="col-lg-3">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listLoyaltyTransaction"
                                                                            @if(@in_array('listLoyaltyTransaction', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createLoyaltyTransaction"
                                                                            @if(@in_array('createLoyaltyTransaction', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.manual_points_adjustment') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportLoyaltyTransaction"
                                                                            @if(@in_array('exportLoyaltyTransaction', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="loyaltyMemberHistory"
                                                                            @if(@in_array('loyaltyMemberHistory', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.loyalty_points_history') }}</span></label>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <div class="tab-pane " id="banners">
                                        <hr class="perm-hr"/>
                                        <div class="perm-section-title">
                                            <i class="ki-outline ki-social-media"></i> {{ trans('sw.media') }}
                                        </div>
                                        <div class="row pt-2 pb-2">


                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listBanner"
                                                                            @if(@in_array('listBanner', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createBanner"
                                                                            @if(@in_array('createBanner', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editBanner"
                                                                            @if(@in_array('editBanner', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteBanner"
                                                                            @if(@in_array('deleteBanner', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportBannerExcel"
                                                                            @if(@in_array('exportBannerExcel', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.excel_download')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="exportBannerPDF"
                                                                            @if(@in_array('exportBannerPDF', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pdf_download')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listGallery"
                                                                            @if(@in_array('listGallery', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.gallery')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editSettingUploadImage"
                                                                            @if(@in_array('editSettingUploadImage', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.gallery_add')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editSettingDeleteUploadImage"
                                                                            @if(@in_array('editSettingDeleteUploadImage', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.gallery_delete')}}</span></label>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="tab-pane " id="statistics">
                                        <hr class="perm-hr"/>
                                        <div class="perm-section-title">
                                            <i class="ki-outline ki-chart-simple"></i> {{ trans('sw.statistics') }}
                                        </div>
                                        <div class="row pt-2 pb-2">
                                            <div class="col-lg-3 ">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="statistics"
                                                                            @if(@in_array('statistics', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.general_statistics')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="memberSubscriptionStatistics"
                                                                            @if(@in_array('memberSubscriptionStatistics', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.member_subscription_statistics')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="ptSubscriptionStatistics"
                                                                            @if(@in_array('ptSubscriptionStatistics', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.pt_subscription_statistics')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="storeStatistics"
                                                                            @if(@in_array('storeStatistics', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.store_statistics')}}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="nonMemberStatistics"
                                                                            @if(@in_array('nonMemberStatistics', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.non_member_statistics')}}</span></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane " id="reports">
                                        <hr class="perm-hr"/>
                                        <div class="perm-section-title">
                                            <i class="ki-outline ki-graph-up"></i> {{ trans('sw.reports') }}
                                        </div>
                                        <div class="row pt-2 pb-2">
                                            <div class="col-lg-3 ">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listMoneyBox"
                                                                            @if(@in_array('listMoneyBox', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.money_report') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportMoneyboxTax"
                                                                            @if(@in_array('reportMoneyboxTax', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.moneybox_tax') }}</span></label>
                                            </div>
                                            @if(config('sw_billing.zatca_enabled'))
                                                <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                    <label class="ckbox"><input name="permissions[]"
                                                                                value="reportZatcaInvoices"
                                                                                @if(@in_array('reportZatcaInvoices', $permission_group->permissions)) checked @endif
                                                                                type="checkbox"> <span>{{ trans('sw.zatca_invoices_report') }}</span></label>
                                                </div>
                                            @endif
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportRenewMemberList"
                                                                            @if(@in_array('reportRenewMemberList', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.memberships_renewal_report') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportExpireMemberList"
                                                                            @if(@in_array('reportExpireMemberList', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.memberships_expire_report') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportDetailMemberList"
                                                                            @if(@in_array('reportDetailMemberList', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.memberships_detail_report') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportSubscriptionMemberList"
                                                                            @if(@in_array('reportSubscriptionMemberList', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.report_subscriptions') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportPTSubscriptionMemberList"
                                                                            @if(@in_array('reportPTSubscriptionMemberList', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.report_pt_subscriptions') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportTodayMemberList"
                                                                            @if(@in_array('reportTodayMemberList', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.client_attendees_today') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportTodayPTMemberList"
                                                                            @if(@in_array('reportTodayPTMemberList', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.client_pt_attendees_today') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportTodayNonMemberList"
                                                                            @if(@in_array('reportTodayNonMemberList', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.non_client_attendees_today') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportUserAttendeesList"
                                                                            @if(@in_array('reportUserAttendeesList', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.attendees_report') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportStoreList"
                                                                            @if(@in_array('reportStoreList', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.store_report') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="reportOnlinePaymentTransactionList"
                                                                            @if(@in_array('reportOnlinePaymentTransactionList', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.online_transaction_report') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listMoneyBoxDaily"
                                                                            @if(@in_array('listMoneyBoxDaily', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.money_daily_report') }}</span></label>
                                            </div>
                                        </div>
                                    </div>
                                    <!--begin::Messages Tab-->
                                    @if(@$mainSettings->active_sms || @$mainSettings->active_telegram || @$mainSettings->active_wa || @$mainSettings->active_notification || @$mainSettings->active_mobile)
                                    <div class="tab-pane " id="messages">
                                        <hr class="perm-hr"/>
                                        <div class="perm-section-title">
                                            <i class="ki-outline ki-message-notif"></i> {{ trans('sw.messages') }}
                                        </div>
                                        <div class="row pt-2 pb-2">
                                            @if(@$mainSettings->active_sms)
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createSMS"
                                                                            @if(@in_array('createSMS', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.sms_add') }}</span></label>
                                            </div>
                                            @endif
                                            @if(@$mainSettings->active_telegram)
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createTelegram"
                                                                            @if(@in_array('createTelegram', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.telegram_add') }}</span></label>
                                            </div>
                                            @endif
                                            @if(@$mainSettings->active_wa)
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createWA"
                                                                            @if(@in_array('createWA', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.wa_add') }}</span></label>
                                            </div>
                                            @if(@env('WA_GATEWAY') == 'ULTRA')
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createWAUltra"
                                                                            @if(@in_array('createWAUltra', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.wa_add') }} (Ultra)</span></label>
                                            </div>
                                            @endif
                                            @endif
                                            @if(@$mainSettings->active_mobile)
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createMyNotification"
                                                                            @if(@in_array('createMyNotification', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.app_add') }}</span></label>
                                            </div>
                                            @endif
                                            @if(@$mainSettings->active_notification && !@$mainSettings->active_mobile)
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createNotification"
                                                                            @if(@in_array('createNotification', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.g_application') }}</span></label>
                                            </div>
                                            @endif
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editEventNotification"
                                                                            @if(@in_array('editEventNotification', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.message_settings') }}</span></label>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Messages Tab-->
                                    @endif
                                    <!--begin::Settings Tab-->
                                    <div class="tab-pane " id="settings">
                                        <hr class="perm-hr"/>
                                        <div class="perm-section-title">
                                            <i class="ki-outline ki-gear"></i> {{ trans('sw.settings') }}
                                        </div>
                                        <div class="row pt-2 pb-2">
                                            <!-- Sale Channel Permissions -->
                                            <div class="col-lg-12">
                                                <h5 class="mb-3">{{ trans('sw.sale_channels') }}</h5>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listSaleChannel"
                                                                            @if(@in_array('listSaleChannel', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createSaleChannel"
                                                                            @if(@in_array('createSaleChannel', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editSaleChannel"
                                                                            @if(@in_array('editSaleChannel', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteSaleChannel"
                                                                            @if(@in_array('deleteSaleChannel', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete') }}</span></label>
                                            </div>
                                            
                                            <!-- Group Discount Permissions -->
                                            <div class="col-lg-12 mt-4">
                                                <h5 class="mb-3">{{ trans('sw.group_discounts') }}</h5>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="listGroupDiscount"
                                                                            @if(@in_array('listGroupDiscount', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.list') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="createGroupDiscount"
                                                                            @if(@in_array('createGroupDiscount', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.add') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="editGroupDiscount"
                                                                            @if(@in_array('editGroupDiscount', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.edit') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="deleteGroupDiscount"
                                                                            @if(@in_array('deleteGroupDiscount', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.delete') }}</span></label>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Settings Tab-->
                                    @if(@$mainSettings->active_ai)
                                    <div class="tab-pane " id="aiReports">
                                        <hr class="perm-hr"/>
                                        <div class="perm-section-title">
                                            <i class="ki-outline ki-cpu"></i> {{ trans('sw.ai_reports') }}
                                        </div>
                                        <div class="row pt-2 pb-2">
                                            <div class="col-lg-3 ">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="aiReportsDashboard"
                                                                            @if(@in_array('aiReportsDashboard', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.ai_dashboard') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="aiReportsJobs"
                                                                            @if(@in_array('aiReportsJobs', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.ai_jobs') }}</span></label>
                                            </div>
                                            <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                                                <label class="ckbox"><input name="permissions[]"
                                                                            value="aiReportsInsights"
                                                                            @if(@in_array('aiReportsInsights', $permission_group->permissions)) checked @endif
                                                                            type="checkbox"> <span>{{ trans('sw.ai_insights') }}</span></label>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                </div>
                    <!--end::Tab content-->
                            </div>
                <!--end::Card body-->
                        </div>
            <!--end::Permissions-->


            @section('scripts')
    @parent
    <script>
        (function(){
            const input = document.getElementById('permission_search');
            if(input){
                input.addEventListener('input', function(){
                    const term = this.value.toLowerCase();
                    document.querySelectorAll('.perm-grid .tab-pane.active .ckbox').forEach(function(el){
                        const text = el.innerText.toLowerCase();
                        const col = el.closest('[class^="col-"]') || el.parentElement;
                        if(col){ col.style.display = text.includes(term) ? '' : 'none'; }
                    });
                });
            }
            function updateSelectedCount(){
                const container = document.querySelector('.perm-grid');
                if(!container) return;
                const checked = container.querySelectorAll('input[type="checkbox"]:checked').length;
                const badge = document.getElementById('permission_count_badge');
                if(badge){ badge.textContent = checked + ' selected'; }
            }
            document.addEventListener('shown.bs.tab', function(){ updateSelectedCount(); });
            document.addEventListener('change', function(e){
                if(e.target && e.target.matches('.perm-grid input[type="checkbox"]')){
                    updateSelectedCount();
                }
            });
            const sectionSelect = document.getElementById('permission_section_select');
            if(sectionSelect){
                sectionSelect.addEventListener('change', function(){
                    const target = this.value;
                    const link = document.querySelector('.permission-tabs a[href="#'+target+'"]');
                    if(link){ link.click(); }
                });
            }
            // init on load
            updateSelectedCount();
        })();
    </script>
@endsection