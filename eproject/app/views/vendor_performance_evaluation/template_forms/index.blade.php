@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{{ trans('forms.templateForms') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <a href="{{ route('vendorPerformanceEvaluation.templateForms.create') }}" class="btn btn-primary btn-md pull-right header-btn">
            <i class="fa fa-plus"></i> {{{ trans('forms.add') }}}
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('forms.templateForms') }}}</h2>
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
    <script>
        $(document).ready(function () {
            var mainTable = new Tabulator('#main-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: {{ json_encode($data) }},
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:true, headerFilter:true},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"vendorGroup", width: 180, hozAlign:"center", headerSort:true, headerFilter:true},
                    {title:"{{ trans('vendorManagement.projectStage') }}", field:"projectStage", width: 150, hozAlign:"center", headerSort:true, headerFilter:true},
                    {title:"{{ trans('vendorManagement.status') }}", field:"status", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('forms.templateForm') }}", width: 100, hozAlign:"center", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml:[
                            {
                                tag: 'a',
                                rowAttributes: {href:'route:form'},
                                attributes: {class:'btn btn-xs btn-default', title:"{{ trans('general.view') }}"},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class:'fa fa-arrow-right'}
                                }
                            }
                        ]
                    }},
                    {title:"{{ trans('general.actions') }}", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml:[
                            {
                                opaque:function(cell){
                                    return cell.getData()['route:edit'];
                                },
                                tag: 'a',
                                rowAttributes: {href:'route:edit'},
                                attributes: {class:'btn btn-xs btn-warning', title:"{{ trans('forms.edit') }}"},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class:'fa fa-edit'}
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                opaque:function(cell){
                                    return cell.getData()['route:approval'] != null;
                                },
                                tag: 'a',
                                rowAttributes: {href:'route:approval'},
                                attributes: {class:'btn btn-xs btn-default', title:"{{ trans('forms.approval') }}"},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class:'fa fa-check'}
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            }, /*{
                                opaque:function(cell){
                                    return cell.getData()['route:newRevision'] != null;
                                },
                                tag: 'a',
                                rowAttributes: {href:'route:newRevision'},
                                attributes: {class:'btn btn-xs btn-info', title:"{{ trans('vendorManagement.createNewRevision') }}"},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class:'fa fa-star'}
                                }
                            },*/ {
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                opaque:function(cell){
                                    return cell.getData()['route:template'] != null;
                                },
                                tag: 'a',
                                rowAttributes: {href:'route:template'},
                                attributes: {class:'btn btn-xs btn-default', title:"{{ trans('forms.viewLatestVersion') }}"},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class:'fa fa-eye'}
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },
                            /*{
                                opaque:function(cell){
                                    return cell.getData()['route:grade'] != null;
                                },
                                tag: 'a',
                                rowAttributes: {href:'route:grade'},
                                attributes: {class:'btn btn-xs btn-success', title:"{{ trans('vendorManagement.updateGradingDefinitions') }}"},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class:'fa fa-sm fa-address-book'}
                                }
                            },*/
                            {
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                tag: 'a',
                                rowAttributes: {href:'route:clone'},
                                attributes: {class:'btn btn-xs btn-primary', title:"{{ trans('general.copy') }}"},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class:'far fa-clone'}
                                }
                            }
                        ]
                    }}
                ],
            });
        });
    </script>
@endsection