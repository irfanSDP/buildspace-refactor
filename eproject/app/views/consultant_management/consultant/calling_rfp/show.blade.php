@extends('layout.main')

<?php
$currencyCode = empty($consultantManagementContract->modified_currency_code) ? $consultantManagementContract->country->currency_code : $consultantManagementContract->modified_currency_code;
$companyName = ($user->company_id) ? $user->company->name : null;
$contactPerson = ($user->company_id) ? $user->company->main_contact : null;
$contactNumber = ($user->company_id) ? $user->company->telephone_number : null;
$email = ($user->company_id) ? $user->company->email : null;
?>

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
            <i class="fa fa-table"></i> {{{ trans('general.callingRFP') }}} {{{trans('documentManagementFolders.revision')}}} {{$callingRfp->consultantManagementRfpRevision->revision}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col col-md-12 col-lg-12">
        <div class="alert alert-warning text-center">
            <i class="fa-fw fa fa-lg fa-exclamation-triangle"></i>
            {{ trans('tenders.submissionDeadline') }}:
            <strong>
                {{{ $consultantManagementContract->getContractTimeZoneTime($callingRfp->closing_rfp_date) }}}
            </strong>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <div>
                <div class="widget-body no-padding">
                    <ul id="consultant-management-contract-tabs" class="nav nav-tabs">
                        <li class="active">
                            <a href="#consultant-management-contract-tab-main-info" data-toggle="tab"><i class="fa fa-fw fa-lg fa-info-circle"></i> {{{ trans('projects.mainInformation') }}}</a>
                        </li>
                        @foreach($consultantManagementContract->consultantManagementSubsidiaries as $key => $consultantManagementSubsidiary)
                        <li>
                            <a href="#consultant-management-subsidiaries-tab-{{$consultantManagementSubsidiary->id}}" title="{{{ $consultantManagementSubsidiary->subsidiary->name}}}" data-toggle="tab"><i class="fa fa-fw fa-lg fa-file-contract"></i> {{{ $consultantManagementSubsidiary->subsidiary->short_name}}}</a>
                        </li>
                        @endforeach
                    </ul>
                    <div id="consultant-management-contract-tab-content" class="tab-content padding-10">
                        <div class="tab-pane fade in active " id="consultant-management-contract-tab-main-info">
                            @include('consultant_management.contracts.partials.main_info')
                        </div>
                        @foreach($consultantManagementContract->consultantManagementSubsidiaries as $key => $consultantManagementSubsidiary)
                        <div class="tab-pane fade in " id="consultant-management-subsidiaries-tab-{{$consultantManagementSubsidiary->id}}">
                            @include('consultant_management.consultant.calling_rfp.partials.phase_form')
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <div>
                <div class="widget-body no-padding">
                    <ul id="consultant-management-consultant-tabs" class="nav nav-tabs">
                        <li class="active">
                            <a href="#consultant-management-consultant-tab-attachment" data-toggle="tab"><i class="fa fa-fw  fa-lg fa-paperclip"></i> {{{ trans('general.attachments') }}}</a>
                        </li>
                        <li>
                            <a href="#consultant-management-consultant-tab-rfp-document" data-toggle="tab"><i class="fa fa-fw fa-lg fa-folder-open"></i> RFP Document(s)</a>
                        </li>
                    </ul>
                    <div id="consultant-management-consultant-tab-content" class="tab-content padding-10">
                        <div class="tab-pane fade in active " id="consultant-management-consultant-tab-attachment">
                            <ol class="breadcrumb" id="attachment-breadcrumb">
                                <li><a href="javascript:void(0)" id="attachment-breadcrumb-home" onclick="goToStorage('home', '')"><i class="fa fa-lg fa-hdd"></i></a></li>
                            </ol>
                            <div style="padding-bottom:8px;display:none;" id="attachment_upload-container">
                                <button type="button" class="btn btn-success" id="attachment_upload-btn">
                                    <i class="fas fa-upload fa-md"></i> {{ trans('forms.upload') }}
                                </button>
                            </div>
                            <div id="attachment-table"></div>
                        </div>
                        <div class="tab-pane fade in " id="consultant-management-consultant-tab-rfp-document">
                            <div id="rfp_documents-table"></div>
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
                <h2> <i class="fa fa-fw fa-file"></i> Common Information </h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(['route' => ['consultant.management.consultant.calling.rfp.common.info.store'], 'class' => 'smart-form']) }}
                    <div class="row">
                        <section class="col col-xs-4 col-sm-4 col-md-4 col-lg-4">
                            <label class="label"> Name to be in LOA <span class="required">*</span>:</label>
                            <label class="input {{{ $errors->has('name_in_loa') ? 'state-error' : null }}}">
                                {{ Form::text('name_in_loa', Input::old('name_in_loa', (isset($consultantRfp) && $consultantRfp->commonInformation) ? $consultantRfp->commonInformation->name_in_loa : $companyName), ['required'=>'required']) }}
                            </label>
                            {{ $errors->first('name_in_loa', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{{ trans('vendorManagement.remarks') }}}:</label>
                            <label class="textarea {{{ $errors->has('remarks') ? 'state-error' : null }}}">
                                {{ Form::textarea('remarks', Input::old('remarks', (isset($consultantRfp) && $consultantRfp->commonInformation) ? $consultantRfp->commonInformation->remarks : null), ['rows' => 3]) }}
                            </label>
                            {{ $errors->first('remarks', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                    <div class="well">
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">Provision of contact details for authorised personnel in the event of fee clarification, verification and other possible araising matters:</label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-4 col-sm-4 col-md-4 col-lg-4">
                                <label class="label"> {{{ trans('users.name') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('contact_name') ? 'state-error' : null }}}">
                                    {{ Form::text('contact_name', Input::old('contact_name', (isset($consultantRfp) && $consultantRfp->commonInformation) ? $consultantRfp->commonInformation->contact_name : $contactPerson), ['required'=>'required']) }}
                                </label>
                                {{ $errors->first('contact_name', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-4 col-sm-4 col-md-4 col-lg-4">
                                <label class="label"> {{{ trans('users.contactNumber') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('contact_number') ? 'state-error' : null }}}">
                                    {{ Form::text('contact_number', Input::old('contact_number', (isset($consultantRfp) && $consultantRfp->commonInformation) ? $consultantRfp->commonInformation->contact_number : $contactNumber), ['required'=>'required']) }}
                                </label>
                                {{ $errors->first('contact_number', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-4 col-sm-4 col-md-4 col-lg-4">
                                <label class="label"> {{{ trans('companies.email') }}} :</label>
                                <label class="input {{{ $errors->has('contact_email') ? 'state-error' : null }}}">
                                    {{ Form::text('contact_email', Input::old('contact_email', (isset($consultantRfp) && $consultantRfp->commonInformation) ? $consultantRfp->commonInformation->contact_email : $email)) }}
                                </label>
                                {{ $errors->first('contact_email', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                    </div>
                    <footer>
                        {{ Form::hidden('id', $callingRfp->id) }}
                        {{ link_to_route('consultant.management.consultant.calling.rfp.index', trans('forms.back'), [], ['class' => 'btn btn-default']) }}
                        {{ Form::button('<i class="fa fa-upload"></i> '.trans('forms.submit'), ['type' => 'submit', 'class' => 'btn btn-success'] )  }}
                    </footer>
                    {{ Form::hidden('id', $callingRfp->id) }}
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uploadAttachmentModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ trans('forms.attachments') }}</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                {{ Form::open(array('id' => 'attachment-upload-form', 'class' => 'smart-form', 'method' => 'post', 'files' => true)) }}
                    <section>
                        <label class="label">{{{ trans('forms.upload') }}}:</label>
                        {{ $errors->first('uploaded_files', '<em class="invalid">:message</em>') }}

                        @include('file_uploads.partials.upload_file_modal', ['id' => 'consultant_attachment-upload'])
                    </section>
                {{ Form::close() }}
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-action="submit-attachments"><i class="fa fa-upload"></i> {{ trans('forms.submit') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript">
var attachmentTbl;
var validateInputFormatter = function(cell, formatterParams, onRendered){
    var rowData = cell.getRow().getData();
    return app_tabulator_utilities.variableHtmlFormatter(cell, formatterParams, onRendered);
};

$(document).ready(function () {

    @if($vendorCategoryRfp->cost_type != PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp::COST_TYPE_LUMP_SUM_COST)
    $(".proposed_fee_percentage-input").on('input', function(e){
        var id = $(this).data('id');
        var cost = 0;
        @if($vendorCategoryRfp->cost_type == PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp::COST_TYPE_CONSTRUCTION_COST)
        cost = $('#hidden_total_construction_cost-'+id).val();
        @elseif($vendorCategoryRfp->cost_type == PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp::COST_TYPE_LANDSCAPE_COST)
        cost = $('#hidden_total_landscape_cost-'+id).val();
        @endif

        var amount = cost * ($(this).val()/100);
        $('#proposed_fee_amount-'+id+'-input').val($.number(amount, 2, '.', ''));
    });
    @endif

    $(".proposed_fee_amount-input").on('input', function(e){
        var id = $(this).data('id');
        var cost = 0;
        @if($vendorCategoryRfp->cost_type == PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp::COST_TYPE_CONSTRUCTION_COST)
        cost = $('#hidden_total_construction_cost-'+id).val();
        @elseif($vendorCategoryRfp->cost_type == PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp::COST_TYPE_LANDSCAPE_COST)
        cost = $('#hidden_total_landscape_cost-'+id).val();
        @endif

        var percentage = (cost != 0) ? ($(this).val()/cost) * 100 : 0;
        $('#proposed_fee_percentage-'+id+'-input').val($.number(percentage, 2, '.', ''));
    });

    var documentTbl = new Tabulator('#rfp_documents-table', {
        placeholder: "{{ trans('general.noRecordsFound') }}",
        height: 420,
        ajaxURL: "{{ route('consultant.management.consultant.rfp.documents.ajax.list', [$vendorCategoryRfp->id, $user->company_id]) }}",
        ajaxConfig: "GET",
        layout:"fitColumns",
        responsiveLayout:'collapse',
        columns:[
            {title:"&nbsp;", field:"title", minWidth: 300, hozAlign:"left", headerSort:false, formatter:function(cell, formatterParams, onRendered){
                var data = cell.getData();
                return '<label class="text-success" style="font-size:14px;"><i class="fa-lg far fa-file"></i></label>&nbsp;&nbsp;' + cell.getValue();
            }},
            {title:"{{ trans('general.remarks') }}", field:"remarks", width: 380, hozAlign:"left", headerSort:false, formatter:validateInputFormatter,
                formatterParams: {
                    tag: 'div',
                    innerHtml: function(rowData){
                        return rowData.remarks.length ? '<div class="well" style="white-space: pre-wrap;">'+rowData.remarks+'</div>' : "";
                    }
                }
            },
            {title:"{{ trans('general.type') }}", field:"extension", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.actions') }}", field:"id", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        return '<a href="'+rowData['route:download']+'" class="btn btn-xs btn-primary" title="{{{ trans("general.download") }}}"><i class="fa fa-download"></i></a>';
                    }
                }]
            }}
        ],
    });

    attachmentTbl = new Tabulator('#attachment-table', {
        placeholder: "{{ trans('general.noRecordsFound') }}",
        height: 320,
        ajaxURL: "{{ route('consultant.management.consultant.attachment.directory.ajax.list', [$vendorCategoryRfp->id, $user->company_id]) }}",
        ajaxConfig: "GET",
        layout:"fitColumns",
        selectable: 1,
        responsiveLayout:'collapse',
        columns:[
            {title:"&nbsp;", field:"title", minWidth: 300, hozAlign:"left", headerSort:false, formatter:function(cell, formatterParams, onRendered){
                var data = cell.getData();
                if(data.type=='dir'){
                    return '<label class="text-warning" style="font-size:14px;"><i class="fa-lg fas fa-folder"></i></label>&nbsp;&nbsp;' + cell.getValue();
                }else{
                    return '<label class="text-success" style="font-size:14px;"><i class="fa-lg far fa-file"></i></label>&nbsp;&nbsp;' + cell.getValue();
                }
            }},
            {title:"{{ trans('general.remarks') }}", field:"remarks", width: 380, hozAlign:"left", visible:false, headerSort:false},
            {title:"Mandatory", field:"mandatory", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:'mandatory'},
            {title:"{{ trans('general.type') }}", field:"extension", width: 100, hozAlign:"center", visible:false, cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.actions') }}", field:"id", width: 100, hozAlign:"center", visible:false, cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        if(rowData.deletable){
                            return '<button class="btn btn-xs btn-danger" data-id="'+rowData.id+'" onclick="attachmentDelete(\''+rowData['route:delete']+'\')"><i class="fa fa-trash"></i></button>';
                        }

                        return '<button type="button" class="btn btn-xs invisible"><i class="fa fa-trash"></i></button>';
                    }
                }]
            }}
        ],
        cellClick:function(e, cell){
            var field = cell.getField();
            if(field == "title"){
                onCellClick(e, cell);
            }
        }
    });
});

Tabulator.prototype.extendModule("format", "formatters", {
    mandatory:function(cell, formatterParams){
        cell.getElement().style.whiteSpace = "pre-wrap";
        var obj = cell.getRow().getData();
        var str = (obj.mandatory) ? '<i class="fas fa-lg fa-fw fa-check-circle text-success"></i>' : '<i class="fas fa-lg fa-fw fa-times-circle text-danger"></i>';
        return this.emptyToSpace(str);
    }
});

function onCellClick(e, cell){
    var row = cell.getRow();
    row.select();
    var data = row.getData();
    if(data.type == 'dir'){
        var params = {id:data.id};
        $("#attachment-breadcrumb").append('<li class="attachment-sub"><a id="attachment-sub-'+data.id+'" href="javascript:void(0)" onclick="goToStorage(\''+data.id+'\', \''+data.title+'\')">'+data.title+'</a></li>');
        row.getTable().setData("{{ route('consultant.management.consultant.attachment.ajax.list', [$vendorCategoryRfp->id, $user->company_id]) }}", params).then(function(){
            $("#attachment_upload-container").show();
            $("#attachment_upload-btn").off('click');//reset click from previous callback set
            $("[data-action=submit-attachments]").off('click');//reset click from previous callback set

            $("#attachment_upload-btn").on('click', function(e){
                $('#uploadAttachmentModal').modal('show');
            });

            $("[data-action=submit-attachments]").on('click', function(e){
                e.preventDefault();

                var uploadedFilesInput = [];

                $('form#attachment-upload-form input[name="uploaded_files[]"]').each(function(index){
                    uploadedFilesInput.push($(this).val());
                });

                app_progressBar.show();

                $.post("{{route('consultant.management.consultant.attachment.upload', [$vendorCategoryRfp->id, $user->company_id])}}",{
                    _token: "{{{csrf_token()}}}",
                    id: data.id,
                    uploaded_files: uploadedFilesInput
                })
                .done(function(data){
                    if(data.success){
                        $(".template-download").remove();
                        $('#uploadAttachmentModal').modal('hide');
                        row.getTable().setData();
                    }
                    app_progressBar.maxOut(null, function(){ app_progressBar.hide(); });
                })
                .fail(function(data){
                    console.error('failed');
                });
            });

            attachmentTbl.showColumn('extension');
            attachmentTbl.showColumn('id');
            attachmentTbl.hideColumn('mandatory');
            attachmentTbl.redraw();
        });
    }else{
        window.open(data['route:download'], '_blank');
    }
}
function goToStorage(id, title){
    if(id=='home'){//only 1 level
        $("#attachment-breadcrumb-home").parent().nextAll("li.attachment-sub").remove();
        $("#attachment_upload-container").hide();

        if(attachmentTbl){
            attachmentTbl.setData("{{ route('consultant.management.consultant.attachment.directory.ajax.list', [$vendorCategoryRfp->id, $user->company_id]) }}").then(function(){
                attachmentTbl.hideColumn('extension');
                attachmentTbl.hideColumn('id');
                attachmentTbl.showColumn('mandatory');

                attachmentTbl.redraw();
            });
        }
    }
}
function attachmentDelete(route){
    var r = confirm('Are you sure you want to delete this record?');
    if (r == true) {
        $.ajax({
            url: route,
            type: 'DELETE',
            data: {
                _token:'{{{csrf_token()}}}'
            },
            success: function(result) {
                if(result.success && attachmentTbl){
                    attachmentTbl.setData();//reload
                }
            }
        });
    }
}
</script>
@endsection