<div class="widget-body no-padding">
	{{ Form::model($wr, array('class' => 'smart-form', 'method' => 'PUT')) }}
		@include('weather_records.partials.wr_form', array('weatherRecord' => $wr, 'project' => $wr->project))

		<footer>
			@if ( $isEditor )
				@if ( $user->hasCompanyProjectRole($wr->project, \PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER) )
					{{ Form::submit('Submit', array('class' => 'btn btn-primary', 'name' => 'verify')) }}
				@else
					{{ Form::submit('Submit for Verify', array('class' => 'btn btn-primary', 'name' => 'issue_wr')) }}
				@endif
			@endif

			{{ Form::submit('Save', array('class' => 'btn btn-primary', 'name' => 'edit')) }}

			{{ link_to_route('wr.delete', 'Delete', [$wr->project->id, $wr->id], ['class' => 'btn btn-danger', 'data-method'=>'delete', 'data-csrf_token' => csrf_token()]) }}

			{{ link_to_route('wr', 'Cancel', [$wr->project->id], ['class' => 'btn btn-default']) }}
		</footer>
	{{ Form::close() }}
</div>

@section('js')
	<script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
@endsection