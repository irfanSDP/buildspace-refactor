@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('contracts', trans('contracts.contracts'), array()) }}</li>
		<li>{{{ $contract->name }}}</li>
		<li>
			{{ link_to_route('clauses', trans('clauses.clauses'), array($contract->id)) }}
		</li>
		<li>{{{ $clause->name }}}</li>
		<li>{{ link_to_route('clauses.items.index', trans('clauses.items'), array($contract->id, $clause->id)) }}</li>
		<li>{{ trans('clauses.addNewItem') }}</li>
	</ol>
@endsection

@section('content')

<div class="row">
	<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		<h1 class="page-title txt-color-blueDark">
			<i class="fa fa-edit"></i> {{ trans('clauses.addNewItem') }}
		</h1>
	</div>
</div>

<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<div class="jarviswidget">
			<header>
				<h2>{{ trans('clauses.addNewItem') }}</h2>
			</header>
			<div>
				<div class="widget-body no-padding">
					{{ Form::open(array('route' => array('clauses.items.store', $contract->id, $clause->id), 'class' => 'smart-form', 'id' => 'add-form')) }}
						@include('clause_items.partials.clauseItemForm')

						<footer>
							<a href="{{ route('clauses.items.index', array($contract->id, $clause->id)) }}" class="btn btn-default">{{ trans('forms.back') }}</a>
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