<div class="widget-body no-padding">
	{{ Form::model($ae, array('class' => 'smart-form', 'method' => 'PUT')) }}
		@include('additional_expenses.partials.ae_form', array(
			'project' => $ae->project,
			'ai' => ($ae->status == PCK\AdditionalExpenses\AdditionalExpense::DRAFT_TEXT) ? $ai : $ae->architectInstruction
		))

		<footer>
			@if ( $isEditor )
				{{ Form::submit('Submit', array('class' => 'btn btn-primary', 'name' => 'issue_ae')) }}
			@endif

			{{ Form::submit('Save', array('class' => 'btn btn-primary', 'name' => 'edit')) }}

			{{ link_to_route('ae.delete', 'Delete', [$ae->project->id, $ae->id], ['class' => 'btn btn-danger', 'data-method'=>'delete', 'data-csrf_token' => csrf_token()]) }}

			{{ link_to_route('ae', 'Cancel', [$ae->project->id], ['class' => 'btn btn-default']) }}
		</footer>
	{{ Form::close() }}
</div>

@section('js')
	<script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
@endsection