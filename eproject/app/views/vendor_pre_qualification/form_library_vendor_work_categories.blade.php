@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorPreQualification.vendorPreQualification') }}}</li>
        <li>{{ link_to_route('vendorPreQualification.formLibrary.index', trans('vendorPreQualification.formLibrary'), array()) }}</li>
        <li>{{{ $vendorGroup->name }}}</li>
    </ol>
@endsection

@section('content')

<div id="content">
    <div class="row">
        <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-users"></i> {{{ trans('vendorPreQualification.vendorWorkCategories') }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{{ $vendorGroup->name }}}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        <div id="main-table"></div>
                    </div>
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
                    {title:"{{ trans('propertyDevelopers.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"{{ trans('vendorManagement.vendorCategories') }}", field:"vendorCategories", width: 200, hozAlign:"left", headerSort:false, headerFilter: true, formatter: function(cell){
                        var vendorCategoriesArray = cell.getData()['vendorCategoriesArray'];
                        var output = [];
                        for(var i in vendorCategoriesArray){
                            output.push('<span class="label label-warning text-white">'+vendorCategoriesArray[i]+'</span>');
                        }
                        return output.join('&nbsp;', output);
                    }},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"workCategory", width: 200, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"{{ trans('vendorManagement.status') }}", field:"status", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"{{ trans('forms.templateForm') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml:[
                            {
                                show:function(cell){
                                    return cell.getData()['route:create'] != null;
                                },
                                tag: 'a',
                                rowAttributes: {href:'route:create'},
                                attributes: {class:'btn btn-xs btn-success', title:"{{ trans('forms.create') }}"},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class:'fa fa-plus'}
                                }
                            },
                            {
                                show:function(cell){
                                    return cell.getData()['route:clone'] != null;
                                },
                                tag: 'a',
                                rowAttributes: {href:'route:clone'},
                                attributes: {class:'btn btn-xs btn-primary', style:'margin-left: 5px;' , title:"{{ trans('forms.clone') }}"},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class:'far fa-clone'}
                                }
                            },
                            {
                                show:function(cell){
                                    return cell.getData()['route:form'] != null;
                                },
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
                                    return cell.getData()['route:edit'] != null;
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
                            },{
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
                            },{
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
                            }
                        ]
                    }}
                ],
            });
        });
    </script>
@endsection