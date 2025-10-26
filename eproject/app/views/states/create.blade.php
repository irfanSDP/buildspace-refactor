@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>
			{{ link_to_route('countries', trans('countries.countries'), array()) }}
		</li>
		<li>{{{ $country->country }}}</li>
		<li>{{ link_to_route('states', trans('states.states'), array($country->id)) }}</li>
		<li>{{ trans('states.addNewState') }}</li>
	</ol>
@endsection

@section('content')

<div class="row">
	<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		<h1 class="page-title txt-color-blueDark">
			<i class="fa fa-edit"></i> {{ trans('states.addNewState') }}
		</h1>
	</div>
</div>

<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<div class="jarviswidget">
			<header>
				<h2>{{ trans('states.addNewState') }}</h2>
			</header>
			<div>
				<div class="widget-body no-padding">
					{{ Form::open(array('class' => 'smart-form', 'id' => 'add-form')) }}
						@include('states.partials.stateForm')

						<footer>
							<a href="{{ route('states', array($country->id)) }}" class="btn btn-default">{{ trans('forms.back') }}</a>
							<button type="submit" class="btn btn-primary"><i class="fa fa-save" aria-hidden="true"></i> {{ trans('forms.save') }}</button>
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