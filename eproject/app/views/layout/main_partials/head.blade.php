<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

<title>BuildSpace eProject</title>
<meta name="description" content="">
<meta name="author" content="">

<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="_token" content="{{{ csrf_token() }}}" />

<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('css/bootstrap.min.css') }}">

@if(Config::get('app.debug', false))
<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('css/fontawesome.css') }}">
@else
<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('css/fontawesome.min.css') }}">
@endif

<!-- SmartAdmin Styles : Caution! DO NOT change the order -->
<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('css/smartadmin-production-plugins.min.css') }}">
<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('css/smartadmin-production.min.css') }}">
@if(Config::get('app.debug', false))
<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('css/main.css') }}">
@else
<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('css/main.min.css') }}">
@endif

<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('css/smartadmin-skins.min.css') }}">

<!-- SmartAdmin RTL Support -->
<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('css/smartadmin-rtl.min.css') }}">

@if(file_exists(public_path('css/client_style.css')))
<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('css/client_style.css') }}">
@endif

<!-- GOOGLE FONT -->
<link rel="apple-touch-icon" href="{{ asset('img/splash/sptouch-icon-iphone.png') }}">
<link rel="apple-touch-icon" sizes="76x76" href="{{ asset('img/splash/touch-icon-ipad.png') }}">
<link rel="apple-touch-icon" sizes="120x120" href="{{ asset('img/splash/touch-icon-iphone-retina.png') }}">
<link rel="apple-touch-icon" sizes="152x152" href="{{ asset('img/splash/touch-icon-ipad-retina.png') }}">

<!-- iOS web-app metas : hides Safari UI Components and Changes Status Bar Appearance -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">

<!-- Startup image for web apps -->
<link rel="apple-touch-startup-image" href="{{ asset('img/splash/ipad-landscape.png') }}"
      media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:landscape)">
<link rel="apple-touch-startup-image" href="{{ asset('img/splash/ipad-portrait.png') }}"
      media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:portrait)">
<link rel="apple-touch-startup-image" href="{{ asset('img/splash/iphone.png') }}" media="screen and (max-device-width: 320px)">

<link rel="stylesheet" href="{{ asset('js/plugin/select2/css/select2.min.css') }}">

<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('css/tabulator.min.css') }}">
<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('css/cust-tabulator.css') }}">

<!-- Upload file modal -->
<link rel="stylesheet" href="{{ asset('css/jquery.fileupload.css') }}">
<link rel="stylesheet" href="{{ asset('css/jquery.fileupload-ui.css') }}">

<!--- Improve ui visuals -->
<link rel="stylesheet" href="{{ asset('css/beautify-ui.css').'?v=1.01' }}">

@section('css')
@show
