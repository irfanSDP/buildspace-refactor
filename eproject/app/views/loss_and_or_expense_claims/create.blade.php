@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($loe->project->title, 50), array($loe->project->id)) }}</li>
		<li>{{ link_to_route('loe', trans('navigation/projectnav.lossOrAndExpenses') . ' (L &amp; E)', array($loe->project->id)) }}</li>
		<li>{{ link_to_route('loe.show', "View Current L &amp; E ({$loe->subject})", array($loe->project->id, $loe->id)) }}</li>
		<li>Messaging Form</li>
	</ol>

    @include('projects.partials.project_status', array('project' => $loe->project))
@endsection

@section('content')

	<div class="row">
		<!-- NEW COL START -->
		<article class="col-sm-12 col-md-12 col-lg-8">
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget">
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>{{{ PCK\Forms\LOEClaimForm::formTitle }}}</h2>
				</header>

				<!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body no-padding">
						{{ Form::open(array('class' => 'smart-form')) }}
							<fieldset>
								<section>
									<label class="label">Project:</label>
									{{{ $loe->project->title }}}
								</section>
								<section>
									<label class="label">Reference to AI (if relevant):</label>
									@if ( $loe->architectInstruction )
										{{ link_to_route('ai.show', $loe->architectInstruction->reference, array($loe->project->id, $loe->architectInstruction->id)) }}
									@else
										Not related to any AI
									@endif
								</section>

								<section>
									<label class="label">Relevant Notice of Intention to Claim Loss And/Or Expense's Reference:</label>
									{{ link_to_route('loe.show', $loe->subject, array($loe->project_id, $loe->id)) }}
								</section>

								<section>
									<label class="label">Deadline to Submit the final Loss And/Or Expense Claim:</label>
									{{{ $loe->project->getProjectTimeZoneTime($loe->contractorConfirmDelay->deadline_to_submit_final_claim) }}}
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
									<label class="label">Final Claim Amount ({{{ $loe->project->modified_currency_code }}})<span class="required">*</span>:</label>
									<label class="input {{{ $errors->has('final_claim_amount') ? 'state-error' : null }}}">
										{{ Form::text('final_claim_amount', Input::old('final_claim_amount'), array('required' => 'required')) }}
									</label>
									{{ $errors->first('final_claim_amount', '<em class="invalid">:message</em>') }}
								</section>

								<section>
									<label class="label">Attachment(s):</label>

									@include('file_uploads.partials.upload_file_modal', ['project' => $loe->project])
								</section>
							</fieldset>

							<footer>
								{{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}

								{{ link_to_route('loe.show', 'Cancel', [$loe->project->id, $loe->id], ['class' => 'btn btn-default']) }}
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