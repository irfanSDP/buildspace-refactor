<div class="widget-body no-padding">
	{{ Form::model($loe, array('class' => 'smart-form', 'method' => 'PUT')) }}
		@include('loss_and_or_expenses.partials.loe_form', array(
			'project' => $loe->project,
			'ai' => ($loe->status == PCK\LossOrAndExpenses\LossOrAndExpense::DRAFT_TEXT) ? $ai : $loe->architectInstruction
		))

		<footer>
			@if ( $isEditor )
				{{ Form::submit('Submit', array('class' => 'btn btn-primary', 'name' => 'issue_loe')) }}
			@endif

			{{ Form::submit('Save', array('class' => 'btn btn-primary', 'name' => 'edit')) }}

			{{ link_to_route('loe.delete', 'Delete', [$loe->project->id, $loe->id], ['class' => 'btn btn-danger', 'data-method'=>'delete', 'data-csrf_token' => csrf_token()]) }}

			{{ link_to_route('loe', 'Cancel', [$loe->project->id], ['class' => 'btn btn-default']) }}
		</footer>
	{{ Form::close() }}
</div>

@section('js')
	<script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
@endsection