<div class="widget-body no-padding">
	{{ Form::model($eot, array('class' => 'smart-form', 'method' => 'PUT')) }}
		@include('extension_of_times.partials.eot_form', array(
			'project' => $eot->project,
			'ai' => ($eot->status == PCK\ExtensionOfTimes\ExtensionOfTime::DRAFT_TEXT) ? $ai : $eot->architectInstruction
		))

		<footer>
			@if ( $isEditor )
				{{ Form::submit('Submit', array('class' => 'btn btn-primary', 'name' => 'issue_eot')) }}
			@endif

			{{ Form::submit('Save', array('class' => 'btn btn-primary', 'name' => 'edit')) }}

			{{ link_to_route('eot.delete', 'Delete', [$eot->project->id, $eot->id], ['class' => 'btn btn-danger', 'data-method'=>'delete', 'data-csrf_token' => csrf_token()]) }}

			{{ link_to_route('eot', 'Cancel', [$eot->project->id], ['class' => 'btn btn-default']) }}
		</footer>
	{{ Form::close() }}
</div>

@section('js')
	<script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
@endsection