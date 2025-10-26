@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('scheduled_maintenance.index', trans('scheduledMaintenance.scheduled_maintenances')) }}</li>
        <li>{{{trans('scheduledMaintenance.add_scheduled_maintenance')}}}</li>
    </ol>

@endsection

@section('content')

<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<div class="jarviswidget">
			<header>
				<h2>{{ trans('scheduledMaintenance.add_scheduled_maintenance') }}</h2>
			</header>
			<div>
				<div class="widget-body no-padding">
                    {{ Form::open(['route' => 'scheduled_maintenance.store', 'class' => 'smart-form', 'id' => 'add-form', 'files' => true]) }}
						@include('scheduled_maintenance.partials.scheduledMaintenanceForm')
						<footer>
							{{ link_to_route('scheduled_maintenance.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
							{{ Form::submit(trans('forms.add'), array('class' => 'btn btn-primary')) }}
						</footer>
					{{ Form::close() }}
				</div>
			</div>
		</div>
	</div>
</div>

@endsection
@section('js')
	<script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
@endsection

@section('inline-js')
	$(document).ready(function() {
		$('#add-form').validate({
            errorPlacement : function(error, element) {
                error.insertAfter(element.parent());
            }
        });
	});
@endsection