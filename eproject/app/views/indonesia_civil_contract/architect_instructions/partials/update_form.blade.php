<div class="widget-body no-padding">
	{{ Form::model($ai, array('class' => 'smart-form', 'method' => 'PUT')) }}
		@include('indonesia_civil_contract.architect_instructions.partials.ai_form', array('project' => $project))

		@if(\PCK\Verifier\Verifier::isRejected($ai))
			@include('verifiers.verifier_status_overview')
		@endif

		<footer>
			{{ Form::submit(trans('architectInstructions.issue'), array('class' => 'btn btn-primary', 'name' => 'issue_ai')) }}

			{{ Form::submit(trans('forms.save'), array('class' => 'btn btn-primary', 'name' => 'edit')) }}

			{{ link_to_route('indonesiaCivilContract.architectInstructions.delete', trans('forms.delete'), [$ai->project->id, $ai->id], ['class' => 'btn btn-danger', 'data-method'=>'delete', 'data-csrf_token' => csrf_token()]) }}

			{{ link_to_route('indonesiaCivilContract.architectInstructions', trans('forms.cancel'), [$ai->project->id], ['class' => 'btn btn-default']) }}
		</footer>
	{{ Form::close() }}
</div>

@section('js')
	<script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
@endsection