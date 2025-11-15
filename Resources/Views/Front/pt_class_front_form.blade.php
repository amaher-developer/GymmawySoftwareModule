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
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.listPTClass') }}" class="text-muted text-hover-primary">{{ trans('sw.pt_classes')}}</a>
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
@section('page_body')
    @include('software::Front.partials.pt_class_form', [
        'mode' => isset($class) && $class->exists ? 'edit' : 'create',
        'class' => $class,
        'subscriptions' => $subscriptions,
        'trainers' => $trainers ?? collect(),
        'mainSettings' => $mainSettings ?? null,
        'formAction' => $formAction ?? (isset($class) && $class->exists ? route('sw.updatePTClass', $class->id) : route('sw.storePTClass')),
        'formMethod' => isset($class) && $class->exists ? 'PUT' : 'POST',
    ])
@endsection
