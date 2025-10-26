@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($ae->project->title, 50), array($ae->project->id)) }}</li>
		<li>{{ link_to_route('ae', trans('navigation/projectnav.additionalExpenses') . ' (AE)', array($ae->project->id)) }}</li>
		<li>View Current AE ({{{ $ae->subject }}})</li>
	</ol>

    @include('projects.partials.project_status', array('project' => $ae->project))
@endsection

@section('content')
	<h1>View Current AE ({{{ $ae->subject }}})</h1>

	<div class="row">
		<article class="col-sm-12 col-md-12 col-lg-7">
			<div class="jarviswidget well" role="widget">
				<div role="content">
					<div class="widget-body">
						<ul id="myTab1" class="nav nav-tabs bordered">
							<li class="active">
								<a href="#s1" data-toggle="tab">Notice to Claim</a>
							</li>

							@if ( $ae->contractorConfirmDelay )
								<li>
									<a href="#s2" data-toggle="tab">Confirmation of AE Ended</a>
								</li>
							@endif

							@if ( $ae->additionalExpenseClaim )
								<li>
									<a href="#s3" data-toggle="tab">Submit Final Claim</a>
								</li>
							@endif

							@if ( $lastArchitectFourthMessage and ! $ae->fourthLevelMessages->isEmpty() )
								<li>
									<a href="#s4" data-toggle="tab">Architect's Decision</a>
								</li>
							@endif
						</ul>

						<div id="myTabContent1" class="tab-content" style="padding: 13px!important;">
							<div class="tab-pane active" id="s1">
								<!-- widget div-->
								<div>
									@if ( $ae->status == PCK\AdditionalExpenses\AdditionalExpense::DRAFT_TEXT )
										@if ($ae->user_id == $user->id or $isEditor)
											@include('additional_expenses.partials.ae_update_form', array('project' => $ae->project))
										@else
											@include('additional_expenses.partials.ae_view_only')
										@endif
									@else
										@include('additional_expenses.partials.ae_view_only')
									@endif
								</div>
							</div>

							@if ( $ae->contractorConfirmDelay )
								@include('additional_expenses.partials.contractor_confirm_delay_tab')
							@endif

							@if ( $ae->additionalExpenseClaim )
								@include('additional_expenses.partials.claim_tab')
							@endif

							@if ( $lastArchitectFourthMessage and ! $ae->fourthLevelMessages->isEmpty() )
								@include('additional_expenses.partials.fourth_level_messages_tab')
							@endif
						</div>
					</div>
				</div>
			</div>
		</article>

		@if ( $ae->status != PCK\AdditionalExpenses\AdditionalExpense::DRAFT_TEXT )
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
							@include('additional_expenses.partials.reminders')
						</div>
						<!-- end widget content -->
					</div>
					<!-- end widget div -->
				</div>
				<!-- end widget -->
			</article>
		@endif
	</div>

	@foreach ( $ae->fourthLevelMessages as $message )
		@if ( $message->type == \PCK\ContractGroups\Types\Role::CLAIM_VERIFIER )
			@include('additional_expenses.partials.qs_fourth_level_info_modal')
		@endif
	@endforeach

	@if ( $ae->additionalExpenseInterimClaim )
		@include('additional_expense_interim_claims.partials.modal_box')
	@endif
@endsection

@section('js')
	<script src="{{ asset('js/app/app.reminderAccordion.js') }}"></script>
	<script src="{{ asset('js/app/app.AICommenceDate.js') }}"></script>
@endsection