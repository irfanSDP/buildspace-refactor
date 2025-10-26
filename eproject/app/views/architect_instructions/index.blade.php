@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
		<li>{{ trans('navigation/projectnav.architectInstruction') }} (AI)</li>
	</ol>

    @include('projects.partials.project_status')
@endsection

@section('content')

	<div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">
				{{ trans('navigation/projectnav.architectInstruction') }} (AI)
			</h1>
		</div>
		
		@if ( $user->hasCompanyProjectRole($project, \PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER) )
			<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				{{ link_to_route('ai.create', 'Issue New AI', $project->id, array('class' => 'btn btn-primary btn-md pull-right header-btn')) }}
			</div>
		@endif
	</div>

	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			<div class="jarviswidget ">
				<header>
					<h2>{{ trans('navigation/projectnav.architectInstruction') }} (AI) Listing </h2>
				</header>
				<div>
					<div class="widget-body no-padding">
						<div class="table-responsive">
							<table class="table table-hover" id="datatable_fixed_column">
								<thead>
									<tr>
										<th>&nbsp;</th>
				                        <th class="hasinput">
				                            <input type="text" class="form-control" placeholder="Filter AI Name" />
				                        </th>
				                        <th class="hasinput icon-addon">
				                            <input id="dateselect_filter" type="text" placeholder="Filter Date Issued" class="form-control datepicker" data-dateformat="dd-M-yy">
				                            <label for="dateselect_filter" class="glyphicon glyphicon-calendar no-margin padding-top-15" rel="tooltip" title="" data-original-title="Filter Date"></label>
				                        </th>
				                        <th class="hasinput">
				                            <input type="text" class="form-control" placeholder="Filter Status" />
				                        </th>
				                        <th class="hasinput icon-addon">
				                            <input id="dateselect_filter2" type="text" placeholder="Filter Date To Comply" class="form-control datepicker" data-dateformat="dd-M-yy">
				                            <label for="dateselect_filter2" class="glyphicon glyphicon-calendar no-margin padding-top-15" rel="tooltip" title="" data-original-title="Filter Date"></label>
				                        </th>
				                    </tr>
									<tr>
										<th style="width: 3%;">No</th>
										<th>AI</th>
										<th style="text-align: center;">Date Issued</th>
										<th style="text-align: center;">Status</th>
										<th style="text-align: center;">Date To Comply</th>
									</tr>
								</thead>
								<tbody>
									<?php $counter = 1; ?>
									@foreach ( $ais as $ai )
										<tr>
											<td class="text-middle text-center">
												<?php echo $counter++; ?>
											</td>
											<td>{{ link_to_route('ai.show', $ai->reference, array($project->id, $ai->id)) }}</td>
											<td class="dateSubmitted text-center">{{{ $project->getProjectTimeZoneTime($ai->created_at) }}}</td>
											<td class="text-center">{{{ $ai->status }}}</td>
											<td class="text-center">{{{ $project->getProjectTimeZoneTime($ai->deadline_to_comply) ?? '-' }}}</td>
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