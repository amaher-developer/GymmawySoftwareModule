@extends('software::layouts.form')

@section('breadcrumb')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home') }}</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-300 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-gray-900">{{ $title }}</li>
    </ul>
@endsection

@section('form_title') {{ $title }} @endsection

@section('page_body')
    @include('software::Front.partials.pt_member_form', [
        'mode' => 'create',
        'member' => $member,
        'subscriptions' => $subscriptions,
        'classes' => $classes,
        'trainers' => $trainers,
        'discounts' => $discounts ?? collect(),
        'paymentTypes' => $paymentTypes ?? collect(),
        'mainSettings' => $mainSettings ?? null,
        'swUser' => $swUser ?? auth('sw')->user(),
        'formAction' => route('sw.storePTMember'),
        'formMethod' => 'POST',
    ])
@endsection

@section('sub_scripts')
    <script src="{{ asset('resources/assets/admin/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js') }}" type="text/javascript"></script>
@endsection


