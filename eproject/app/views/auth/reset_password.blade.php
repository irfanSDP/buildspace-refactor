@extends('auth.auth_master_layout')

@section('content')
    <div class="wrapper">
        <div class="main content clearfix">
            <div class="banner">
                <h1>{{{ $myCompanyProfile->name }}}</h1>
                <h2 class="hidden-small">
                    {{ trans('auth.enterNewPassword') }}
                </h2>
            </div>

            <div class="block signin-block clearfix">
                <img class="buildspace-img" src="{{ $themeSettings->logo_path }}" alt="{{ $themeSettings->logo_title }}">

                @if (Session::get('error'))
                    <div class="alert alert-danger" style="margin-bottom: 10px; color: red; text-align: center;">
                        <i class="fa-fw fa fa-times"></i>
                        {{{ Session::get('error') }}}
                    </div>
                @endif

                @if (Session::get('notice'))
                    <div class="alert alert-warning" style="margin-bottom: 10px; color: green; text-align: center;">
                        <i class="fa-fw fa fa-exclamation-triangle"></i>
                        {{{ Session::get('notice') }}}
                    </div>
                @endif

                {{ Form::open(array('id' => 'login-form')) }}
                    <label class="hidden-label" for="password">{{ trans('auth.password') }}:</label>
                    <input class="form-control" placeholder="{{{ trans('confide::confide.password') }}}" type="password" name="password" id="password">

                    <br clear="all">

                    <label class="hidden-label" for="confirm_password">{{ trans('auth.confirmPassword') }}:</label>
                    <input class="form-control" placeholder="{{{ trans('confide::confide.password_confirmation') }}}" type="password" name="password_confirmation" id="confirm_password">

                    {{ $errors->first('password', '<em style="color:red;text-align: center;margin: 10px 0;display: block;">:message</em>') }}

                    <input type="hidden" name="token" value="{{{ $token }}}">
                    <input name="forgot_password" class="rc-button-submit" type="submit" value="{{ trans('auth.resetPassword') }}">
                {{ Form::close() }}

                <div class="ribbon-banner">&nbsp;</div>
            </div>
        </div>

        @include('auth.partials.footer')
    </div>
@endsection