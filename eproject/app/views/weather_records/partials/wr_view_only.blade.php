<div class="widget-body no-padding">
	{{ Form::open(array('route' => array('wr.architect_update', $wr->project_id, $wr->id), 'class' => 'smart-form', 'method' => 'PUT')) }}
		<fieldset>
			<section>
				<strong>Project Title:</strong><br>
				{{{ $wr->project->title }}}
			</section>

			<section>
				<strong>Date:</strong><br>
				{{{ $wr->project->getProjectTimeZoneTime($wr->date) }}}
			</section>

			@if ( $wr and ! $wr->weatherRecordReports->isEmpty() )
				<section>
					<strong>Weather:</strong><br>
					<table style="width: 100%;">
						@foreach ( $wr->weatherRecordReports as $wrReport )
							<tr style="border-bottom: 1px solid #a9a9a9;">
								<td style="padding: 5px;">
									{{{ $wrReport->from_time }}} - {{{ $wrReport->to_time }}}
								</td>
								<td style="padding: 5px;">
									{{{ $wrReport->weather_status }}}
								</td>
							</tr>
						@endforeach
					</table>
				</section>
			@endif

			<section>
				<strong>Note:</strong><br>
				{{{ $wr->note }}}
			</section>

			@if ( ! $wr->attachments->isEmpty() )
				<section>
					<strong>Attachment(s):</strong><br>

					@include('file_uploads.partials.uploaded_file_show_only', ['files' => $wr->attachments, 'projectId' => $wr->project_id])
				</section>
			@endif

			<section>
				<strong>Recorded On:</strong><br>
				<strong class="dateSubmitted"><i>{{{ $wr->project->getProjectTimeZoneTime($wr->created_at) }}}</i></strong> by {{{ $wr->createdBy->present()->byWhoAndRole($wr->project, $wr->created_at) }}}
			</section>

			@if ( $wr->status == PCK\WeatherRecords\WeatherRecord::VERIFIED_TEXT )
				<section>
					<strong>Verified By:</strong><br>
					<strong><i>{{{ $wr->project->getProjectTimeZoneTime($wr->updated_at) }}}</i></strong> by {{{ $wr->verifiedBy->present()->byWhoAndRole($wr->project, $wr->created_at) }}}
				</section>
			@endif
		</fieldset>

		@if ( $wr->status == PCK\WeatherRecords\WeatherRecord::NOT_YET_VERIFY_TEXT and $isEditor and $user->hasCompanyProjectRole($wr->project, \PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER) )
			<footer>
				{{ Form::submit('Verify', array('class' => 'btn btn-primary')) }}

				{{ link_to_route('wr', 'Cancel', [$wr->project->id], ['class' => 'btn btn-default']) }}
			</footer>
		@endif
	{{ Form::close() }}
</div>