@extends('layout.main')

@section('breadcrumb')
<ol class="breadcrumb">
    <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
    <li>{{{ $vendorCategoryRfp->vendorCategory->name }}}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-trophy"></i> {{{ trans('general.callingRFP') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <div>
                <div class="widget-body no-padding">
                    <div id="calling_rfp-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">
$(document).ready(function () {
    Tabulator.prototype.extendModule("format", "formatters", {
        textarea:function(cell, formatterParams){
            cell.getElement().style.whiteSpace = "pre-wrap";
            var obj = cell.getRow().getData();
            var str = '<a href="'+obj["route:show"]+'" class="plain">'
                + this.sanitizeHTML(obj.title)
                + '</a>';
            return this.emptyToSpace(str);
        }
    });

    var callingRfpTable = new Tabulator('#calling_rfp-table', {
        fillHeight:true,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.calling.rfp.ajax.list', $vendorCategoryRfp->id) }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.title') }}", field:"title", minWidth: 300, hozAlign:"left", headerSort:false, formatter:"textarea"},
            {title:"{{ trans('documentManagementFolders.revision') }}", field:"revision", width:80, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.status') }}", field:"status_txt", width:180, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.createdAt') }}", field:"created_at", width:160, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.updatedAt') }}", field:"updated_at", width:160, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false}
        ]
    });
});
</script>
@endsection