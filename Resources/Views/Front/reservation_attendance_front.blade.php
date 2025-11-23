@extends('software::layouts.form')
@section('form_title') {{ @$title }} @endsection
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
@section('page_body')
<div class="card">
    <div class="card-body">
        <h4>{{ $title }}</h4>
        <p><strong>ID:</strong> {{ $reservation->id }}</p>
        <p><strong>Client:</strong> @if($reservation->client_type == 'member') {{ optional($reservation->member)->name ?? 'Member #' . $reservation->member_id }} @else {{ optional($reservation->nonMember)->name ?? 'Non-Member #' . $reservation->non_member_id }} @endif</p>
        <p><strong>Activity:</strong> {{ optional($reservation->activity)->name ?? 'Activity #' . $reservation->activity_id }}</p>
        <p><strong>Date / Time:</strong> {{ $reservation->reservation_date }} {{ $reservation->start_time }} - {{ $reservation->end_time }}</p>

        <form method="POST" action="{{ route('sw.attendReservation', $reservation->id) }}">
            @csrf
            <div class="mb-3"><label>{{ trans('sw.notes') }}</label><textarea name="notes" class="form-control"></textarea></div>
            <button class="btn btn-success">{{ trans('sw.confirm_attendance') }}</button>
            <a href="{{ route('sw.listReservation') }}" class="btn btn-light">{{ trans('admin.cancel') }}</a>
        </form>
    </div>
</div>
@endsection


