@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ trans('countries.countries') }}</li>
	</ol>
@endsection

@section('content')

<div class="row">
	<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		<h1 class="page-title txt-color-blueDark">
			<i class="fa fa-flag"></i> {{ trans('countries.countries') }}
		</h1>
	</div>

	<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
		<a href="{{route('countries.create')}}" class="btn btn-primary btn-md pull-right header-btn">
			<i class="fa fa-plus"></i> {{ trans('countries.addNewCountry') }}
		</a>
	</div>
</div>

<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<div class="jarviswidget ">
			<header>
				<h2>{{ trans('countries.countryListing') }}</h2>
			</header>
			<div>
				<div class="widget-body no-padding">
					<div class="table-responsive">
						<table class="table " id="dt_basic">
							<thead>
								<tr>
									<th>{{ trans('general.name') }}</th>
									<th>{{ trans('currencies.currencyCode') }}</th>
									<th>{{ trans('currencies.currencyName') }}</th>
									<th>{{ trans('countries.iso') }}</th>
									<th>{{ trans('countries.phonePrefix') }}</th>
									<th>{{ trans('countries.languages') }}</th>
									<th style="width:13%;">{{ trans('countries.actions') }}</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($countries as $country)
								<tr>
									<td>{{{ $country->country }}}</td>
									<td>{{{ $country->currency_code }}}</td>
									<td>{{{ $country->currency_name }}}</td>
									<td>{{{ $country->iso }}}</td>
									<td>{{{ $country->phone_prefix }}}</td>
									<td>{{{ $country->languages }}}</td>
									<td>
										{{ link_to_route('states', trans('countries.states'), array($country->id), array('class'=> 'btn btn-default btn-xs btn-success')) }}
										&nbsp;
										<div class="btn-group btn-group-xs">
											{{ link_to_route('countries.edit', trans('forms.edit'), array($country->id), array('class'=> 'btn btn-default btn-xs btn-default')) }}
										</div>
									</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection

@section('js')
    <script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
	<script>
		$(document).ready(function() {
			$('#dt_basic').dataTable({
				"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
				"t"+
				"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
				"autoWidth" : true
			});
		});
	</script>
@endsection