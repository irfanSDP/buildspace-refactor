@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
		<li>{{ trans('extensionOfTime.extensionOfTime') }}</li>
		<li>{{ link_to_route('indonesiaCivilContract.extensionOfTime', trans('extensionOfTime.extensionOfTime'), array($project->id)) }}</li>
		<li>{{ trans('extensionOfTime.viewCurrentExtensionOfTime') }} ({{{ $eot->reference }}})</li>
	</ol>

    @include('projects.partials.project_status', array('project' => $project))
@endsection

@section('content')
	<h1>{{ trans('extensionOfTime.viewCurrentExtensionOfTime') }} ({{{ $eot->reference }}})</h1>

	<div class="row">
		<article class="col-sm-12 col-md-12 col-lg-7">
			<div class="jarviswidget well" role="widget">
				<div role="content">
					<div class="widget-body">
						<ul id="myTab1" class="nav nav-tabs bordered">
							<li class="active">
								<a href="#s1" data-toggle="tab">{{ trans('extensionOfTime.extensionOfTimeInformation') }}</a>
							</li>
						</ul>

						<div id="myTabContent1" class="tab-content" style="padding: 13px!important;">
							<div class="tab-pane active" id="s1">
								<!-- widget div-->
								<div>
									@if ( $eot->isEditable($currentUser) )
										@include('indonesia_civil_contract.extension_of_time.partials.update_form', array('project' => $project))
									@else
										<div class="widget-body no-padding">
											<div class="smart-form">
												@include('indonesia_civil_contract.extension_of_time.partials.information')
												@foreach($eot->responses as $response)
													@include('indonesia_civil_contract.extension_of_time.partials.responseInformation', array('response' => $response))
												@endforeach
												@if($eot->canRespond($currentUser))
													@if($eot->responses->isEmpty() || $eot->responses->last()->type == \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_PLAIN)
														@include('indonesia_civil_contract.extension_of_time.partials.decisionResponseForm')
													@else
														@include('indonesia_civil_contract.extension_of_time.partials.plainResponseForm')
													@endif
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

		@if ( $eot->status != \PCK\IndonesiaCivilContract\ExtensionOfTime\ExtensionOfTime::STATUS_DRAFT )
			@include('indonesia_civil_contract.extension_of_time.partials.workflow')
		@endif
	</div>
@endsection

@section('js')
	<script>
		$('a[data-type=goToForm]').on('click', function(){
			$('#responseForm input[name=subject]').focus();
		});
		$('#responseForm').on('click', 'input[type=radio][name=type][value={{{ \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_GRANT }}}]', function(){
			$('#responseForm input[name=proposed_value]').focus();
		});
		$('a[href^="#response-"][data-id]').on('click', function(){
			app_expandable.toggleExpand($('[data-type=expandable][data-id=' + $(this).data('id') +']'));
		});
	</script>
@endsection