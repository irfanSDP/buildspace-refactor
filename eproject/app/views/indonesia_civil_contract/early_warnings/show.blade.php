@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
		<li>{{ trans('earlyWarnings.earlyWarning') }}</li>
		<li>{{ link_to_route('indonesiaCivilContract.earlyWarning', trans('earlyWarnings.earlyWarning'), array($project->id)) }}</li>
		<li>{{ trans('earlyWarnings.view') }} ({{{ $ew->reference }}})</li>
	</ol>

    @include('projects.partials.project_status', array('project' => $project))
@endsection

@section('content')
	<h1>{{ trans('earlyWarnings.view') }} ({{{ $ew->reference }}})</h1>

	<div class="row">
		<article class="col-sm-12 col-md-12 col-lg-7">
			<div class="jarviswidget well" role="widget">
				<div role="content">
					<div class="widget-body">
						<ul id="myTab1" class="nav nav-tabs bordered">
							<li class="active">
								<a href="#s1" data-toggle="tab">{{ trans('earlyWarnings.earlyWarningInformation') }}</a>
							</li>
						</ul>

						<div id="myTabContent1" class="tab-content" style="padding: 13px!important;">
							<div class="tab-pane active" id="s1">
								<!-- widget div-->
								<div>
									<div class="widget-body no-padding">
										<div class="smart-form">
											<fieldset>
												<section>
													<strong>{{ trans('projects.project') }}:</strong><br>
													<label class="input">
														{{{ $project->title }}}
													</label>
												</section>

												<section>
													<strong>{{ trans('earlyWarnings.reference') }}:</strong><br>
													{{{ $ew->reference }}}
												</section>

												<section>
													<strong>{{ trans('earlyWarnings.details') }}:</strong><br>
													{{{ $ew->impact }}}
												</section>

												<section>
													<strong>{{ trans('earlyWarnings.reportedAt') }}:</strong><br>
													<label class="input">
														<strong class="dateSubmitted"><i>{{{ $project->getProjectTimeZoneTime($ew->created_at) }}}</i></strong> by {{{ $ew->createdBy->present()->byWhoAndRole($ew->project, $ew->created_at) }}}
													</label>
												</section>

												<section>
													<strong>{{ trans('earlyWarnings.commencementDate') }}:</strong><br>
													<label class="input">
														<strong class="dateSubmitted"><i>{{{ $project->getProjectTimeZoneTime($ew->commencement_date) }}}</i></strong>
													</label>
												</section>

												@if ( ! $ew->attachments->isEmpty() )
													<section>
														<strong>{{ trans('general.attachments') }}:</strong><br>

														@include('file_uploads.partials.uploaded_file_show_only', ['files' => $ew->attachments, 'projectId' => $ew->project_id])
													</section>
												@endif
											</fieldset>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</article>

		@include('indonesia_civil_contract.early_warnings.partials.workflow')
	</div>
@endsection