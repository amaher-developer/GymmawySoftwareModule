<style>
    #myTotalAfterDiscountModel {
        display: none;
    }

    /* ── Renew modal option-group UI ── */
    .pos-pill{display:inline-flex;align-items:center;padding:3px 10px;border:1.5px solid #e4e6ef;border-radius:20px;font-size:12px;background:#f5f8fa;cursor:pointer;transition:all .15s;user-select:none;white-space:nowrap;}
    .pos-pill:hover{border-color:#009ef7;color:#009ef7;}
    .pos-pill.active{background:#009ef7;color:#fff;border-color:#009ef7;}
    .pos-prod-thumb{width:36px;height:36px;object-fit:cover;border-radius:4px;flex-shrink:0;}
    .pos-product-grid::-webkit-scrollbar,.pos-option-list::-webkit-scrollbar{width:4px;}
    .pos-product-grid::-webkit-scrollbar-thumb,.pos-option-list::-webkit-scrollbar-thumb{background:#d1d3e0;border-radius:4px;}

    /* ── Payment Gateway Cards ── */
    .pgw-section {
        background: linear-gradient(135deg, #f8fbff 0%, #f0f5ff 100%);
        border: 1px solid #dde6f7;
        border-radius: 14px;
        padding: 16px 14px 14px;
    }
    .pgw-section-title {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 0.88rem;
        font-weight: 700;
        color: #3a3f5c;
        margin-bottom: 12px;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }
    .pgw-section-title i {
        font-size: 1.1rem;
        color: #009ef7;
    }
    .pgw-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .pgw-card {
        flex: 1 1 140px;
        max-width: 190px;
        border: 2px solid #e4e8f0;
        border-radius: 12px;
        padding: 13px 11px 10px;
        cursor: pointer;
        background: #fff;
        position: relative;
        transition: border-color 0.18s, box-shadow 0.18s, background 0.18s, transform 0.15s;
        user-select: none;
    }
    .pgw-card:hover {
        border-color: #009ef7;
        box-shadow: 0 4px 14px rgba(0,158,247,0.14);
        transform: translateY(-2px);
    }
    .pgw-card.pgw-active {
        border-color: #009ef7;
        background: #f0faff;
        box-shadow: 0 6px 20px rgba(0,158,247,0.22);
    }
    /* checkbox is hidden via inline style="display:none" — no extra rule needed */
    .pgw-check {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid #c8d0de;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.18s;
        flex-shrink: 0;
    }
    .pgw-active .pgw-check {
        border-color: #009ef7;
        background: #009ef7;
    }
    .pgw-check::after {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #fff;
        display: none;
    }
    .pgw-active .pgw-check::after {
        display: block;
    }
    .pgw-logo-wrap {
        display: flex;
        align-items: center;
        min-height: 36px;
        margin-bottom: 8px;
        padding-right: 24px;
    }
    .pgw-logo {
        max-height: 30px;
        max-width: 110px;
        object-fit: contain;
    }
    .pgw-methods {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        align-items: center;
    }
    .pgw-method-icon {
        height: 16px;
        width: auto;
        object-fit: contain;
        opacity: 0.75;
        border-radius: 3px;
        transition: opacity 0.15s;
    }
    .pgw-card:hover .pgw-method-icon,
    .pgw-active .pgw-method-icon { opacity: 1; }
    .pgw-desc {
        font-size: 0.72rem;
        color: #7e8299;
        margin-top: 5px;
        line-height: 1.4;
    }
    /* RTL support */
    [dir=rtl] .pgw-check { right: auto; left: 8px; }
    [dir=rtl] .pgw-logo-wrap { padding-right: 0; padding-left: 24px; }




    /*-------------------------------------*/
    
    span#client_balance span {
        padding: 3px;
        border-radius: 3px !important;
    }

    .store-balance-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 999px !important;
        font-weight: 600;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        min-width: 120px;
        justify-content: center;
    }
    .store-balance-pill i {
        font-size: 1.1rem;
    }
    .store-balance-pill .value-text {
        font-size: 0.95rem;
    }
    .store-balance-pill.positive {
        background: rgba(15, 157, 88, 0.1);
        color: #0f9d58;
        border-color: rgba(15, 157, 88, 0.3);
    }
    .store-balance-pill.negative {
        background: rgba(217, 48, 37, 0.1);
        color: #d93025;
        border-color: rgba(217, 48, 37, 0.3);
    }
    .store-balance-pill.neutral {
        background: rgba(71, 85, 105, 0.1);
        color: #475569;
        border-color: rgba(71, 85, 105, 0.3);
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
    <div class="modal-dialog modal-lg" role="document">
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

                <!-- Option Groups -->
                <div id="renew_pos_option_groups_card" class="mb-4" style="display:none">
                    <label class="form-label fw-bold">{{ trans('sw.option_groups') }}</label>
                    <div id="renew_pos_option_groups_body" class="border rounded p-3">
                        <div class="text-center py-2"><span class="spinner-border spinner-border-sm text-primary"></span></div>
                    </div>
                </div>
                <div id="renew_pos_option_ids_container"></div>
                <!-- Options Breakdown -->
                <div id="renew_pos_breakdown" class="mb-4" style="display:none"></div>

                <!-- Member Activities -->
                <div id="renew_member_activities_card" class="mb-4" style="display:none">
                    <label class="form-label fw-bold">{{ trans('sw.select_activities_for_member') }}</label>
                    <div id="renew_member_activities_body" class="border rounded p-3">
                        <div class="text-center py-2"><span class="spinner-border spinner-border-sm text-primary"></span></div>
                    </div>
                </div>

                <!-- Price Information -->
                <div class="mb-4">
                    <label class="form-label fw-bold">{{trans('sw.price')}}</label>
                    <div class="d-flex gap-2">
                        <span class="badge badge-success fs-6" id="myTotalModel">0</span>
                        <span class="badge badge-primary fs-6" id="myTotalAfterDiscountModel">{{trans('sw.after_discount')}} = 0</span>
                    </div>
                </div>

                <!-- Discount Subscription Message -->
                <div class="col-md-12 mb-4">
                    <div id="renew_discount_subscription_message"></div>
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

                @php
                    $hasAnyRenewGateway = !empty($mainSettings->payments['tabby']['merchant_code'] ?? null)
                        || !empty($mainSettings->payments['tamara']['token'] ?? null)
                        || !empty($mainSettings->payments['paymob']['api_key'] ?? null)
                        || (!empty($mainSettings->payments['paytabs']['profile_id'] ?? null) && !empty($mainSettings->payments['paytabs']['server_key'] ?? null));
                @endphp
                @if($hasAnyRenewGateway)
                <!-- Payment Gateway Cards -->
                <div class="mb-4 pgw-section" id="renew_payment_gateway_section">
                    <div class="pgw-section-title">
                        <i class="ki-outline ki-send"></i>
                        {{ trans('sw.send_payment_link') }}
                    </div>
                    <div class="pgw-grid">

                        @if(!empty($mainSettings->payments['tabby']['merchant_code'] ?? null))
                        <div class="pgw-card" id="renew_tabby_payment_option">
                            <input type="checkbox" name="send_tabby_link" id="renew_send_tabby_link" value="1" class="pgw-checkbox" style="display:none"/>
                            <div class="pgw-check"></div>
                            <div class="pgw-logo-wrap">
                                <img src="{{ asset('resources/assets/new_front/images/tabby-logo.webp') }}" alt="Tabby" class="pgw-logo">
                            </div>
                            <div class="pgw-methods">
                                <span class="badge badge-light-success fs-8 fw-semibold px-2 py-1">{{ trans('sw.buy_now_pay_later') }}</span>
                            </div>
                            <div class="pgw-desc">{{ trans('sw.tabby_payment_description') }}</div>
                        </div>
                        @endif

                        @if(!empty($mainSettings->payments['tamara']['token'] ?? null))
                        <div class="pgw-card" id="renew_tamara_payment_option">
                            <input type="checkbox" name="send_tamara_link" id="renew_send_tamara_link" value="1" class="pgw-checkbox" style="display:none"/>
                            <div class="pgw-check"></div>
                            <div class="pgw-logo-wrap">
                                <img src="{{ asset('resources/assets/new_front/images/tamara-logo.svg') }}" alt="Tamara" class="pgw-logo">
                            </div>
                            <div class="pgw-methods">
                                <span class="badge badge-light-warning fs-8 fw-semibold px-2 py-1">{{ trans('sw.buy_now_pay_later') }}</span>
                            </div>
                            <div class="pgw-desc">{{ trans('sw.tamara_payment_description') }}</div>
                        </div>
                        @endif

                        @if(!empty($mainSettings->payments['paymob']['api_key'] ?? null))
                        <div class="pgw-card" id="renew_paymob_payment_option">
                            <input type="checkbox" name="send_paymob_link" id="renew_send_paymob_link" value="1" class="pgw-checkbox" style="display:none"/>
                            <div class="pgw-check"></div>
                            <div class="pgw-logo-wrap">
                                <img src="{{ asset('resources/assets/new_front/images/paymob.png') }}" alt="Paymob" class="pgw-logo">
                            </div>
                            <div class="pgw-methods">
                                <img src="{{ asset('resources/assets/new_front/images/visa_logo.svg') }}" alt="Visa" class="pgw-method-icon">
                                <img src="{{ asset('resources/assets/new_front/images/mada-logo.svg') }}" alt="Mada" class="pgw-method-icon">
                                <img src="{{ asset('resources/assets/new_front/images/apple-pay-logo.svg') }}" alt="Apple Pay" class="pgw-method-icon">
                                <img src="{{ asset('resources/assets/new_front/images/american_express_logo.svg') }}" alt="Amex" class="pgw-method-icon">
                            </div>
                            <div class="pgw-desc">{{ trans('sw.paymob_payment_description') }}</div>
                        </div>
                        @endif

                        @if(!empty($mainSettings->payments['paytabs']['profile_id'] ?? null) && !empty($mainSettings->payments['paytabs']['server_key'] ?? null))
                        <div class="pgw-card" id="renew_paytabs_payment_option">
                            <input type="checkbox" name="send_paytabs_link" id="renew_send_paytabs_link" value="1" class="pgw-checkbox" style="display:none"/>
                            <div class="pgw-check"></div>
                            <div class="pgw-logo-wrap">
                                <img src="{{ asset('resources/assets/new_front/images/paytabs-logo.svg') }}" alt="PayTabs" class="pgw-logo">
                            </div>
                            <div class="pgw-methods">
                                <img src="{{ asset('resources/assets/new_front/images/visa_logo.svg') }}" alt="Visa" class="pgw-method-icon">
                                <img src="{{ asset('resources/assets/new_front/images/mada-logo.svg') }}" alt="Mada" class="pgw-method-icon">
                                <img src="{{ asset('resources/assets/new_front/images/apple-pay-logo.svg') }}" alt="Apple Pay" class="pgw-method-icon">
                                <img src="{{ asset('resources/assets/new_front/images/american_express_logo.svg') }}" alt="Amex" class="pgw-method-icon">
                            </div>
                            <div class="pgw-desc">{{ trans('sw.paytabs_payment_description') }}</div>
                        </div>
                        @endif

                    </div>
                </div>
                @endif
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
                <input value=""  id="renew_member_person_id"   type="hidden">
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

<script>
/* ── Payment Gateway Cards — mutual exclusivity (vanilla JS, no jQuery required) ── */
(function () {
    function initPgwCards(contextOrSelector) {
        var context = typeof contextOrSelector === 'string'
            ? document.querySelector(contextOrSelector)
            : (contextOrSelector || document);
        if (!context) return;

        var cards = context.querySelectorAll('.pgw-card');
        if (!cards.length) return;

        cards.forEach(function (card) {
            // Remove previous listener to avoid duplicates
            if (card._pgwHandler) {
                card.removeEventListener('click', card._pgwHandler);
            }
            card._pgwHandler = function () {
                var cb        = card.querySelector('.pgw-checkbox');
                var grid      = card.closest('.pgw-grid');
                var wasActive = card.classList.contains('pgw-active');

                // Deactivate all cards in this grid
                grid.querySelectorAll('.pgw-card').forEach(function (c) {
                    c.querySelector('.pgw-checkbox').checked = false;
                    c.classList.remove('pgw-active');
                });

                // Toggle: select if it wasn't selected; clicking again deselects
                if (!wasActive) {
                    cb.checked = true;
                    card.classList.add('pgw-active');
                }
            };
            card.addEventListener('click', card._pgwHandler);
        });
    }

    // Init for renew modal cards on DOM ready
    document.addEventListener('DOMContentLoaded', function () {
        initPgwCards('#renew_payment_gateway_section');
    });

    // Re-init when the renew modal is shown (Bootstrap fires this on the DOM element)
    document.addEventListener('DOMContentLoaded', function () {
        var modelRenew = document.getElementById('modelRenew');
        if (modelRenew) {
            modelRenew.addEventListener('show.bs.modal', function () {
                initPgwCards('#renew_payment_gateway_section');
            });
        }
    });

    // Expose for other contexts (new member form, edit form)
    window.initPgwCards = initPgwCards;
})();
</script>

<script>
// ── Renew Modal Option Groups ────────────────────────────────────────────────
window.renewLastPaidTotal = 0;
(function() {
    var renewPosOptionsUrl = '{{ route("sw.subscription.options", ":id") }}';
    var renewPosCalcUrl    = '{{ route("sw.subscription.calculatePrice", ":id") }}';
    var RENEW_VAT_PCT      = {{ (float)(@$mainSettings->vat_details['vat_percentage'] ?? 0) }};
    var renewLang          = '{{ app()->getLocale() }}';

    window.renewPosLoadOptionGroups = function(subId, preSelectedIds) {
        var $card = $('#renew_pos_option_groups_card');
        var $body = $('#renew_pos_option_groups_body');
        if (!subId) { $card.hide(); return; }
        $card.show();
        $body.html('<div class="text-center py-2"><span class="spinner-border spinner-border-sm text-primary"></span></div>');
        $.ajax({
            url: renewPosOptionsUrl.replace(':id', subId),
            method: 'GET', data: { channel: 1 },
            headers: { 'Accept': 'application/json' },
            success: function(res) {
                var groups = res.option_groups || [];
                if (!groups.length) { $card.hide(); return; }
                renewPosRenderGroups(groups, preSelectedIds || [], subId);
            },
            error: function() { $card.hide(); }
        });
    };

    var renewMemberActivitiesUrl = '{{ route("sw.subscription.memberActivities", ":id") }}';

    window.renewLoadMemberActivities = function(subId) {
        var $card = $('#renew_member_activities_card');
        var $body = $('#renew_member_activities_body');
        if (!subId) { $card.hide(); $body.empty(); return; }
        $card.show();
        $body.html('<div class="text-center py-2"><span class="spinner-border spinner-border-sm text-primary"></span></div>');
        $.ajax({
            url: renewMemberActivitiesUrl.replace(':id', subId),
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            success: function(res) {
                var activities = res.activities || [];
                if (!activities.length) { $card.hide(); $body.empty(); return; }
                renewRenderMemberActivities(activities, res.activity_limit);
            },
            error: function() { $card.hide(); $body.empty(); }
        });
    };

    function renewRenderMemberActivities(activities, activityLimit) {
        var $body = $('#renew_member_activities_body');
        var hasLimit = !!activityLimit;
        $body.empty();
        var $row = $('<div class="row g-3">');
        activities.forEach(function(activity, idx) {
            var checked = !hasLimit || idx < activityLimit;
            var $col = $('<div class="col-md-6">');
            var $wrap = $('<div class="form-check form-check-custom form-check-solid p-2">');
            var $input = $('<input type="checkbox" class="form-check-input renew-member-activity-check">')
                .attr('name', 'renew_member_activity_' + activity.activity_id)
                .attr('id', 'renew_member_activity_' + activity.activity_id)
                .val(activity.activity_id)
                .prop('checked', checked)
                .on('change', function() { renewEnforceMemberActivityLimit(activityLimit); });
            var $label = $('<label class="form-check-label ms-1">')
                .attr('for', 'renew_member_activity_' + activity.activity_id)
                .html('<span class="fw-bold">' + activity.name + '</span>'
                    + (activity.trainer_name ? '<span class="text-muted fs-8 d-block"><i class="bi bi-person-badge me-1"></i>' + activity.trainer_name + '</span>' : '')
                    + '<span class="text-muted fs-8 d-block"><i class="bi bi-repeat me-1"></i>{{ trans("sw.training_times") }}: ' + (activity.training_times || 0) + '</span>');
            $wrap.append($input).append($label);
            $col.append($wrap);
            $row.append($col);
        });
        $body.append($row);
        renewEnforceMemberActivityLimit(activityLimit);
    }

    window.renewEnforceMemberActivityLimit = function(activityLimit) {
        if (!activityLimit) return;
        var checkedCount = $('.renew-member-activity-check:checked').length;
        $('.renew-member-activity-check:not(:checked)').prop('disabled', checkedCount >= activityLimit);
    };

    window.renewCollectSelectedActivities = function() {
        var ids = [];
        $('.renew-member-activity-check:checked').each(function() {
            ids.push(parseInt($(this).val()));
        });
        return ids;
    };

    function renewPosRenderGroups(groups, preSelectedIds, subId) {
        var $body = $('#renew_pos_option_groups_body');
        $body.empty();
        var $row = $('<div class="row g-3">');
        groups.forEach(function(group) {
            var isSingle   = group.selection_type === 'single';
            var isRequired = group.is_required;
            var optCount   = (group.options || []).length;
            var isProduct  = group.source_type === 'product';
            var isPill     = !isProduct && optCount <= 6;
            var $col = $('<div class="col-md-6">');

            var $hdr = $('<div class="d-flex flex-wrap align-items-center gap-1 mb-1">');
            $hdr.append($('<span class="fw-semibold fs-7">').text(group['name_' + renewLang] || group.name_ar || ''));
            if (isRequired) $hdr.append($('<span class="badge badge-light-danger fs-9 px-1">').text('{{ trans("sw.mandatory") }}'));
            $hdr.append($('<span class="badge badge-light-secondary fs-9 px-1">').text(
                isSingle ? '{{ trans("sw.single") }}' : '{{ trans("sw.multiple") }}'
            ));
            $col.append($hdr);

            if (isPill) {
                var $pills = $('<div class="d-flex flex-wrap gap-1">');
                (group.options || []).forEach(function(opt) {
                    var price = parseFloat(opt.price_modifier || 0);
                    var name;
                    if (opt.product) {
                        name = opt.product['display_name_' + renewLang] || opt.product['name_' + renewLang] || opt.product.name_ar || '';
                    } else if (opt.activity) {
                        name = opt.activity['name_' + renewLang] || opt.activity.name_ar || '';
                    } else {
                        name = opt['name_' + renewLang] || opt.name_ar || '';
                    }
                    var $pill = $('<label class="pos-pill">');
                    var $inp  = $('<input class="d-none renew-pos-opt-check">')
                        .attr('type', isSingle ? 'radio' : 'checkbox')
                        .attr('name', 'renew_grp_' + group.id)
                        .attr('data-group-id', group.id)
                        .attr('data-price', price)
                        .val(opt.id)
                        .on('change', function() {
                            if (isSingle) {
                                $pills.find('.pos-pill').removeClass('active');
                                $('#renew_pos_option_groups_body .renew-pos-opt-check[data-group-id="' + group.id + '"]').not(this).prop('checked', false);
                            }
                            $(this).closest('.pos-pill').toggleClass('active', $(this).is(':checked'));
                            renewPosUpdatePrice(subId);
                        });
                    var lbl = name + (price !== 0 ? ' (' + (price > 0 ? '+' : '') + Math.round(price) + ')' : '');
                    $pill.append($inp).append($('<span>').text(lbl));
                    if ((preSelectedIds || []).indexOf(opt.id) !== -1) { $inp.prop('checked', true); $pill.addClass('active'); }
                    $pills.append($pill);
                });
                $col.append($pills);

            } else if (isProduct) {
                if (optCount > 6) {
                    $col.append(
                        $('<input type="text" class="form-control form-control-sm mb-1" placeholder="بحث...">').on('input', function() {
                            var q = $(this).val().toLowerCase();
                            $(this).next('.pos-product-grid').find('.pos-prod-item').each(function() {
                                $(this).toggle($(this).data('name').toLowerCase().indexOf(q) !== -1);
                            });
                        })
                    );
                }
                var $grid = $('<div class="row g-1 pos-product-grid" style="max-height:200px;overflow-y:auto;padding:2px;">');
                (group.options || []).forEach(function(opt) {
                    var price  = parseFloat(opt.price_modifier || 0);
                    var name   = '', imgSrc = null;
                    if (opt.product) {
                        name   = opt.product['display_name_' + renewLang] || opt.product['name_' + renewLang] || opt.product.name_ar || '';
                        imgSrc = opt.product.image || null;
                    } else if (opt.activity) {
                        name   = opt.activity['name_' + renewLang] || opt.activity.name_ar || '';
                        imgSrc = opt.activity.image || null;
                    } else {
                        name = opt['name_' + renewLang] || opt.name_ar || '';
                    }
                    var $cell  = $('<div class="col-6 pos-prod-item">').data('name', name);
                    var $label = $('<label class="d-flex align-items-center gap-1 p-1 rounded border-hover-primary cursor-pointer" style="min-height:44px;">');
                    var $inp   = $('<input class="form-check-input renew-pos-opt-check flex-shrink-0 mt-0">')
                        .attr('type', isSingle ? 'radio' : 'checkbox')
                        .attr('name', 'renew_grp_' + group.id)
                        .attr('data-group-id', group.id)
                        .attr('data-price', price)
                        .val(opt.id)
                        .on('change', function() {
                            if (isSingle) $('#renew_pos_option_groups_body .renew-pos-opt-check[data-group-id="' + group.id + '"]').not(this).prop('checked', false);
                            renewPosUpdatePrice(subId);
                        });
                    if ((preSelectedIds || []).indexOf(opt.id) !== -1) $inp.prop('checked', true);
                    $label.append($inp);
                    if (imgSrc) $label.append($('<img>').attr('src', imgSrc).addClass('pos-prod-thumb'));
                    var $info = $('<div class="overflow-hidden lh-sm">');
                    $info.append($('<div class="fs-9 text-truncate" style="max-width:80px;" title="' + name + '">').text(name));
                    if (price !== 0) $info.append($('<span class="badge badge-light-primary px-1" style="font-size:10px;">').text((price > 0 ? '+' : '') + Math.round(price)));
                    $label.append($info);
                    $cell.append($label);
                    $grid.append($cell);
                });
                $col.append($grid);

            } else {
                $col.append(
                    $('<input type="text" class="form-control form-control-sm mb-1" placeholder="بحث / Search...">').on('input', function() {
                        var q = $(this).val().toLowerCase();
                        $(this).siblings('.pos-option-list').find('.pos-option-item').each(function() {
                            $(this).toggle($(this).text().toLowerCase().indexOf(q) !== -1);
                        });
                    })
                );
                var $list = $('<div class="d-flex flex-column gap-1 pos-option-list" style="max-height:180px;overflow-y:auto;">');
                (group.options || []).forEach(function(opt) {
                    var price = parseFloat(opt.price_modifier || 0);
                    var name  = opt['name_' + renewLang] || opt.name_ar || '';
                    if (opt.product) name = opt.product['display_name_' + renewLang] || opt.product['name_' + renewLang] || opt.product.name_ar || '';
                    else if (opt.activity) name = opt.activity['name_' + renewLang] || opt.activity.name_ar || '';
                    var $label = $('<label class="d-flex align-items-center gap-2 cursor-pointer p-1 rounded border-hover-primary">');
                    var $inp   = $('<input class="form-check-input renew-pos-opt-check mt-0">')
                        .attr('type', isSingle ? 'radio' : 'checkbox')
                        .attr('name', 'renew_grp_' + group.id)
                        .attr('data-group-id', group.id)
                        .attr('data-price', price)
                        .val(opt.id)
                        .on('change', function() {
                            if (isSingle) $('#renew_pos_option_groups_body .renew-pos-opt-check[data-group-id="' + group.id + '"]').not(this).prop('checked', false);
                            renewPosUpdatePrice(subId);
                        });
                    if ((preSelectedIds || []).indexOf(opt.id) !== -1) $inp.prop('checked', true);
                    $label.append($inp).append($('<span class="flex-grow-1 fs-8">').text(name));
                    if (price !== 0) $label.append($('<span class="badge badge-light-primary fs-9">').text((price > 0 ? '+' : '') + Math.round(price)));
                    $list.append($('<div class="pos-option-item">').append($label));
                });
                $col.append($list);
            }

            $row.append($col);
        });
        $body.append($row);
        if ((preSelectedIds || []).length) renewPosUpdatePrice(subId);
    }

    function renewPosUpdatePrice(subId) {
        var optionIds = [];
        $('#renew_pos_option_groups_body .renew-pos-opt-check:checked').each(function() {
            optionIds.push(parseInt($(this).val()));
        });
        var $cont = $('#renew_pos_option_ids_container');
        $cont.empty();
        optionIds.forEach(function(id) { $cont.append($('<input type="hidden" name="option_ids[]">').val(id)); });
        $.ajax({
            url: renewPosCalcUrl.replace(':id', subId),
            method: 'POST', data: { option_ids: optionIds },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' },
            dataType: 'json',
            success: function(res) {
                window.renewPosOptionsTotal = parseFloat(res.options_total || 0);
                var baseP = parseFloat(res.base_price || 0);
                renewPosRenderBreakdown(res, baseP, window.renewPosOptionsTotal);
                // Refresh amount paid with new total (accounting for any existing discount)
                var discount = parseFloat($('#renew_discount_value').val() || 0);
                var subtotal = baseP + window.renewPosOptionsTotal;
                var vat = RENEW_VAT_PCT > 0 ? (subtotal - discount) * RENEW_VAT_PCT / 100 : 0;
                var total = parseFloat((subtotal + (RENEW_VAT_PCT > 0 ? subtotal * RENEW_VAT_PCT / 100 : 0)).toFixed(2));
                var paidTotal = parseFloat((subtotal - discount + vat).toFixed(2));
                window.renewLastPaidTotal = paidTotal;
                $('#renew_amount_paid').val(paidTotal.toFixed(2)).attr('max', paidTotal.toFixed(2));
                $('#renew_amount_remaining').val((0).toFixed(2));
                $('#myTotalModel').text((typeof trans_price !== 'undefined' ? trans_price : 'Price') + ' = ' + (baseP + window.renewPosOptionsTotal).toFixed(2));
                $('#myTotalWithVatModal').text((typeof trans_price !== 'undefined' ? trans_price : 'Price') + ' = ' + total.toFixed(2));
            },
            error: function() { window.renewPosOptionsTotal = 0; }
        });
    }

    function renewPosRenderBreakdown(res, baseP, optsP) {
        var $wrap = $('#renew_pos_breakdown');
        var opts = res.selected_options || [];
        if (!opts.length) { $wrap.hide(); return; }
        var html = '<div class="p-3 rounded" style="background:#f0fdf4;border:1px dashed #16a34a">'
            + '<div class="fw-bold text-success mb-2 fs-7"><i class="bi bi-receipt me-1"></i>' + (typeof trans_price !== 'undefined' ? '{{ trans("sw.price_breakdown") }}' : 'Price Breakdown') + '</div>'
            + '<div class="d-flex justify-content-between text-muted fs-7 mb-1"><span>{{ trans("sw.base_price") }}</span><span>' + baseP.toFixed(2) + ' {{ trans("sw.app_currency") }}</span></div>';
        opts.forEach(function(o) {
            var mod = parseFloat(o.price_modifier || 0);
            var name = o['name_' + renewLang] || o.name_ar || o.name_en || '';
            if (!name) return;
            var modLabel = mod === 0 ? '{{ trans("sw.app_currency") == "ر.س" ? "مجاناً" : "Free" }}' : (mod > 0 ? '+' : '') + mod.toFixed(2) + ' {{ trans("sw.app_currency") }}';
            html += '<div class="d-flex justify-content-between text-success fs-7 mb-1"><span><i class="bi bi-check2 me-1"></i>' + $('<span>').text(name).html() + '</span><span>' + modLabel + '</span></div>';
        });
        var subtotal = baseP + optsP;
        html += '<div class="d-flex justify-content-between fw-bold border-top border-success mt-2 pt-2"><span>{{ trans("sw.total") }}</span><span>' + subtotal.toFixed(2) + ' {{ trans("sw.app_currency") }}</span></div>';
        if (RENEW_VAT_PCT > 0) {
            var vatAmt = parseFloat((subtotal * RENEW_VAT_PCT / 100).toFixed(2));
            html += '<div class="d-flex justify-content-between text-muted fs-7 mt-1"><span>{{ trans("sw.vat") }} (' + RENEW_VAT_PCT + '%)</span><span>+' + vatAmt.toFixed(2) + ' {{ trans("sw.app_currency") }}</span></div>';
            html += '<div class="d-flex justify-content-between fw-bold text-primary mt-1"><span>{{ trans("sw.total_after_vat") }}</span><span>' + (subtotal + vatAmt).toFixed(2) + ' {{ trans("sw.app_currency") }}</span></div>';
        }
        html += '</div>';
        $wrap.html(html).show();
    }
})();
// ── End Renew Modal Option Groups ────────────────────────────────────────────
</script>

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
                                <span id="client_balance" class="store-balance-pill neutral">
                                    <i class="ki-outline ki-wallet"></i>
                                    <span class="value-text">0</span>
                                </span>
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



