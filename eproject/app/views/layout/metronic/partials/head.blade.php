<head>
	<title>{{ trans('projectOpenTenderBM.metaTitle') }}</title>
	<!--begin::Fonts-->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
	<!--end::Fonts-->

	<!--begin::FontAwesome-->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
	<!--end::FontAwesome-->

	<!--begin::Global Theme Styles(used by all pages)-->
	<link rel="stylesheet" type="text/css" href="{{ asset('metronic/plugins/global/plugins.bundle.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('metronic/plugins/custom/prismjs/prismjs.bundle.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('metronic/css/style.bundle.css') }}">
	<!--end::Global Theme Styles-->
	@yield('css')
</head>