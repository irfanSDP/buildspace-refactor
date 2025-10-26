@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('general.productTypes') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-city"></i> {{{ trans('general.productTypes') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <a href="{{ route('consultant.management.maintenance.product.type.create') }}" class="btn btn-primary btn-md pull-right header-btn">
            <i class="fa fa-plus"></i> {{{ trans('forms.add') }}}
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> {{{ trans('general.productTypes') }}} </h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div id="product-types-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="<?php echo asset('js/app/app.restfulDelete.js'); ?>"></script>
<script>
$(document).ready(function () {
    var productTypeTable = new Tabulator('#product-types-table', {
        height:450,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.maintenance.product.type.ajax.list') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('projects.title') }}", field:"title", width: 200, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
            {title:"{{ trans('general.developmentTypes') }}", field:"development_types", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: function(rowData){
                    var c = '<div class="well">';
                    if(rowData.development_types.length){
                        c+='<p>'+rowData.development_types.join(', ')+'</p>';
                    }
                    c+='</div>';
                    return c;
                }
            }},
            {title:"{{ trans('general.actions') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [
                    {
                        tag: 'a',
                        attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("general.edit") }}'},
                        rowAttributes: {'href': 'route:edit'},
                        innerHtml: {
                            tag: 'i',
                            attributes: {class: 'fa fa-edit'}
                        }
                    },{
                        innerHtml: function(){
                            return '&nbsp;';
                        }
                    },{
                        innerHtml: function(rowData){
                            if(rowData.deletable){
                                return '<a href="'+rowData['route:delete']+'" class="btn btn-xs btn-danger" data-id="'+rowData.id+'" data-method="delete" data-csrf_token="{{{ csrf_token() }}}"><i class="fa fa-trash"></i></a>';
                            }

                            return '<button type="button" class="btn btn-xs invisible"><i class="fa fa-trash"></i></button>';
                        }
                    }
                ]
            }}
        ],
    });
});
</script>
@endsection