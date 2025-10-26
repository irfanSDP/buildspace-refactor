@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('costData.masterCostData') }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="far fa-map"></i> {{{ trans('costData.masterCostData') }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <a href="{{ route('costData.master.create') }}" class="btn btn-primary btn-md pull-right header-btn">
                <i class="fa fa-plus"></i> {{{ trans('forms.add') }}}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('costData.masterCostData') }}} </h2>
                </header>
                <div>
                   <div class="widget-body no-padding">
                        <div id="master-cost-data-table"></div>
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
        new Tabulator('#master-cost-data-table', {
            height:450,
            ajaxURL: "{{ route('costData.master.list') }}",
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            ajaxFiltering:true,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('general.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
                {title:"{{ trans('general.createdBy') }}", field: 'created_by', cssClass:"text-center text-middle", width: 140, headerSort:false, headerFilter:true},
                {title:"{{ trans('general.actions') }}", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml:[
                        {
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