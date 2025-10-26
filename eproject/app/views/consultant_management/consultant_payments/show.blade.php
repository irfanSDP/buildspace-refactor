@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{ link_to_route('consultant.management.consultant.payments.index', 'Consultant Payments') }}</li>
        <li>{{{ $company->name }}}</li>
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
                <h2><i class="fa fa-building"></i> {{{ $company->name }}}</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div id="details-table"></div>
                    <footer>
                        <div class="pull-right" style="padding-right:13px;">
                            {{ link_to_route('consultant.management.consultant.payments.index', trans('forms.back'), [], ['class' => 'btn btn-default']) }}
                        </div>
                    </footer>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript">
Tabulator.prototype.extendModule("format", "formatters", {
    title:function(cell, formatterParams){
        cell.getElement().style.whiteSpace = "pre-wrap";
        var obj = cell.getRow().getData();
        var str = '<div class="well"><a href="'+obj["route:show"]+'" class="plain">'
            + this.sanitizeHTML(obj.title)
            + '</a></div>';
        return this.emptyToSpace(str);
    },
    prewrap: function(cell, formatterParams, onRendered){
        cell.getElement().style.whiteSpace = "pre-wrap";
        return this.emptyToSpace(cell.getValue());
    },
    consultantFees: function(cell, formatterParams, onRendered){
        var rowData = cell.getRow().getData();
        var fees = cell.getValue();
        var str = '';
        if(fees.length){
            for(var i=0;i < fees.length;i++){
                obj = fees[i];
                var e = {
                    getValue: function(){
                        return obj.fee_amount;
                    }
                };
                var formattedAmount = this.formatters.money.call(this, e, formatterParams);
                str += '<div style="padding-bottom:4px;"><table style="width:100%;border:1px solid #e5e5e5;">'
                str += '<tr><td style="color:#4d8af0;padding:2px;">'+obj.subsidiary_name+'</td></tr>';
                str += '<tr><td style="padding:2px;">'+rowData.currency_code+' '+formattedAmount+'</td></tr>';
                str += '</table></div>';
            }
        }
        cell.getElement().style.whiteSpace = "pre-wrap";
        return this.emptyToSpace(str);
    }
});
$(document).ready(function () {
    var consultantTable = new Tabulator('#details-table', {
        fillHeight: true,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.consultant.payments.consultant.list', [$company->id]) }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:48, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('companies.referenceNo') }}", field:"reference_no", width: 180, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:"prewrap"},
            {title:"Planning Title", field:"title", minWidth: 420, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:"title"},
            {title:"{{ trans('projects.businessUnit') }}", field:"business_unit", width: 280, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:"prewrap"},
            {title:"{{ trans('contractGroupCategories.vendorCategories') }}", field:"vendor_category", width: 280, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:"prewrap"},
            {title:"LOA No.", field:"loa_no", width: 180, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:"prewrap"},
            {title:"LOA Date", field:"loa_date", width: 120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"Total Fees", field:"fees", width: 380, hozAlign:"left", cssClass:"text-left text-middle", headerSort:false, formatter:"consultantFees"}
        ]
    });
});
</script>
@endsection
