<div class="widget-body no-padding">
	{{ Form::open(array('route' => array('ei.architect_update', $ei->project_id, $ei->id), 'class' => 'smart-form', 'method' => 'PUT')) }}
		<fieldset>
			<section>
				<strong>Project Title:</strong><br>
				{{{ $ei->project->title }}}
			</section>

			<section>
				<strong>Issued On:</strong><br>
				<label class="input">
					<strong class="dateSubmitted"><i>{{{ $ei->project->getProjectTimeZoneTime($ei->created_at) }}}</i></strong> by {{{ $ei->createdBy->present()->byWhoAndRole($ei->project, $ei->created_at) }}}
				</label>
			</section>

			<section>
				<strong>Subject/EI's Reference:</strong><br>
				{{{ $ei->subject }}}
			</section>

			<section>
				<strong>Details/Cover Letter:</strong><br>
				{{{ $ei->detailed_elaborations }}}
			</section>

			<section>
				<strong>Deadline to Comply With:</strong><br>
				{{{ $ei->project->getProjectTimeZoneTime($ei->deadline_to_comply_with) }}}
			</section>

			@if ( ! $ei->attachments->isEmpty() )
				<section>
					<strong>Attachment(s):</strong><br>

					@include('file_uploads.partials.uploaded_file_show_only', ['files' => $ei->attachments, 'projectId' => $ei->project_id])
				</section>
			@endif

			<section>
				<strong>Architect to confirm that EI has been confirmed through the following AI:</strong>
				{{ $errors->first('ais', '<em style="color:red;" class="invalid">Please select an AI before proceeding.</em>') }}

				@if ( $ei->status == PCK\EngineerInstructions\EngineerInstruction::NOT_YET_CONFIRMED_TEXT and $isEditor and $user->hasCompanyProjectRole($ei->project, \PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER) )
					<div style="height: 380px; overflow-y: scroll;">
						@foreach ( $ais as $ai )
							<label class="checkbox">
								{{ Form::checkbox('ais[]', $ai->id, in_array($ai->id, $selectedAIIds) ? true : false) }}
								<i></i> {{{ $ai->reference }}} ({{{ $ai->project->getProjectTimeZoneTime($ai->created_at) }}})
							</label>
						@endforeach
					</div>
				@else
					<ol style="margin: 0 0 0 20px;">
						@foreach ( $ei->architectInstructions as $ai )
							<li>{{ link_to_route('ai.show', "{$ai->reference} ({$ei->project->getProjectTimeZoneTime($ai->created_at)})", array($ei->project_id, $ai->id)) }}</li>
						@endforeach
					</ol>
				@endif
			</section>
		</fieldset>

		@if ( $ei->status == PCK\EngineerInstructions\EngineerInstruction::NOT_YET_CONFIRMED_TEXT and $isEditor and $user->hasCompanyProjectRole($ei->project, \PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER) )
			<footer>
				{{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}

				{{ link_to_route('ei', 'Cancel', [$ei->project->id], ['class' => 'btn btn-default']) }}
			</footer>
		@endif
	{{ Form::close() }}
</div>