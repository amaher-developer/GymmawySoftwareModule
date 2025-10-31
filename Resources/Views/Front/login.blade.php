@extends('generic::Front.layouts.auth_master')
@section('title'){{ $title }} | @endsection
@section('style')
    <style>
        .login {
            {{--background: url({{asset('resources/assets/admin/global/img/bg_login.png')}}) center center no-repeat fixed;--}}
            background: url({{asset('resources/assets/admin/global/img/bg_login.png')}})  no-repeat fixed;
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;
            min-height: 100vh;
            width: 100%;
        }
    </style>
@endsection
@section('content')

    <!-- BEGIN LOGIN -->
    <div class="content">
        <!-- BEGIN LOGIN FORM -->

        <form class="login-form" role="form" method="POST" action="{{ route('sw.login') }}">
            {{ csrf_field() }}
            <h3 class="form-title">{{$title}}</h3>
            @include('generic::errors')
{{--            <div class="alert alert-danger display-hide">--}}
{{--                <ul style="margin: 0 0 10px 0;list-style: none;">--}}
{{--                        <li>{{ trans('global.error_login')}}</li>--}}
{{--                </ul>--}}
{{--            </div>--}}
{{--            <div class="alert alert-danger display-hide">--}}
{{--                <button class="close" data-close="alert"></button>--}}
{{--                <span>{{ trans('global.error_login')}}</span>--}}
{{--            </div>--}}
            <div class="form-group">
                <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                <label class="control-label visible-ie8 visible-ie9">{{ trans('global.email')}}</label>
                <div class="input-icon">
                    <i class="fa fa-user"></i>
                    <input class="form-control placeholder-no-fix" required autocomplete="off" type="email" name="email"
                           placeholder="{{ trans('global.email')}}" dir="ltr"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label visible-ie8 visible-ie9">{{ trans('global.password')}}</label>
                <div class="input-icon">
                    <i class="fa fa-lock"></i>
                    <input class="form-control placeholder-no-fix" type="password" autocomplete="off"
                           placeholder="{{ trans('global.password')}}" name="password" dir="ltr"/>
                </div>
            </div>
            <div class="form-actions">
                <label class="checkbox">
                    @if($lang == 'ar')
                        <a href="{{preg_replace('/'.request()->segment(1).'/', 'en', strtolower(request()->fullUrl()),1)}}">English</a>
                    @else
                        <a href="{{preg_replace('/'.request()->segment(1).'/', 'ar', strtolower(request()->fullUrl()),1)}}">العربيه</a>
                    @endif
                    {{--                    <input type="checkbox" name="remember" value="1"/> Remember me --}}
                </label>
                <button type="submit" class="btn green-haze pull-right">
                    {{ trans('global.login')}} <i class="m-icon-swapright m-icon-white"></i>
                </button>
            </div>
        </form>
        <!-- END LOGIN FORM -->
    </div>
    <!-- END LOGIN -->





@endsection
@section('script')

@stop
