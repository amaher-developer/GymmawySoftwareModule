@extends('generic::layouts.form')
@section('breadcrumb')
    <ul class="page-breadcrumb breadcrumb">
        <li>
            <a href="{{ url('/operate') }}">{{trans('admin.home')}}</a>
             <i class="fa fa-circle"></i>
        </li>
        <li>
            <a href="{{ route('listActivity') }}">Activities</a>
             <i class="fa fa-circle"></i>
        </li>
        <li>{{ $title }}</li>
    </ul>
@endsection
@section('form_title') {{ @$title }} @endsection
@section('page_body')
    <form method="post" action="" class="form-horizontal" role="form" enctype="multipart/form-data">
     <div class="form-body">
        {{csrf_field()}}
    <div class="form-group col-md-6">
    <label class="col-md-3 control-label">Name En</label>
    <div class="col-md-9">
        <input id="name_en" value="{{ old('name_en', $activity->name_en) }}"
               name="name_en" type="text" class="form-control" required>
    </div>
</div>
            <div class="form-group col-md-6">
    <label class="col-md-3 control-label">Name Ar</label>
    <div class="col-md-9">
        <input id="name_ar" value="{{ old('name_ar', $activity->name_ar) }}"
               name="name_ar" type="text" class="form-control" required>
    </div>
</div>
            <div class="form-group col-md-6">
    <label class="col-md-3 control-label">Name Ar</label>
    <div class="col-md-9">
        <input id="name_ar" value="{{ old('name_ar', $activity->name_ar) }}"
               name="name_ar" type="text" class="form-control" required>
    </div>
</div>
            

    <div class="form-group col-md-6" style="clear:both;">
        <label class="col-md-3 control-label">{{trans('admin.disable')}}</label>
        <div class="col-md-9">
            <div class="mt-checkbox-list">
                <label class="mt-checkbox mt-checkbox-outline">
                    <input type="hidden" name="deleted_at" value="">
                    <input type="checkbox" value="{{ date('Y-m-d') }}" name="deleted_at"
                            {{ $activity->trashed()?'checked':'' }}>
                    <span></span>
                </label>
            </div>
        </div>

    </div>

    <div class="form-actions" style="clear:both;">
        <div class="row">
            <div class="col-md-offset-3 col-md-9">
                <button type="submit" class="btn green">{{trans('admin.submit')}}</button>
                <input type="reset" class="btn default" value="{{trans('admin.reset')}}">
            </div>
        </div>
    </div>
    </div>
    </form>
@endsection
