@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($loe->project->title, 50), array($loe->project->id)) }}</li>
		<li>{{ link_to_route('loe', trans('navigation/projectnav.lossOrAndExpenses') . ' (L &amp; E)', array($loe->project->id)) }}</li>
		<li>View Current L &amp; E ({{{ $loe->subject }}})</li>
	</ol>

    @include('projects.partials.project_status', array('project' => $loe->project))
@endsection

@section('content')
	<h1>View Current L &amp; E ({{{ $loe->subject }}})</h1>

	<div class="row">
		<article class="col-sm-12 col-md-12 col-lg-7">
			<div class="jarviswidget well" role="widget">
				<div role="content">
					<div class="widget-body">
						<ul id="myTab1" class="nav nav-tabs bordered">
							<li class="active">
								<a href="#s1" data-toggle="tab">Notice to Claim</a>
							</li>
							@if ( $loe->contractorConfirmDelay )
								<li>
									<a href="#s2" data-toggle="tab">Confirmation of L &amp; E Ended</a>
								</li>
							@endif

							@if ( $loe->lossOrAndExpenseClaim )
								<li>
									<a href="#s3" data-toggle="tab">Submit Final Claim</a>
								</li>
							@endif

							@if ( $lastArchitectFourthMessage and ! $loe->fourthLevelMessages->isEmpty() )
								<li>
									<a href="#s4" data-toggle="tab">Architect's Decision</a>
								</li>
							@endif
						</ul>

						<div id="myTabContent1" class="tab-content" style="padding: 13px!important;">
							<div class="tab-pane active" id="s1">
								<!-- widget div-->
								<div>
									@if ( $loe->status == PCK\LossOrAndExpenses\LossOrAndExpense::DRAFT_TEXT )
										@if (($loe->created_by == $user->id && $user->stillInSameAssignedCompany($loe->project, $loe->created_at)) or $isEditor)
											@include('loss_and_or_expenses.partials.loe_update_form', array('project' => $loe->project))
										@else
											@include('loss_and_or_expenses.partials.loe_view_only')
										@endif
									@else
										@include('loss_and_or_expenses.partials.loe_view_only')
									@endif
								</div>
							</div>

							@if ( $loe->contractorConfirmDelay )
								@include('loss_and_or_expenses.partials.contractor_confirm_delay_tab')
							@endif

							@if ( $loe->lossOrAndExpenseClaim )
								@include('loss_and_or_expenses.partials.loe_claim_tab')
							@endif

							@if ( $lastArchitectFourthMessage and ! $loe->fourthLevelMessages->isEmpty() )
								@include('loss_and_or_expenses.partials.fourth_level_messages_tab')
							@endif
						</div>
					</div>
				</div>
			</div>
		</article>

		@if ( $loe->status != PCK\LossOrAndExpenses\LossOrAndExpense::DRAFT_TEXT )
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
							@include('loss_and_or_expenses.partials.reminders')
						</div>
						<!-- end widget content -->
					</div>
					<!-- end widget div -->
				</div>
				<!-- end widget -->
			</article>
		@endif
	</div>

	@foreach ( $loe->fourthLevelMessages as $message )
		@if ( $message->type == \PCK\ContractGroups\Types\Role::CLAIM_VERIFIER )
			@include('loss_and_or_expenses.partials.qs_fourth_level_info_modal')
		@endif
	@endforeach

	@if ( $loe->lossOrAndExpenseInterimClaim )
		@include('loss_and_or_expense_interim_claims.partials.modal_box')
	@endif
@endsection

@section('js')
	<script src="{{ asset('js/app/app.reminderAccordion.js') }}"></script>
	<script src="{{ asset('js/app/app.AICommenceDate.js') }}"></script>
@endsection