<!DOCTYPE html>
<html>
<head>
    @include('layout.main_partials.head')

    <!-- CSS -->
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('css/release.css') }}">
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('css/your_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">

    <script src="{{ asset('js/jquery/dist/jquery.min.js') }}"></script>

    <script src="{{ asset('js/bootstrap/bootstrap.min.js') }}"></script>

    <script src="{{ asset('js/plugin/bootstrap-slider/bootstrap-slider.min.js') }}"></script>

    <title>{{{ trans('companies.companyRegistration') }}}</title>
</head>
<body>
    <header id="header">
        <div id="logo-group">
            <a href="javascript:void(0);" id="logo" style="margin-top: 10px; margin-left: 12px;">
                @if(file_exists(public_path('img/company-logo.png')))
                    <img src="{{{ asset('img/company-logo.png') }}}" alt="{{{ \PCK\MyCompanyProfiles\MyCompanyProfile::all()->first()->name }}}">
                @else
                    <img src="{{{ asset('img/buildspace-login-logo.png') }}}" alt="BuildSpace eProject">
                @endif
            </a>

            <div class="ribbon-banner-small"></div>
        </div>
    </header>
    <div class="container bg-white padded-bottom" style="height:500px; ">

        <div style="padding-top:170px">
            <h2 class="text-center color-bootstrap-success">
                {{{ trans('auth.accountCreated') }}}
            </h2>
            <hr/>
            <h6 class="text-center text-danger">
                {{{ trans('auth.checkMailBox') }}}
            </h6>
        </div>

    </div>
</body>
</html>