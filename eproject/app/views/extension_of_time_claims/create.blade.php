@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($eot->project->title, 50), array($eot->project->id)) }}</li>
		<li>{{ link_to_route('eot', trans('navigation/projectnav.extensionOfTime') . ' (EOT)', array($eot->project->id)) }}</li>
		<li>{{ link_to_route('eot.show', "View Current EOT ({$eot->subject})", array($eot->project->id, $eot->id)) }}</li>
		<li>Messaging Form</li>
	</ol>

    @include('projects.partials.project_status', array('project' => $eot->project))
@endsection

@section('content')
	<div class="row">
		<!-- NEW COL START -->
		<article class="col-sm-12 col-md-12 col-lg-8">
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget">
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>{{{ PCK\Forms\EOTClaimForm::formTitle }}}</h2>
				</header>

				<!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body no-padding">
						{{ Form::open(array('class' => 'smart-form')) }}
							<fieldset>
								<section>
									<label class="label">Project:</label>
									{{{ $eot->project->title }}}
								</section>
								<section>
									<label class="label">Reference to AI (if relevant):</label>
									@if ( $eot->architectInstruction )
										{{ link_to_route('ai.show', $eot->architectInstruction->reference, array($eot->project->id, $eot->architectInstruction->id)) }}
									@else
										Not related to any AI
									@endif
								</section>

								<section>
									<label class="label">Relevant Notice of Intention to Claim EOT's Reference:</label>
									{{ link_to_route('eot.show', $eot->subject, array($eot->project_id, $eot->id)) }}
								</section>

								<section>
									<label class="label">Deadline to Submit the final EOT Claim:</label>
									{{{ $eot->project->getProjectTimeZoneTime($eot->eotContractorConfirmDelay->deadline_to_submit_final_eot_claim) }}}
								</section>

								<section>
									<label class="label">Subject/Reference<span class="required">*</span>:</label>
									<label class="input {{{ $errors->has('subject') ? 'state-error' : null }}}">
										{{ Form::text('subject', Input::old('subject'), array('required' => 'required')) }}
									</label>
									{{ $errors->first('subject', '<em class="invalid">:message</em>') }}
								</section>

								<section>
									<label class="label">Detailed Elaborations to Substantiate Claim/Cover Letter<span class="required">*</span>:</label>
									<label class="textarea {{{ $errors->has('message') ? 'state-error' : null }}}">
										{{ Form::textarea('message', Input::old('message'), array('required' => 'required', 'rows' => 3)) }}
									</label>
									{{ $errors->first('message', '<em class="invalid">:message</em>') }}
								</section>

								<section>
									<label class="label">EOT Claimed (Days)<span class="required">*</span>:</label>
									<label class="input {{{ $errors->has('days_claimed') ? 'state-error' : null }}}">
										{{ Form::text('days_claimed', Input::old('days_claimed'), array('required' => 'required')) }}
									</label>
									{{ $errors->first('days_claimed', '<em class="invalid">:message</em>') }}
								</section>

								<section>
									<label class="label">Attachment(s):</label>

									@include('file_uploads.partials.upload_file_modal', ['project' => $eot->project])
								</section>
							</fieldset>

							<footer>
								{{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}

								{{ link_to_route('eot.show', 'Cancel', [$eot->project->id, $eot->id], ['class' => 'btn btn-default']) }}
							</footer>
						{{ Form::close() }}
					</div>
					<!-- end widget content -->
				</div>
				<!-- end widget div -->
			</div>
			<!-- end widget -->
		</article>
		<!-- END COL -->
	</div>
@endsection