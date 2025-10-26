@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>LOA Templates</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-file-code"></i> LOA Templates
        </h1>
    </div>
    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
        <a href="{{ route('consultant.management.loa.templates.create') }}" class="btn btn-primary btn-md pull-right header-btn">
            <i class="fa fa-plus"></i> {{{ trans('general.new') }}} {{{trans('formOfTender.template')}}}
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> LOA Templates </h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div id="load_templates-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">
$(document).ready(function () {
    new Tabulator('#load_templates-table', {
        fillHeight:true,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.loa.templates.ajax.list') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.title') }}", field:"title", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                show:function(cell){
                    return cell.getData().hasOwnProperty('id');
                },
                tag: 'a',
                attributes: {},
                rowAttributes: {href:'route:show'},
                innerHtml: function(rowData){
                    return rowData.title;
                }
            }},
            {title:"{{ trans('general.createdAt') }}", field:"created_at", width: 140, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false}
        ]
    });
});
</script>
@endsection