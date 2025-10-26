@extends('layout.main')

@section('content')
	<article class="col-sm-12 col-md-12 col-lg-6 col-md-offset-3" style="padding-top: 10px;">
		@include('layout.partials.flash_message')

		<div class="jarviswidget jarviswidget-sortable">
			<header role="heading">
				<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
				<h2>{{ trans('clauses.addNewClause') }}</h2>
			</header>

			<!-- widget div-->
			<div role="content">
				<!-- widget content -->
				<div class="widget-body no-padding">
					{{ Form::open(array('route' => array('clauses.store', $contract->id), 'class' => 'smart-form', 'id' => 'add-form')) }}
						@include('clauses.partials.clauseForm')

						<footer>
							{{ link_to_route('clauses.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
							<button type="submit" class="btn btn-primary"><i class="fa fa-save" aria-hidden="true"></i> {{ trans('forms.save') }}</button>
						</footer>
					{{ Form::close() }}
				</div>
				<!-- end widget content -->
			</div>
			<!-- end widget div -->
		</div>
	</article>
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