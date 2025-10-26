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
					<h2>{{{ PCK\Forms\EOTContractorConfirmDelayForm::formTitle }}}</h2>
				</header>

				<!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body no-padding">
						{{ Form::open(array('class' => 'smart-form')) }}
							<fieldset>
								<section>
									<label class="label">Notice of Intention To Claim EOT's Reference:</label>
									{{ link_to_route('eot.show', $eot->subject, array($eot->project_id, $eot->id)) }}
								</section>

								<section>
									<label class="label">Date of Submission:</label>
									<span class="dateSubmitted">{{{ $eot->project->getProjectTimeZoneTime($eot->created_at) }}}</span>
								</section>

								<section>
									<label class="label">Subject/Reference<span class="required">*</span>:</label>
									<label class="input {{{ $errors->has('subject') ? 'state-error' : null }}}">
										{{ Form::text('subject', Input::old('subject'), array('required' => 'required')) }}
									</label>
									{{ $errors->first('subject', '<em class="invalid">:message</em>') }}
								</section>

								<section>
									<label class="label">Date on which the cause of delay is over<span class="required">*</span>:</label>
									<label class="input {{{ $errors->has('date_on_which_delay_is_over') ? 'state-error' : null }}}">
										{{ Form::text('date_on_which_delay_is_over', Input::old('date_on_which_delay_is_over'), array('class' => 'finishdate')) }}
									</label>
									{{ $errors->first('date_on_which_delay_is_over', '<em class="invalid">:message</em>') }}
								</section>

								<section>
									<label class="label">Deadline to Submit the final EOT Claim ({{{ $eot->project->pam2006Detail->deadline_submitting_final_claim_eot }}} days from the end of the cause of delay):</label>
									<span id="new_deadline">
										@if ( Input::old('date_on_which_delay_is_over') and strtotime(Input::old('date_on_which_delay_is_over')) )
											{{{ ($calendarRepo->calculateFinalDate($eot->project, Input::old('date_on_which_delay_is_over'), $eot->project->pam2006Detail->deadline_submitting_final_claim_eot)) }}}
										@else
											Please select a date from above
										@endif
									</span>
								</section>

								<section>
									<label class="label">Letter to the Architect<span class="required">*</span>:</label>
									<label class="textarea {{{ $errors->has('message') ? 'state-error' : null }}}">
										{{ Form::textarea('message', Input::old('message'), array('required' => 'required', 'rows' => 3)) }}
									</label>
									{{ $errors->first('message', '<em class="invalid">:message</em>') }}
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

							{{ Form::hidden('deadline_days', $eot->project->pam2006Detail->deadline_submitting_final_claim_eot, ['id' => 'deadline_days']) }}
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