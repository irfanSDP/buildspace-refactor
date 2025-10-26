@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($ai->project->title, 50), array($ai->project->id)) }}</li>
		<li>{{ link_to_route('ai', trans('navigation/projectnav.architectInstruction') . ' (AI)', array($ai->project->id)) }}</li>
		<li>{{ link_to_route('ai.show', "View Current AI ({$ai->reference})", array($ai->project->id, $ai->id)) }}</li>
		<li>Messaging Form</li>
	</ol>

    @include('projects.partials.project_status', array('project' => $ai->project))
@endsection

@section('content')
	<div class="row">
		<!-- NEW COL START -->
		<article class="col-sm-12 col-md-12 col-lg-8">
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget">
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>{{{ PCK\Forms\ArchitectInstructionInterimClaimForm::formTitle }}}</h2>
				</header>

				<!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body no-padding">
						{{ Form::open(array('class' => 'smart-form')) }}
							<fieldset>
								<section>
									<label class="label">Project:</label>
									{{{ $ai->project->title }}}
								</section>

								<section>
									<label class="label">AI's Reference:</label>
									{{ link_to_route('ai.show', $ai->reference, array($ai->project_id, $ai->id)) }}
								</section>

								<section>
									<label class="label">Date AI Issued:</label>
									{{{ $ai->project->getProjectTimeZoneTime($ai->created_at) }}}
								</section>

								<section>
									<label class="label">Subject/Reference<span class="required">*</span>:</label>
									<label class="input {{{ $errors->has('subject') ? 'state-error' : null }}}">
										{{ Form::text('subject', Input::old('subject'), array('required' => 'required')) }}
									</label>
									{{ $errors->first('subject', '<em class="invalid">:message</em>') }}
								</section>

								<section>
									<label class="label">Letter to the Contractor<span class="required">*</span>:</label>
									<label class="textarea {{{ $errors->has('letter_to_contractor') ? 'state-error' : null }}}">
										{{ Form::textarea('letter_to_contractor', Input::old('letter_to_contractor'), array('required' => 'required', 'rows' => 3)) }}
									</label>
									{{ $errors->first('letter_to_contractor', '<em class="invalid">:message</em>') }}
								</section>

								<section>
									<label class="label">Set-off is made in the following Interim Certificate<span class="required">*</span>:</label>
									<label class="{{{ $errors->has('interim_claim_id') ? 'state-error' : null }}}">
										{{ Form::select('interim_claim_id', $ics) }}
									</label>
									{{ $errors->first('interim_claim_id', '<em class="invalid">:message</em>') }}
								</section>

								<section>
									<label class="label">Attachment(s):</label>

									@include('file_uploads.partials.upload_file_modal', ['project' => $ai->project])
								</section>
							</fieldset>

							<footer>
								{{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}

								{{ link_to_route('ai.show', 'Cancel', [$ai->project->id, $ai->id], ['class' => 'btn btn-default']) }}
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