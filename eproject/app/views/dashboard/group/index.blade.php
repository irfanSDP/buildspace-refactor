@extends('layout.main')

@section('breadcrumb')
<ol class="breadcrumb">
    <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
    <li>{{ trans('dashboard.dashboardGroups') }}</li>
</ol>
@endsection

@section('content')

<div class="row">
    <div class="col-xs-12">
        <h1 class="page-title">
            <i class="fa fa-layer-group"></i> {{ trans('dashboard.dashboardGroups') }}
        </h1>
    </div>
</div>
<div class="jarviswidget">
    <header>
        <h2> {{ trans('dashboard.listOfDashboardGroups') }} </h2>
    </header>
    <div>
        <div class="widget-body no-padding">
            <div id="dashboard_group_list-table"></div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">
    $(document).ready(function() {
        var table = new Tabulator("#dashboard_group_list-table", {
                data: {{json_encode($dashboardGroups)}},
                layout:"fitColumns",
                placeholder: "{{ trans('general.noMatchingResults') }}",
                height: 400,
                tooltips:true,
                resizableColumns:false,
                columns: [
                    {title:"{{ trans('general.no') }}", cssClass:"text-center text-middle", width: 12, headerSort:false, formatter:"rownum"},
                    {title:"{{ trans('dashboard.name') }}", field: 'name', cssClass:"auto-width text-left", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                        formatterParams: {
                            innerHtml: function(rowData){
                                return '<div class="well"><a href="'+rowData.url+'" class="plain">'+rowData.name+'</a></div>';
                            }
                        }
                    },
                    {title:"{{ trans('dashboard.users') }}", field: 'total_users', cssClass:"text-center text-middle", width: 100, headerSort:false },
                    {title:"{{ trans('dashboard.excludedProjects') }}", field: 'total_excluded_projects', cssClass:"text-center text-middle", width: 120, headerSort:false },
                    {title:"{{ trans('dashboard.actions') }}", cssClass:"text-center text-middle", width: 100, headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                        formatterParams: {
                            innerHtml: function(rowData){
                                return '<a href="'+rowData.url+'" class="btn btn-xs btn-primary" title="{{ trans("general.view") }}"><i class="fa fa-sign-in-alt"></i></a>';
                            }
                        }
                    },
                ],
            });
    });
</script>
@endsection