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
            <i class="fa fa-clipboard-list"></i> Tracker
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2><i class="fa fa-building"></i> {{{ $vendorCategoryRfp->vendorCategory->name }}}</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <ul id="tracker-tabs" class="nav nav-tabs">
                        <li class="active">
                            <a href="#tracker-tab-recommendation_of_consultant" data-toggle="tab" id="recommendation_of_consultant-tab"><i class="fa fa-sm fa-fw fa-file-signature"></i> Rec. of Consultant</a>
                        </li>
                        <li>
                            <a href="#tracker-tab-list_of_consultant" data-toggle="tab" id="list_of_consultant-tab"><i class="fa fa-sm fa-fw fa-th-list"></i> List of Consultant</a>
                        </li>
                        <li>
                            <a href="#tracker-tab-calling_rfp" data-toggle="tab" id="calling_rfp-tab"><i class="fa fa-sm fa-fw fa-trophy"></i> {{{ trans('general.callingRFP')}}}</a>
                        </li>
                        <li>
                            <a href="#tracker-tab-open_rfp" data-toggle="tab" id="open_rfp-tab"><i class="fa fa-sm fa-fw fa-star"></i> {{{ trans('general.openRFP')}}}</a>
                        </li>
                    </ul>
                    <div id="tracker-tab-content" class="tab-content padding-10">
                        <div class="tab-pane fade in active" id="tracker-tab-recommendation_of_consultant">
                            @include('consultant_management.tracker.partials.recommendation_of_consultant')
                        </div>
                        <div class="tab-pane fade in" id="tracker-tab-list_of_consultant">
                            @include('consultant_management.tracker.partials.list_of_consultant')
                        </div>
                        <div class="tab-pane fade in" id="tracker-tab-calling_rfp">
                            @include('consultant_management.tracker.partials.calling_rfp')
                        </div>
                        <div class="tab-pane fade in" id="tracker-tab-open_rfp">
                            @include('consultant_management.tracker.partials.open_rfp')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">
Tabulator.prototype.extendModule("format", "formatters", {
    remarks:function(cell, formatterParams){
        cell.getElement().style.whiteSpace = "pre-wrap";
        var obj = cell.getRow().getData();
        var str = (obj.remarks.length) ? '<div class="well">' +this.sanitizeHTML(obj.remarks)+ '</div>' : '<div>&nbsp;</div>';
        return this.emptyToSpace(str);
    },
    interviewConsultantName:function(cell, formatterParams){
        cell.getElement().style.whiteSpace = "pre-wrap";
        var obj = cell.getRow().getData();
        var str = this.sanitizeHTML(obj.company_name);
        if(obj.consultant_interview_remarks.length){
            str += '<div class="well">'+this.sanitizeHTML(obj.consultant_interview_remarks)+'</div>';
        }
        return this.emptyToSpace(str);
    },
    interviewConsultantRemarks:function(cell, formatterParams){
        cell.getElement().style.whiteSpace = "pre-wrap";
        var obj = cell.getRow().getData();
        var str = this.sanitizeHTML(obj.consultant_remarks);
        return this.emptyToSpace(str);
    }
});

$(document).ready(function () {
    <?php
    $verifierTables = [
        'roc-verifier_logs-table' => route('consultant.management.tracker.roc.verifier.log', $vendorCategoryRfp->id),
        'loc-verifier_logs-table' => route('consultant.management.tracker.loc.verifier.log', $vendorCategoryRfp->id),
        'calling_rfp-verifier_logs-table' => route('consultant.management.tracker.calling.rfp.verifier.log', $vendorCategoryRfp->id),
        'open_rfp-verifier_logs-table' => route('consultant.management.tracker.open.rfp.verifier.log', $vendorCategoryRfp->id),
    ];
    ?>
    @foreach($verifierTables as $elemId => $route)
    new Tabulator('#{{$elemId}}', {
        height:240,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ $route }}",
        ajaxConfig: "GET",
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"id", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:'rownum'},
            {title:"{{ trans('general.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
            {title:"{{ trans('documentManagementFolders.revision') }}", field:"version", width:80, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.status') }}", field:"status_txt", width:120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.remarks') }}", field:"remarks", width:380, hozAlign:'left', headerSort:false, formatter:'remarks'},
            {title:"{{ trans('general.updatedAt') }}", field:"updated_at", width:160, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false}
        ]
    });
    @endforeach

    @if($callingRfp && $vendorCategoryRfp->rfpInterviews->count())
        @foreach($vendorCategoryRfp->rfpInterviews as $rfpInterview)

        new Tabulator('#consultant_list-{{$rfpInterview->id}}-table', {
            height:240,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxURL: "{{ route('consultant.management.consultant.rfp.interview.selected.consultant.list', [$vendorCategoryRfp->id, $callingRfp->id, $rfpInterview->id]) }}",
            ajaxConfig: "GET",
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", field:"id", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:'rownum'},
                {title:"Consultant(s)", field:"company_name", minWidth: 300, hozAlign:"left", headerSort:false, formatter:'interviewConsultantName'},
                {title:"{{ trans('companies.referenceNumber') }}", field:"reference_no", width:180, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('general.time') }}", field:"interview_timestamp", width: 160, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false}
                @if($rfpInterview->status == PCK\ConsultantManagement\ConsultantManagementRfpInterview::STATUS_SENT)
                ,{title:"{{ trans('general.status') }}", field:"consultant_interview_status_txt", width: 140, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false}
                ,{title:"Consultant Remarks", field:"consultant_remarks", width: 300, hozAlign:"left", headerSort:false, formatter:'interviewConsultantRemarks'}}
                @endif
            ]
        });

        @endforeach
    @endif

});
</script>
@endsection