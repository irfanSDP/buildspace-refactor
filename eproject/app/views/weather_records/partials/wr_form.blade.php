<fieldset>
	<section>
		<label class="label">Project Title:</label>
		<label class="input">
			{{{ $project->title }}}
		</label>
	</section>

	<section>
		<label class="label">Weather<span class="required">*</span> [{{ link_to_route('wrReport.create', 'Add Record', array($project->id, $weatherRecord->id ?: 0, $wrReportMode)) }}]:</label>

		@if ( $weatherRecord )
			<table style="width: 100%;">
				@foreach ( $weatherRecord->weatherRecordReports as $weatherRecordReport )
					<tr style="border-bottom: 1px solid #a9a9a9;">
						<td style="padding: 5px;">
							{{{ $weatherRecordReport->from_time }}} - {{{ $weatherRecordReport->to_time }}}
						</td>
						<td style="padding: 5px;">
							{{{ $weatherRecordReport->weather_status }}}
						</td>
						<td style="padding: 5px;">
							{{ link_to_route('wrReport.delete', 'Delete', [$project->id, $weatherRecord->id, $weatherRecordReport->id, $wrReportMode], ['data-method'=>'delete', 'data-csrf_token' => csrf_token()]) }}
						</td>
					</tr>
				@endforeach
			</table>
		@endif
	</section>

	<section>
		<label class="label">Date<span class="required">*</span>:</label>
		<label class="input {{{ $errors->has('date') ? 'state-error' : null }}}">
			{{ Form::text('date', Input::old('date'), array('class' => 'finishdate')) }}
		</label>
		{{ $errors->first('date', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">Note<span class="required">*</span>:</label>
		<label class="textarea {{{ $errors->has('note') ? 'state-error' : null }}}">
			{{ Form::textarea('note', Input::old('note'), array('required' => 'required', 'rows' => 3)) }}
		</label>
		{{ $errors->first('note', '<em class="invalid">:message</em>') }}
	</section>

	<section>
		<label class="label">Attachment(s):</label>

		@include('file_uploads.partials.upload_file_modal', ['project' => $project])
	</section>
</fieldset>

@section('js')
	<script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
@endsection