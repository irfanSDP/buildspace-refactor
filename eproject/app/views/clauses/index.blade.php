@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('contracts', trans('contracts.contracts'), array()) }}</li>
		<li>{{{ $contract->name }}}</li>
		<li>{{ trans('clauses.clauses') }}</li>
	</ol>
@endsection

@section('content')

<div class="row">
	<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		<h1 class="page-title txt-color-blueDark">
			<i class="fa fa-list"></i> {{ trans('clauses.clauses') }}
		</h1>
	</div>
</div>

<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<div class="jarviswidget ">
			<header>
				<h2> {{ trans('clauses.clauses') }} </h2>
			</header>
			<div>
				<div class="widget-body no-padding">
					<div class="table-responsive">
						<table class="table">
							<thead>
								<tr>
									<th style="width: 5%;">{{ trans('general.no') }}</th>
									<th style="width: auto;">{{ trans('clauses.clauseName') }}</th>
								</tr>
							</thead>
							<tbody>
								<?php $counter = 1; ?>
								@foreach ($clauses as $clause)
								<tr>
									<td class="text-middle text-center">
										<?php echo $counter++; ?>
									</td>
									<td class="text-middle">
										<div class="row">
											<div class="col-md-10 col-lg-10 col-xs-12">
												<h5 class="title">
													{{ trans("clauses.$clause->name") }}
												</h5>
												<p>
													<span class="label label-info">
														{{{ $clause->contract->name }}}
													</span>
												</p>
											</div>
											<div class="col-md-2 col-lg-2 col-xs-12 action-overlap">
												<span class="label label-success pull-right">
													{{{ date('d M Y', strtotime($clause->created_at))}}}
												</span>
												<div class="action">
													<div class="btn-group btn-group-xs">
														<a href="{{route('clauses.items.index', array($contract->id, $clause->id))}}" rel="tooltip" title="Clause Items" class="btn btn-default">
															<i class="fa fa-list"></i>
														</a>
														<a href="{{route('clauses.edit', array($contract->id, $clause->id))}}" rel="tooltip" title="Edit Clause" class="btn btn-default">
															<i class="fa fa-edit"></i>
														</a>
													</div>
												</div>
											</div>
										</div>
									</td>
								</tr>
								@endforeach
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