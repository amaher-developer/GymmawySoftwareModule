@extends('generic::layouts.form')
@section('breadcrumb')
    <ul class="page-breadcrumb breadcrumb">
        <li>
            <a href="{{ url('/operate') }}">{{trans('admin.home')}}</a>
             <i class="fa fa-circle"></i>
        </li>
        <li>
            <a href="{{ route('listGymUser') }}">GymUsers</a>
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
    <label class="col-md-3 control-label">Name</label>
    <div class="col-md-9">
        <input id="name" value="{{ old('name', $gymuser->name) }}"
               name="name" type="text" class="form-control" >
    </div>
</div>
            <div class="form-group col-md-6">
    <label class="col-md-3 control-label">Email</label>
    <div class="col-md-9">
        <input id="email" value="{{ old('email', $gymuser->email) }}"
               name="email" type="email" class="form-control" >
    </div>
</div>
            <div class="form-group col-md-6">
    <label class="col-md-3 control-label">Password</label>
    <div class="col-md-9">
        <input id="password" value="{{ old('password', $gymuser->password) }}"
               name="password" type="text" class="form-control" >
    </div>
</div>
            <div class="form-group col-md-6">
    <label class="col-md-3 control-label">Phone</label>
    <div class="col-md-9">
        <input id="phone" value="{{ old('phone', $gymuser->phone) }}"
               name="phone" type="text" class="form-control" >
    </div>
</div>
            <div class="form-group col-md-6">
    <label class="col-md-3 control-label">Address</label>
    <div class="col-md-9">
        <input id="address" value="{{ old('address', $gymuser->address) }}"
               name="address" type="text" class="form-control" >
    </div>
</div>
            <div class="form-group col-md-6">
    <label class="col-md-3 control-label">Address</label>
    <div class="col-md-9">
        <input id="address" value="{{ old('address', $gymuser->address) }}"
               name="address" type="text" class="form-control" >
    </div>
</div>
            <div class="form-group col-md-6">
    <label class="col-md-3 control-label">Start Time Work</label>
    <div class="col-md-9">
        <input id="start_time_work" value="{{ old('start_time_work', $gymuser->start_time_work) }}"
               name="start_time_work" type="text" class="form-control" >
    </div>
</div>
            <div class="form-group col-md-6">
    <label class="col-md-3 control-label">End Time Work</label>
    <div class="col-md-9">
        <input id="end_time_work" value="{{ old('end_time_work', $gymuser->end_time_work) }}"
               name="end_time_work" type="text" class="form-control" >
    </div>
</div>
            <div class="form-group col-md-6">
    <label class="col-md-3 control-label">Image</label>
    <div class="col-md-8">
        <input id="image" value="{{ old('image', $gymuser->image) }}"
               name="image" type="file" class="form-control" >
    </div>
    @if(!empty($gymuser->image))
       <label class="col-md-1 control-label">
            <a href="{{ $gymuser->image }}" class="fancybox-button" data-rel="fancybox-button">
                view
            </a>
       </label>
    @endif
</div>

            <div class="form-group col-md-6">
    <label class="col-md-3 control-label">Permission</label>
    <div class="col-md-9">
        <input id="permission" value="{{ old('permission', $gymuser->permission) }}"
               name="permission" type="text" class="form-control" >
    </div>
</div>
            

    <div class="form-group col-md-6" style="clear:both;">
        <label class="col-md-3 control-label">{{trans('admin.disable')}}</label>
        <div class="col-md-9">
            <div class="mt-checkbox-list">
                <label class="mt-checkbox mt-checkbox-outline">
                    <input type="hidden" name="deleted_at" value="">
                    <input type="checkbox" value="{{ date('Y-m-d') }}" name="deleted_at"
                            {{ $gymuser->trashed()?'checked':'' }}>
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
