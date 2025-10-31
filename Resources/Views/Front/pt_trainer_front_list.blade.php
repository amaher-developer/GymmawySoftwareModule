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
    </style>
@endsection
@section('page_body')

<!--begin::PT Trainers-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <!--begin::Card title-->
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-user fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>    
            </div>
        </div>
        <!--end::Card title-->
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!--begin::Add PT Trainer-->
                @if(in_array('createPTTrainer', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.createPTTrainer')}}" class="btn btn-sm btn-flex btn-light-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ trans('admin.add')}}
                    </a>
                @endif
                <!--end::Add PT Trainer-->
            </div>
        </div>
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">
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

        @if(count($trainers) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_pt_trainers_table">
                <thead>
                    <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-200px text-nowrap">
                            <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('sw.name')}}
                        </th>
                        <th class="min-w-100px text-nowrap">
                            <i class="ki-outline ki-phone fs-6 me-2"></i>{{ trans('sw.phone')}}
                        </th>
                        <th class="min-w-100px text-nowrap">
                            <i class="ki-outline ki-dollar fs-6 me-2"></i>{{ trans('sw.bonus_amount')}}
                        </th>
                        <th class="text-end min-w-70px actions-column">
                            <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
                        </th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach($trainers as $key=> $trainer)
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
                                            {{ $trainer->name }}
                                        </div>
                                        <!--end::Title-->
                                    </div>
                                </div>
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold">{{ $trainer->phone }}</span>
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold text-primary">{{ number_format($trainer->price, 2) }}</span>
                            </td>
                            <td class="text-end actions-column">
                                <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                    @if(in_array('createTrainerPayPercentageAmountForm', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <!--begin::Trainer Percentage-->
                                        <a data-target="#modalTrainerPay{{$trainer->id}}" data-toggle="modal" href="#"
                                           id="{{$trainer->id}}" style="cursor: pointer;"
                                           class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm" title="{{ trans('sw.trainer_percentage')}}">
                                            <i class="ki-outline ki-dollar fs-2"></i>
                                        </a>
                                        <!--end::Trainer Percentage-->
                                    @endif
                                    
                                    @if(in_array('editPTTrainerSubscription', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <!--begin::Edit Subscription-->
                                        <a href="{{route('sw.editPTTrainerSubscription',$trainer->id)}}"
                                           class="btn btn-icon btn-bg-light btn-active-color-info btn-sm" title="{{ trans('sw.edit_subscription')}}">
                                            <i class="ki-outline ki-calendar fs-2"></i>
                                        </a>
                                        <!--end::Edit Subscription-->
                                    @endif
                                    
                                    @if(in_array('editPTTrainer', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <!--begin::Edit-->
                                        <a href="{{route('sw.editPTTrainer',$trainer->id)}}"
                                           class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" title="{{ trans('admin.edit')}}">
                                            <i class="ki-outline ki-pencil fs-2"></i>
                                        </a>
                                        <!--end::Edit-->
                                    @endif
                                    
                                    @if(in_array('deletePTTrainer', (array)$swUser->permissions) || $swUser->is_super_user)
                                        @if(request('trashed'))
                                            <!--begin::Enable-->
                                            <a title="{{ trans('admin.enable')}}"
                                               href="{{route('sw.deletePTTrainer',$trainer->id)}}"
                                               class="confirm_delete btn btn-icon btn-bg-light btn-active-color-success btn-sm" title="{{ trans('admin.enable')}}">
                                                <i class="ki-outline ki-check-circle fs-2"></i>
                                            </a>
                                            <!--end::Enable-->
                                        @else
                                            <!--begin::Delete-->
                                            <a title="{{ trans('admin.disable')}}"
                                               href="{{route('sw.deletePTTrainer',$trainer->id)}}"
                                               class="confirm_delete btn btn-icon btn-bg-light btn-active-color-danger btn-sm" title="{{ trans('admin.disable')}}">
                                                <i class="ki-outline ki-trash fs-2"></i>
                                            </a>
                                            <!--end::Delete-->
                                        @endif
                                    @endif
                                </div>
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
                        'from' => $trainers->firstItem() ?? 0,
                        'to' => $trainers->lastItem() ?? 0,
                        'total' => $trainers->total()
                    ]) }}
                </div>
                <ul class="pagination">
                    {!! $trainers->appends($search_query)->render() !!}
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
<!--end::PT Trainers-->


    @foreach($trainers as $trainer)
        <!-- start model pay -->
        <div class="modal" id="modalTrainerPay{{$trainer->id}}">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-content-demo">
                    <div class="modal-header">
                        <h6 class="modal-title">{{ trans('sw.trainer_cal')}}</h6>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                                    aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div id="modalPayResult"></div>
                        @php 
                            $total_trainer_pay = 0; 
                            $total_rows = 0;
                            if($trainer->pt_members_trainer_amount_status_false){
                                foreach($trainer->pt_members_trainer_amount_status_false as $m){
                                    $total_rows++;
                                    $total_trainer_pay += ($m->amount_paid ? ($m->trainer_percentage / 100 * ($m->amount_paid - $m->vat)) : 0);
                                }
                            }
                        @endphp
                        <div class="row" style="margin-bottom:10px;">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between align-items-center p-3" style="background:#f7f7f7;border-radius:6px;">
                                    <div>
                                        <div class="fw-bold">{{ @$trainer->name }}</div>
                                        <div class="text-muted" style="font-size:12px;">{{ trans('sw.trainer_percentage_for_member') }}</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-success" style="font-size:16px;">{{ number_format($total_trainer_pay, 2) }}</div>
                                        <div class="badge badge-light">{{ trans('sw.items') }}: {{ $total_rows }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="portlet grey-cascade box">
                                    <div class="portlet-title">
                                        <div class="caption">
                                            <i class="fa fa-shopping-cart"></i> {{ trans('sw.trainer_percentage_for_member')}}
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover table-bordered align-middle" id="cart_table">
                                                <thead class="table-light">
                                                <tr class="text-center">
                                                    <th>
                                                        {{ trans('sw.subscriber')}}
                                                    </th>
                                                    <th>
                                                        {{ trans('sw.membership')}}
                                                    </th>
                                                    <th>
                                                        {{ trans('sw.amount_paid')}}
                                                    </th>
                                                    <th>
                                                        {{ trans('sw.amount_remaining')}}
                                                    </th>
                                                    <th>
                                                        {{ trans('sw.amount_paid_to_trainer')}}
                                                    </th>
                                                    <th>
                                                        {{ trans('sw.pt_classes')}}
                                                    </th>
                                                    <th>
                                                        {{ trans('sw.status')}}
                                                    </th>
                                                </tr>
                                                </thead>
                                                <tbody id="cart_result">
                                                @if($trainer->pt_members_trainer_amount_status_false)
                                                    @foreach($trainer->pt_members_trainer_amount_status_false as $member)
                                                    @php $trainer_amount = ($member->amount_paid ? ($member->trainer_percentage / 100 * ($member->amount_paid - $member->vat)) : 0); @endphp
                                                    <tr id="tr_trainer_member_{{$member->id}}">
                                                        <td>{{@$member->member->name}}</td>
                                                        <td>{{@$member->pt_class->pt_subscription->name}}</td>
                                                        <td class="text-end">{{ number_format(($member->amount_paid - $member->vat), 2) }}</td>
                                                        <td class="text-end">{{ number_format(round($member->amount_remaining, 2), 2) }}</td>
                                                        <td class="text-end">{{ number_format($trainer_amount, 2) }} {{' ( '.$member->trainer_percentage.'%'.' ) '}}</td>
                                                        <td class="text-center">{{$member->visits}} / {{$member->classes}}</td>
                                                        <td class="text-center">
                                                            @if((round($member->amount_remaining) == 0) && ($member->visits == $member->classes))
                                                            <a data-target="#trainer_confirm" data-toggle="modal" href="#"
                                                                    style="cursor: pointer;"
                                                                   onclick="getMemberInfo({{$member->id}}, '{{ trans('sw.pay_to_trainer_amount', ['amount' => number_format($trainer_amount , 2), 'trainer_name' => @$trainer->name, 'member_name' => @$member->member->name])}}');"
                                                                   class="btn btn-success btn-sm"
                                                                   title="{{ trans('sw.free_to_pay')}}">
                                                                    {{ trans('sw.free_to_pay')}}
                                                                </a>

{{--                                                                <a type="button" class="btn btn-success btn-sm">{{ trans('sw.free_to_pay')}}</a> --}}
                                                            @else
                                                                <span class="badge badge-info">{{ trans('sw.freeze_account')}}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                @else
                                                <tr id="empty_cart"><td colspan="7" class="text-center">{{ trans('sw.no_record_found')}}</td></tr>
                                                @endif
                                                </tbody>
                                                @if($trainer->pt_members_trainer_amount_status_false)
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="4" class="text-end">{{ trans('sw.amount_paid_to_trainer') }}</th>
                                                        <th class="text-end">{{ number_format($total_trainer_pay, 2) }}</th>
                                                        <th colspan="2"></th>
                                                    </tr>
                                                </tfoot>
                                                @endif
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- End model pay -->
    @endforeach

    <input type="hidden" id="trainer_member_id" name="trainer_member_id" value="">
    <div class="modal bs-modal-sm" id="trainer_confirm" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom: none;">
                    <h5 class="modal-title d-flex align-items-center">
                        <i class="ki-outline ki-alert fs-2 text-warning {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>
                        {{ trans('admin.are_you_sure')}}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning d-flex align-items-center" role="alert" style="border-radius: 6px;">
                        <i class="ki-outline ki-information-2 fs-2 {{ app()->getLocale() == 'ar' ? 'ms-3' : 'me-3' }}"></i>
                        <div id="confirm_msg_for_trainer" class="fw-semibold"></div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: none;">
                    <button type="button" class="btn btn-primary" onclick="trainer_pay_btn();">
                        <i class="ki-outline ki-check-circle {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i>{{ trans('sw.yes')}}
                    </button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">
                        <i class="ki-outline ki-cross-circle {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i>{{ trans('admin.cancelled')}}
                    </button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
@endsection

@section('scripts')
    @parent

    <script>
       function getMemberInfo(id, msg){
            $('#trainer_member_id').val(id);
            $('#confirm_msg_for_trainer').html(msg);
        }
        function trainer_pay_btn() {
            let id = $('#trainer_member_id').val();
            var $confirmModal = $('#trainer_confirm');
            var $confirmBtn = $confirmModal.find('.btn.btn-primary');
            $confirmBtn.prop('disabled', true);
            $.ajax({
                url: '{{route('sw.createTrainerPayPercentageAmountForm')}}',
                cache: false,
                type: 'POST',
                dataType: 'text',
                data: {id: id, "_token": '{{csrf_token()}}'},
                success: function () {
                    // Hide confirmation modal (support Bootstrap 4/5)
                    try { $confirmModal.modal('hide'); } catch(e) {}
                    try {
                        if (window.bootstrap && bootstrap.Modal) {
                            var inst = bootstrap.Modal.getInstance(document.getElementById('trainer_confirm'));
                            if (inst) inst.hide();
                        }
                    } catch(e) {}
                    // Also hide any open modal as a fallback
                    setTimeout(function(){
                        try { $('.modal.show').modal('hide'); } catch(e) {}
                        try { $('.modal').removeClass('show').hide(); } catch(e) {}
                        try { $('.modal-backdrop').remove(); } catch(e) {}
                        try { $('body').removeClass('modal-open').css('padding-right',''); } catch(e) {}
                    }, 100);

                    $('#tr_trainer_member_'+id).remove();
                    $('#modalPayResult').html('<div class="alert alert-success">{{ trans('admin.successfully_paid')}}</div>');
                    // Clear state
                    $('#trainer_member_id').val('');
                    swal({
                        title: '{{ trans('admin.done') }}',
                        text: '{{ trans('admin.successfully_processed') }}',
                        type: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function (request, error) {
                    swal("Operation failed", "Something went wrong.", "error");
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                },
                complete: function(){
                    $confirmBtn.prop('disabled', false);
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

        $("#filter_form").slideUp();
        $(".filter_trigger_button").click(function () {
            $("#filter_form").slideToggle(300);
        });

        $(document).on('click', '.remove_filter', function (event) {
            event.preventDefault();
            var filter = $(this).attr('id');
            $("#" + filter).val('');
            $("#filter_form").submit();
        });


    </script>

@endsection
