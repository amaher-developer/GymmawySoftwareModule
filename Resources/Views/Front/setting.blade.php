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
@section('list_title') {{ @$title }} @endsection

@section('form_title') {{ @$title }} @endsection
@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('resources/assets/admin/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('resources/assets/admin/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css')}}"/>
    <style>
        .form .form-bordered .form-group {
            margin: 0;
            border: 1px solid #efefef;
        }
        .setting-images{object-fit: contain;
            height: 150px !important;
            object-fit: contain;

        }
    </style>

@endsection
@section('page_body')

<!--begin::Container-->
<div id="kt_content_container" class="container-xxl">
<!--begin::Card-->
<div class="card card-flush">
									<!--begin::Card body-->
									<div class="card-body">
			<form action="" method="post" enctype='multipart/form-data' id="settings_form">
            {{ csrf_field() }}
										<!--begin:::Tabs-->
										<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-4 fw-semibold mb-15">
											<!--begin:::Tab item-->
											<li class="nav-item">
					<a class="nav-link text-active-primary d-flex align-items-center pb-5 active" data-bs-toggle="tab" href="#info">
					<i class="ki-outline ki-home fs-2 me-2"></i>{{ trans('sw.webSite_info')}}</a>
											</li>
											<!--end:::Tab item-->
											<!--begin:::Tab item-->
											<li class="nav-item">
					<a class="nav-link text-active-primary d-flex align-items-center pb-5" data-bs-toggle="tab" href="#social">
					<i class="ki-outline ki-fasten fs-2 me-2"></i>{{ trans('sw.links')}}</a>
											</li>
											<!--end:::Tab item-->
											<!--begin:::Tab item-->
											<li class="nav-item">
					<a class="nav-link text-active-primary d-flex align-items-center pb-5" data-bs-toggle="tab" href="#contacts">
					<i class="ki-outline ki-phone fs-2 me-2"></i>{{ trans('sw.contacts')}}</a>
											</li>
											<!--end:::Tab item-->
											<!--begin:::Tab item-->
											<li class="nav-item">
					<a class="nav-link text-active-primary d-flex align-items-center pb-5" data-bs-toggle="tab" href="#vat">
					<i class="ki-outline ki-percentage fs-2 me-2"></i>{{ trans('sw.vat_file')}}</a>
											</li>
											<!--end:::Tab item-->
											<!--begin:::Tab item-->
											<li class="nav-item">
					<a class="nav-link text-active-primary d-flex align-items-center pb-5" data-bs-toggle="tab" href="#meta">
					<i class="ki-outline ki-code fs-2 me-2"></i>{{ trans('sw.meta_tags')}}</a>
											</li>
											<!--end:::Tab item-->
										</ul>
										<!--end:::Tabs-->
										<!--begin:::Tab content-->
										<div class="tab-content" id="myTabContent">
											<!--begin:::Tab pane-->
				<div class="tab-pane fade show active" id="info" role="tabpanel">
					<!--begin::Section Header-->
                    <div class="mb-10">
                        <h4 class="text-dark fw-bold mb-5">
                            <i class="ki-outline ki-home fs-2 me-2 text-primary"></i>
                            {{ trans('sw.webSite_info') ?? 'Website Information' }}
                        </h4>
                        <p class="text-muted fs-6">{{ trans('sw.website_info_description') ?? 'Configure your website basic information, logos, and content.' }}</p>
														</div>
                    <!--end::Section Header-->

                    <!--begin::Row - Website Titles-->
													<div class="row fv-row mb-7">
                        <div class="col-md-6 fv-row">
															<!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-notepad-edit me-2 text-success"></i>{{ trans('sw.title_ar')}}
                                <span class="required"></span>
															</label>
															<!--end::Label-->
															<!--begin::Input-->
                            <input type="text" class="form-control form-control-solid" dir="rtl"
                                   placeholder="{{ trans('sw.enter_title_ar') ?? 'Enter Arabic title' }}"
                                   value="{{$mainSettings->name_ar}}" name="name_ar" required
                                   data-bv-trigger="keyup change"
                                   data-bv-notempty-message="{{ trans('generic::global.required')}}"/>
															<!--end::Input-->
														</div>
                        <div class="col-md-6 fv-row">
															<!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-notepad-edit me-2 text-info"></i>{{ trans('sw.title_en')}}
                                <span class="required"></span>
															</label>
															<!--end::Label-->
															<!--begin::Input-->
                            <input type="text" class="form-control form-control-solid" dir="ltr"
                                   placeholder="{{ trans('sw.enter_title_en') ?? 'Enter English title' }}"
                                   value="{{$mainSettings->name_en}}" name="name_en" required
                                   data-bv-trigger="keyup change"
                                   data-bv-notempty-message="{{ trans('generic::global.required')}}"/>
															<!--end::Input-->
														</div>
													</div>
                    <!--end::Row-->

                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-10"></div>
                    <!--end::Separator-->

                    <!--begin::Row - Logos-->
													<div class="row fv-row mb-7">
                        <div class="col-md-6 fv-row">
															<!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-image me-2 text-warning"></i>{{ trans('sw.logo_ar')}}
                                <span class="required"></span>
															</label>
															<!--end::Label-->
                            @if($mainSettings->logo_ar!='')
                                <div class="text-center mb-3">
                                    <img width="200" src="{{$mainSettings->logo_ar}}" class="img-thumbnail"/>
														</div>
                            @endif
															<!--begin::Input-->
                            <input type="file" class="form-control form-control-solid"
                                   name="logo_ar" @if($mainSettings->logo_ar=='')required
                                   data-bv-notempty-message="{{ trans('generic::global.required')}}"
                                   @endif/>
															<!--end::Input-->
														</div>
                        <div class="col-md-6 fv-row">
															<!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-image me-2 text-primary"></i>{{ trans('sw.logo_en')}}
                                <span class="required"></span>
															</label>
															<!--end::Label-->
                            @if($mainSettings->logo_en!='')
                                <div class="text-center mb-3">
                                    <img width="200" src="{{$mainSettings->logo_en}}" class="img-thumbnail"/>
														</div>
                            @endif
															<!--begin::Input-->
                            <input type="file" class="form-control form-control-solid"
                                   name="logo_en" @if($mainSettings->logo_en=='')required
                                   data-bv-notempty-message="{{ trans('generic::global.required')}}"
                                   @endif/>
															<!--end::Input-->
														</div>
													</div>
                    <!--end::Row-->

                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-10"></div>
                    <!--end::Separator-->

                    <!--begin::Row - About Content-->
													<div class="row fv-row mb-7">
                        <div class="col-md-6 fv-row">
															<!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-document me-2 text-success"></i>{{ trans('sw.about_ar')}}
                                <span class="required"></span>
															</label>
															<!--end::Label-->
															<!--begin::Input-->
                            <textarea required data-bv-trigger="keyup change" dir="rtl"
                                      data-bv-notempty-message="{{ trans('generic::global.required')}}"
                                      id="about_ar_editor"
                                      name="about_ar" placeholder="{{ trans('sw.enter_about_ar') ?? 'Enter Arabic about content' }}">{{$mainSettings->about_ar}}</textarea>
															<!--end::Input-->
														</div>
                        <div class="col-md-6 fv-row">
															<!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-document me-2 text-info"></i>{{ trans('sw.about_en')}}
                                <span class="required"></span>
															</label>
															<!--end::Label-->
															<!--begin::Input-->
                            <textarea required data-bv-trigger="keyup change"
                                      data-bv-notempty-message="{{ trans('generic::global.required')}}"
                                      id="about_en_editor"
                                      name="about_en" placeholder="{{ trans('sw.enter_about_en') ?? 'Enter English about content' }}">{{$mainSettings->about_en}}</textarea>
															<!--end::Input-->
														</div>
													</div>
                    <!--end::Row-->

                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-10"></div>
                    <!--end::Separator-->

                    <!--begin::Row - Terms Content-->
													<div class="row fv-row mb-7">
                        <div class="col-md-6 fv-row">
															<!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-shield-tick me-2 text-success"></i>{{ trans('sw.terms_ar')}}
                                <span class="required"></span>
															</label>
															<!--end::Label-->
															<!--begin::Input-->
                            <textarea required data-bv-trigger="keyup change" dir="rtl"
                                      id="terms_ar_editor"
                                      name="terms_ar" placeholder="{{ trans('sw.enter_terms_ar') ?? 'Enter Arabic terms and conditions' }}">{{$mainSettings->terms_ar}}</textarea>
															<!--end::Input-->
														</div>
                        <div class="col-md-6 fv-row">
															<!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-shield-tick me-2 text-info"></i>{{ trans('sw.terms_en')}}
                                <span class="required"></span>
															</label>
															<!--end::Label-->
															<!--begin::Input-->
                            <textarea required data-bv-trigger="keyup change"
                                      data-bv-notempty-message="{{ trans('generic::global.required')}}"
                                      id="terms_en_editor"
                                      name="terms_en" placeholder="{{ trans('sw.enter_terms_en') ?? 'Enter English terms and conditions' }}">{{$mainSettings->terms_en}}</textarea>
															<!--end::Input-->
														</div>
													</div>
                    <!--end::Row-->
														</div>
				<!--end:::Tab pane-->
				<!--begin:::Tab pane-->
				<div class="tab-pane fade" id="social" role="tabpanel">
					<!--begin::Section Header-->
                    <div class="mb-10">
                        <h4 class="text-dark fw-bold mb-5">
                            <i class="ki-outline ki-link-5 fs-2 me-2 text-primary"></i>
                            {{ trans('sw.links') ?? 'Social Media Links' }}
                        </h4>
                        <p class="text-muted fs-6">{{ trans('sw.social_links_description') ?? 'Configure your social media links and URLs.' }}</p>
														</div>
                    <!--end::Section Header-->

                    <!--begin::Row - Social Links-->
													<div class="row fv-row mb-7">
                        <div class="col-md-6 fv-row">
															<!--begin::Label-->
                            <label class="form-label">
                                <i class="fab fa-facebook me-2 text-primary"></i>{{ trans('sw.facebook')}}
															</label>
															<!--end::Label-->
															<!--begin::Input-->
                            <input type="url" class="form-control form-control-solid" dir="ltr"
                                   placeholder="{{ trans('sw.enter_facebook_url') ?? 'Enter Facebook URL' }}"
                                   value="{{$mainSettings->facebook}}" name="facebook"
                                   data-bv-uri-message="{{ trans('generic::global.valid_url')}}"/>
															<!--end::Input-->
														</div>
                        <div class="col-md-6 fv-row">
															<!--begin::Label-->
                            <label class="form-label">
                                <i class="fab fa-twitter me-2 text-info"></i>{{ trans('sw.twitter')}}
															</label>
															<!--end::Label-->
															<!--begin::Input-->
                            <input type="url" class="form-control form-control-solid" dir="ltr"
                                   placeholder="{{ trans('sw.enter_twitter_url') ?? 'Enter Twitter URL' }}"
                                   value="{{$mainSettings->twitter}}" name="twitter" />
															<!--end::Input-->
														</div>
													</div>
                    <!--end::Row-->

                    <!--begin::Row - Social Links 2-->
													<div class="row fv-row mb-7">
                        <div class="col-md-6 fv-row">
															<!--begin::Label-->
                            <label class="form-label">
                                <i class="fab fa-instagram me-2 text-danger"></i>{{ trans('sw.instagram')}}
															</label>
															<!--end::Label-->
															<!--begin::Input-->
                            <input type="url" class="form-control form-control-solid" dir="ltr"
                                   placeholder="{{ trans('sw.enter_instagram_url') ?? 'Enter Instagram URL' }}"
                                   value="{{$mainSettings->instagram}}" name="instagram"
                                   data-bv-uri-message="{{ trans('generic::global.valid_url')}}"/>
															<!--end::Input-->
														</div>
                        <div class="col-md-6 fv-row">
															<!--begin::Label-->
                            <label class="form-label">
                                <i class="fab fa-tiktok me-2 text-dark"></i>{{ trans('sw.tiktok')}}
															</label>
															<!--end::Label-->
															<!--begin::Input-->
                            <input type="url" class="form-control form-control-solid" dir="ltr"
                                   placeholder="{{ trans('sw.enter_tiktok_url') ?? 'Enter TikTok URL' }}"
                                   value="{{$mainSettings->tiktok}}" name="tiktok"
                                   data-bv-uri-message="{{ trans('generic::global.valid_url')}}"/>
															<!--end::Input-->
														</div>
													</div>
                    <!--end::Row-->

                    <!--begin::Row - Snapchat-->
													<div class="row fv-row mb-7">
                        <div class="col-md-6 fv-row">
															<!--begin::Label-->
                            <label class="form-label">
                                <i class="fab fa-snapchat me-2 text-warning"></i>{{ trans('sw.snapchat')}}
															</label>
															<!--end::Label-->
															<!--begin::Input-->
                            <input type="url" class="form-control form-control-solid" dir="ltr"
                                   placeholder="{{ trans('sw.enter_snapchat_url') ?? 'Enter Snapchat URL' }}"
                                   value="{{$mainSettings->snapchat}}" name="snapchat"
                                   data-bv-uri-message="{{ trans('generic::global.valid_url')}}"/>
															<!--end::Input-->
														</div>
													</div>
                    <!--end::Row-->
											</div>
											<!--end:::Tab pane-->
											<!--begin:::Tab pane-->
				<div class="tab-pane fade" id="contacts" role="tabpanel">
					<!--begin::Section Header-->
                    <div class="mb-10">
                        <h4 class="text-dark fw-bold mb-5">
                            <i class="ki-outline ki-phone fs-2 me-2 text-primary"></i>
                            {{ trans('sw.contacts') ?? 'Contact Information' }}
                        </h4>
                        <p class="text-muted fs-6">{{ trans('sw.contact_info_description') ?? 'Manage your contact details, addresses, and map location.' }}</p>
														</div>
                    <!--end::Section Header-->

                    <!--begin::Row - Addresses-->
													<div class="row fv-row mb-7">
                        <div class="col-md-6 fv-row">
															<!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-geolocation me-2 text-success"></i>{{ trans('sw.address_ar')}}
                                <span class="required"></span>
															</label>
															<!--end::Label-->
															<!--begin::Input-->
                            <textarea required data-bv-trigger=" " dir="rtl"
                                      class="form-control form-control-solid"
                                      name="address_ar" placeholder="{{ trans('sw.enter_address_ar') ?? 'Enter Arabic address' }}">{{$mainSettings->address_ar}}</textarea>
															<!--end::Input-->
														</div>
                        <div class="col-md-6 fv-row">
                                    <!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-geolocation me-2 text-info"></i>{{ trans('sw.address_en')}}
                                <span class="required"></span>
                            </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                            <textarea required data-bv-trigger="keyup change"
                                      data-bv-notempty-message="{{ trans('generic::global.required')}}"
                                      class="form-control form-control-solid"
                                      name="address_en" placeholder="{{ trans('sw.enter_address_en') ?? 'Enter English address' }}">{{$mainSettings->address_en}}</textarea>
                                    <!--end::Input-->
                                </div>
                                </div>
                    <!--end::Row-->
                            
                    <!--begin::Separator-->
                            <div class="separator separator-dashed my-10"></div>
                    <!--end::Separator-->

                    <!--begin::Row - Location Map-->
                    <div class="row fv-row mb-7">
                        <div class="col-md-12 fv-row">
                                    <!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-map fs-2 me-2 text-warning"></i>{{ trans('sw.location_map')}}
                            </label>
                                    <!--end::Label-->
                            <!--begin::Map Inputs-->
                            <input name="latitude" id="latitude" value="{{$mainSettings->latitude}}" type="hidden"/>
                            <input name="longitude" id="longitude" value="{{$mainSettings->longitude}}" type="hidden"/>
                            <!--end::Map Inputs-->
                            <!--begin::Map Container-->
                            <div class="map_container" style="width: 100%;height: 400px; border-radius: 8px; overflow: hidden;">
                                <div class="map" id="googleMap" style="width: 100%;height: 400px;"></div>
                                        </div>
                            <!--end::Map Container-->
                                </div>
                                        </div>
                    <!--end::Row-->

                    <!--begin::Separator-->
                            <div class="separator separator-dashed my-10"></div>
                    <!--end::Separator-->

                    <!--begin::Row - Phone & Email-->
                    <div class="row fv-row mb-7">
                        <div class="col-md-6 fv-row">
                                    <!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-phone me-2 text-success"></i>{{ trans('sw.phone')}}
                            </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                            <input type="tel" class="form-control form-control-solid" dir="ltr"
                                   placeholder="{{ trans('sw.enter_phone') ?? 'Enter phone number' }}"
                                   value="{{$mainSettings->phone}}" name="phone" />
                                    <!--end::Input-->
                                </div>
                        <div class="col-md-6 fv-row">
                                    <!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-sms me-2 text-info"></i>{{ trans('sw.email')}}
                                <span class="required"></span>
                            </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                            <input type="email" class="form-control form-control-solid" dir="ltr"
                                   placeholder="{{ trans('sw.enter_email') ?? 'Enter email address' }}"
                                   value="{{$mainSettings->support_email}}" name="support_email"
                                   required data-bv-trigger="keyup change"
                                              data-bv-notempty-message="{{ trans('generic::global.required')}}"
                                   data-bv-emailaddress-message="{{ trans('generic::global.valid_email')}}"/>
                                    <!--end::Input-->
                                </div>
                    </div>
                    <!--end::Row-->
				</div>
				<!--end:::Tab pane-->
				<!--begin:::Tab pane-->
                <div class="tab-pane fade" id="vat" role="tabpanel">
@php
    $billingSettings = $billingSettings ?? ($mainSettings->billing ?? []);
    $billingSections = data_get($billingSettings, 'sections', []);
    $billingBindings = data_get($billingSettings, 'bindings', []);
    $autoInvoice = config('sw_billing.auto_invoice');
@endphp
					<!--begin::Section Header-->
                            <div class="mb-10">
                        <h4 class="text-dark fw-bold mb-5">
                            <i class="ki-outline ki-receipt fs-2 me-2 text-primary"></i>
                            {{ trans('sw.vat_file') ?? 'VAT Information' }}
                        </h4>
                        <p class="text-muted fs-6">{{ trans('sw.vat_info_description') ?? 'Manage your VAT details and tax settings.' }}</p>
                            </div>
                    <!--end::Section Header-->

                    <!--begin::Row - VAT Details-->
                    <div class="row fv-row mb-7">
                        <div class="col-md-6 fv-row">
                                    <!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-profile-circle me-2 text-success"></i>{{ trans('sw.seller_name')}}
                            </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                            <input type="text" class="form-control form-control-solid"
                                   placeholder="{{ trans('sw.enter_seller_name') ?? 'Enter seller name' }}"
                                   value="{{@$mainSettings->vat_details['seller_name']}}" name="vat_details[seller_name]"/>
                                    <!--end::Input-->
                                </div>
                        <div class="col-md-6 fv-row">
                                    <!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-tag me-2 text-info"></i>{{ trans('sw.vat_number')}}
                            </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                            <input type="text" class="form-control form-control-solid"
                                   placeholder="{{ trans('sw.enter_vat_number') ?? 'Enter VAT number' }}"
                                   value="{{@$mainSettings->vat_details['vat_number']}}" name="vat_details[vat_number]"/>
                                    <!--end::Input-->
                                </div>
                            </div>
                    <!--end::Row-->

                    <!--begin::Row - VAT Percentage & Saudi-->
                    <div class="row fv-row mb-7">
                        <div class="col-md-6 fv-row">
                                    <!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-percentage me-2 text-warning"></i>{{ trans('sw.vat_percentage')}}
                            </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                            <input type="number" class="form-control form-control-solid" min="0" max="100"
                                   placeholder="{{ trans('sw.enter_vat_percentage') ?? 'Enter VAT percentage' }}"
                                   value="{{@$mainSettings->vat_details['vat_percentage']}}" name="vat_details[vat_percentage]"/>
                                    <!--end::Input-->
                                </div>
                        <div class="col-md-6 fv-row">
                                    <!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-flag me-2 text-danger"></i>{{ trans('sw.vat_according_to_saudi')}}
                            </label>
                                    <!--end::Label-->
                            <!--begin::Switch-->
                            <div class="form-check form-switch form-check-custom form-check-solid mt-3">
                                <input class="form-check-input" type="checkbox" name="vat_details[saudi]" value="1"
                                       @if(@$mainSettings->vat_details['saudi'] == true) checked @endif />
                                <label class="form-check-label" for="vat_details[saudi]"></label>
                                </div>
                            <!--end::Switch-->
                            </div>
                                </div>
                    <!--end::Row-->

                    @if(config('sw_billing.zatca_enabled'))
                        @if($autoInvoice)
                            <div class="separator separator-dashed my-10"></div>

                            <div class="mb-10">
                                <h4 class="text-dark fw-bold mb-5">
                                    <i class="ki-outline ki-abstract-26 fs-2 me-2 text-primary"></i>
                                    {{ trans('sw.billing_settings') ?? 'Billing & ZATCA Settings' }}
                                </h4>
                                <p class="text-muted fs-6">{{ trans('sw.billing_settings_description') ?? 'Control which sections trigger automatic invoice generation and the identifiers stored with each invoice.' }}</p>
                            </div>

                            <div class="row fv-row mb-10">
                                <div class="col-md-6">
                                    <div class="card border-dashed border-secondary">
                                        <div class="card-header pb-0">
                                            <h5 class="card-title fw-bold mb-0">{{ trans('sw.billing_sections') ?? 'Enabled Sections' }}</h5>
                                        </div>
                                        <div class="card-body pt-2">
                                            <p class="text-muted fs-7 mb-4">{{ trans('sw.billing_sections_help') ?? 'Choose which areas of the system should create ZATCA invoices automatically.' }}</p>
                                            <div class="d-flex flex-column gap-4">
                                                @foreach([
                                                    'store_orders' => trans('sw.store_orders') ?? 'Store Orders',
                                                    'non_members' => trans('sw.non_members') ?? 'Non Members',
                                                    'members' => trans('sw.members') ?? 'Members',
                                                    'pt_members' => trans('sw.pt_members') ?? 'PT Members',
                                                    'money_boxes' => trans('sw.money_boxes') ?? 'Money Boxes',
                                                ] as $sectionKey => $label)
                                                    <label class="form-check form-switch form-check-custom form-check-solid">
                                                        <input class="form-check-input" type="checkbox" name="billing[sections][{{ $sectionKey }}]" value="1" @checked(data_get($billingSections, $sectionKey, false))>
                                                        <span class="form-check-label">{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-dashed border-secondary h-100">
                                        <div class="card-header pb-0">
                                            <h5 class="card-title fw-bold mb-0">{{ trans('sw.billing_bindings') ?? 'Invoice Bindings' }}</h5>
                                        </div>
                                        <div class="card-body pt-2">
                                            <p class="text-muted fs-7 mb-4">{{ trans('sw.billing_bindings_help') ?? 'Select which identifiers are stored on the invoice when the section is enabled.' }}</p>
                                            <div class="d-flex flex-column gap-4">
                                                @foreach([
                                                    'store_order_id' => trans('sw.store_orders') ?? 'Store Orders',
                                                    'non_member_id' => trans('sw.non_members') ?? 'Non Members',
                                                    'member_id' => trans('sw.members') ?? 'Members',
                                                    'member_pt_subscription_id' => trans('sw.pt_members') ?? 'PT Members',
                                                    'money_box_id' => trans('sw.money_boxes') ?? 'Money Boxes',
                                                ] as $bindingKey => $label)
                                                    @php $bindingEnabled = data_get($billingBindings, $bindingKey, false); @endphp
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span>{{ $label }}</span>
                                                        <span class="badge {{ $bindingEnabled ? 'badge-light-success' : 'badge-light-secondary' }}">
                                                            {{ $bindingEnabled ? (trans('sw.enabled') ?? __('Enabled')) : (trans('sw.disabled') ?? __('Disabled')) }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-dismissible bg-light-info border border-info border-dashed d-flex flex-column flex-sm-row p-5">
                                <i class="ki-outline ki-information fs-2hx text-info me-4 mb-5 mb-sm-0"></i>
                                <div class="d-flex flex-column pe-0 pe-sm-10">
                                    <h5 class="mb-1">{{ trans('sw.billing_auto_invoice_status') ?? 'Automatic Invoice Status' }}</h5>
                                    <span class="text-gray-700">{{ trans('sw.billing_auto_invoice_env_help') ?? 'Automatic invoice creation is controlled by the SW_ZATCA_AUTO_INVOICE environment variable.' }}</span>
                                    <span class="fw-bold mt-2">{{ trans('sw.status') ?? 'Status' }}: <span class="badge {{ $autoInvoice ? 'badge-light-success' : 'badge-light-danger' }}">{{ $autoInvoice ? (trans('sw.enabled') ?? __('Enabled')) : (trans('sw.disabled') ?? __('Disabled')) }}</span></span>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-dismissible bg-light-warning border border-warning border-dashed d-flex flex-column flex-sm-row p-5">
                                <i class="ki-outline ki-information-5 fs-2hx text-warning me-4 mb-5 mb-sm-0"></i>
                                <div class="d-flex flex-column pe-0 pe-sm-10">
                                    <h5 class="mb-1">{{ trans('sw.billing_auto_invoice_disabled_title') ?? 'Automatic invoicing is disabled' }}</h5>
                                    <span class="text-gray-700">{{ trans('sw.billing_auto_invoice_disabled_help') ?? 'Enable SW_ZATCA_AUTO_INVOICE in your environment file to manage billing sections from here.' }}</span>
                                </div>
                            </div>
                        @endif
                    @endif
                                </div>
				<!--end:::Tab pane-->
				<!--begin:::Tab pane-->
				<div class="tab-pane fade" id="meta" role="tabpanel">
					<!--begin::Section Header-->
                    <div class="mb-10">
                        <h4 class="text-dark fw-bold mb-5">
                            <i class="ki-outline ki-code fs-2 me-2 text-primary"></i>
                            {{ trans('sw.meta_tags') ?? 'SEO Meta Tags' }}
                        </h4>
                        <p class="text-muted fs-6">{{ trans('sw.meta_tags_description') ?? 'Configure meta tags for better search engine optimization.' }}</p>
                            </div>
                    <!--end::Section Header-->

                    <!--begin::Row - Meta Keywords Arabic-->
                    <div class="row fv-row mb-7">
                        <div class="col-md-12 fv-row">
                                    <!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-tag me-2 text-success"></i>{{ trans('sw.meta_tags_keyword_ar')}}
                                <span class="required"></span>
                            </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                            <select class="form-control form-control-solid js-tags-multi-ar" name="meta_keywords_ar[]"
                                            multiple required
                                            data-bv-notempty-message="{{ trans('generic::global.required')}}">
                                        @if(count($mainSettings->meta_keywords_ar )>0)
                                            @foreach($mainSettings->meta_keywords_ar as $keyword)
                                                <option selected="selected">{{$keyword}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                            <!--end::Input-->
                                </div>
                    </div>
                    <!--end::Row-->

                    <!--begin::Row - Meta Description Arabic-->
                    <div class="row fv-row mb-7">
                        <div class="col-md-12 fv-row">
                            <!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-abstract-38 me-2 text-info"></i>{{ trans('sw.meta_tags_description_ar')}}
                                <span class="required"></span>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <textarea required class="form-control form-control-solid" dir="rtl"
                                              data-bv-trigger="keyup change"
                                              data-bv-notempty-message="{{ trans('generic::global.required')}}"
                                              name="meta_description_ar"
                                      id="meta_description_ar" placeholder="{{ trans('sw.enter_meta_description_ar') ?? 'Enter Arabic meta description' }}">{{old('meta_description_ar') ?old('meta_description_ar'):($mainSettings->meta_description_ar ? $mainSettings->meta_description_ar: '')}}</textarea>
                            <!--end::Input-->
                                </div>
                                </div>
                    <!--end::Row-->

                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-10"></div>
                    <!--end::Separator-->

                    <!--begin::Row - Meta Keywords English-->
                    <div class="row fv-row mb-7">
                        <div class="col-md-12 fv-row">
                            <!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-tag me-2 text-warning"></i>{{ trans('sw.meta_tags_keyword_en')}}
                                <span class="required"></span>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select class="form-control form-control-solid js-tags-multi" name="meta_keywords_en[]"
                                    multiple required
                                    data-bv-notempty-message="{{ trans('generic::global.required')}}">
                                        @if(count($mainSettings->meta_keywords_en )>0)
                                            @foreach($mainSettings->meta_keywords_en as $keyword)
                                                <option selected="selected">{{$keyword}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                            <!--end::Input-->
                                </div>
                    </div>
                    <!--end::Row-->

                    <!--begin::Row - Meta Description English-->
                    <div class="row fv-row mb-7">
                        <div class="col-md-12 fv-row">
                            <!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-abstract-38 me-2 text-danger"></i>{{ trans('sw.meta_tags_description_en')}}
                                <span class="required"></span>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <textarea required class="form-control form-control-solid"
                                              data-bv-trigger="keyup change"
                                              data-bv-notempty-message="{{ trans('generic::global.required')}}"
                                              name="meta_description_en"
                                      id="meta_description_en" placeholder="{{ trans('sw.enter_meta_description_en') ?? 'Enter English meta description' }}">{{old('meta_description_en') ?old('meta_description_en'):($mainSettings->meta_description_en ? $mainSettings->meta_description_en: '')}}</textarea>
                            <!--end::Input-->
                                </div>
                            </div>
                    <!--end::Row-->
                                </div>
				<!--end:::Tab pane-->
                                            </div>
			<!--end:::Tab content-->
            <!--begin::Form Actions-->
            <div class="d-flex justify-content-end mt-5">
                <button type="reset" class="btn btn-light me-5">{{ trans('admin.reset')}}</button>
                <button type="submit" class="btn btn-primary">
                    <span class="indicator-label">{{ trans('global.save')}}</span>
                    <span class="indicator-progress">Please wait... 
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                </button>
            </div>
            <!--end::Form Actions-->
    </form>
		</div>
		<!--end::Card body-->
	</div>
	<!--end::Card-->
</div>
<!--end::Container-->

@endsection
@section('scripts')
    <script type="text/javascript"
            src="{{asset('resources/assets/admin/custom/bootstrapValidator.js')}}"></script>
    
    <!--CKEditor Build Bundles:: Only include the relevant bundles accordingly-->
    <script src="{{asset('resources/assets/new_front/plugins/custom/ckeditor/ckeditor-classic.bundle.js')}}"></script>
    <script src="{{asset('resources/assets/new_front/plugins/custom/ckeditor/ckeditor-inline.bundle.js')}}"></script>
    <script src="{{asset('resources/assets/new_front/plugins/custom/ckeditor/ckeditor-balloon.bundle.js')}}"></script>
    <script src="{{asset('resources/assets/new_front/plugins/custom/ckeditor/ckeditor-balloon-block.bundle.js')}}"></script>
    <script src="{{asset('resources/assets/new_front/plugins/custom/ckeditor/ckeditor-document.bundle.js')}}"></script>

        <script>
        $(document).ready(function() {
            // Initialize tabs functionality
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                //e.target // newly activated tab
                //e.relatedTarget // previous active tab
            });

            // Initialize Select2 for meta keywords
        $(".js-tags-multi").select2({
            tags: true,
            tokenSeparators: ['&']
        });

        $(".js-tags-multi-ar").select2({
            tags: true,
            tokenSeparators: ['&'],
            direction: 'rtl'
        });

            // Initialize editors
            ClassicEditor.create(document.querySelector('#about_ar_editor'), { language: 'ar' }).catch(error => { console.error(error); });
            ClassicEditor.create(document.querySelector('#about_en_editor')).catch(error => { console.error(error); });
            ClassicEditor.create(document.querySelector('#terms_ar_editor'), { language: 'ar' }).catch(error => { console.error(error); });
            ClassicEditor.create(document.querySelector('#terms_en_editor')).catch(error => { console.error(error); });


            // Set up form validation
        $('form').bootstrapValidator({
            live: 'enable',
            submitted: 'enable'
        });
        });
    </script>

    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyBUtpFU1OSQwyfjIdsUdKgzRAdedm5Atmg"
            type="text/javascript"></script>
    <script>

        $('a[data-bs-toggle="tab"][href="#contacts"]').on('shown.bs.tab', function () {
            var marker;
            var map;

            function initialize(lat, lng) {

                var mapProp = {
                    center: new google.maps.LatLng(lat, lng),
                    zoom: 8,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };
                map = new google.maps.Map(document.getElementById("googleMap"), mapProp);


                placeMarker(new google.maps.LatLng(lat, lng));

                //Add listener
                google.maps.event.addListener(map, "click", function (event) {
                    placeMarker(event.latLng);
                }); //end addListener

                function placeMarker(location) {
                    if (marker == undefined) {
                        marker = new google.maps.Marker({
                            position: location,
                            map: map,
                            animation: google.maps.Animation.DROP
                        });
                    } else {
                        marker.setPosition(location);
                    }
                    var latitude = location.lat();
                    var longitude = location.lng();
                    $('#latitude').val(latitude);
                    $('#longitude').val(longitude);
                    console.log(latitude + ', ' + longitude);
                }
            }

            @if($mainSettings->latitude&& $mainSettings->longitude )
            google.maps.event.addDomListener(window, 'load', initialize('{{$mainSettings->latitude}}', '{{$mainSettings->longitude}}'));
            @else
            google.maps.event.addDomListener(window, 'load', initialize(30.047607020301598, 31.23380307133947));
            @endif

        });
    </script>
@endsection
