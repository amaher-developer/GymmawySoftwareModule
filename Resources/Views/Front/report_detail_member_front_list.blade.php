@extends('software::layouts.list')
@section('list_title') {{ @$title }} @endsection
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
@section('styles')
    <style>
        .avatar-md {
            width: 48px !important;
            height: 48px !important;
            font-size: 24px !important;
        }

        .rounded-circle {
            border-radius: 50% !important;
        }

        .details {
            display: none;
        }
        .dt-control-after:before  {
            content: "-" !important;
            background-color: #d33333 !important;
        }
        .dt-control{
            vertical-align: inherit;
            padding: 0 5px;
        }
        .dt-control:before {
            height: 1em;
            width: 1em;
            margin-top: -9px;
            display: inline-block;
            color: white;
            border: 0.15em solid white;
            border-radius: 1em;
            box-shadow: 0 0 0.2em #444;
            box-sizing: content-box;
            text-align: center;
            text-indent: 0 !important;
            font-family: "Courier New",Courier,monospace;
            line-height: 1em;
            content: "+";
            background-color: #31b131;
        }
        .form-control {
            height: 36px;
        }

        .details {
            text-align: center;
        }
        .details th {
            text-align: center;
        }

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

<!--begin::Report-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-user-tick fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!--begin::Filter-->
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_detail_members_filter_collapse">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter')}}
                </button>
                <!--end::Filter-->
            </div>
        </div>
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Filter-->
        <div class="collapse" id="kt_detail_members_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" action="" method="get">
                    <div class="row g-6">
                        <div class="col-md-12">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.memberships')}}</label>
                            <select name="subscription" class="form-select form-select-solid">
                                <option value="0" @if(request('subscription') == 0) selected="" @endif>{{ trans('sw.from_highest_memberships')}}</option>
                                <option value="1" @if(request('subscription') == 1) selected="" @endif>{{ trans('sw.from_lowest_memberships')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-5">
                        <a href="{{ route('sw.reportDetailMemberList') }}" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6">{{ trans('admin.reset')}}</a>
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
                <input type="text" name="search" class="form-control form-control-solid ps-12" value="@php echo @strip_tags($_GET['search']) @endphp" placeholder="{{ trans('sw.search_on')}}">
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
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_detail_members_table">
                    <thead>
                        <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-barcode fs-6 me-2"></i>{{ trans('sw.identification_code')}}
                            </th>
                            <th class="min-w-200px text-nowrap">
                                <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('sw.name')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-phone fs-6 me-2"></i>{{ trans('sw.phone')}}
                            </th>
                            <th class="min-w-150px text-nowrap">
                                <i class="ki-outline ki-sort-numeric-asc fs-6 me-2"></i>{{ trans('sw.memberships_count')}}
                            </th>
                            <th class="text-end actions-column">
                                <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @foreach($members as $key=> $member)
                            <tr>
                                <td>
                                    @if(count($member->member_subscriptions) > 0)
                                    <span class="dt-control" role="button" data-bs-toggle="collapse" data-bs-target="#details-{{$member->id}}"></span>
                                    @endif
                                    <span class="fw-bold">{{ $member->code }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-50px me-3">
                                            <img alt="avatar" class="rounded-circle" src="{{$member->image}}">
                                        </div>
                                        <div>
                                            <div class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                                {{ $member->name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $member->phone }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ (int)@$member->member_subscriptions->count() }}</span>
                                </td>
                                <td class="text-end actions-column">
                                </td>
                            </tr>
                            @if(count($member->member_subscriptions) > 0)
                            <tr class="collapse" id="details-{{$member->id}}">
                                <td colspan="5" class="p-0">
                                    <div class="p-4">
                                        <table class="table table-row-dashed">
                                            <thead>
                                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                    <th>{{ trans('sw.membership')}}</th>
                                                    <th>{{ trans('sw.workouts')}}</th>
                                                    <th>{{ trans('sw.number_of_visits')}}</th>
                                                    <th>{{ trans('sw.joining_date')}}</th>
                                                    <th>{{ trans('sw.expire_date')}}</th>
                                                    <th>{{ trans('sw.amount_paid')}}</th>
                                                    <th>{{ trans('sw.amount_remaining')}}</th>
                                                    <th><i class="ki-outline ki-calendar fs-6"></i> {{ trans('sw.date')}}</th>
                                                    <th class="text-end">{{ trans('admin.actions')}}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($member->member_subscriptions as $member_subscription)
                                                    <tr>
                                                        <td>{{ @$member_subscription->subscription->name}}</td>
                                                        <td>{{ @$member_subscription->workouts }}</td>
                                                        <td>{{ @$member_subscription->visits }}</td>
                                                        <td>{{ @\Carbon\Carbon::parse($member_subscription->joining_date)->toDateString() }}</td>
                                                        <td>{{ @\Carbon\Carbon::parse($member_subscription->expire_date)->toDateString() }}</td>
                                                        <td>{{ @$member_subscription->amount_paid }}</td>
                                                        <td>{{ @$member_subscription->amount_remaining }}</td>
                                                        <td>
                                                            <div class="d-flex flex-column">
                                                                <div class="text-muted fw-bold d-flex align-items-center">
                                                                    <i class="ki-outline ki-calendar fs-6 text-muted me-2"></i>
                                                                    <span>{{ $member_subscription->created_at ? $member_subscription->created_at->format('Y-m-d') : $member_subscription->updated_at->format('Y-m-d')}}</span>
                                                                </div>
                                                                <div class="text-muted fs-7 d-flex align-items-center">
                                                                    <i class="ki-outline ki-time fs-6 text-muted me-2"></i>
                                                                    <span>{{ $member_subscription->created_at ? $member_subscription->created_at->format('h:i a') : $member_subscription->updated_at->format('h:i a') }}</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-end actions-column">
                                                            <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                                                @if(@$member_subscription->amount_remaining)
                                                                    @if(in_array('createMemberPayAmountRemainingForm', (array)$swUser->permissions) || $swUser->is_super_user)
                                                                        <!--begin::Pay-->
                                                                        <a data-target="#modalPay" data-toggle="modal" href="#" id="{{$member_subscription->id}}" class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm"
                                                                           title="{{ trans('sw.pay')}}">
                                                                            <i class="ki-outline ki-dollar fs-2"></i>
                                                                        </a>
                                                                        <!--end::Pay-->
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!--end::Table-->
            
            <!--begin::Pagination-->
            <div class="d-flex flex-stack flex-wrap pt-10">
                <div class="fs-6 fw-semibold text-gray-700">
                    Showing {{ $members->firstItem() ?? 0 }} to {{ $members->lastItem() ?? 0 }} of {{ $members->total() }} entries
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
                        <i class="ki-outline ki-user-tick fs-2"></i>
                    </div>
                </div>
                <h4 class="text-gray-800 fw-bold">{{ trans('sw.no_record_found')}}</h4>
            </div>
            <!--end::Empty State-->
        @endif

        <div class="clearfix" style="clear: both;float: none;padding-top: 10px"></div>
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
            </div>
        </div>
    </div>
    <!--end::Card body-->
</div>
<!--end::Report-->

    <!-- start model pay -->
    <div class="modal" id="modalPay">
        <div class="modal-dialog" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">{{ trans('sw.amount_paid')}}</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h6>{{ trans('sw.amount_paid')}}</h6>
                    <div id="modalPayResult"></div>
                    <form id="form_pay" action="" method="GET">
                        <div class="form-group col-lg-6">
                            <input name="amount_paid" class="form-control" type="number" id="amount_paid" placeholder="{{ trans('sw.enter_amount_paid')}}">
                        </div>
                        <div class="form-group col-lg-6">
                            <select class="form-control" name="payment_type" id="payment_type">
                                @foreach($payment_types as $payment_type)
                                    <option value="{{$payment_type->payment_id}}" @if(@old('payment_type',$order->payment_type) == $payment_type->payment_id) selected="" @endif>{{$payment_type->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn ripple btn-primary rounded-3" id="form_pay_btn" type="submit">{{ trans('sw.pay')}}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End model pay -->

@endsection

@section('scripts')
    @parent
    <script src="https://code.highcharts.com/highcharts.js"></script>

    <script>
        $('#container').highcharts({
            title: {
                text: '{{ trans('sw.memberships')}}',
                x: -20 //center
            },
            xAxis: {
                reversed: true,
                categories: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر']
            },
            yAxis: {
                title: {
                    text: ' {{ trans('sw.members')}} '
                },
                opposite: true,
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },
            series: [
                @php $t = 1; @endphp
                @foreach($members as $member)
                {
                    name: '{{$member->name}}',
                    @php $member_subscriptions = $member->member_subscriptions; @endphp
                    data: [
                        @for($i = 1; $i < 13;$i++)
                            @php
                                $member_after_filter = $member->member_subscriptions->filter(function($item) use ($i, $t) {
                                    if (Carbon\Carbon::parse($item->expire_date)->format('Y-m-d') >= Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y').'-'.$i.'-1')->format('Y-m-d')
                                     && Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y').'-'.$i.'-1')->format('Y-m-d') > Carbon\Carbon::parse($item->joining_date)->format('Y-m-d')) {
                                            return  true;
                                          }else{
                                            return false;
                                          }
                                    });
                                    if(count($member_after_filter) > 0)
                                        echo $t.',';
                                    else
                                        echo '0,';

                                @endphp
                            @endfor
                    ]
                },
                    @php $t++ @endphp
                @endforeach
                ]
        });
    </script>
    <script>
        $('.btn-indigo').off('click').on('click', function (e) {
            var that = $(this);
            var attr_id = that.attr('id');
            $('#form_pay').append('<input value="' + attr_id + '"  id="pay_id" name="pay_id"  hidden>');
        });
        $(document).on('click', '#form_pay_btn', function (event) {
            event.preventDefault();
            let id = $('#pay_id').val();
            let amount_paid = $('#amount_paid').val();
            let payment_type = $('#payment_type').val();
            $.ajax({
                url: '{{route('sw.createMemberPayAmountRemainingForm')}}',
                cache: false,
                type: 'GET',
                dataType: 'text',
                data: {id: id, amount_paid: amount_paid, payment_type:payment_type},
                success: function (response) {
                    if (response == '1') {
                        $('#modalPayResult').html('<div class="alert alert-success">{{ trans('admin.successfully_paid')}}</div>');
                        location.reload();
                    } else {
                        $('#modalPayResult').html('<div class="alert alert-danger">' + response + '</div>');
                    }
                },
                error: function (request, error) {
                    swal("Operation failed", "Something went wrong.", "error");
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                }
            });
        });

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
        
        var collapseElementList = [].slice.call(document.querySelectorAll('.collapse'))
        var collapseList = collapseElementList.map(function (collapseEl) {
          collapseEl.addEventListener('show.bs.collapse', function () {
            let trigger = document.querySelector('[data-bs-target="#' + collapseEl.id + '"]');
            if (trigger) {
                trigger.classList.add('dt-control-after');
            }
          });
          collapseEl.addEventListener('hide.bs.collapse', function () {
            let trigger = document.querySelector('[data-bs-target="#' + collapseEl.id + '"]');
            if (trigger) {
                trigger.classList.remove('dt-control-after');
            }
          });
        })
    </script>
@endsection

