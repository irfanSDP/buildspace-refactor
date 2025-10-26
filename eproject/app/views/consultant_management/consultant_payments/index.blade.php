@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>Consultant Payments</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-money-check-alt"></i> Consultant Payments
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> Consultant Payments </h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div id="consultant_payments-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">
Tabulator.prototype.extendModule("format", "formatters", {
    companyName:function(cell, formatterParams){
        cell.getElement().style.whiteSpace = "pre-wrap";
        var obj = cell.getRow().getData();
        var str = '<a href="'+obj["route:show"]+'" class="plain">'
            + this.sanitizeHTML(obj.company_name)
            + '</a>';
        return this.emptyToSpace(str);
    },
    categories: function(cell, formatterParams, onRendered){
        var rowData = cell.getRow().getData();
        var str = '';
        if(rowData.vendor_categories.length){
            str = '<div class="well">'+rowData.vendor_categories.join(", ")+'</div>';
        }
        cell.getElement().style.whiteSpace = "pre-wrap";
        return this.emptyToSpace(str);
    }
});
$(document).ready(function () {
    var consultantTable = new Tabulator('#consultant_payments-table', {
        fillHeight: true,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.consultant.payments.list') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:48, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"Consultants", field:"company_name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:"companyName"},
            {title:"{{ trans('companies.companyRegistration') }}", field:"reference_no", width: 180, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
            {title:"Awarded Categories", field:"vendor_categories", width: 380, hozAlign:"left", cssClass:"text-left text-middle", headerSort:false, formatter:"categories"},
            {title:"Total Fees", field:"total_fees", width: 140, hozAlign:"right", cssClass:"text-right text-middle", headerSort:false, formatter:"money"},
        ]
    });
});
</script>
@endsection