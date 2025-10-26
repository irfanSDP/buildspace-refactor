@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($ai->project->title, 50), array($ai->project->id)) }}</li>
		<li>{{ link_to_route('ai', trans('navigation/projectnav.architectInstruction') . ' (AI)', array($ai->project->id)) }}</li>
		<li>View Current AI ({{{ $ai->reference }}})</li>
	</ol>

    @include('projects.partials.project_status', array('project' => $ai->project))
@endsection

@section('content')
	<h1>View Current AI ({{{ $ai->reference }}})</h1>

	<div class="row">
		<article class="col-sm-12 col-md-12 col-lg-7">
			<div class="jarviswidget well" role="widget">
				<div role="content">
					<div class="widget-body">
						<ul id="myTab1" class="nav nav-tabs bordered">
							<li class="active">
								<a href="#s1" data-toggle="tab">AI Information</a>
							</li>
							@if ( $ai->thirdLevelMessages->count() > 0 )
							<li>
								<a href="#s2" data-toggle="tab">Compliance of AI</a>
							</li>
							@endif
						</ul>

						<div id="myTabContent1" class="tab-content" style="padding: 13px!important;">
							<div class="tab-pane active" id="s1">
								<!-- widget div-->
								<div>
									@if ( $ai->status == PCK\ArchitectInstructions\ArchitectInstruction::DRAFT_TEXT )
										@if ($ai->user_id == $user->id or $isEditor)
											@include('architect_instructions.partials.ai_update_form', array('project' => $ai->project))
										@else
											@include('architect_instructions.partials.ai_view_only')
										@endif
									@else
										@include('architect_instructions.partials.ai_view_only')
									@endif
								</div>
							</div>
							@if ( $ai->thirdLevelMessages->count() > 0 )
							<div id="s2" class="tab-pane padding-10">
								@include('architect_instructions.partials.third_level_conversations', array('messages' => $ai->thirdLevelMessages))
							</div>
							@endif
						</div>
					</div>
				</div>
			</div>
		</article>

		@if ( $ai->status != PCK\ArchitectInstructions\ArchitectInstruction::DRAFT_TEXT )
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
							@include('architect_instructions.partials.reminders')
						</div>
						<!-- end widget content -->
					</div>
					<!-- end widget div -->
				</div>
				<!-- end widget -->
			</article>
		@endif
	</div>

	@if ( $ai->architectInstructionInterimClaim )
		@include('architect_instruction_interim_claims.partials.modal_box')
	@endif
@endsection

@section('js')
	<script src="{{ asset('js/app/app.reminderAccordion.js') }}"></script>
@endsection