@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ trans('users.updateMyProfile') }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-edit"></i> {{ trans('users.updateMyProfile') }}
			</h1>
		</div>
	</div>

	<div class="row">
		@include('users.partials.userUpdateForm')
	</div>
@endsection