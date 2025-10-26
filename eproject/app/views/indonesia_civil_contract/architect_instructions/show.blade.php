@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
		<li>{{ trans('architectInstructions.architectInstruction') }}</li>
		<li>{{ link_to_route('ai', trans('architectInstructions.architectInstruction'), array($project->id)) }}</li>
		<li>{{ trans('architectInstructions.viewCurrentArchitectInstruction') }} ({{{ $ai->reference }}})</li>
	</ol>

    @include('projects.partials.project_status', array('project' => $project))
@endsection

@section('content')
	<h1>{{ trans('architectInstructions.viewCurrentArchitectInstruction') }} ({{{ $ai->reference }}})</h1>

	<div class="row">
		<article class="col-sm-12 col-md-12 col-lg-7">
			<div class="jarviswidget well" role="widget">
				<div role="content">
					<div class="widget-body">
						<ul id="myTab1" class="nav nav-tabs bordered">
							<li class="active">
								<a href="#s1" data-toggle="tab">{{ trans('architectInstructions.architectInstructionInformation') }}</a>
							</li>
						</ul>

						<div id="myTabContent1" class="tab-content" style="padding: 13px!important;">
							<div class="tab-pane active" id="s1">
								<!-- widget div-->
								<div>
									@if ( $ai->isEditable($currentUser) )
										@include('indonesia_civil_contract.architect_instructions.partials.update_form', array('project' => $project))
									@else
										<div class="widget-body no-padding">
											<div class="smart-form">
												@include('indonesia_civil_contract.architect_instructions.partials.information')
												@if(\PCK\Verifier\Verifier::isCurrentVerifier($currentUser, $ai))
													<footer>
														@include('verifiers.verifier_status_overview')
														@include('verifiers.approvalForm', array('object' => $ai))
													</footer>
												@endif
												@foreach($ai->responses as $response)
													@include('indonesia_civil_contract.architect_instructions.partials.responseInformation', array('response' => $response))
												@endforeach
												@if($ai->canRespond($currentUser))
													@include('indonesia_civil_contract.architect_instructions.partials.responseForm')
												@endif
											</div>
										</div>
									@endif
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</article>

		@if ( $ai->status == PCK\IndonesiaCivilContract\ArchitectInstruction\ArchitectInstruction::STATUS_SUBMITTED )
			@include('indonesia_civil_contract.architect_instructions.partials.workflow')
		@endif
	</div>
@endsection

@section('js')
	<script>
		$('a[data-type=goToForm]').on('click', function(){
			$('#responseForm input[name=subject]').focus();
		});
		$('a[href^="#response-"][data-id]').on('click', function(){
			app_expandable.toggleExpand($('[data-type=expandable][data-id=' + $(this).data('id') +']'));
		});
	</script>
@endsection