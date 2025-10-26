@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($ae->project->title, 50), array($ae->project->id)) }}</li>
		<li>{{ link_to_route('ae', trans('navigation/projectnav.additionalExpenses') . ' (AE)', array($ae->project->id)) }}</li>
		<li>{{ link_to_route('ae.show', "View Current AE ({$ae->subject})", array($ae->project->id, $ae->id)) }}</li>
		<li>Messaging Form</li>
	</ol>

	@include('projects.partials.project_status', array('project' => $ae->project))
@endsection

@section('content')
	<div class="row">
		<!-- NEW COL START -->
		<article class="col-sm-12 col-md-12 col-lg-8">
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget">
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>{{{ PCK\Forms\AEContractorConfirmDelayForm::formTitle }}}</h2>
				</header>

				<!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body no-padding">
						{{ Form::open(array('class' => 'smart-form')) }}
							<fieldset>
								<section>
									<label class="label">Notice of Intention To Claim Additional Expense's Reference:</label>
									{{ link_to_route('ae.show', $ae->subject, array($ae->project_id, $ae->id)) }}
								</section>

								<section>
									<label class="label">Date of Submission:</label>
									<span class="dateSubmitted">{{{ $ae->project->getProjectTimeZoneTime($ae->created_at) }}}</span>
								</section>

								<section>
									<label class="label">Subject/Reference<span class="required">*</span>:</label>
									<label class="input {{{ $errors->has('subject') ? 'state-error' : null }}}">
										{{ Form::text('subject', Input::old('subject'), array('required' => 'required')) }}
									</label>
									{{ $errors->first('subject', '<em class="invalid">:message</em>') }}
								</section>

								<section>
									<label class="label">Date on which the matters referred to in the claim have ended<span class="required">*</span>:</label>
									<label class="input {{{ $errors->has('date_on_which_delay_is_over') ? 'state-error' : null }}}">
										{{ Form::text('date_on_which_delay_is_over', Input::old('date_on_which_delay_is_over'), array('class' => 'finishdate')) }}
									</label>
									{{ $errors->first('date_on_which_delay_is_over', '<em class="invalid">:message</em>') }}
								</section>

								<section>
									<label class="label">Deadline to Submit the final Additional Expense Claim ({{{ $ae->project->pam2006Detail->deadline_submitting_final_claim_ae }}} days from the end of the cause of delay):</label>
									<span id="new_deadline">
										@if ( Input::old('date_on_which_delay_is_over') and strtotime(Input::old('date_on_which_delay_is_over')) )
											{{{ ($calendarRepo->calculateFinalDate($ae->project, Input::old('date_on_which_delay_is_over'), $ae->project->pam2006Detail->deadline_submitting_final_claim_ae)) }}}
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

									@include('file_uploads.partials.upload_file_modal', ['project' => $ae->project])
								</section>
							</fieldset>

							<footer>
								{{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}

								{{ link_to_route('ae.show', 'Cancel', [$ae->project->id, $ae->id], ['class' => 'btn btn-default']) }}
							</footer>

							{{ Form::hidden('deadline_days', $ae->project->pam2006Detail->deadline_submitting_final_claim_ae, ['id' => 'deadline_days']) }}
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