@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($project->title, 50), [$project->id]) }}</li>
        <li>{{ trans('letterOfAward.userPermissions') }}</li>
	</ol>
	@include('projects.partials.project_status')
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-users"></i> {{{ trans('inspection.requestForInspection') }}}
            </h1>
        </div>
        @if(PCK\Filters\InspectionFilters::canRequestInspection($project, $currentUser))
            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                <a href="{{route('inspection.request.create', array($project->id))}}" class="btn btn-primary btn-md pull-right header-btn">
                    <i class="fa fa-plus"></i> {{{ trans('requestForInspection.issueNew') }}}
                </a>
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header>
                    <h2> {{{ trans('inspection.inspection') }}} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div id="request-for-inspections-table"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script>
        $(document).ready(function(e) {
            var descriptionFormatter = function(cell, formatterParams, onRendered) {
                if(cell.getRow().getData().route_show){
                    var link = document.createElement('a');
                    link.href = cell.getRow().getData().route_show;
                    link.innerHTML = cell.getRow().getData().inspectionListCategoryName;
                    return link;
                }

                return cell.getValue();
            };

            var requestForInspectionsTable = new Tabulator('#request-for-inspections-table', {
                height:400,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL:"{{ route('inspection.requests.all.get', array($project->id)) }}",
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle"},
                    {title:"{{ trans('inspection.dateIssued') }}", field:"dateIssued", width: 140, align:"center", cssClass:"text-center", resizable:false, headerSort:false},
                    {title:"{{ trans('inspection.description') }}", field:"inspectionListCategoryName", align:"left", resizable:false, headerSort:false, formatter:descriptionFormatter},
                    {title:"{{ trans('inspection.completion') }} (%)", field:"completion", width: 120, align:"center", cssClass:"text-center", resizable:false, headerSort:false},
                    {title:"{{ trans('inspection.readyDate') }}", field:"readyForInspectionDate", width: 140, align:"center", cssClass:"text-center", resizable:false, headerSort:false},
                    {title:"{{ trans('inspection.status') }}", field:"status", width: 120, align:"center", cssClass:"text-center", resizable:false, headerSort:false},
                ],
            });
        });
    </script>
@endsection