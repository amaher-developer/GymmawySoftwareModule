@extends('software::layouts.list')
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
@section('styles')
<style>
    .help-block {
        font-size: 12px;
    }
    .tiles .tile .tile-object>.name{
        margin-left: 0px !important;
        margin-right: 0px !important;
        text-align: center;
        position: initial !important;
    }
    {{--.tiles {--}}
    {{--    @if($lang == 'ar')--}}
    {{--        margin-right: 40% !important;--}}
    {{--    @else--}}
    {{--        margin-left: 40% !important;--}}
    {{--    @endif--}}
    {{--}--}}
    .note.note-info {
        background-color: #eef7fb;
        border-color: #91d9e8;
        color: #31708f, 80%;
    }
    .note {
        margin: 0 0 20px 0;
        padding: 15px 15px 15px 30px;
        border-right: 5px solid #eee;
    }
</style>
@endsection
@section('list_title') {{ @$title }} @endsection
@section('list_add_button') <a href="{{route('sw.listNotificationLog')}}"><i class="fa fa-history"></i> {{ trans('sw.notification_logs')}}</a>@endsection
@section('page_body')


    <!-- BEGIN PAGE CONTENT INNER -->
    <div class="page-content-inner">

        <div class="row col-md-12">
            <div class="tiles">
                <div class="col-6">
                <a href="https://play.google.com/store/apps/details?id=com.gymmawy" target="_blank">
                <div class="tile bg-red-sunglo">
                    <div class="tile-body">
                        <i class="fa fa-android"></i>
                    </div>
                    <div class="tile-object">
                        <div class="name">
                            {{ trans('sw.android_app_download')}}
                        </div>
                    </div>
                </div>
                </a>
                </div>

                <div class="col-6">

                <a href="https://apps.apple.com/us/app/gymmawy/id1616309138" target="_blank">
                <div class="tile bg-blue-steel">
                    <div class="tile-body">
                        <i class="fa fa-apple"></i>
                    </div>
                    <div class="tile-object">
                        <div class="name" >
                            {{ trans('sw.ios_app_download')}}
                        </div>
                    </div>
                </div>
                </a>
                </div>

                <div class="col-6">
                    <a href="https://onelink.to/gymmawy" target="_blank">
                    <div class="tile image selected">
                        <div class="tile-body">
                            <img src="{{asset('resources/assets/front/images/onlink_to_gymmawy_small.png')}}" alt="">
                        </div>
                        <div class="tile-object">
                            <div class="name">

                            </div>
                        </div>
                    </div>
                    </a>
                </div>

                <div class="col-6" style="clear: both;">
                    <div class="note note-info">
                        <h4 class="block">{{ trans('sw.m_steps')}}</h4>
                        <p>
                            <ol>
                                <li>{{ trans('sw.n_step_1')}}</li>
                                <li>{{ trans('sw.n_step_2')}}</li>
                                <li>{{ trans('sw.n_step_3')}}</li>
                                <li>{{ trans('sw.n_step_4')}}</li>
                            </ol>
                        </p>
                    </div>
                </div>
            </div>

            </div>

        </div>

    <div class="col-md-12" style="clear: both;float: none"><hr/></div>



        <div class="portlet-body form">
            <form role="form" action="{{route('sw.storeNotification')}}" method="post"  onsubmit="return confirm('{{ trans('admin.are_you_sure')}}');">
                {{csrf_field()}}
                <div class="form-body">

                    <div class="form-group row">
                        @if(count($members) > 0)
                        <label class="col-md-8"><input type="checkbox" id="check_all" onclick="" value="1" checked=""> {{ trans('sw.all_clients')}}</label>
{{--                        <div class="col-md-4"><a href="https://play.google.com/store/apps/details?id=com.gymmawy" target="_blank"><img src="{{asset('resources/assets/front/images/play_store_icon.png')}}" style="width: 140px;"></a></div>--}}
                        <div style="clear: both;float: none"><hr/></div>
                        <div class="radio-list col-md-12 ">
                            @foreach($members as $member)
                            <label class=" col-md-3">
                                <div class="checkbox" >
                                    <span class=""><input type="checkbox"  class="member_notification" name="member_codes[]" value="{{$member->code}}" checked=""></span>
                                </div> {{$member->name}}</label>
                            @endforeach

                        </div>
                        @else
                            <h4 class="col-lg-12 text-center">{{ trans('sw.no_record_found')}}</h4>
                        @endif
                    </div>

                    <div class="clearfix padding-tb-20"></div>


                    <div class="form-group">
                        <label>{{ trans('sw.message')}}</label>
                        <textarea class="form-control" rows="5"  name="message" id="message" required></textarea>
                    </div>

                </div>

                <div class="form-actions" style="clear:both;">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">
                            <button type="submit" class="btn btn-primary">{{ trans('global.send')}}</button>
                            <input type="reset" class="btn default" value="{{ trans('admin.reset')}}">
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>

@endsection
@section('scripts')
    <script>
        $("#check_all").click(function() {
            $('.member_notification').each(function() {
                this.click();
            });
        });
    </script>
@endsection
