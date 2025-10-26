@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
		<li>{{ trans('navigation/projectnav.weatherRecord') }} (WR)</li>
	</ol>

    @include('projects.partials.project_status')
@endsection

@section('content')

	<div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">
				{{ trans('navigation/projectnav.weatherRecord') }} (WR)
			</h1>
		</div>

		<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
			{{ link_to_route('wr.create', 'Update New WR', $project->id, array('class' => 'btn btn-primary btn-md pull-right header-btn')) }}
		</div>
	</div>

	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			<div class="jarviswidget ">
				<header>
					<h2>{{ trans('navigation/projectnav.weatherRecord') }} (WR) Listing </h2>
				</header>
				<div>
					<div class="widget-body no-padding">
						<div class="table-responsive">
							<table class="table  table-hover" id="datatable_fixed_column">
								<thead>
									<tr>
										<th>&nbsp;</th>
										<th class="hasinput icon-addon">
				                            <input id="dateselect_filter" type="text" placeholder="Weather Date" class="form-control datepicker" data-dateformat="dd-M-yy">
				                            <label for="dateselect_filter" class="glyphicon glyphicon-calendar no-margin padding-top-15" rel="tooltip" title="" data-original-title="Filter Date"></label>
				                        </th>
				                        <th class="hasinput">
				                            <input type="text" class="form-control" placeholder="Recorded By" />
				                        </th>
				                        <th class="hasinput">
				                            <input type="text" class="form-control" placeholder="Verified By" />
				                        </th>
				                        <th class="hasinput">
				                            <input type="text" class="form-control" placeholder="Note" />
				                        </th>
				                        <th class="hasinput">
				                            <input type="text" class="form-control" placeholder="Status" />
				                        </th>
				                    </tr>
									<tr>
										<th style="width: 3%;">No</th>
										<th>Weather Date</th>
										<th style="text-align: center;">Recorded By</th>
										<th style="text-align: center;">Verified By</th>
										<th>Note</th>
										<th style="text-align: center;">Status</th>
									</tr>
								</thead>
								<tbody>
									@if ( ! $wrs->isEmpty() )
										<?php $counter = 1; ?>
										@foreach ( $wrs as $wr )
										<tr>
											<td class="text-middle text-center">
												<?php echo $counter++; ?>
											</td>
											<td style="vertical-align: middle;">{{ link_to_route('wr.show', $project->getProjectTimeZoneTime($wr->date), array($project->id, $wr->id)) }}</td>
											<td style="vertical-align: middle; text-align: center;">{{{ $wr->createdBy->present()->byWhoAndRole($project, $wr->created_at) }}}<br><span class="dateSubmitted">{{{ $project->getProjectTimeZoneTime($wr->created_at) }}}</span></td>
											<td style="vertical-align: middle; text-align: center;">
												@if ( $wr->status == PCK\WeatherRecords\WeatherRecord::VERIFIED_TEXT )
													{{{ $wr->verifiedBy->present()->byWhoAndRole($project, $wr->created_at) }}}<br>{{{ $project->getProjectTimeZoneTime($wr->updated_at) }}}
												@else
													-
												@endif
											</td>
											<td style="vertical-align: middle; text-align: left;">{{{ $wr->note }}}</td>
											<td style="vertical-align: middle; text-align: center;">{{{ $wr->status }}}</td>
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
    <script>
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
    </script>
@endsection
