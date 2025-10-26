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
		<li>{{ trans('clauses.items') }}</li>
	</ol>
@endsection

@section('content')

<div class="row">
	<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		<h1 class="page-title txt-color-blueDark">
			{{{ trans("clauses.$clause->name") }}}
		</h1>
	</div>
	
	<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
		{{ link_to_route('clauses.items.create', trans('clauses.addNewItem'), array($contract->id, $clause->id), array('class' => 'btn btn-info btn-md pull-right header-btn hidden-mobile')) }}
	</div>
</div>

<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<div class="jarviswidget ">
			<header>
				<h2> {{ trans('clauses.items') }} </h2>
			</header>
			<div>
				<div class="widget-body no-padding">
					<div class="table-responsive">
						<table class="table " id="dt_basic">
							<thead>
								<tr>
									<th style="width: 5%;">{{ trans('general.no') }}</th>
									<th style="text-align: center;width:8%;">{{ trans('clauses.priority') }}</th>
									<th style="text-align: center;width:8%;">{{ trans('clauses.no') }}</th>
									<th style="width:auto;">{{ trans('clauses.description') }}</th>
									<th style="text-align: center;width:10%;">{{ trans('general.actions') }}</th>
								</tr>
							</thead>
							<tbody>

								@if ( $clause->items->count() > 0 )
									<?php $counter = 1; ?>
									@foreach ( $clause->items as $item )
									<tr>
										<td class="text-middle text-center">
											<?php echo $counter++; ?>
										</td>
										<td class="text-center text-middle">
											<div class="btn-group">
												@if ($item->priority > 0 )
													<a href="{{ route('clauses.items.up', array($contract->id, $clause->id, $item->id)) }}" class="btn btn-default btn-xs">
														<i class="glyphicon glyphicon-chevron-up"></i>
													</a>
												@endif
												@if (($item->priority + 1) < $clause->items->count() )
													<a href="{{ route('clauses.items.down', array($contract->id, $clause->id, $item->id)) }}" class="btn btn-default btn-xs">
														<i class="glyphicon glyphicon-chevron-down"></i>
													</a>
												@endif
											</div>
										</td>
										<td class="text-center">{{{ $item->no }}}</td>
										<td>{{{ $item->description }}}</td>
										<td class="text-center">
											{{ link_to_route('clauses.items.edit', trans('forms.edit'), array($contract->id, $clause->id, $item->id), array('class'=> 'btn btn-default btn-xs btn-default') ) }}
										</td>
									</tr>
									@endforeach
								@else
									<tr>
										<td colspan="8" style="text-align: center; padding: 30px 0;">{{ trans('clauses.noClauseItems') }}</td>
									</tr>
								@endif
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection

@section('js')
    <script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
	<script>
		$(document).ready(function() {
			$('#dt_basic').dataTable({
				"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
				"t"+
				"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
				"autoWidth" : true
			});
		});
	</script>
@endsection