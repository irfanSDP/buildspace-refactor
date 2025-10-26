<div class="widget-body no-padding">
	{{ Form::model($ei, array('class' => 'smart-form', 'method' => 'PUT')) }}
		@include('engineer_instructions.partials.ei_form', array('project' => $ei->project))

		<footer>
			@if ( $isEditor )
				{{ Form::submit('Issue EI', array('class' => 'btn btn-primary', 'name' => 'issue_ei')) }}
			@endif

			{{ Form::submit('Save', array('class' => 'btn btn-primary', 'name' => 'edit')) }}

			{{ link_to_route('ei.delete', 'Delete', [$ei->project->id, $ei->id], ['class' => 'btn btn-danger', 'data-method'=>'delete', 'data-csrf_token' => csrf_token()]) }}

			{{ link_to_route('ei', 'Cancel', [$ei->project->id], ['class' => 'btn btn-default']) }}
		</footer>
	{{ Form::close() }}
</div>

@section('js')
	<script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
@endsection