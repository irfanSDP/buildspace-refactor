<div class="widget-body no-padding">
	{{ Form::model($ai, array('class' => 'smart-form', 'method' => 'PUT')) }}
		@include('architect_instructions.partials.ai_form', array('project' => $ai->project))

		<footer>
			@if ( $isEditor )
				{{ Form::submit('Issue AI', array('class' => 'btn btn-primary', 'name' => 'issue_ai')) }}
			@endif

			{{ Form::submit('Save', array('class' => 'btn btn-primary', 'name' => 'edit')) }}

			{{ link_to_route('ai.delete', 'Delete', [$ai->project->id, $ai->id], ['class' => 'btn btn-danger', 'data-method'=>'delete', 'data-csrf_token' => csrf_token()]) }}

			{{ link_to_route('ai', 'Cancel', [$ai->project->id], ['class' => 'btn btn-default']) }}
		</footer>
	{{ Form::close() }}
</div>

@section('js')
	<script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
@endsection