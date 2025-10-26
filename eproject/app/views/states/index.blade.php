@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>
			{{ link_to_route('countries', trans('countries.countries'), array()) }}
		</li>
		<li>{{{ $country->country }}}</li>
		<li>{{ trans('states.states') }}</li>
	</ol>
@endsection

@section('content')

<div class="row">
	<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		<h1 class="page-title txt-color-blueDark">
			{{{ $country->country }}}
		</h1>
	</div>
	
	<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
		{{ link_to_route('states.create', trans('states.addNewState'), array($country->id), array('class' => 'btn btn-info btn-md pull-right header-btn hidden-mobile')) }}
	</div>
</div>

<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<div class="jarviswidget ">
			<header>
				<h2> {{ trans('states.states') }} </h2>
			</header>
			<div>
				<div class="widget-body no-padding">
					<div class="table-responsive">
						<table class="table " id="dt_basic">
							<thead>
								<tr>
									<th style="text-align: center;width:auto;">{{ trans('states.name') }}</th>
									<th style="width:40%;">{{ trans('states.timezone') }}</th>
									<th style="text-align: center;width:10%;">{{ trans('states.actions') }}</th>
								</tr>
							</thead>
							<tbody>
								@foreach ( $country->states as $state )
									<tr>
										<td>{{{ $state->name }}}</td>
										<td>{{{ $state->timezone }}}</td>
										<td style="text-align: center;">
											{{ link_to_route('states.edit', trans('forms.edit'), array($country->id, $state->id), array('class'=> 'btn btn-default btn-xs btn-default') ) }}
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
				"autoWidth" : true,
				"language": {
					"emptyTable": "There are currently no states available."
				}
			});
		});
	</script>
@endsection
