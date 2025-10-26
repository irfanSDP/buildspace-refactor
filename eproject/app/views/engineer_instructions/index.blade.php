@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
		<li>{{ trans('navigation/projectnav.engineerInstruction') }} (EI)</li>
	</ol>

    @include('projects.partials.project_status')
@endsection

@section('content')

	<div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">
				{{ trans('navigation/projectnav.engineerInstruction') }} (EI)
			</h1>
		</div>

		@if ( $user->hasCompanyProjectRole($project, array(PCK\ContractGroups\Types\Role::CONSULTANT_1, PCK\ContractGroups\Types\Role::CONSULTANT_2)) )
			<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
			{{ link_to_route('ei.create', 'Issue New EI', $project->id, array('class' => 'btn btn-primary btn-md pull-right header-btn')) }}
			</div>
		@endif
	</div>

	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			<div class="jarviswidget ">
				<header>
					<h2>{{ trans('navigation/projectnav.engineerInstruction') }} (EI) Listing </h2>
				</header>
				<div>
					<div class="widget-body no-padding">
						<div class="table-responsive">
							<table class="table  table-hover" id="datatable_fixed_column">
								<thead>
									<tr>
										<th>&nbsp;</th>
				                        <th class="hasinput">
				                            <input type="text" class="form-control" placeholder="Filter EI Reference" />
				                        </th>
				                        <th class="hasinput icon-addon">
				                            <input id="dateselect_filter" type="text" placeholder="Filter Date Issued" class="form-control datepicker" data-dateformat="dd-M-yy">
				                            <label for="dateselect_filter" class="glyphicon glyphicon-calendar no-margin padding-top-15" rel="tooltip" title="" data-original-title="Filter Date"></label>
				                        </th>
				                        <th class="hasinput">
				                            <input type="text" class="form-control" placeholder="Filter Status" />
				                        </th>
				                        <th class="hasinput icon-addon">
				                            <input id="dateselect_filter2" type="text" placeholder="Filter Deadline To Comply" class="form-control datepicker" data-dateformat="dd-M-yy">
				                            <label for="dateselect_filter2" class="glyphicon glyphicon-calendar no-margin padding-top-15" rel="tooltip" title="" data-original-title="Filter Date"></label>
				                        </th>
				                    </tr>
									<tr>
										<th style="width: 3%;">No</th>
										<th>EI Reference</th>
										<th style="text-align: center;">Date Issued</th>
										<th style="text-align: center;">Status</th>
										<th style="text-align: center;">Deadline To Comply</th>
									</tr>
								</thead>
								<tbody>
									@if ( ! $eis->isEmpty() )
										<?php $counter = 1; ?>
										@foreach ( $eis as $ei )
										<tr>
											<td class="text-middle text-center">
												<?php echo $counter++; ?>
											</td>
											<td>{{ link_to_route('ei.show', $ei->subject, array($project->id, $ei->id)) }}</td>
											<td class="dateSubmitted" style="text-align: center;">{{{ $project->getProjectTimeZoneTime($ei->created_at) }}}</td>
											<td style="text-align: center;">{{{ $ei->status }}}</td>
											<td style="text-align: center;">{{{ $project->getProjectTimeZoneTime($ei->deadline_to_comply_with) }}}</td>
										</tr>
										@endforeach
									@endif
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
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
@endsection

@section('inline-js')
	$(document).ready(function() {
		var otable = $('#datatable_fixed_column').DataTable({
			"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6 hidden-xs'f><'col-sm-6 col-xs-12 hidden-xs'<'toolbar'>>r>"+
					"t"+
					"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
			"autoWidth" : false
		});

		$("#datatable_fixed_column thead th input[type=text]").on( 'keyup change', function () {
			otable
				.column( $(this).parent().index()+':visible' )
				.search( this.value )
				.draw();
		} );
	});
@endsection