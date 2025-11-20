@extends('generic::Front.layouts.auth_master')
@section('title'){{ $title ?? trans('global.login') }} | @endsection
@section('style')
    <style>
        /* Override any old CSS and ensure new design classes are applied */
       
    </style>
@endsection
@section('content')
							<!--begin::Form-->
							<form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" action="{{ route('sw.login') }}" method="POST">
								@csrf
								@include('generic::errors')
								<!--begin::Heading-->
								<div class="text-center mb-11">
									<!--begin::Title-->
									<h1 class="text-gray-900 fw-bolder mb-3">{{ $title ?? trans('sw.login') }}</h1>
									<!--end::Title-->
									<!--begin::Subtitle-->
									<div class="text-gray-500 fw-semibold fs-6">{{ trans('sw.login_to_account') }}</div>
									<!--end::Subtitle=-->
								</div>
								<!--begin::Heading-->
								<!--begin::Input group=-->
								<div class="fv-row mb-8">
									<!--begin::Email-->
									<input type="text" placeholder="{{ trans('global.email') }}" name="email" autocomplete="off" class="form-control" value="{{ old('email') }}" style="direction: ltr;" />
									<!--end::Email-->
								</div>
								<!--end::Input group=-->
								<div class="fv-row mb-3">
									<!--begin::Password-->
									<input type="password" placeholder="{{ trans('global.password') }}" name="password" autocomplete="off" class="form-control" style="direction: ltr;" />
									<!--end::Password-->
								</div>
								<!--end::Input group=-->
								<!--begin::Wrapper-->
								<div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
									
									<!--begin::Link-->
									<div></div>
									<!--end::Link-->
								</div>
								<!--end::Wrapper-->
								<!--begin::Submit button-->
								<div class="d-grid mb-10">
									<button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
										<!--begin::Indicator label-->
										<span class="indicator-label">{{ trans('admin.login') }}</span>
										<!--end::Indicator label-->
										<!--begin::Indicator progress-->
										<span class="indicator-progress">{{ trans('sw.please_wait') ?? 'Please wait...' }} 
										<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
										<!--end::Indicator progress-->
									</button>
								</div>
								<!--end::Submit button-->
								
							</form>
							<!--end::Form-->
@endsection
@section('script')
<script>
	// Standard form submission (general.js is disabled in layout)
	document.addEventListener('DOMContentLoaded', function() {
		var form = document.getElementById('kt_sign_in_form');
		var submitButton = document.getElementById('kt_sign_in_submit');
		
		if (form && submitButton) {
			// Remove novalidate attribute to enable HTML5 validation
			form.removeAttribute('novalidate');
			
			// Handle form submission
			form.addEventListener('submit', function(e) {
				// Show loading state
				submitButton.setAttribute('data-kt-indicator', 'on');
				submitButton.disabled = true;
				// Allow form to submit normally (don't prevent default)
			});
			
			// Also handle button click for better UX
			submitButton.addEventListener('click', function(e) {
				// Let form validation handle it
				if (!form.checkValidity()) {
					form.reportValidity();
					return;
				}
			});
		}
	});
</script>
@stop
