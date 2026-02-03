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
    <!---Internal Fileupload css-->
    <link href="{{asset('/')}}resources/assets/new_front/global/scripts/css/fileupload.css" rel="stylesheet"
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
    </style>
@endsection
@section('page_body')


    <!--begin::User Edit Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
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
                        <a class="modal-effect" data-effect="effect-newspaper" onclick="startWebCam()"
                           data-toggle="modal" href="#modalCamera"> <i class="fa fa-camera text-muted"
                                                                       title="{{ trans('sw.camera_msg')}}"
                                                                       aria-hidden="true"></i></a>
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
                        <label class="required form-label">{{ trans('sw.identification_code')}}</label>
                        <!--end::Label-->
                        <!--begin::Input group-->
                        <div class="row">
                            <div class="col-md-10">
                                <input name="barcode" onkeydown="return event.key!=='Enter';" value="{{ old('barcode', $member->barcode) }}"
                                       type="text" class="form-control mb-2"
                                       id="subscribedClientInputCode" placeholder="{{ trans('sw.enter_identification_code')}}" required>
                            </div>
                            <div class="col-md-2">
                                <input type="button" onclick="getRandomBarcode();" class="btn btn-primary rounded-3" value="{{ trans('sw.generate_identification_code')}}">
                            </div>
                        </div>
                        <!--end::Input group-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.name')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input name="name" value="{{ old('name', $member->name) }}" type="text" class="form-control mb-2"
                               id="unsubscribedClientInputName" placeholder="{{ trans('sw.enter_name')}}">
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.phone')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input name="phone" value="{{ old('phone', $member->phone) }}" type="text" class="form-control mb-2"
                               id="subscribedClientInputPhone"
                               placeholder="{{ trans('sw.enter_phone')}}">
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.address')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input name="address" value="{{ old('address', $member->address) }}" type="text" class="form-control mb-2"
                               id="subscribedClientInputAddress"
                               placeholder="{{ trans('sw.enter_address')}}">
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.date_of_barth')}}</label>
                        <!--end::Label-->
                        <!--begin::Date picker-->
                        <div class="input-group date date-picker" data-date-format="yyyy-mm-dd" data-date-start-date="+0d">
                            <input class="form-control mb-2" autocomplete="off" placeholder="{{ trans('sw.date_of_barth')}}"
                                   name="dob"
                                   value="{{ old('dob', $member->dob) }}"
                                   type="text">
                            <span class="input-group-btn">
                                <button class="btn btn-light" type="button"><i class="fa fa-calendar"></i></button>
                            </span>
                        </div>
                        <!--end::Date picker-->
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group-->
                    <div id="EditExistsExpireDate" class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label" for="subscribedClientInputExistsExpireDate">{{ trans('sw.current_expire_date')}}</label>
                        <!--end::Label-->
                        <!--begin::Date picker-->
                        <div class="input-group date date-picker" data-date-format="yyyy-mm-dd" data-date-start-date="+0d">
                            <input class="form-control mb-2"
                                   placeholder="{{ trans('sw.current_expire_date')}}"
                                   @if(!$member->member_subscription_info->subscription->is_expire_changeable) disabled @endif
                                   id="subscribedClientInputExistsExpireDate"
                                   name="exists_expire_date"
                                   value="{{\Carbon\Carbon::parse($member->member_subscription_info->expire_date)->toDateString()}}"
                                   type="text">
                            <span class="input-group-btn">
                                <button class="btn btn-light" type="button"><i class="fa fa-calendar"></i></button>
                            </span>
                        </div>
                        <!--end::Date picker-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.membership')}}</label>
                        <!--end::Label-->
                        <!--begin::Select-->
                        <select id="EditMembershipSelect" name="subscription_id" class="form-select form-select-solid mb-2 select2" disabled>
                            @foreach($subscriptions as $subscription)
                                <option value="{{$subscription->id}}" expire_date="{{\Carbon\Carbon::now()->addDays($subscription->period)->toDateString()}}" IsChangeable="{{$subscription->is_expire_changeable}}" price="{{$subscription->price}}" @if(@$member->member_subscription_info->subscription_id == $subscription->id) selected="" @endif>{{$subscription->name}}</option>
                            @endforeach
                        </select>
                        <!--end::Select-->
                    </div>
                    <!--end::Input group-->
            <div id="EditCustomExpireDiv" class="form-group">

                <label class="col-md-3">{{ trans('sw.membership_expire_date')}}</label>
                <div class="col-md-9">
                    <div class="input-group input-medium date date-picker" data-date-format="yyyy-mm-dd" data-date-start-date="+0d">
                    <input id="expire_date_membership"  class="form-control form-control-inline input-medium "  autocomplete="off" title="{{ trans('sw.membership_expire_date')}}" placeholder="{{ trans('sw.membership_expire_date')}}" name="custom_expire_date" value="" type="text">
                        <span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
                    </div>
                </div>

            </div><!-- end custom expire date div -->
            <div class="form-group  col-md-12">
                <label class="col-md-3  control-label">&nbsp;</label>
                <div class="col-md-9 ">
            <span class="tag tag-orange font-weight-bolder rounded-3" id="myFinalExpireDate"></span>
                </div>
            </div><!-- end total price div -->

            <div class="form-group  col-md-12">
                <label class="col-md-3  control-label">&nbsp;</label>
                <div class="col-md-9 ">
                <span class="tag tag-orange font-weight-bolder rounded-3" id="myTotal">@if(@$member) {{ trans('sw.price')}} = {{@($member->member_subscription_info->subscription->price - $member->member_subscription_info->amount_remaining)}} @endif</span>
                </div>
            </div><!-- end total price div -->


            <div style="clear:both;padding-top: 25px"></div>

            <div class="form-group col-md-12">
                <label class="col-md-3">&nbsp;</label>
                <div class="col-lg-3">
                    <label class="rdiobox"><input checked name="typeRequest" value="edit_request"
                                                  type="radio"> <span>{{ trans('sw.edit')}}</span></label>
                </div>
                <div class="col-lg-3 mg-t-20 mg-lg-t-0">
                    <label class="rdiobox"><input name="typeRequest" value="renew_request" type="radio">
                        <span>{{ trans('sw.renew_membership')}}</span></label>
                </div>

            </div>
            <div style="clear: both"></div>
            <div class="form-group total_amount_renew_div col-md-12">
                <label class="col-md-3 control-label">{{ trans('sw.amount_paid')}} <span class="required"></span></label>
                <div class="col-md-3">
                    <input id="create_amount_paid" class="form-control" name="amount_paid" value="{{ old('amount_paid', @($member->member_subscription_info->subscription->price - $member->member_subscription_info->amount_remaining)) }}"
                           placeholder="{{ trans('sw.enter_amount_paid')}}" type="number" min="0"/>
                </div>
                <label class="col-md-3 control-label">{{ trans('sw.amount_remaining')}} <span class="required"></span></label>
                <div class="col-md-3">
                    <input  id="create_amount_remaining" class="form-control" name="amount_remaining" value="{{@old('amount_remaining', @$member->member_subscription_info->amount_remaining)}}"
                            placeholder="{{ trans('sw.enter_amount_remaining')}}" disabled type="number" min="0"/>
                </div>
            </div><!-- end total amount paid and amount remaining  div -->



            <div class="form-actions" style="clear:both;">
                <div class="row">
                    <div class="col-md-offset-3 col-md-9">
                        <button type="submit" class="btn green">{{ trans('global.save')}}</button>
                        <input type="reset" class="btn default" value="{{ trans('admin.reset')}}">
                    </div>
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
    <script src="{{asset('resources/assets/new_front/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
            type="text/javascript"></script>
    <script src="{{asset('/')}}resources/assets/new_front/global/scripts/metronic.js" type="text/javascript"></script>
    <script src="{{asset('/')}}resources/assets/new_front/pages/scripts/components-pickers.js"></script>
    <script>

        jQuery(document).ready(function() {
            ComponentsPickers.init();
        });

        var selectedMembershipPrice = 0;

        EditGetPriceMemberShip();
        $('#EditMembershipSelect').change(function () {
            console.log('EditMembershipSelect change ');

            EditGetPriceMemberShip();


        });

        function EditGetPriceMemberShip() {
            console.log('in getPriceMemberShip ');
            selectedMembershipPrice = 0;

            selectedMembershipPrice = $('#EditMembershipSelect').children("option:selected").attr('price');
            console.log('after each getPriceMemberShip ');
            $('#myTotal').text("السعر = " + parseFloat(selectedMembershipPrice));

            $('#create_amount_paid').val(selectedMembershipPrice);
            $('#create_amount_remaining').val(0);
            editSetUpInputExpire();

        }

        function editSetUpInputExpire() {
            var EditSelectedMembership = $('#EditMembershipSelect').children("option:selected");


            // let editDivExpire = '<label class="col-md-3">تاريخ انتهاء العضوية</label>\n' +
            //     '<div class="col-md-9">\n' +
            //     '<input id="expire_date_membership"  class="form-control form-control-inline input-medium date-picker"  autocomplete="off" title="تاريخ انتهاء العضوية" placeholder="تاريخ انتهاء العضوية" name="custom_expire_date" value="' + EditSelectedMembership.attr('expire_date') + '" type="text">\n</div>' +
            //     ' \n';


            if (EditSelectedMembership.attr('ischangeable') === '1') {
                console.log('in if edit page :' + EditSelectedMembership.attr('ischangeable'));
                $('#EditCustomExpireDiv').show();
                document.getElementById('expire_date_membership').value = EditSelectedMembership.attr('expire_date');

                 //$('#EditCustomExpireDiv').html(editDivExpire);

                $('.fc-datepicker').datepicker({
                    format: 'yyyy-mm-dd',
                    autoclose: true

                });
                getFinalExpireDate();
                $('#myFinalExpireDate').show(500, 'swing');

                $('#expire_date_membership').change(function () {
                    getFinalExpireDate();
                });

            } else {
                 // $('#EditCustomExpireDiv').html('');
                 $('#EditCustomExpireDiv').hide(500, 'swing');

                $('#myFinalExpireDate').hide(500, 'swing');
                console.log('in else edit page :' + EditSelectedMembership.attr('ischangeable'));
            }

        }

        function getFinalExpireDate() {
            let days_renew = '{{(\Carbon\Carbon::now()->diffInDays($member->member_subscription_info->expire_date) )}}';
            var edit_d = new Date($('#expire_date_membership').val());
            edit_d.setDate(edit_d.getDate() + parseInt(days_renew));
            var edit_nd = new Date(edit_d);
            var final_expire_date = edit_nd.getFullYear() + '-' + (edit_nd.getMonth() + 1) + '-' + edit_nd.getDate();
            $('#myFinalExpireDate').text("{{ trans('sw.expire_date_after_new_membership')}} = " + (final_expire_date));

        }

        @if(!@$member->member_subscription_info->amount_remaining)
            getPriceMemberShip();
        @endif

        @if(!@$member->barcode)
            getRandomBarcode();
        @endif

        function getRandomBarcode() {
            min = Math.ceil({{$maxId+5}});
            max = Math.floor({{$maxId+15}});
            number =  (Math.floor(Math.random() * (max - min + 1)) + min);
            document.getElementById("subscribedClientInputCode").value = number;

        }

        $("#create_amount_paid").change(function () {
            selectedMembershipPrice = 0;
            $.each($("#EditMembershipSelect option:selected"), function () {

                selectedMembershipPrice = selectedMembershipPrice + (parseFloat($(this).attr('price')));

            });
            let valueAmountPaid = $('#create_amount_paid').val();
            $('#create_amount_remaining').val(selectedMembershipPrice - valueAmountPaid);
        });

        $('input[type="radio"]').on('click change', function (e) {

            checkRadioRequest();
        });
        checkRadioRequest();

        function checkRadioRequest() {
            let typeRadioChecked = $('input[name=typeRequest]:checked').val();
            if (typeRadioChecked === "renew_request") {
                $('.total_amount_renew_div').show(500, 'swing');
                $('#EditCustomExpireDiv').show(500, 'swing');

                $('#EditMembershipSelect').prop("disabled", false);
                $('#EditExistsExpireDate').hide(500, 'swing');
                EditGetPriceMemberShip();

            } else {

                $('.total_amount_renew_div').hide(500, 'swing');
                $('#EditCustomExpireDiv').hide(500, 'swing');
                $('#myFinalExpireDate').hide(500, 'swing');
                $('#EditMembershipSelect').prop("disabled", true);

                $('#EditExistsExpireDate').show(500, 'swing');
            }
        }

        $('#membership').change(function () {
            selectedMembershipPrice = 0;
            $.each($("#membership option:selected"), function () {

                selectedMembershipPrice = selectedMembershipPrice + (parseFloat($(this).attr('price')));

            });

            $('#create_amount_paid').val(selectedMembershipPrice);
            $('#create_amount_remaining').val(0);

            $('#myTotal').text("{{ trans('sw.price')}} = " + selectedMembershipPrice);

        });

        function getPriceMemberShip() {
            selectedMembershipPrice = 0;
            $.each($("#membership option:selected"), function () {

                selectedMembershipPrice = selectedMembershipPrice + (parseFloat($(this).attr('price')));

            });

            $('#create_amount_paid').val(selectedMembershipPrice);
            $('#create_amount_remaining').val(0);
            $('#myTotal').text("{{ trans('sw.price')}} = " + selectedMembershipPrice);
        }

    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.25/webcam.min.js"></script>
    <script>
        function startWebCam() {
            Webcam.set({
                width: 450,
                height: 360,
                image_format: 'png',
                jpeg_quality: 100
            });

            Webcam.attach('#my_camera');


        }

        function take_snapshot() {
            var path_mp3 = 'https://demo.egym.site/assets/mp3';
            var shutter = new Audio();
            shutter.autoplay = true;
            shutter.src = navigator.userAgent.match(/Firefox/) ? 'shutter.ogg' : path_mp3 + '/' + 'shutter.mp3';

            Webcam.snap(function (data_uri) {
                $('.dropify-preview').attr('style', 'display:block');
                $('.dropify-render').html('<img src="' + data_uri + '" style="max-height: 200px;">');
                $('#photo_camera').val(data_uri);
                shutter.play();

            });
        }
    </script>

    <!--Internal Fileuploads js-->
    <script src="{{asset('/')}}resources/assets/new_front/global/scripts/js/fileupload.js"></script>
    <script src="{{asset('/')}}resources/assets/new_front/global/scripts/js/file-upload.js"></script>


    <script>
        $('.dropify-infos-message').html("{{ trans('sw.upload_image')}}");
        $('.dropify-message p:first').html("{{ trans('sw.upload_image')}}");
        $('.dropify-clear').html("{{ trans('sw.remove')}}");
    </script>

    <script>
        // showing modal with effect
        $('.modal-effect').on('click', function (e) {
            e.preventDefault();
            var effect = $(this).attr('data-effect');
            $('#modalCamera').addClass(effect);

        });
    </script>

@endsection


