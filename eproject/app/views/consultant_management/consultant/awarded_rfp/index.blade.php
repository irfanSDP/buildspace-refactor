@extends('layout.main')

@section('breadcrumb')
<ol class="breadcrumb">
    <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
    <li>{{{ trans('general.consultantManagement') }}} Awarded RFP</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-trophy"></i> Awarded RFP
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> Awarded RFP </h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div id="rfp-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript">
Tabulator.prototype.extendModule("format", "formatters", {
    textarea:function(cell, formatterParams){
        cell.getElement().style.whiteSpace = "pre-wrap";
        var obj = cell.getRow().getData();
        var str = '<a href="'+obj["route:show"]+'" class="plain">'
            + '<div class="well">' +this.sanitizeHTML(obj.title)+ '</div>'
            + '<p style="padding-top:4px;">'
            + '<span class="label label-success">'
            + this.sanitizeHTML(obj.created_at)
            + '</span>'
            + '&nbsp;'
            + '<span class="label label-info">'
            + this.sanitizeHTML(obj.country) + ', ' + this.sanitizeHTML(obj.state)
            + '</span>'
            +'</p></a>';
        return this.emptyToSpace(str);
    },
    rfp:function(cell, formatterParams){
        cell.getElement().style.whiteSpace = "pre-wrap";
        var obj = cell.getRow().getData();
        var str = '<a href="'+obj["route:show"]+'" class="plain">'
            + '<div class="well">' +this.sanitizeHTML(obj.rfp_name)+ '</div>'
            + '</a>';
        return this.emptyToSpace(str);
    }
});

$(document).ready(function () {
    new Tabulator('#rfp-table', {
        height:520,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.consultant.awarded.rfp.ajax.list') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('companies.referenceNo') }}", field:"reference_no", width: 180, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
            {title:"RFP", field:"rfp_name", width: 320, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:"rfp"},
            {title:"{{ trans('projects.contract') }}", field:"title", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:"textarea"},
            {title:"Awarded Date", field:"closing_date", width:160, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false}
        ]
    });
});
</script>
@endsection