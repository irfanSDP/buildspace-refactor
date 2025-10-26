@extends('layout.main')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/smartwizard@5/dist/css/smart_wizard_all.min.css" rel="stylesheet" type="text/css" />
@endsection

<?php
$currencyCode = empty($consultantManagementContract->modified_currency_code) ? $consultantManagementContract->country->currency_code : $consultantManagementContract->modified_currency_code;
?>

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{ link_to_route('consultant.management.open.rfp.show', $vendorCategoryRfp->vendorCategory->name, [$vendorCategoryRfp->id, $openRfp->id]) }}</li>
        <li>{{{ trans('general.approvalDocument') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-file-contract"></i> {{{ trans('general.approvalDocument') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
        @if(isset($approvalDocument) && $approvalDocument->status != PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT)
        <div class="btn-group pull-right header-btn">
            {{ HTML::decode(link_to_route('consultant.management.approval.document.print', '<i class="fa fa-print"></i> '.trans('general.print'), [$vendorCategoryRfp->id, $openRfp->id], ['class' => 'btn btn-info'])) }}
        </div>
        
        @endif
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <?php
                if(isset($approvalDocument))
                {
                    switch($approvalDocument->status)
                    {
                        case PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT:
                            $bgColor = 'bg-color-red';
                            break;
                        case PCK\ConsultantManagement\ApprovalDocument::STATUS_APPROVAL:
                            $bgColor = 'bg-color-yellow';
                            break;
                        default:
                            $bgColor = 'bg-color-green';
                    }
                }
                ?>
                <h2>{{{ trans('general.approvalDocument') }}} @if(isset($approvalDocument)) <span class="label {{$bgColor}}">{{ $approvalDocument->getStatusText() }}</span>@endif</h2>
            </header>
            <div>
                <div class="widget-body">
                    @if(!isset($approvalDocument) or $approvalDocument->status == PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT)
                    {{ Form::open(['route' => ['consultant.management.approval.document.store', $vendorCategoryRfp->id], 'class' => 'smart-form']) }}
                    <div class="row">
                        <section class="col col-xs-12 col-md-6 col-lg-6">
                            <label class="label">Document Ref No. <span class="required">*</span>:</label>
                            <label class="input {{{ ($errors->has('document_reference_no') or $errors->has('document_invalid')) ? 'state-error' : null }}}">
                                {{ Form::text('document_reference_no', Input::old('document_reference_no', isset($approvalDocument) ? $approvalDocument->document_reference_no : null), ['required' => 'required', 'autofocus' => 'autofocus']) }}
                            </label>
                            {{ $errors->first('document_reference_no', '<em class="invalid">:message</em>') }}
                            {{ $errors->first('document_invalid', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                    <footer>
                        {{ Form::hidden('id', isset($approvalDocument) ? $approvalDocument->id : -1) }}
                        {{ Form::hidden('open_rfp_id', $openRfp->id) }}
                        {{ link_to_route('consultant.management.open.rfp.show', trans('forms.back'), [$vendorCategoryRfp->id, $openRfp->id], ['class' => 'btn btn-default']) }}
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                    </footer>
                    {{ Form::close() }}
                    @else
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>Document Ref No:</dt>
                                <dd>{{{$approvalDocument->document_reference_no}}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </section>
                    </div>
                        @if($approvalDocument->status == PCK\ConsultantManagement\ApprovalDocument::STATUS_APPROVAL && $approvalDocument->needApprovalFromUser($user))
                        {{ Form::open(['route' => ['consultant.management.approval.document.verify', $vendorCategoryRfp->id], 'class' => 'smart-form']) }}
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('vendorManagement.remarks') }}}:</label>
                                <label class="textarea {{{ ($errors->has('remarks')) ? 'state-error' : null }}}">
                                    {{ Form::textarea('remarks', Input::old('remarks'), ['rows' => 3]) }}
                                </label>
                                {{ $errors->first('remarks', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <footer>
                            {{ Form::hidden('id', $approvalDocument->id) }}
                            {{ Form::hidden('open_rfp_id', $openRfp->id) }}
                            {{ link_to_route('consultant.management.open.rfp.show', trans('forms.back'), [$vendorCategoryRfp->id, $openRfp->id], ['class' => 'btn btn-default']) }}
                            {{ Form::button('<i class="fa fa-times-circle"></i> '.trans('forms.reject'), ['type' => 'submit', 'name'=>'reject', 'value'=>0, 'class' => 'btn btn-danger'] )  }}
                            {{ Form::button('<i class="fa fa-check-circle"></i> '.trans('forms.approve'), ['type' => 'submit', 'name'=>'approve', 'value'=>1, 'class' => 'btn btn-success'] )  }}
                        </footer>
                        {{ Form::close() }}
                        @else
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <div class="pull-right">
                                    {{ link_to_route('consultant.management.open.rfp.show', trans('forms.back'), [$vendorCategoryRfp->id, $openRfp->id], ['class' => 'btn btn-default']) }}
                                </div>
                            </section>
                        </div>
                        @endif
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

@if(isset($approvalDocument))
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2><i class="fa fa-list"></i> Sections</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <div id="smartwizard">
                                <ul class="nav">
                                    <li>
                                        <a class="nav-link" href="#section-a">{{{ trans('formBuilder.section') }}} A</a>
                                    </li>
                                    <li>
                                        <a class="nav-link" href="#section-b">{{{ trans('formBuilder.section') }}} B</a>
                                    </li>
                                    <li>
                                        <a class="nav-link" href="#section-c">{{{ trans('formBuilder.section') }}} C</a>
                                    </li>
                                    <li>
                                        <a class="nav-link" href="#section-d">{{{ trans('formBuilder.section') }}} D</a>
                                    </li>
                                    <li>
                                        <a class="nav-link" href="#section-appendix">Appendix</a>
                                    </li>
                                </ul>
                                
                                <div class="tab-content">
                                    <div id="section-a" class="tab-pane well" role="tabpanel">
                                        @include('consultant_management.approval_document.partials.section_a')
                                    </div>
                                    <div id="section-b" class="tab-pane well" role="tabpanel">
                                        @include('consultant_management.approval_document.partials.section_b')
                                    </div>
                                    <div id="section-c" class="tab-pane well" role="tabpanel">
                                        @include('consultant_management.approval_document.partials.section_c')
                                    </div>
                                    <div id="section-d" class="tab-pane well" role="tabpanel">
                                        @include('consultant_management.approval_document.partials.section_d')
                                    </div>
                                    <div id="section-appendix" class="tab-pane well" role="tabpanel">
                                        @include('consultant_management.approval_document.partials.section_appendix')
                                    </div>
                                </div>
                            </div>

                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(isset($approvalDocument))
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2><i class="fa fa-user-tie"></i> {{{ trans('verifiers.verifiers') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <h1 class="page-title txt-color-blueDark">{{{ trans('verifiers.verifierLogs') }}}</h1>
                            <div id="verifier_logs-table">
                        </section>
                    </div>
                    @if($approvalDocument->status == PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT)
                    <hr class="simple">
                    {{ Form::open(['route' => ['consultant.management.approval.document.verifier.store', $vendorCategoryRfp->id], 'class' => 'smart-form']) }}
                    <div class="row">
                        <section class="col col-xs-12 col-md-6 col-lg-6">
                            @include('verifiers.select_verifiers', array(
                                'verifiers' => $verifiers,
                                'selectedVerifiers' => $selectedVerifiers,
                            ))
                            <label class="input {{{ $errors->has('verifiers') ? 'state-error' : null }}}"></label>
                            {{ $errors->first('verifiers', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                    <footer>
                        {{ Form::hidden('id', $approvalDocument->id) }}
                        {{ Form::hidden('open_rfp_id', $openRfp->id) }}
                        {{ Form::button('<i class="fa fa-upload"></i> '.trans('forms.submit'), ['type' => 'submit', 'name'=>'send_to_verify', 'value'=>1, 'class' => 'btn btn-success'] )  }}
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                    </footer>
                    {{ Form::close() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(isset($approvalDocument) && $approvalDocument->status == PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT)
<div class="modal fade" id="remarkInputModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => ['consultant.management.approval.document.section.c.store', $vendorCategoryRfp->id], 'id' => 'document_remarks-form', 'method' => 'post']) }}
            <div class="modal-header">
                <h6 class="modal-title"><i class="fa fa-pencil-alt"></i> {{trans('general.remarks')}}</h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body smart-form" style="padding:4px;">
                <section>
                    <label class="label" for="remarks-input"></label>
                    <label class="textarea ">
                        <textarea rows="1" autofocus="autofocus" name="remarks" id="remarks-input" cols="50"></textarea>
                    </label>
                </section>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="remarks-save-button" type="submit"><i class="fa fa-save"></i> {{trans('forms.save')}}</button>
            </div>
            {{ Form::hidden('open_rfp_id', $openRfp->id) }}
            {{ Form::hidden('cm_subsidiary_id', -1, ['id'=>'cm_subsidiary_id']) }}
            {{ Form::hidden('cid', -1, ['id'=>'consultant-company_id']) }}
            {{ Form::close() }}
        </div>
    </div>
</div>

<div class="modal fade" id="appendixDetailsUploadModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => ['consultant.management.approval.document.section.appendix.attachment.upload', $vendorCategoryRfp->id], 'id' => 'appendix_details-attachment-form', 'method' => 'post', 'enctype' => "multipart/form-data"]) }}
            <div class="modal-header">
                <h6 class="modal-title"><i class="fa fa-upload"></i> {{trans('forms.upload')}}</h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body smart-form" style="padding:4px;">
                <div class="well">
                    <section>
                        <label class="label" for="appendix_details-attachment">Attachment <span class="required">*</span>:</label>
                        <input type="file" name="appendix_details-attachment" id="appendix_details-attachment" required>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="appendix_details-attachment-button" type="submit"><i class="fa fa-upload"></i> {{trans('forms.upload')}}</button>
            </div>
            {{ Form::hidden('open_rfp_id', $openRfp->id) }}
            {{ Form::hidden('id', -1, ['id'=>'attachment-appendix_details_id']) }}
            {{ Form::close() }}
        </div>
    </div>
</div>
@endif
@include('consultant_management.partials.vendor_profile.modal')

@endsection

@section('js')
<script src="<?php echo asset('js/app/app.restfulDelete.js'); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/smartwizard@5/dist/js/jquery.smartWizard.min.js" type="text/javascript"></script>
<script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
@include('consultant_management.partials.vendor_profile.modal_javascript')
<script type="text/javascript">
Tabulator.prototype.extendModule("format", "formatters", {
    textarea:function(cell, formatterParams){
        cell.getElement().style.whiteSpace = "pre-wrap";
        var obj = cell.getRow().getData();
        var str = '<div>' +this.sanitizeHTML(obj.remarks)+ '</div>';
        return this.emptyToSpace(str);
    },
    validateInputFormatter: function(cell, formatterParams, onRendered){
        var rowData = cell.getRow().getData();
        return app_tabulator_utilities.variableHtmlFormatter(cell, formatterParams, onRendered);
    }
});
$(document).ready(function () {

    $('#smartwizard').smartWizard({
        selected: {{$selectedWizardStep}}, // Initial selected step, 0 = first step
        theme: 'dots',
        autoAdjustHeight: false,
        anchorSettings: {
            anchorClickable: true,
            enableAllAnchors: true
        },
        toolbarSettings: {
            showNextButton: false,
            showPreviousButton: false
        }
    });

    @if(isset($approvalDocument))
    var verifierLogTable = new Tabulator('#verifier_logs-table', {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.approval.document.verifier.ajax.log', [$vendorCategoryRfp->id, $openRfp->id, $approvalDocument->id]) }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
            {title:"{{ trans('documentManagementFolders.revision') }}", field:"version", width:80, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.status') }}", field:"status_txt", width:120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.remarks') }}", field:"remarks", width:380, hozAlign:'left', headerSort:false, formatter:'textarea'},
            {title:"{{ trans('general.updatedAt') }}", field:"updated_at", width:160, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false}
        ]
    });
    

    @foreach($consultantManagementContract->consultantManagementSubsidiaries as $consultantManagementSubsidiary)

    new Tabulator('#consultant-{{$consultantManagementSubsidiary->id}}-table', {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.approval.document.section.c.consultants.list', [$vendorCategoryRfp->id, $openRfp->id, $consultantManagementSubsidiary->id]) }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:40, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.name') }}", field:"company_name", minWidth: 300, hozAlign:"left", headerSort:false},
            {title:"{{{ trans('tenders.amount') }}} ({{{ $currencyCode }}})", field: "consultant_amount", width:160, cssClass:"text-right", hozAlign:"center", headerSort:false},
            {title:"{{{ trans('general.proposedFee') }}} %", field: "consultant_percentage", width:120, cssClass:"text-center",  hozAlign:"center", headerSort:false},
            {title:"{{ trans('general.remarks') }}", hozAlign:"left", width:380, headerSort:false, formatter:'validateInputFormatter',
                formatterParams: {
                    tag: 'div',
                    rowAttributes: {'data-id': 'id' },
                    attributes: {'class': 'fill consultant_remarks', 'data-type': 'remarks_view', 'data-tooltip': 'data-tooltip', 'title': "{{ trans('forms.remarks') }}", 'data-placement': 'left', 'data-action': 'remark_input_toggle'},
                    innerHtml: function(rowData){
                        var defaultTxt = @if($approvalDocument->status == PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT) '<i class="fa fa-sm fa-edit"></i> ' @else '' @endif;
                        @if($approvalDocument->status == PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT)
                        return rowData.remarks.length ? '<div class="well" style="white-space: pre-wrap;">'+defaultTxt+rowData.remarks+'</div>' : defaultTxt+'Click to enter remarks';
                        @else
                        return rowData.remarks.length ? '<div class="well" style="white-space: pre-wrap;">'+defaultTxt+rowData.remarks+'</div>' : defaultTxt;
                        @endif
                    }
                }
                @if($approvalDocument->status == PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT)
                ,cellClick:function(e, cell){
                    e.preventDefault();

                    var rowData = cell.getRow().getData();
                    var saveButton =$('#remarks-save-button');
                    saveButton.removeData('id');
                    saveButton.attr('data-id', rowData.id);

                    $('#cm_subsidiary_id').val({{$consultantManagementSubsidiary->id}});
                    $('#consultant-company_id').val(parseInt(rowData.id));

                    //populate the textarea with current remark
                    var textView = $('[data-type=remarks_view][data-id='+rowData.id+']');
                    var currentRemarks = textView.text().trim();
                    if(currentRemarks.toLowerCase() == 'click to enter remarks'){
                        currentRemarks = "";
                    }
                    var textArea = $('#remarks-input');
                    textArea.val(currentRemarks);

                    //show modal
                    $('#remarkInputModal').modal('show');
                }
                @endif
            },
            {title:"{{ trans('vendorProfile.vendorProfile') }}", field:"vendor_profile", width:100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        return '<button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#vendorProfileModal"><i class="fa fa-search"></i> {{{trans("forms.view")}}}</button>';
                    }
                }]
            }, cellClick:function(e, cell){
                var row = cell.getRow();
                var item = row.getData();

                $('#vp-vendor_categories').val(null).trigger('change');
                $('#contractor-section').hide();
                $('#consultant-section').hide();
                $('#vp-vendor_performance_evaluation-rows').empty();

                var url = "{{ route('consultant.management.vendor.profile.info', ':id')}}";
                url = url.replace(':id', parseInt(item.id));

                app_progressBar.toggle();
                $.get(url, function(data){
                    $.each(data.details, function(key,val){
                        if(key=='vendor_categories'){
                            $.each(val, function(k,v){
                                $('#vp-vendor_categories').append('<option selected>'+v+'</option>').trigger('change');
                            });
                        }else if(key != 'is_contractor' || key != 'is_consultant'){
                            $('#vp-'+key).html(val);
                        }
                    });

                    if(data.details.is_contractor){
                        $('#contractor-section').show();
                    }

                    if(data.details.is_consultant){
                        $('#consultant-section').show();
                    }

                    $.each(data.vpe_rows, function(key,row){
                        $('#vp-vendor_performance_evaluation-rows').append(row);
                    });

                    url = "{{ route('vendorProfile.company.personnel.list', [':id', \PCK\CompanyPersonnel\CompanyPersonnel::TYPE_DIRECTOR]) }}";
                    url = url.replace(':id', parseInt(item.id));
                    var CPDTable = Tabulator.prototype.findTable("#company-personnel-directors-table")[0];
                    if(CPDTable){
                        CPDTable.setData(url);
                    }

                    url = "{{ route('vendorProfile.company.personnel.list', [':id', \PCK\CompanyPersonnel\CompanyPersonnel::TYPE_SHAREHOLDERS]) }}";
                    url = url.replace(':id', parseInt(item.id));
                    var CPSTable = Tabulator.prototype.findTable("#company-personnel-shareholders-table")[0];
                    if(CPSTable){
                        CPSTable.setData(url);
                    }

                    url = "{{ route('vendorProfile.company.personnel.list', [':id', \PCK\CompanyPersonnel\CompanyPersonnel::TYPE_HEAD_OF_COMPANY]) }}";
                    url = url.replace(':id', parseInt(item.id));
                    var CPHODTable = Tabulator.prototype.findTable("#company-personnel-head-of-company-table")[0];
                    if(CPHODTable){
                        CPHODTable.setData(url);
                    }

                    url = "{{ route('vendorProfile.track.record.list', [':id', \PCK\TrackRecordProject\TrackRecordProject::TYPE_COMPLETED]) }}";
                    url = url.replace(':id', parseInt(item.id));
                    var CompPTRTable = Tabulator.prototype.findTable("#completed-project-track-record-table")[0];
                    if(CompPTRTable){
                        CompPTRTable.setData(url);
                    }

                    url = "{{ route('vendorProfile.track.record.list', [':id', \PCK\TrackRecordProject\TrackRecordProject::TYPE_CURRENT]) }}";
                    url = url.replace(':id', parseInt(item.id));
                    var CurrPTRTable = Tabulator.prototype.findTable("#current-project-track-record-table")[0];
                    if(CurrPTRTable){
                        CurrPTRTable.setData(url);
                    }

                    var preQUrl = "{{ route('consultant.management.vendor.profile.preq.list', ':id') }}";
                    preQUrl = preQUrl.replace(':id', parseInt(item.id));
                    var preQTable = Tabulator.prototype.findTable("#vendor-prequalification-table")[0];
                    if(preQTable){
                        preQTable.setData(preQUrl, {}, "GET");
                    }

                    var vwcUrl = "{{ route('vendorProfile.vendor.list', [':id']) }}";
                    vwcUrl = vwcUrl.replace(':id', parseInt(item.id));
                    var vwcTable = Tabulator.prototype.findTable("#vendor_work_categories-table")[0];
                    if(vwcTable){
                        vwcTable.setData(vwcUrl, {}, "GET");
                    }

                    var apUrl = "{{ route('vendorProfile.awardedProjects', [':id']) }}";
                    apUrl = apUrl.replace(':id', parseInt(item.id));
                    var apTable = Tabulator.prototype.findTable("#awarded-projects-table")[0];
                    if(apTable){
                        $.get(apUrl, function(data){
                            apTable.setData(data.data, {}, "GET");
                        });
                    }

                    var cpUrl = "{{ route('vendorProfile.completedProjects', [':id']) }}";
                    cpUrl = cpUrl.replace(':id', parseInt(item.id));
                    var cpTable = Tabulator.prototype.findTable("#completed-projects-table")[0];
                    if(cpTable){
                        $.get(cpUrl, function(data){
                            cpTable.setData(data.data, {}, "GET");
                        });
                    }

                    app_progressBar.maxOut();
                    app_progressBar.toggle();
                });
            }}
        ]
    });

    @endforeach

    var completedProjectTrackRecordTbl = new Tabulator('#completed-project-track-record-table', {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('vendorProfile.track.record.list', [$awardedConsultant->company_id, \PCK\TrackRecordProject\TrackRecordProject::TYPE_COMPLETED]) }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('projects.title') }}", field:"title", width: 480, hozAlign:"left", headerSort:false, headerFilter: true},
            {title:"{{ trans('propertyDevelopers.propertyDeveloper') }}", field:"property_developer_name", width: 250, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"vendor_category_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.vendorSubWorkCategory') }}", field:"vendor_work_subcategory_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.projectAmount') }}", field:"project_amount", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:"money", headerFilter: true},
            {title:"{{ trans('currencies.currency') }}", field:"currency", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.projectAmountRemarks') }}", field:"project_amount_remarks", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.yearOfSitePosession') }}", field:"year_of_site_possession", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.yearOfCompletion') }}", field:"year_of_completion", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.qlassicOrConquasScore') }}", field:"has_qlassic_or_conquas_score", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:'tick', headerFilter: true},
            {title:"{{ trans('vendorManagement.qlassicScore') }}", field:"qlassic_score", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.qlassicYearOfAchievement') }}", field:"qlassic_year_of_achievement", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.conquasScore') }}", field:"conquas_score", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.conquasYearOfAchievement') }}", field:"conquas_year_of_achievement", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.awardsReceived') }}", field:"awards_received", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.yearOfAwardsReceived') }}", field:"year_of_recognition_awards", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.remarks') }}", field:"remarks", minWidth: 320, hozAlign:"left", headerSort:false, cssClass:"text-center text-middle", headerFilter: true}
        ]
    });

    var currentProjectTrackRecordTbl = new Tabulator('#current-project-track-record-table', {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL:"{{ route('vendorProfile.track.record.list', [$awardedConsultant->company_id, \PCK\TrackRecordProject\TrackRecordProject::TYPE_CURRENT]) }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('projects.title') }}", field:"title", minWidth: 480, hozAlign:"left", headerSort:false, headerFilter: true},
            {title:"{{ trans('propertyDevelopers.propertyDeveloper') }}", field:"property_developer_name", width: 250, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"vendor_category_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.vendorSubWorkCategory') }}", field:"vendor_work_subcategory_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.projectAmount') }}", field:"project_amount", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:"money", headerFilter: true},
            {title:"{{ trans('currencies.currency') }}", field:"currency", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.projectAmountRemarks') }}", field:"project_amount_remarks", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.yearOfSitePosession') }}", field:"year_of_site_possession", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.yearOfCompletion') }}", field:"year_of_completion", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
            {title:"{{ trans('vendorManagement.remarks') }}", field:"remarks", minWidth: 320, hozAlign:"left", headerSort:false, cssClass:"text-center text-middle", headerFilter: true}
        ],
    });

    new Tabulator('#appendix-table', {
        height:320,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.approval.document.section.appendix.list', [$vendorCategoryRfp->id, $openRfp->id]) }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:40, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.title') }}", field:"title", minWidth: 300, hozAlign:"left", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        return '<div class="well">'+rowData.title+'</div>';
                    }
                }]
            }},
            {title:"Uploaded Document", field: "attachment_filename", width:300, cssClass:"text-left", hozAlign:"left", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        return (rowData.attachment_filename && rowData.attachment_filename.length) ? '<a href="'+rowData['route:download']+'" class="plain">'+rowData.attachment_filename+'</a>' : '';
                    }
                }]
            }}
            @if($approvalDocument->status == PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT)
            ,{title:"{{ trans('general.actions') }}", field:"id", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        var content = '<button type="button" class="btn btn-xs btn-primary" data-id="'+rowData.id+'" onclick="editAppendix('+rowData.id+')" title="{{{ trans("general.edit") }}}"><i class="fa fa-edit"></i></button>';
                            content += '&nbsp;<button type="button" class="btn btn-xs btn-success" data-id="'+rowData.id+'" onclick="uploadAppendix('+rowData.id+')" title="{{{ trans("forms.upload") }}}"><i class="fa fa-upload"></i></button>';
                            content += '&nbsp;<a href="'+rowData['route:delete']+'" class="btn btn-xs btn-danger" data-id="'+rowData.id+'" data-id="'+rowData.id+'" data-method="delete" data-csrf_token="{{csrf_token()}}" title="{{{ trans("forms.delete") }}}"><i class="fa fa-trash"></i></a>';

                        return content;
                    }
                }]
            }}
            @endif
        ]
    });
    @endif

    $('a.project-track-record-tab[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var id = $(e.target).attr("id");
        switch(id){
            case 'current-project-track-record-tab':
                currentProjectTrackRecordTbl.redraw(true);
                break;
            case 'completed-project-track-record-tab':
                completedProjectTrackRecordTbl.redraw(true);
                break;
        }
    });

    var itemCodeSettingsAmountInformationVue = new Vue({
        el: '#item-code-settings-amount-information',
        data: {
            totalAmount: {{ $proposedFeeAmount }},
            assignedAmount: 0,
            balance: 0,
            saveStatusLabel: "",
            labelClass: "text-success",
        },
        methods: {
            updateDisplay: function(updateAmount = true){
                this.labelClass = "text-warning";

                var total = 0;
                accountCodeProportionTable.getData().forEach(function(row){
                    return total += parseFloat(row['amount']);
                });

                var balance = this.totalAmount - total;

                itemCodeSettingsAmountInformationVue.assignedAmount = total.toLocaleString('en-US', {minimumFractionDigits: 2});
                itemCodeSettingsAmountInformationVue.balance = balance.toLocaleString('en-US', {minimumFractionDigits: 2});

                if(balance === 0){
                    this.labelClass = "text-success";
                    if(updateAmount) this.updateItemCodeSettingsAmounts();
                }
            },
            updateItemCodeSettingsAmounts: function()
            {
                var self = this;
                var data = [];

                accountCodeProportionTable.getData().forEach((row) => {
                    data.push({
                        id: row.id,
                        amount: row.amount,
                    });
                });

                $.ajax({
                    url: "{{{ route('consultant.management.approval.document.accountCodes.amount.update', $vendorCategoryRfp->id) }}}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        account_code_amounts: data,
                    },
                    success: function (data) {
                        if (data['success']) {
                            self.saveStatusLabel = "";
                            SmallErrorBox.saved("{{ trans('general.success') }}", "{{ trans('forms.saved') }}");
                        }
                        else
                        {
                            SmallErrorBox.refreshAndRetry();
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        SmallErrorBox.refreshAndRetry();
                    }
                });
            }
        }
    });

    var accountCodeProportionTable = new Tabulator('#account-codes-proportion-table', {
        height:400,
        columns: [
            { title: "{{ trans('accountCodes.accountCode') }}", field: 'accountCode', width: 150, cssClass:"text-center", align: 'center', headerSort: false },
            { title: "{{ trans('accountCodes.description') }}", field: 'description', minWidth:280, cssClass:"text-left", align: 'left', headerSort: false },
            { title: "{{ trans('accountCodes.amount') }}", field: 'amount', width: 180, cssClass:"text-right", align: 'right', headerSort: false ,
                editor:"number",
                editorParams:{
                    min:0,
                    verticalNavigation:"table"
                },
                formatter:"money", formatterParams:{
                    decimal:".",
                    thousand:",",
                    symbolAfter:"p",
                    precision:2,
                },
                cellEdited: function(cell){
                    var row   = cell.getRow();
                    var value = cell.getData()['amount'];

                    if(value == '') value = 0;

                    row.update({'amount': Math.round(value*100)/100});

                    row.reformat();

                    itemCodeSettingsAmountInformationVue.saveStatusLabel = "{{ trans('forms.editing') }}";

                    itemCodeSettingsAmountInformationVue.updateDisplay();
                }
            },
            { title: "{{ trans('accountCodes.taxCode') }}", field: 'taxCode', width: 150, cssClass:"text-center", align: 'center', headerSort: false },
        ],
        layout:"fitColumns",
        ajaxURL: "{{ route('consultant.management.approval.document.accountCodes.list', [$vendorCategoryRfp->id]) }}",
        ajaxConfig: "GET",
        placeholder:"{{ trans('general.noDataAvailable') }}",
        columnHeaderSortMulti:false,
        dataLoaded: function(){
            itemCodeSettingsAmountInformationVue.updateDisplay(false);
        },
    });
});

@if(isset($approvalDocument) && $approvalDocument->status == PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT)
function editAppendix(id){
    var url = "{{route('consultant.management.approval.document.section.appendix.details.info', [$vendorCategoryRfp->id, ':id'])}}";
    url = url.replace(':id', parseInt(id));

    $.get(url)
    .done(function(data){
        $('#appendix_details-form').find('#appendix_details-id').val(data.id);
        $('#appendix_details-form').find('#appendix_details-title').focus().val(data.title);
    })
    .fail(function(data){
        console.error('failed');
    });
}
function uploadAppendix(id){
    $('#appendixDetailsUploadModal').modal('show');
    $('#appendix_details-attachment-form').find('#attachment-appendix_details_id').val(id);
}
@endif
</script>
@endsection