<div class="widget-body no-padding">
	{{ Form::model($eot, array('class' => 'smart-form', 'method' => 'PUT')) }}
		@include('indonesia_civil_contract.extension_of_time.partials.eot_form', array('project' => $project))

		<footer>
			{{ Form::submit(trans('extensionOfTime.issue'), array('class' => 'btn btn-primary', 'name' => 'issue')) }}

			{{ Form::submit(trans('forms.save'), array('class' => 'btn btn-primary', 'name' => 'edit')) }}

			{{ link_to_route('indonesiaCivilContract.extensionOfTime.delete', trans('forms.delete'), [$eot->project->id, $eot->id], ['class' => 'btn btn-danger', 'data-method'=>'delete', 'data-csrf_token' => csrf_token()]) }}

			{{ link_to_route('indonesiaCivilContract.extensionOfTime', trans('forms.cancel'), [$eot->project->id], ['class' => 'btn btn-default']) }}
		</footer>
	{{ Form::close() }}
</div>

@section('js')
	<script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
@endsection