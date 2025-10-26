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
		<li>{{ trans('eBiddingSession.pageTitle') }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-7 col-md-7 col-lg-7">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-gavel"></i> {{ trans('eBiddingSession.pageTitle') }}
			</h1>
		</div>
	</div>

	<div class="jarviswidget ">
		<header>
			<h2> {{ trans('eBiddingSession.pageTitle') }} </h2>
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

        const actionsFormatter = function(cell, formatterParams, onRendered) {
            const rowData = cell.getRow().getData();

            let container = document.createElement('div');
            container.style.textAlign = 'left';

            if (rowData.hasOwnProperty('route:show')) {
                const btn = document.createElement('a');
                btn.href = rowData['route:show'];
                //btn.target = '_blank';
                btn.dataset.toggle = 'tooltip';
                btn.title = "{{ trans('eBiddingSession.openConsole') }}";
                btn.className = 'btn btn-xs btn-primary';
                btn.innerHTML = '<i class="fa fa-gavel"></i>';
                //btn.style['margin-right'] = '5px';

                container.appendChild(btn);
            }

            return container;
        }

        dataTable = new Tabulator('#data-table', {
            fillHeight: true,
            pagination: "local",
            paginationSize: 30,
            columns: [
                { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false },
                { title:"{{ trans('projects.reference') }}", field: "reference", width: 180, cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('eBidding.project') }}", field: "projectTitle", cssClass:"auto-width text-left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('eBidding.status') }}", field: "status", width: 180, cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('eBiddingSession.previewDateTime') }}", field: "previewDateTime", width: 180, headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('eBiddingSession.startDateTime') }}", field: "startDateTime", width: 180, headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('eBiddingSession.endDateTime') }}", field: "endDateTime", width: 180, headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('eBiddingSession.sessionDuration') }}", field: "duration", width: 180, headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                { title:"{{ trans('general.actions') }}", formatter: actionsFormatter, width: 120, hozAlign: "center", cssClass:"text-center text-middle", headerSort:false },
            ],
            layout: "fitColumns",
            ajaxURL: "{{ route('e-bidding.list.sessions') }}",
            placeholder: "{{ trans('errors.noDataAvailable') }}",
            columnHeaderSortMulti: false,
        });
    });
</script>	
@endsection