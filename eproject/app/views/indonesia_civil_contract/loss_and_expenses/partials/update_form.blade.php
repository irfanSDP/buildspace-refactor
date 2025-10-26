<div class="widget-body no-padding">
	{{ Form::model($le, array('class' => 'smart-form', 'method' => 'PUT')) }}
		@include('indonesia_civil_contract.loss_and_expenses.partials.le_form', array('project' => $project))

		<footer>
			{{ Form::submit(trans('lossAndExpenses.issue'), array('class' => 'btn btn-primary', 'name' => 'issue')) }}

			{{ Form::submit(trans('forms.save'), array('class' => 'btn btn-primary', 'name' => 'edit')) }}

			{{ link_to_route('indonesiaCivilContract.lossAndExpenses.delete', trans('forms.delete'), [$le->project->id, $le->id], ['class' => 'btn btn-danger', 'data-method'=>'delete', 'data-csrf_token' => csrf_token()]) }}

			{{ link_to_route('indonesiaCivilContract.lossOrAndExpenses', trans('forms.cancel'), [$le->project->id], ['class' => 'btn btn-default']) }}
		</footer>
	{{ Form::close() }}
</div>

@section('js')
	<script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
@endsection