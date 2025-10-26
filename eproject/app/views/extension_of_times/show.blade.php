@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($eot->project->title, 50), array($eot->project->id)) }}</li>
		<li>{{ link_to_route('eot', trans('navigation/projectnav.extensionOfTime') . ' (EOT)', array($eot->project->id)) }}</li>
		<li>View Current EOT ({{{ $eot->subject }}})</li>
	</ol>

    @include('projects.partials.project_status', array('project' => $eot->project))
@endsection

@section('content')
	<h1>View Current EOT ({{{ $eot->subject }}})</h1>

	<div class="row">
		<article class="col-sm-12 col-md-12 col-lg-7">
			<div class="jarviswidget well" role="widget">
				<div role="content">
					<div class="widget-body">
						<ul id="myTab1" class="nav nav-tabs bordered">
							<li class="active">
								<a href="#s1" data-toggle="tab">Notice to Claim</a>
							</li>
							@if ( $eot->eotContractorConfirmDelay )
								<li>
									<a href="#s2" data-toggle="tab">Confirmation of EOT Ended</a>
								</li>
							@endif

							@if ( $eot->extensionOfTimeClaim )
								<li>
									<a href="#s3" data-toggle="tab">Submit Final Claim</a>
								</li>
							@endif

							@if ( ! $eot->fourthLevelMessages->isEmpty() )
								<li>
									<a href="#s4" data-toggle="tab">Architect's Decision</a>
								</li>
							@endif
						</ul>

						<div id="myTabContent1" class="tab-content" style="padding: 13px!important;">
							<div class="tab-pane active" id="s1">
								<!-- widget div-->
								<div>
									@if ( $eot->status == PCK\ExtensionOfTimes\ExtensionOfTime::DRAFT_TEXT )
										@if (($eot->created_by == $user->id && $user->stillInSameAssignedCompany($eot->project, $eot->created_at)) or $isEditor)
											@include('extension_of_times.partials.eot_update_form', array('project' => $eot->project))
										@else
											@include('extension_of_times.partials.eot_view_only')
										@endif
									@else
										@include('extension_of_times.partials.eot_view_only')
									@endif
								</div>
							</div>

							@if ( $eot->eotContractorConfirmDelay )
								@include('extension_of_times.partials.contractor_confirm_delay_tab')
							@endif

							@if ( $eot->extensionOfTimeClaim )
								@include('extension_of_times.partials.eot_claim_tab')
							@endif

							@if ( ! $eot->fourthLevelMessages->isEmpty() )
								@include('extension_of_times.partials.fourth_level_messages_tab')
							@endif
						</div>
					</div>
				</div>
			</div>
		</article>

		@if ( $eot->status != PCK\ExtensionOfTimes\ExtensionOfTime::DRAFT_TEXT )
			<article class="col-sm-12 col-md-12 col-lg-5">
				<!-- Widget ID (each widget will need unique ID)-->
				<div class="jarviswidget jarviswidget-color-darken" role="widget">
					<header>
						<span class="widget-icon"><i class="fa fa-arrows-alt-v"></i></span>
						<h2><strong><i>Workflow</i></strong></h2>
					</header>

					<!-- widget div-->
					<div>
						<!-- widget content -->
						<div class="widget-body">
							@include('extension_of_times.partials.reminders')
						</div>
						<!-- end widget content -->
					</div>
					<!-- end widget div -->
				</div>
				<!-- end widget -->
			</article>
		@endif
	</div>
@endsection

@section('js')
	<script src="{{ asset('js/app/app.reminderAccordion.js') }}"></script>
	<script src="{{ asset('js/app/app.AICommenceDate.js') }}"></script>
@endsection