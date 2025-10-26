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
            <i class="fa fa-star"></i> {{{ trans('general.openRFP') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <div>
                <div class="widget-body no-padding">
                    <div id="open_rfp-table"></div>
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

        @if($user->hasAccessToConsultantManagementByRole($consultantManagementContract, PCK\ConsultantManagement\ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT))
        ,verifier:function(cell, formatterParams){
            var obj = cell.getRow().getData();
            var str;
            switch(parseInt(obj.status)){
                case {{PCK\ConsultantManagement\ConsultantManagementOpenRfp::STATUS_DRAFT}}:
                    str = @if($user->isConsultantManagementCallingRfpEditor($consultantManagementContract)) '<a href="'+obj["route:verifier"]+'" class="plain">{{{ trans("contractManagement.assignVerifiers") }}}</a>' @else obj.updated_at @endif;
                    break;
                case {{PCK\ConsultantManagement\ConsultantManagementOpenRfp::STATUS_APPROVAL}}:
                    str = '<a href="'+obj["route:verifier"]+'" class="plain">{{{ trans("formBuilder.pendingForApproval") }}}</a>';
                    break;
                default:
                    str = '<a href="'+obj["route:verifier"]+'" class="plain">{{{ trans("verifiers.approved") }}}</a>';
                    break;
            }
            return this.emptyToSpace(str);
        }
        @endif
    });

    var openRfpTable = new Tabulator('#open_rfp-table', {
        fillHeight:true,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.open.rfp.ajax.list', $vendorCategoryRfp->id) }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.title') }}", field:"title", minWidth: 300, hozAlign:"left", headerSort:false, formatter:"textarea"},
            {title:"{{ trans('documentManagementFolders.revision') }}", field:"revision", width:80, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"No. of Submitted RFP", field:"total_rfp_submission", width:160, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('tenders.closingDate') }}", field:"closing_date", width:160, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            @if($user->hasAccessToConsultantManagementByRole($consultantManagementContract, PCK\ConsultantManagement\ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT))
            {title:"{{ trans('verifiers.verifiers') }}", field:"updated_at", width:160, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:"verifier"},
            @endif
            {title:"{{ trans('general.status') }}", field:"status_txt", width:160, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false}
        ]
    });
});
</script>
@endsection