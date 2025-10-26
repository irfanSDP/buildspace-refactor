@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ link_to_route('vendorPerformanceEvaluation.cycle.index', trans('vendorManagement.vendorPerformanceEvaluationCycles'), array()) }}</li>
        <li>{{ trans('vendorManagement.projects') }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.projects') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('vendorManagement.projects') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="main-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script>
        $(document).ready(function () {
            var mainTable = new Tabulator('#main-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: {{ json_encode($data) }},
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('projects.reference') }}", field:"reference", width:150, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:true},
                    {title:"{{ trans('projects.title') }}", field:"title", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true},
                    {title:"{{ trans('projects.businessUnit') }}", field:"business_unit", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:true},
                    {title:"{{ trans('vendorManagement.projectStage') }}", field:"statusText", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:true},
                    {title:"{{ trans('vendorManagement.startDate') }}", field:"start_date", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.endDate') }}", field:"end_date", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.status') }}", field:"status", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:true},
                    {title:"{{ trans('general.actions') }}", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        show: function(cell){
                            return cell.getData().hasOwnProperty('id');
                        },
                        innerHtml: [
                            {
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-default', title: '{{ trans("vendorManagement.vendors") }}'},
                                rowAttributes: {'href': 'route:vendors'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-list'},
                                    innerHtml: function(rowData){
                                        return " {{ trans('vendorManagement.vendors') }}";
                                    }
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                opaque: function(cell){
                                    return cell.getData()['route:initiate'];
                                },
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-warning', title: '{{ trans("vendorManagement.initiate") }}'},
                                rowAttributes: {'href': 'route:initiate'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-arrow-right'},
                                    innerHtml: function(rowData){
                                        return " {{ trans('vendorManagement.initiate') }}";
                                    }
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                opaque: function(cell){
                                    return cell.getData()['route:delete'];
                                },
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-danger', title: '{{ trans("forms.delete") }}', 'data-method':'delete', 'data-csrf_token': _csrf_token },
                                rowAttributes: {'href': 'route:delete'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-trash'},
                                }
                            },{
                                innerHtml: function(rowData){
                                    if(rowData['deletable'])
                                    {
                                        return '<a href="'+rowData['route:delete']+'" class="btn btn-xs btn-danger" data-id="'+rowData['id']+'" data-method="delete" data-csrf_token="{{{ csrf_token() }}}"><i class="fa fa-trash"></i></a>';
                                    }

                                    return '<button type="button" class="btn btn-xs invisible"><i class="fa fa-trash"></i></button>';
                                }
                            },
                        ]
                    }}
                ],
            });
        });
    </script>
@endsection