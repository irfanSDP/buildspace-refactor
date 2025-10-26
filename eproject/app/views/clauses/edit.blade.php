@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('contracts', trans('contracts.contracts'), array()) }}</li>
		<li>{{{ $contract->name }}}</li>
		<li>
			{{ link_to_route('clauses', trans('clauses.clauses'), array($contract->id)) }}
		</li>
		<li>{{ trans("clauses.$clause->name") }}</li>
	</ol>
@endsection

@section('content')

<div class="row">
	<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		<h1 class="page-title txt-color-blueDark">
			<i class="fa fa-edit"></i> {{ trans('clauses.editClause') }}
		</h1>
	</div>
</div>

<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<div class="jarviswidget">
			<header>
				<h2>{{ trans('clauses.editClause') }}</h2>
			</header>
			<div>
				<div class="widget-body no-padding">
					{{ Form::model($clause, array('route' => array('clauses.update', $contract->id, $clause->id), 'class' => 'smart-form', 'id'=> 'add-form', 'method' => 'put')) }}
						@include('clauses.partials.clauseForm')

						<footer>
							{{ link_to_route('clauses', trans('forms.back'), array($contract->id), array('class' => 'btn btn-default')) }}
							{{ Form::submit(trans('forms.update'), array('class' => 'btn btn-primary')) }}
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
	<script>
		$(document).ready(function() {
			$('#add-form').validate({
				errorPlacement : function(error, element) {
					error.insertAfter(element.parent());
				}
			});
		});
	</script>
@endsection