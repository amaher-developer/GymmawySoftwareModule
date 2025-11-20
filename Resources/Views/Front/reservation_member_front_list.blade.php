@extends('software::layouts.list')
@section('list_title') {{ @$title }} @endsection
@section('breadcrumb')
    <!--begin::Breadcrumb-->
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <!--begin::Item-->
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home')}}</a>
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
@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.css"/>
    <style>
        /* Actions column styling */
        .actions-column {
            min-width: 120px;
            text-align: right;
        }

        .actions-column .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .actions-column .d-flex {
            gap: 0.25rem;
        }

        .more {display: none;}
        
        .loader {
            border: 16px solid #f3f3f3;
            border-radius: 50% !important;
            border-top: 16px solid #3498db;
            width: 32px;
            height: 32px;
            -webkit-animation: spin 2s linear infinite;
            animation: spin 2s linear infinite;
        }

        @-webkit-keyframes spin {
            0% { -webkit-transform: rotate(0deg); }
            100% { -webkit-transform: rotate(360deg); }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
@endsection
@section('page_body')
    <!--begin::Reservation Members-->
    <div class="card card-flush">
        <!--begin::Card header-->
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                <div class="d-flex align-items-center my-1">
                    <i class="ki-outline ki-user fs-2 me-3"></i>
                    <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>
                </div>
            </div>
            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <!--begin::Filter-->
                    <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_reservation_members_filter_collapse">
                        <i class="ki-outline ki-filter fs-6"></i>
                        {{ trans('sw.filter')}}
                    </button>
                    <!--end::Filter-->
                </div>
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body pt-0">
            <!--begin::Filter-->
            <div class="collapse" id="kt_reservation_members_filter_collapse">
                <div class="card card-body mb-5">
                    <form id="filter_form" action="" method="get">
                        <div class="row g-6">
                            <div class="col-md-4">
                                <label class="form-label fs-6 fw-semibold">{{ trans('sw.date_range')}}</label>
                                <div class="input-group date-picker input-daterange">
                                    <input type="text" class="form-control" name="from" id="from_date" value="@php echo @strip_tags($_GET['from']) @endphp" placeholder="{{ trans('sw.from')}}" autocomplete="off">
                                    <span class="input-group-text">{{ trans('sw.to')}}</span>
                                    <input type="text" class="form-control" name="to" id="to_date" value="@php echo @strip_tags($_GET['to']) @endphp" placeholder="{{ trans('sw.to')}}" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-6 fw-semibold">{{ trans('sw.status')}}</label>
                                <select name="status" class="form-select form-select-solid">
                                    <option value="">{{ trans('sw.choose_status')}}...</option>
                                    <option value="{{\Modules\Software\Classes\TypeConstants::NotFound}}" @if(isset($_GET['status']) && ((request('status') != "") && (request('status') == \Modules\Software\Classes\TypeConstants::NotFound))) selected="" @endif>{{ trans('sw.not_subscribed')}}</option>
                                    <option value="{{\Modules\Software\Classes\TypeConstants::Found}}" @if(request('status') == \Modules\Software\Classes\TypeConstants::Found) selected="" @endif>{{ trans('sw.subscribed')}}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-6 fw-semibold">{{ trans('sw.type')}}</label>
                                <select name="type" class="form-select form-select-solid">
                                    <option value="">{{ trans('sw.choose_type')}}...</option>
                                    <option value="1" @if(request('type') == 1) selected="" @endif>{{ trans('sw.memberships')}}</option>
                                    <option value="2" @if(request('type') == 2) selected="" @endif>{{ trans('sw.activities')}}</option>
                                    <option value="3" @if(request('type') == 3) selected="" @endif>{{ trans('sw.pt')}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-5">
                            <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6">{{ trans('admin.reset')}}</button>
                            <button type="submit" class="btn btn-primary fw-semibold px-6">
                                <i class="ki-outline ki-check fs-6"></i>
                                {{ trans('sw.filter')}}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!--end::Filter-->
            <!--begin::Search-->
            <div class="d-flex align-items-center position-relative my-1 mb-5">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <form class="d-flex" action="" method="get" style="max-width: 400px;">
                    <input type="text" name="search" class="form-control form-control-solid ps-12" value="{{ request('search') }}" placeholder="{{ trans('sw.search_on')}}">
                    <button class="btn btn-primary" type="submit">
                        <i class="ki-outline ki-magnifier fs-3"></i>
                    </button>
                </form>
            </div>
            <!--end::Search-->

            <!--begin::Total count-->
            <div class="d-flex align-items-center mb-5">
                <div class="symbol symbol-50px me-5">
                    <div class="symbol-label bg-light-primary">
                        <i class="ki-outline ki-chart-simple fs-2x text-primary"></i>
                    </div>
                </div>
                <div class="d-flex flex-column">
                    <span class="fs-6 fw-semibold text-gray-900">{{ trans('admin.total_count')}}</span>
                    <span class="fs-2 fw-bold text-primary">{{ $total }}</span>
                </div>
            </div>
            <!--end::Total count-->

            @if(count($members) > 0)
                <!--begin::Table-->
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_reservation_members_table">
                    <thead>
                        <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-200px text-nowrap">
                                <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('sw.name')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-phone fs-6 me-2"></i>{{ trans('sw.phone')}}
                            </th>
                            <th class="min-w-200px text-nowrap">
                                <i class="ki-outline ki-information-5 fs-6 me-2"></i>{{ trans('sw.details')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-check-circle fs-6 me-2"></i>{{ trans('sw.repeat_in_members')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.date')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('sw.by')}}
                            </th>
                            <th class="text-end min-w-70px text-nowrap">
                                <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @foreach($members as $key=> $member)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <!--begin::Avatar-->
                                        <div class="symbol symbol-50px me-3">
                                            <div class="symbol-label fs-3 bg-light-primary text-primary">
                                                <i class="ki-outline ki-user fs-2"></i>
                                            </div>
                                        </div>
                                        <!--end::Avatar-->
                                        <div>
                                            <!--begin::Title-->
                                            <div class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                                {{ $member->name }}
                                            </div>
                                            @if($member->national_id)
                                                <div class="text-muted fs-7">
                                                    <i class="ki-outline ki-credit-cart fs-6 me-1"></i> {{$member->national_id}}
                                                </div>
                                            @endif
                                            <!--end::Title-->
                                        </div>
                                    </div>
                                </td>
                                <td class="pe-0">
                                    <span class="fw-bold">{{ $member->phone }}</span>
                                </td>
                                <td class="pe-0">
                                    <div>
                                        @if($member->subscription) 
                                            <div class="badge badge-light-info mb-1">
                                                <i class="ki-outline ki-credit-cart fs-6 me-1"></i> {{ trans('sw.pm_subscription_msg', ['name' => @$member->subscription->name])}}
                                            </div><br/> 
                                        @endif
                                        @if($member->pt_subscription) 
                                            <div class="badge badge-light-warning mb-1">
                                                <i class="ki-outline ki-shield-tick fs-6 me-1"></i> {{ trans('sw.pm_pt_subscription_msg', ['name' => @$member->pt_subscription->name])}}
                                            </div><br/> 
                                        @endif
                                        @if($member->activity) 
                                            <div class="badge badge-light-success mb-1">
                                                <i class="ki-outline ki-list fs-6 me-1"></i> {{ trans('sw.pm_activity_msg', ['name' => @$member->activity->name])}}
                                            </div><br/> 
                                        @endif
                                    </div>
                                </td>
                                <td class="pe-0">
                                    @if(@$member->member)
                                        <div class="badge badge-light-success">{!! trans('sw.subscribed') !!}</div>
                                    @else 
                                        <div class="badge badge-light-danger">{!! trans('sw.not_subscribed') !!}</div>
                                    @endif
                                </td>
                                <td class="pe-0">
                                    <div class="d-flex flex-column">
                                        <div class="text-muted fw-bold d-flex align-items-center">
                                            <i class="ki-outline ki-calendar fs-6 text-muted me-2"></i>
                                            <span>{{ $member->created_at->format('Y-m-d') }}</span>
                                        </div>
                                        <div class="text-muted fs-7 d-flex align-items-center">
                                            <i class="ki-outline ki-time fs-6 text-muted me-2"></i>
                                            <span>{{ $member->created_at->format('h:i a') }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="pe-0">
                                    <span class="fw-bold">{{@$member->user ? @$member->user->name : trans('sw.p_application', ['name' => env('APP_NAME_AR')])}}</span>
                                </td>
                                <td class="text-end">
                                    @if($member->member && (in_array('memberSubscriptionRenewStore', (array)$swUser->permissions) || $swUser->is_super_user))
                                        <!--begin::Renew-->
                                        <a class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
                                           onclick="list_renew_membership('{{@$member->member->member_subscription_info->id}}')"
                                           expire_msg="{{ trans('sw.expire_date_msg', ['date' => @$member->member->member_subscription_info->formatted_expire_date ?? ''])}}"
                                           expire_color="{{@$member->member->member_subscription_info->expire_color ?? 'red'}}"
                                           title="{{ trans('sw.renew_membership')}}"
                                           id="list_member_{{@$member->member->member_subscription_info->id}}" style="cursor: pointer;"
                                           data-target="#modelRenew" data-toggle="modal">
                                            <i class="ki-outline ki-arrows-circle fs-2"></i>
                                        </a>
                                        <!--end::Renew-->
                                    @elseif(in_array('createMember', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <!--begin::Add Member-->
                                        <a href="{{route('sw.createMember').'?reservation_id='.$member->id}}"
                                           class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="{{ trans('sw.add')}}">
                                            <i class="ki-outline ki-user-plus fs-2"></i>
                                        </a>
                                        <!--end::Add Member-->
                                    @endif
                                    
                                    <!--begin::WhatsApp-->
                                    <a href="https://web.whatsapp.com/send?phone={{ ((substr( $member->phone, 0, 1 ) === "+") || (substr( $member->phone, 0, 2 ) === "00")) ? $member->phone : '+'.env('APP_COUNTRY_CODE').$member->phone}}"
                                       target="_blank" class="btn btn-icon btn-bg-light btn-active-color-success btn-sm me-1" title="{{ trans('sw.whatsapp')}}">
                                        <i class="ki-outline ki-message-text-2 fs-2"></i>
                                    </a>
                                    <!--end::WhatsApp-->
                                    
                                    @if(in_array('deleteReservationMember', (array)$swUser->permissions) || $swUser->is_super_user)
                                        @if(request('trashed'))
                                            <!--begin::Enable-->
                                            <a title="{{ trans('admin.enable')}}"
                                               href="{{route('sw.deleteReservationMember',$member->id)}}"
                                               class="confirm_delete btn btn-icon btn-bg-light btn-active-color-success btn-sm" title="{{ trans('admin.enable')}}">
                                                <i class="ki-outline ki-check-circle fs-2"></i>
                                            </a>
                                            <!--end::Enable-->
                                        @else
                                            <!--begin::Delete-->
                                            <a title="{{ trans('admin.disable')}}"
                                               href="{{route('sw.deleteReservationMember',$member->id)}}"
                                               class="confirm_delete btn btn-icon btn-bg-light btn-active-color-danger btn-sm" title="{{ trans('admin.disable')}}">
                                                <i class="ki-outline ki-trash fs-2"></i>
                                            </a>
                                            <!--end::Delete-->
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                <!--end::Table-->
                
                <!--begin::Pagination-->
                <div class="d-flex flex-stack flex-wrap pt-10">
                    <div class="fs-6 fw-semibold text-gray-700">
                        {{ trans('sw.showing_entries', [
                            'from' => $members->firstItem() ?? 0,
                            'to' => $members->lastItem() ?? 0,
                            'total' => $members->total()
                        ]) }}
                    </div>
                    <ul class="pagination">
                        {!! $members->appends($search_query)->render() !!}
                    </ul>
                </div>
                <!--end::Pagination-->
            @else
                <!--begin::Empty State-->
                <div class="text-center py-10">
                    <div class="symbol symbol-100px mb-5">
                        <div class="symbol-label fs-2x fw-semibold text-success bg-light-success">
                            <i class="ki-outline ki-user fs-2"></i>
                        </div>
                    </div>
                    <h4 class="text-gray-800 fw-bold">{{ trans('sw.no_record_found')}}</h4>
                </div>
                <!--end::Empty State-->
            @endif
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Reservation Members-->

@endsection

@section('scripts')
    @parent

    <script   src="{{asset('resources/assets/admin/global/scripts/software/renew_member.js')}}"></script>
    <script src="{{asset('resources/assets/admin/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
            type="text/javascript"></script>
    <script>
        function list_renew_membership(id) {
            let attr_id = id;
            $('#renew_member_id').val(attr_id);
            // $('#btn_renew_membership').before('<input value="' + attr_id + '"  id="renew_member_id"   hidden>');
            getRenewMembershipsSelection(attr_id);

            let expire_date = $('#list_member_'+id);
            let expire_date_msg = expire_date.attr('expire_msg');
            let expire_date_color = expire_date.attr('expire_color');
            let expire_message = '<span style="color: '+expire_date_color+'">'+expire_date_msg+'</span>';
            $('#membership_expire_date_msg').html(expire_message);

            {{--             @if($mainSettings->active_zk)--}}
            {{--             fingerprint_open_popup();--}}
            {{--             @endif--}}

                return false;
        }

        function showMore(id){
            var dots = document.getElementById("dots_"+id);
            var moreText = document.getElementById("more_"+id);
            var btnText = document.getElementById("myBtn_"+id);

            if (dots.style.display === "none") {
                dots.style.display = "inline";
                btnText.innerHTML = "{{ trans('sw.read_more')}}";
                moreText.style.display = "none";
            } else {
                dots.style.display = "none";
                btnText.innerHTML = "{{ trans('sw.read_less')}}";
                moreText.style.display = "inline";
            }
        }
        function membership_status_refresh(){
            $('#membership_status_refresh').hide().after('<div class="col-md-12"><div class="loader"></div></div>');
            $.ajax({
                url: '{{route('sw.updatePotentialMember')}}',
                cache: false,
                type: 'GET',
                dataType: 'text',
                data: {},
                success: function (response) {
                    {{--if (response == '1') {--}}
                setTimeout(function () {
                        window.location.replace("{{asset(route('sw.listPotentialMember'))}}");
                    }, 500);

                    {{--} else {--}}
                    {{--    $('#modalPayResult').html('<div class="alert alert-danger">' + response + '</div>');--}}
                    {{--}--}}

                },
                error: function (request, error) {
                    swal("Operation failed", "Something went wrong.", "error");
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                }
            });

        }
        $(document).on('click', '#export', function (event) {
            event.preventDefault();
            $.ajax({
                url: $(this).attr('url'),
                cache: false,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    var a = document.createElement("a");
                    a.href = response.file;
                    a.download = response.name;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                },
                error: function (request, error) {
                    swal("Operation failed", "Something went wrong.", "error");
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                }
            });

        });

        // Initialize menu components
        KTMenu.init();
        
        // Handle remove filter functionality
        $(document).on('click', '.remove_filter', function (event) {
            event.preventDefault();
            var filter = $(this).attr('id');
            $("#" + filter).val('');
            $("#form_filter").submit();
        });
        jQuery(document).ready(function () {
            $('.input-daterange').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                clearBtn: true,
                orientation: 'bottom auto'
            });

            $('button[type="reset"]').on('click', function() {
                setTimeout(() => {
                    $(this).closest('form').find('select').trigger('change');
                }, 100);
            });
        });

    </script>

@endsection
