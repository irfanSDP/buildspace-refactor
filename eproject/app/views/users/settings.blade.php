@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ trans('users.settings') }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-cogs"></i> {{ trans('users.settings') }}
			</h1>
		</div>
	</div>

	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			<article class="col-sm-12 col-md-12 col-lg-12" style="padding-top: 10px;">
				<div class="jarviswidget jarviswidget-sortable">
					<header role="heading">
						<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
						<h2>{{ trans('users.updateSettings') }}</h2>
					</header>

					<!-- widget div-->
					<div role="content">
						<!-- widget content -->
						<div class="widget-body no-padding">
							@include('users.partials.settingsForm')
						</div>
					</div>
				</div>
			</article>
		</div>
	</div>
@endsection