@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('costData.costData') }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fas fa-map"></i> {{{ trans('costData.costData') }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            @if(\PCK\ModulePermission\ModulePermission::isEditor($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_COST_DATA))
                <a href="{{ route('costData.create') }}" class="btn btn-primary btn-md pull-right header-btn">
                    <i class="fa fa-plus"></i> {{{ trans('forms.add') }}}
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('costData.costData') }}} </h2>
                </header>
                <div>
                   <div class="widget-body no-padding">
                        <div id="cost-data-table"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script>
        new Tabulator('#cost-data-table', {
            height:450,
            ajaxURL: "{{ route('costData.list') }}",
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            ajaxFiltering:true,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('general.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
                {title:"{{ trans('costData.masterCostData') }}", field:"master_cost_data", width:300, hozAlign:"center", headerSort:false, headerFilter:true},
                {title:"{{ trans('projects.businessUnit') }}", field:"business_unit", width:180, hozAlign:"center", headerSort:false, headerFilter:true},
                {title:"{{ trans('costData.tenderYear') }}", field:"tender_year", width:180, hozAlign:"center", headerSort:false, headerFilter:true},
                {title:"{{ trans('costData.awardYear') }}", field:"award_year", width:180, hozAlign:"center", headerSort:false, headerFilter:true},
                {title:"{{ trans('general.createdBy') }}", field: 'created_by', cssClass:"text-center text-middle", width: 140, headerSort:false, headerFilter:true},
                {title:"{{ trans('general.actions') }}", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml:[
                        {
                            opaque:function(cell){
                                return cell.getData()['show_app_link'];
                            },
                            tag: 'a',
                            attributes: {target: '_blank', class:'btn btn-xs btn-warning', title: "{{ trans('general.view') }}"},
                            rowAttributes: {'href': 'app_link'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fas fa-external-link-square-alt'},
                            }
                        },{
                            innerHtml: function(){
                                return '&nbsp;';
                            }
                        },{
                            opaque:function(cell){
                                return cell.getData()['show_route:users'];
                            },
                            tag: 'a',
                            attributes: {class:'btn btn-xs btn-default', title: "{{ trans('users.assignUsers') }}"},
                            rowAttributes: {'href': 'route:users'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fas fa-users'},
                            }
                        },{
                            innerHtml: function(){
                                return '&nbsp;';
                            }
                        },{
                            opaque:function(cell){
                                return cell.getData()['show_route:edit'];
                            },
                            tag: 'a',
                            attributes: {class:'btn btn-xs btn-default', title: "{{ trans('forms.edit') }}"},
                            rowAttributes: {'href': 'route:edit'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fas fa-pen-square'},
                            }
                        },{
                            innerHtml: function(){
                                return '&nbsp;';
                            }
                        },{
                            tag: 'a',
                            attributes: {class:'btn btn-xs btn-default', title: "{{ trans('costData.details') }}"},
                            rowAttributes: {'href': 'route:show'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fas fa-search'},
                            }
                        },{
                            innerHtml: function(){
                                return '&nbsp;';
                            }
                        },{
                            opaque:function(cell){
                                return cell.getData()['show_route:delete'];
                            },
                            tag: 'a',
                            attributes: {class:'btn btn-xs btn-danger', title: "{{ trans('forms.delete') }}", 'data-method': 'delete', 'data-csrf_token':"{{{ csrf_token() }}}"},
                            rowAttributes: {'href': 'route:delete'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fa fa-times'},
                            }
                        }
                    ]
                }}
            ]
        });
    </script>
@endsection