@extends('layout.main')

<?php
$currencyCode = empty($consultantManagementContract->modified_currency_code) ? $consultantManagementContract->country->currency_code : $consultantManagementContract->modified_currency_code;
$callingRfp = $openRfp->consultantManagementRfpRevision->callingRfp;
$latestRevision = $vendorCategoryRfp->getLatestRfpRevision();

?>

@section('breadcrumb')
<ol class="breadcrumb">
    <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
    <li>{{{ $vendorCategoryRfp->vendorCategory->name }}}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-trophy"></i> {{{ trans('general.openRFP') }}} {{{trans('documentManagementFolders.revision')}}} {{$openRfp->consultantManagementRfpRevision->revision}}
        </h1>
    </div>

    @if($showResubmissionVerifierLog || ($latestRevision && $latestRevision->callingRfp && $latestRevision->callingRfp->status == PCK\ConsultantManagement\ConsultantManagementCallingRfp::STATUS_APPROVED && !$latestRevision->callingRfp->isCallingRFpStillOpen() && $openRfp->status == PCK\ConsultantManagement\ConsultantManagementOpenRfp::STATUS_APPROVED))
    <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
        <!-- Header buttons -->
        <div class="btn-group pull-right header-btn">
            <div class="dropdown {{{ $classes ?? 'pull-right' }}}">
                <a data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
                <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
                @if($latestRevision && $latestRevision->callingRfp && $latestRevision->callingRfp->status == PCK\ConsultantManagement\ConsultantManagementCallingRfp::STATUS_APPROVED && !$latestRevision->callingRfp->isCallingRFpStillOpen() && $openRfp->status == PCK\ConsultantManagement\ConsultantManagementOpenRfp::STATUS_APPROVED)
                    @if($user->isConsultantManagementCallingRfpEditor($consultantManagementContract) && (!$vendorCategoryRfp->approvalDocument or $vendorCategoryRfp->approvalDocument->status == PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT))
                    <li>
                        <a href="{{route('consultant.management.open.rfp.resubmission', [$vendorCategoryRfp->id, $openRfp->id] )}}" class="btn btn-block btn-md btn-danger">
                            <i class="fa fa-sync-alt"></i> {{ trans("tenders.tenderRevision") }}
                        </a>
                    </li>
                    <li class="divider"></li>
                    @endif
                    <li>
                        <a href="{{route('consultant.management.approval.document.index', [$vendorCategoryRfp->id, $openRfp->id] )}}" class="btn btn-block btn-md btn-primary">
                            <i class="fa fa-file-contract"></i> {{ trans('general.approvalDocument') }}
                        </a>
                    </li>
                    <li class="divider"></li>
                @endif
                @if($showResubmissionVerifierLog)
                    <li>
                    {{ HTML::decode(link_to_route('consultant.management.open.rfp.resubmission', '<i class="fa fa-search"></i> Resubmission Verifier Logs', [$vendorCategoryRfp->id, $openRfp->id], ['class' => 'btn btn-block btn-md btn-info'])) }}
                    </li>
                    <li class="divider"></li>
                @endif
                </ul>
            </div>
        </div>
    </div>
    @endif
</div>

@if($openRfp->status == PCK\ConsultantManagement\ConsultantManagementOpenRfp::STATUS_RESUBMISSION_APPROVAL && $openRfp->needResubmissionApprovalFromUser($user))
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2><i class="fa fa-user-tie"></i> {{{$openRfp->getStatusText()}}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(['route' => ['consultant.management.rfp.resubmission.verify', $vendorCategoryRfp->id], 'class' => 'smart-form']) }}
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{{ trans('vendorManagement.remarks') }}}:</label>
                            <label class="textarea {{{ ($errors->has('remarks') or $errors->has('rfp_closing_date')) ? 'state-error' : null }}}">
                                {{ Form::textarea('remarks', Input::old('remarks'), ['rows' => 3]) }}
                            </label>
                            {{ $errors->first('remarks', '<em class="invalid">:message</em>') }}
                            {{ $errors->first('rfp_closing_date', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                    <footer>
                        {{ Form::hidden('id', $openRfp->id) }}
                        {{ link_to_route('consultant.management.open.rfp.index', trans('forms.back'), [$vendorCategoryRfp->id], ['class' => 'btn btn-default']) }}
                        {{ HTML::decode(link_to_route('consultant.management.open.rfp.resubmission', '<i class="fa fa-search"></i> '.trans('verifiers.verifierLogs'), [$vendorCategoryRfp->id, $openRfp->id], ['class' => 'btn btn-primary'])) }}
                        {{ Form::button('<i class="fa fa-times-circle"></i> '.trans('forms.reject'), ['type' => 'submit', 'name'=>'reject', 'class' => 'btn btn-danger'] )  }}
                        {{ Form::button('<i class="fa fa-check-circle"></i> '.trans('forms.approve'), ['type' => 'submit', 'name'=>'approve', 'class' => 'btn btn-success'] )  }}
                    </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            @if($callingRfp->isCallingRFpStillOpen())
            <header>
                <h2><i class="fa fa-sm fa-fw fa-exclamation-triangle text-warning"></i> Calling RFP is still active :: {{{ trans('general.closingRfpDate') }}} : {{{ $callingRfp->closing_rfp_date }}}</h2>
            </header>
            @endif
            <div>
                <div class="widget-body no-padding">
                    <ul id="consultant-management-contract-tabs" class="nav nav-tabs">
                        <li class="active">
                            <a href="#consultant-management-contract-tab-main-info" data-toggle="tab"><i class="fa fa-fw fa-lg fa-info-circle"></i> {{{ trans('projects.mainInformation') }}}</a>
                        </li>
                        <li>
                            <a href="#consultant-management-contract-tab-phases" data-toggle="tab"><i class="fa fa-fw fa-lg fa-file-contract"></i> {{{ trans('general.phases') }}}</a>
                        </li>
                    </ul>
                    <div id="consultant-management-contract-tab-content" class="tab-content padding-10">
                        <div class="tab-pane fade in active " id="consultant-management-contract-tab-main-info">
                            @include('consultant_management.contracts.partials.main_info')
                        </div>
                        <div class="tab-pane fade in " id="consultant-management-contract-tab-phases">
                            @if($consultantManagementContract->consultantManagementSubsidiaries->count())
                            <ul id="consultant-management-subsidiaries-tabs" class="nav nav-pills">
                                @foreach($consultantManagementContract->consultantManagementSubsidiaries as $key => $consultantManagementSubsidiary)
                                <li class="nav-item @if($key == 0) active @endif">
                                    <a href="#consultant-management-subsidiaries-tab-{{$consultantManagementSubsidiary->id}}" title="{{{ $consultantManagementSubsidiary->subsidiary->name}}}" data-toggle="tab">{{{ $consultantManagementSubsidiary->subsidiary->short_name}}}</a>
                                </li>
                                @endforeach
                            </ul>
                            <div id="consultant-management-subsidiaries-tab-content" class="tab-content" style="padding-top:1rem!important;">
                            @foreach($consultantManagementContract->consultantManagementSubsidiaries as $key => $consultantManagementSubsidiary)
                                <div class="tab-pane fade in @if($key==0) active @endif" id="consultant-management-subsidiaries-tab-{{$consultantManagementSubsidiary->id}}">
                                    @include('consultant_management.contracts.partials.phase_info')
                                </div>
                            @endforeach
                            </div>
                            @else
                            <div class="row">
                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                    <div class="alert alert-warning text-center">
                                        <i class="fa-fw fa fa-info"></i>
                                        <strong>Info!</strong> There is no Phase for this Development Plan.
                                    </div>
                                </section>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2><i class="fa fa-building"></i> {{{ $vendorCategoryRfp->vendorCategory->name }}} <span class="badge bg-color-green inbox-badge">{{{$openRfp->getStatusText()}}}</span> </h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div id="consultants-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($openRfp->status != PCK\ConsultantManagement\ConsultantManagementOpenRfp::STATUS_DRAFT)
@include('templates.generic_table_modal', [
    'modalId'    => 'consultantAttachmentModal',
    'title'      => trans('general.attachments'),
    'tableId'    => 'consultant_attachment-table',
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])
@endif

@include('consultant_management.partials.general_attachment')

@endsection

@section('js')

<script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
<script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
<script src="{{ asset('js/moment/min/moment.min.js') }}"></script>
<script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
<script src="{{ asset('js/app/app.functions.js') }}"></script>
@include('consultant_management.partials.general_attachment_javascript')

<script type="text/javascript">
Tabulator.prototype.extendModule("format", "formatters", {
    mandatory:function(cell, formatterParams){
        var obj = cell.getRow().getData();
        var str = (obj.mandatory) ? '<i class="fas fa-lg fa-fw fa-check-circle text-success"></i>' : '<i class="fas fa-lg fa-fw fa-times-circle text-danger"></i>';
        return this.emptyToSpace(str);
    },
    submittedDate: function(cell, formatterParams){
        var obj    = cell.getRow().getData();
        var textClass = "text-danger";
        var text = "{{ trans('tenders.notSubmitted') }}";

        if(obj.submitted_at){
            textClass = "text-success";
            text = obj.submitted_at;
        }
        return this.emptyToSpace('<p class="'+textClass+'">'+text+'</p>');
    },
    validateInput: function(cell, formatterParams, onRendered){
        var obj = cell.getRow().getData();
        if(obj.submitted_at){
            return app_tabulator_utilities.variableHtmlFormatter(cell, formatterParams, onRendered);
        }
    },
    attachmentDownloadButton: function(cell, formatterParams, onRendered) {
        var obj = cell.getRow().getData();
        if(obj.type=='file'){
            var btn = document.createElement('a');
            btn.dataset.toggle = 'tooltip';
            btn.className = 'btn btn-xs btn-primary';
            btn.innerHTML = '<i class="fas fa-download"></i>';
            btn.style['margin-right'] = '5px';
            btn.href = obj['route:download'];

            return btn;
        }
    },
    attachmentTitle: function(cell, formatterParams, onRendered){
        var obj = cell.getRow().getData();
        if(obj.type=='folder'){
            return this.emptyToSpace('&nbsp;<strong>'+obj.title+'</strong>');
        }else{
            return this.emptyToSpace(obj.title);
        }
    }
});
$(document).ready(function () {

    var columns;

    @if($openRfp->status != PCK\ConsultantManagement\ConsultantManagementOpenRfp::STATUS_APPROVED && $openRfp->status != PCK\ConsultantManagement\ConsultantManagementOpenRfp::STATUS_RESUBMISSION_APPROVED)
    columns = [
        {title:"{{ trans('general.no') }}", field:"counter", width:48, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
        {title:"{{ trans('general.consultantsList') }}", field: 'company_name', cssClass:"text-left", minWidth:300, headerFilter: "input", headerSort:false},
        {title:"{{ trans('companies.referenceNumber') }}", field:"reference_no", width:180, headerFilter: "input", hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
        {title:"{{ trans('tenders.submittedDate') }}", cssClass:"text-center", width:160, headerSort:false, formatter:'submittedDate'},
    ];
    @else
    columns = [
        @if($user->isConsultantManagementCallingRfpEditor($consultantManagementContract) && $openRfp->status == PCK\ConsultantManagement\ConsultantManagementOpenRfp::STATUS_APPROVED && (!$vendorCategoryRfp->approvalDocument || $vendorCategoryRfp->approvalDocument->status != PCK\ConsultantManagement\ApprovalDocument::STATUS_APPROVED))
        {title:"", cssClass:"text-center", field: 'id', width: 10, frozen: true, headerSort:false, formatter:'validateInput', formatterParams: {
            tag: 'input',
            rowAttributes: {'value': 'id', 'checked': 'awarded'},
            attributes: {'type': 'radio', 'name': 'consultant'},
        }},
        @endif
        {title:"{{ trans('general.no') }}", field:"counter", width:60, frozen:true, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
        {title:"{{ trans('general.consultantsList') }}", field: 'company_name', frozen:true, cssClass:"text-left", minWidth:300, headerFilter: "input", headerSort:false, formatter:function(cell, formatterParams){
            cell.getElement().style.whiteSpace = "pre-wrap";
            var cellValue = cell.getValue();
            var rowData = cell.getRow().getData();
            var textColor = rowData.awarded ? '#fd3995' : '#333';

            if(rowData.awarded){
                cell.getRow().getElement().style.backgroundColor = '#b3ffb3';
            }
            var remarks = (rowData.remarks.length) ? '<div class="well">' +this.sanitizeHTML(rowData.remarks)+ '</div>' : '';

            var awardedIcon = (rowData.awarded) ? '<i class="fa fa-lg fa-trophy text-warning"></i>' : '';
            return this.emptyToSpace('<p style="text-align:left;color:'+textColor+';">'+this.sanitizeHTML(cellValue)+' '+awardedIcon+'</p>'+remarks);
        }},
        {title:"{{ trans('companies.referenceNumber') }}", field:"reference_no", width:160, frozen:true, headerFilter: "input", hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
        {title:"{{ trans('tenders.submittedDate') }}", cssClass:"text-center", width:160, frozen:true, headerSort:false, formatter:'submittedDate'},
        @foreach($consultantManagementContract->consultantManagementSubsidiaries as $consultantManagementSubsidiary)
        {
            title:"{{{ PCK\Helpers\StringOperations::shorten($consultantManagementSubsidiary->subsidiary->name, 42) }}}", cssClass: 'text-center text-nowrap',
            columns:[
                {title:"{{{ trans('tenders.amount') }}} ({{{ $currencyCode }}})", field: "consultant_{{{ $consultantManagementSubsidiary->id }}}_amount", width:@if($vendorCategoryRfp->cost_type != PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp::COST_TYPE_LUMP_SUM_COST) 200 @else 320 @endif, cssClass:"text-right", hozAlign:"center", headerSort:false},
                @if($vendorCategoryRfp->cost_type != PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp::COST_TYPE_LUMP_SUM_COST)
                {title:"{{{ trans('general.proposedFee') }}} %", field: "consultant_{{{ $consultantManagementSubsidiary->id }}}_percentage", width:120, cssClass:"text-center",  hozAlign:"center", headerSort:false}
                @endif
            ],
        },
        @endforeach
        {title:"{{ trans('general.attachments') }}", field: 'attachments', width:120, cssClass:"text-center", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
            innerHtml: function(rowData){
                return '<button class="btn btn-xs btn-info"><i class="fa fa-sm fa-paperclip"></i>  '+rowData.attachments_count+'</button>';
            }
        }, cellClick:function(e, cell){
            var data = cell.getRow().getData();
            var table = Tabulator.prototype.findTable("#consultant_attachment-table")[0];
            if(!table){
                table = new Tabulator('#consultant_attachment-table', {
                    height:420,
                    dataTree:true,
                    dataTreeStartExpanded:true,
                    dataTreeExpandElement:'<i class="fa-lg fas fa-folder-plus text-warning"></i>',
                    dataTreeCollapseElement:'<i class="fa-lg fas fa-folder-minus text-warning"></i>',
                    columns: [
                        { title:"{{ trans('general.attachments') }}", field: 'title', cssClass:"text-left", headerSort:false, formatter:'attachmentTitle'},
                        {title:"Mandatory", field:"mandatory", width: 92, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:'mandatory'},
                        {title:"{{ trans('general.total') }}", field:"total_attachment", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                        { title:"{{ trans('general.download') }}", width: 92, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter: 'attachmentDownloadButton' },
                    ],
                    layout:"fitColumns",
                    ajaxURL: data['route:attachment-list'],
                    ajaxConfig: "GET",
                    placeholder:"{{ trans('general.noRecordsFound') }}"
                });
            }else{
                table.setData(data['route:attachment-list']);
            }

            $('#consultantAttachmentModal').modal('show');
        }}
    ];
    @endif

    var consultantsTable = new Tabulator("#consultants-table", {
        columnHeaderVertAlign:"middle",
        ajaxURL: "{{ route('consultant.management.open.rfp.consultants.ajax.list', [$vendorCategoryRfp->id, $openRfp->id]) }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        columns: columns,
        layout: 'fitColumns',
        height: 520,
        renderComplete:function(){
            @if($user->isConsultantManagementCallingRfpEditor($consultantManagementContract) && $openRfp->status == PCK\ConsultantManagement\ConsultantManagementOpenRfp::STATUS_APPROVED)
            $("input[name=consultant][type=radio]").on("change", function(e){
                e.preventDefault();
                if($(this).length && $(this).prop("checked")){
                    $('#progressBarModal').modal('show');
                    $.ajax({
                        url: "{{ route('consultant.management.open.rfp.award.consultant', [$vendorCategoryRfp->id]) }}",
                        method: 'POST',
                        data: {
                            _token: '{{{ csrf_token() }}}',
                            id: '{{{ $openRfp->id }}}',
                            cid: parseInt($(this).val())
                        },
                        success: function(data){
                            $('#progressBarModal').modal('hide');
                            if(data['success'] == 'success') {
                                setTimeout(function(){
                                    location.reload();
                                }, 100);
                            }
                        },
                        error: function(jqXHR,textStatus, errorThrown ){
                            $('#progressBarModal').modal('hide');
                        }
                    });
                }
            });
            @endif
        }
    });
});
</script>
@endsection
