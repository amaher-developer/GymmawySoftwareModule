<style>
    #myTotalAfterDiscountModel {
        display: none;
    }
    span#client_balance span {
        padding: 3px;
        border-radius: 3px !important;
    }
</style>
<!-- start model Barcode -->
<div class="modal" id="modelBarcode">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">{{trans('sw.barcode')}}</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <h6 >{{trans('sw.qty')}}</h6>
                <form action="{{route('sw.generateBarcode')}}" method="GET" id="generateBarcode">
                    <div class="form-group">
                        <input name="qty" class="form-control" min="0" max="50" type="number" required
                               placeholder="{{trans('sw.qty')}}">
                    </div><!-- end div qty  -->

                    <button class="btn ripple btn-primary"  onclick="generateBarcode();return false;"
                            type="button">{{trans('sw.generate_barcode')}}</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End model Barcode -->

<!-- start model Renew -->
<div class="modal" id="modelRenew">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">{{trans('sw.renew_membership')}}</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <!-- Current Membership Info -->
                <div class="mb-4">
                    <label class="form-label fw-bold">{{trans('sw.membership')}}</label>
                    <p class="text-muted mb-0" id="membership_expire_date_msg"></p>
                </div>

                <!-- New Membership Selection -->
                <div class="mb-4">
                    <label class="form-label fw-bold">{{trans('sw.select_new_membership')}}</label>
                    <select id="select_membership" class="form-control form-control-solid select2_renew">
                        <option value="">{{trans('sw.choose')}}</option>
                    </select>
                    <div id="myDivExpireRenewModal" class="mt-2"></div>
                    <span id="error_expire_date" class="text-danger"></span>
                </div>

                <!-- Price Information -->
                <div class="mb-4">
                    <label class="form-label fw-bold">{{trans('sw.price')}}</label>
                    <div class="d-flex gap-2">
                        <span class="badge badge-success fs-6" id="myTotalModel">0</span>
                        <span class="badge badge-primary fs-6" id="myTotalAfterDiscountModel">{{trans('sw.after_discount')}} = 0</span>
                    </div>
                </div>

                <!-- Discount Section -->
                <div class="row mb-4" @if($swUser && ((in_array('editMemberDiscount', (array)($swUser->permissions ?? []))) || $swUser->is_super_user)) style="display: block;" @else style="display: none;" @endif>
                    <div class="col-md-6">
                        <label class="form-label">{{trans('sw.discount_value')}}</label>
                        <input class="form-control form-control-solid" autocomplete="off" 
                               placeholder="{{trans('sw.discount_value')}}"
                               name="discount_value"
                               id="renew_discount_value"
                               value="0"
                               min="0"
                               max="0"
                               type="number" step="0.01">
                    </div>
                    <div class="col-md-6" @if($swUser && (count($group_discounts) > 0) && ((in_array('editMemberDiscountGroup', (array)($swUser->permissions ?? []))) || $swUser->is_super_user)) style="display: block;" @else style="display: none;" @endif>
                        <label class="form-label">{{trans('sw.group_discount')}}</label>
                        <select id="renew_group_discount_id" name="renew_group_discount_id" class="form-control form-control-solid select2">
                            <option value="0" type="0" amount="0">{{trans('sw.choose')}}</option>
                            @foreach($group_discounts as $discount)
                                <option value="{{$discount->id}}" type="{{$discount->type}}" amount="{{$discount->amount}}">{{$discount->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- VAT Information -->
                @if(@$mainSettings->vat_details['vat_percentage'])
                <div class="mb-4">
                    <label class="form-label">{{trans('sw.include_vat', ['vat' => @$mainSettings->vat_details['vat_percentage']])}}</label>
                    <span class="badge badge-warning fs-6" id="myTotalWithVatModal">{{trans('sw.price')}} = 0</span>
                </div>
                @endif

                <!-- Payment Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">{{trans('sw.amount_paid')}}</label>
                        <input id="renew_amount_paid" class="form-control form-control-solid" name="amount_paid"
                               placeholder="{{trans('sw.enter_amount_paid')}}" type="number" min="0" step="0.01"/>
                        <span id="amount_paid_error" class="text-danger"></span>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{trans('sw.amount_remaining')}}</label>
                        <input id="renew_amount_remaining" class="form-control form-control-solid" name="amount_remaining" step="0.01"
                               placeholder="{{trans('sw.enter_amount_remaining')}}" disabled
                               type="number" min="0"/>
                    </div>
                </div>

                <!-- Payment Type -->
                <div class="mb-4">
                    <label class="form-label">{{trans('sw.payment_type')}}</label>
                    <select class="form-control form-control-solid" name="payment_type" id="renew_payment_type">
                        @foreach($payment_types as $payment_type)
                            <option value="{{$payment_type->payment_id}}">{{$payment_type->name}}</option>
                        @endforeach
                    </select>
                    <span id="payment_type_error" class="text-danger"></span>
                </div>

                <!-- Notes -->
                <div class="mb-4">
                    <label class="form-label">{{trans('sw.notes')}}</label>
                    <textarea rows="3" maxlength="255" name="notes" id="renew_notes" 
                              class="form-control form-control-solid" 
                              placeholder="{{trans('sw.enter_notes_optional')}}"></textarea>
                </div>
            </div>

            @if(@$mainSettings->active_loyalty)
            <!--begin::Loyalty Points Earning Info-->
            <div class="alert alert-dismissible bg-light-success border border-success border-dashed d-flex flex-column flex-sm-row p-4 mb-3" id="renew_loyalty_earning_info" style="display: none !important;">
                <i class="ki-outline ki-gift fs-2hx text-success me-3 mb-3 mb-sm-0"></i>
                <div class="d-flex flex-column pe-0 pe-sm-5">
                    <h6 class="mb-1">{{ trans('sw.points_earning_info')}}</h6>
                    <span class="text-gray-700 fs-7">{!! trans('sw.you_will_earn_points', ['points' => '<span id="renew_estimated_earning_points" class="fw-bold text-success">0</span>'])!!}</span>
                    <span class="text-gray-600 fs-8" id="renew_loyalty_earning_rate"></span>
                </div>
            </div>
            <!--end::Loyalty Points Earning Info-->
            @endif

            <div style="clear: both;float: none"></div>
            <div class="modal-footer">
                <input value=""  id="renew_member_id"   type="hidden">
                <button id="btn_renew_membership" class="btn ripple btn-primary"
                        type="button">{{trans('sw.renew_membership')}}</button>
                <button class="btn ripple btn-secondary" data-dismiss="modal"
                        type="button">{{trans('sw.exist')}}</button>
            </div>


            </div>
        </div>
    </div>
</div>
<!-- End model Renew -->

<!-- Modal QR Scanner with effects -->
<div class="modal effect-newspaper" id="modalScanner">
    <div class="modal-dialog modal-dialog-scrollable " role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title"><i class="fa fa-check-circle text-success"></i> <span
                            id="title">{{trans('sw.scan_qr_by_camera')}}</span></h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body ">
                <div id="divCamera" class="row">
                    <div id="div_no_camera" class="col-xs-12">{{trans('sw.no_camera_info_msg')}}</div>
                    <div id="divQRPanel" class="col-xs-12">
                        <video muted playsinline id="qr-video" style="width: 100%;"></video>
                        <p class="text-center"><br>{{trans('sw.scan_info_msg')}}</p>
                        <p><input class="btnBig" type="button" id="btnStopScan" value="Stop Scaning"
                                  style="display: none"></p>
                    </div>
                    <div id="divQRResult" class="col-xs-12">
                        <div id="cam-qr-result" ></div>
                        <input class="btnBig" type="button" id="btnRescan" value="Start Scanning"
                               style="display: none">
                        <div id="allow_cam"></div>
                        <script>
                            navigator.mediaDevices.getUserMedia().then(
                                function () {
                                    document.getElementById('allow_cam').innerHTML = '';
                                }
                            ).catch(function (err) {
                                {{--document.getElementById('allow_cam').innerHTML = '{{trans('sw.no_camera_alert_msg')}}';--}}
                            });
                        </script>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- Modal QR Scanner with effects-->

<!-- Modal Attends with effects -->
<div class="modal fade effect-newspaper" id="modalAttends" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('sw.member_attendance_status') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Status Banner -->
                <div id="status_banner" class="text-center p-4 mb-4 text-white bg-success rounded">
                    <h3 id="p_messages" class="mb-0"></h3>
                </div>

                <div class="text-center mb-5">
                    <img id="client_img" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;" src="{{asset('uploads/settings/default.jpg')}}">
                    <h3 id="client_name" class="mt-3 mb-0"></h3>
                    <p class="text-muted" id="client_code"></p>
                </div>

                <!-- Key Stats -->
                <div class="row g-2 text-center mb-5">
                    <div class="col-4">
                        <div class="bg-light-primary p-3 rounded">
                            <div class="fs-7 text-muted">{{ trans('sw.membership') }}</div>
                            <div class="fs-4 fw-bold" id="client_membership"></div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light-danger p-3 rounded">
                            <div class="fs-7 text-muted">{{ trans('sw.amount_remaining') }}</div>
                            <div class="fs-4 fw-bold" id="client_amount_remaining"></div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light-info p-3 rounded">
                            <div class="fs-7 text-muted">{{ trans('sw.expire_date') }}</div>
                            <div class="fs-4 fw-bold" id="client_expire_date"></div>
                        </div>
                    </div>
                </div>

                <div id="myData">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <span class="fw-bold">{{trans('sw.phone')}}:</span>
                                <span id="client_phone" class="text-muted"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <span class="fw-bold">{{trans('sw.last_attend_date')}}:</span>
                                <span id="client_last_attend_date" class="text-muted"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <span class="fw-bold">{{trans('sw.joining_date')}}:</span>
                                <span id="client_joining_date" class="text-muted"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <span class="fw-bold">{{trans('sw.total_amount_remaining')}}:</span>
                                <span id="client_total_amount_remaining" class="text-muted"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <span class="fw-bold">{{trans('sw.remaining_workouts')}}:</span>
                                <span id="client_workouts" class="text-muted"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <span class="fw-bold">{{trans('sw.invitations_num')}}:</span>
                                <a href="javascript:void(0)" onclick="client_invitations();" class="text-primary">
                                    <i class="fa fa-hand-pointer-o" aria-hidden="true"></i>
                                    <span id="client_invitations" class="text-muted"></span>
                                </a>
                            </div>
                        </div>
                        @if(@$mainSettings->active_loyalty)
                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <span class="fw-bold">
                                    <i class="ki-outline ki-gift text-primary me-1"></i>
                                    {{trans('sw.loyalty_points')}}:
                                </span>
                                <span id="client_loyalty_points" class="text-primary fw-bold fs-4">0</span>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <span class="fw-bold">{{trans('sw.store_credit')}}:</span>
                                <span id="client_balance" class="text-muted"></span>
                            </div>
            </div>

                        @if($mainSettings->active_reservation)
                        <div class="col-12" id="client_reservation_h5">
                            <div class="d-flex flex-column">
                                <span class="fw-bold">{{trans('sw.reservation_today')}}:</span>
                                <span id="client_reservation" class="text-muted"></span>
                            </div>
                        </div>
                        @endif

                        <div class="col-12" id="client_pt_membership_h5">
                             <div class="d-flex flex-column">
                                <span class="fw-bold">{{trans('sw.pt_subscription')}}:</span>
                                <span id="client_pt_membership" class="text-muted"></span>
                            </div>
                        </div>

                        <div class="col-12" id="client_activities_h5">
                            <div class="d-flex flex-column">
                                <span class="fw-bold">{{trans('sw.activities')}}:</span>
                                <span id="client_activities" class="text-muted"></span>
                            </div>
                        </div>
                    </div>
                </div>
        </div>

            <div class="modal-footer d-flex justify-content-center gap-2">
                @if($swUser && (in_array('memberSubscriptionRenewStore', (array)($swUser->permissions ?? [])) || $swUser->is_super_user))<div id="div_renew"></div>@endif
                @if($swUser && (in_array('unfreezeMember', (array)($swUser->permissions ?? [])) || $swUser->is_super_user))<div id="div_freeze"></div>@endif
            </div>
        </div>
    </div>
</div>
<!-- Modal Attends with effects-->



            <div class="modal-footer d-flex justify-content-center gap-2">
                @if($swUser && (in_array('memberSubscriptionRenewStore', (array)($swUser->permissions ?? [])) || $swUser->is_super_user))<div id="div_renew"></div>@endif
                @if($swUser && (in_array('unfreezeMember', (array)($swUser->permissions ?? [])) || $swUser->is_super_user))<div id="div_freeze"></div>@endif
            </div>
        </div>
    </div>
</div>
<!-- Modal Attends with effects-->

