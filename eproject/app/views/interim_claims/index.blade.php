@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
		<li>{{ trans('navigation/projectnav.interimClaim') }} (IC)</li>
	</ol>

    @include('projects.partials.project_status')
@endsection

@section('content')

	<div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">
				{{ trans('navigation/projectnav.interimClaim') }} (IC)
			</h1>
		</div>

		@if ( $user->hasCompanyProjectRole($project, \PCK\ContractGroups\Types\Role::CONTRACTOR) and $isEditor )
			@if ( $ics->isEmpty() or $ics[0]->status == \PCK\InterimClaims\InterimClaim::GRANTED_TEXT )
				<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				{{ link_to_route('ic.create', 'Prepare New IC', $project->id, array('class' => 'btn btn-primary btn-md pull-right header-btn')) }}
				</div>
			@endif
		@endif
	</div>

	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			<div class="jarviswidget ">
				<header>
					<h2>{{ trans('navigation/projectnav.interimClaim') }} (IC) Listing </h2>
				</header>
				<div>
					<div class="widget-body no-padding">
						<div class="table-responsive">
							<table class="table  table-hover" id="datatable_fixed_column">
								<thead>
									<tr>
										<th>&nbsp;</th>
				                        <th class="hasinput">
				                            <input type="text" class="form-control" placeholder="Claim No" />
				                        </th>
				                        <th class="hasinput">
				                            <input type="text" class="form-control" placeholder="Month/Year" />
				                        </th>
				                        <th class="hasinput" style="width:12%;">
				                            <input type="text" class="form-control" placeholder="Amount Claimed" />
				                        </th>
				                        <th class="hasinput" style="width:12%;">
				                            <input type="text" class="form-control" placeholder="Amount Granted" />
				                        </th>
				                        <th class="hasinput icon-addon">
				                            <input id="dateselect_filter2" type="text" placeholder="Date Submitted" class="form-control datepicker" data-dateformat="dd-M-yy">
				                            <label for="dateselect_filter2" class="glyphicon glyphicon-calendar no-margin padding-top-15" rel="tooltip" title="" data-original-title="Filter Date"></label>
				                        </th>
				                        <th class="hasinput" style="width:10%;">
				                            <input type="text" class="form-control" placeholder="Status" />
				                        </th>
				                    </tr>
									<tr>
										<th style="width: 3%;">No</th>
										<th>IC Claim No./ Reference</th>
										<th style="text-align: center;">Month/Year</th>
										<th style="text-align: center;">Amount Claimed ({{{ $project->modified_currency_code }}})</th>
										<th style="text-align: center;">Amount Granted ({{{ $project->modified_currency_code }}})</th>
										<th style="text-align: center;">Date Submitted</th>
										<th style="text-align: center;">Status</th>
									</tr>
								</thead>
								<tbody>
									@if ( $ics->count() > 0 )
										<?php $counter = 1; ?>
										@foreach ( $ics as $ic )
										<tr>
											<td class="text-middle text-center">
												<?php echo $counter++; ?>
											</td>
											<td>{{ link_to_route('ic.show', $ic->claim_no, array($project->id, $ic->id)) }}</td>
											<td style="text-align: center;">{{{ date("F", mktime(0, 0, 0, $ic->month, 10)) }}}/{{{ $ic->year }}}</td>
											<td style="text-align: center;">{{{ number_format($ic->amount_claimed, 2) }}}</td>
											<td style="text-align: center;">{{{ number_format($ic->amount_granted, 2) }}}</td>
											<td class="dateSubmitted" style="text-align: center;">{{{ $project->getProjectTimeZoneTime($ic->created_at) }}}</td>
											<td style="text-align: center;">
												@if ( $ic->status == PCK\InterimClaims\InterimClaim::GRANTED_TEXT )
													{{{ $ic->status }}}<br/>{{{ $project->getProjectTimeZoneTime($ic->updated_at) }}}
												@else
													{{{ $ic->status }}}
												@endif
											</td>
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