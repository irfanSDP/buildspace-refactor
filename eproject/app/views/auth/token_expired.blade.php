@extends('auth.auth_master_layout')

@section('content')
    <div class="wrapper">
        <div class="main content clearfix">
            <div class="banner">
                <h1>{{{ $myCompanyProfile->name }}}</h1>
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

                <h2 style="color: red; text-align: center;">{{ trans('auth.passwordTokenExpired') }}</h2>

                <div class="ribbon-banner">&nbsp;</div>
            </div>
        </div>

        @include('auth.partials.footer')
    </div>
@endsection