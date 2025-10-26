<div class="widget-body no-padding">
	<div class="smart-form">
		<fieldset>
			<section>
				<strong>Project Title:</strong><br>
				{{{ $ic->project->title }}}
			</section>

			<section>
				<strong>Date Notice Submitted:</strong><br>
				<strong class="dateSubmitted"><i>{{{ $ic->project->getProjectTimeZoneTime($ic->created_at) }}}</i></strong> by {{{ $ic->createdBy->present()->byWhoAndRole($ic->project, $ic->created_at) }}}
			</section>

			<section>
				<strong>Interim Claim No:</strong><br>
				{{{ $ic->claim_no }}}
			</section>

			<section>
				<strong>Month/Year:</strong><br>
				{{{ date("F", mktime(0, 0, 0, $ic->month, 10)) }}}/{{{ $ic->year }}}
			</section>

			<section>
				<strong>Deadline to Issue Interim Certificate ({{{ $ic->project->pam2006Detail->period_of_architect_issue_interim_certificate }}} days from submitted date):</strong><br>
				{{{ $ic->project->getProjectTimeZoneTime($ic->issue_certificate_deadline) }}}
			</section>

			<section>
				<strong>Cover Letter:</strong><br>
				{{{ $ic->note }}}
			</section>

			@if ( ! $ic->attachments->isEmpty() )
				<section>
					<strong>Attachment(s):</strong><br>

					@include('file_uploads.partials.uploaded_file_show_only', ['files' => $ic->attachments, 'projectId' => $ic->project_id])
				</section>
			@endif
		</fieldset>
	</div>
</div>