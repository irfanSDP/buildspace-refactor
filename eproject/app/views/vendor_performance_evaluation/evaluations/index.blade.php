@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ trans('vendorManagement.evaluations') }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.evaluations') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('vendorManagement.evaluations') }}}</h2>
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
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
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
                                show: function(cell){
                                    return cell.getData()['route:forms'];
                                },
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-default', title: '{{ trans("vendorManagement.forms") }}'},
                                rowAttributes: {'href': 'route:forms'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-list'},
                                    innerHtml: function(rowData){
                                        return " {{ trans('vendorManagement.forms') }}";
                                    }
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                show: function(cell){
                                    return cell.getData()['route:evaluators'];
                                },
                                innerHtml: [
                                    {
                                        tag: 'a',
                                        attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("vendorManagement.evaluators") }}'},
                                        rowAttributes: {'href': 'route:evaluators'},
                                        innerHtml: {
                                            tag: 'i',
                                            attributes: {class: 'fa fa-users'},
                                        }
                                    },{
                                        innerHtml: function(){
                                            return '&nbsp;';
                                        }
                                    },{
                                        show:function(cell){
                                            return cell.getData()['route:remove'] && !cell.getData()['removalRequestSent'];
                                        },
                                        tag: 'a',
                                        attributes: {class:'btn btn-xs btn-warning text-white', title: '{{ trans("vendorManagement.sendRemovalRequest") }}'},
                                        rowAttributes: {'href': 'route:remove'},
                                        innerHtml: {
                                            tag: 'i',
                                            attributes: {class: 'fa fa-trash'},
                                        }
                                    },{
                                        show:function(cell){
                                            return cell.getData()['removalRequestSent'];
                                        },
                                        tag: 'a',
                                        attributes: {class:'btn btn-xs btn-warning text-white', disabled: 'disabled', title: '{{ trans("vendorManagement.evaluationRequestedToBeRemoved") }}'},
                                        innerHtml: {
                                            tag: 'i',
                                            attributes: {class: 'fa fa-exclamation-triangle'},
                                        }
                                    }
                                ]
                            }
                        ]
                    }}
                ],
            });
        });
    </script>
@endsection