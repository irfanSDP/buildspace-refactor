@extends('layout.main')

@section('css')
    <style>
		/*custom styling since table fully occupied container*/
        .tabulator .tabulator-tableHolder {
			border: none;
		}

        .button-row {
            display: flex;
            gap: 5px;
            justify-content: flex-end;
        }
    </style>
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ trans('orders.pageTitle') }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-7 col-md-7 col-lg-7">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ trans('orders.pageTitle') }}
			</h1>
		</div>
	</div>

	<div class="jarviswidget ">
		<header>
			<h2> {{ trans('orders.pageTitle') }} </h2>
		</header>
		<div>
			<div class="widget-body no-padding">
				<div id="data-table"></div>
			</div>
		</div>
	</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        let dataTable = null;

        dataTable = new Tabulator('#data-table', {
            fillHeight: true,
            pagination: "local",
            paginationSize: 30,
            columns: [
                { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                { title:"{{ trans('orders.date') }}", field: 'date', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('orders.referenceId') }}", field: 'referenceId', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('orders.type') }}", field: 'type', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('orders.project') }}", field: 'project', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('orders.projectReference') }}", field: 'projectReference', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('orders.buyer') }}", field: 'buyer', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('orders.seller') }}", field: 'seller', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('orders.amount') }}", field: 'total', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('orders.status') }}", field: 'status', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
            ],
            layout: "fitColumns",
            ajaxURL: "{{ route('order.list') }}",
            placeholder: "{{ trans('errors.noDataAvailable') }}",
            columnHeaderSortMulti: false,
        });
    });
</script>	
@endsection