@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>
			{{ link_to_route('countries', trans('countries.countries'), array()) }}
		</li>
		<li>{{ trans('countries.addNewCountry') }}</li>
	</ol>
@endsection

@section('content')
	
<div class="row">
	<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		<h1 class="page-title txt-color-blueDark">
			<i class="fa fa-edit"></i> {{ trans('countries.addNewCountry') }}
		</h1>
	</div>
</div>

<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<div class="jarviswidget">
			<header>
				<h2>{{ trans('countries.addNewCountry') }}</h2>
			</header>
			<div>
				<div class="widget-body no-padding">
					{{ Form::open(array('class' => 'smart-form', 'id' => 'add-form')) }}
						@include('countries.partials.countryForm')

						<footer>
							{{ link_to_route('countries', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
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