@extends('layout.main')

@section('css')
<style>
    .parent-clause-numbering {
        vertical-align: text-top;
        font-size: 13px;
    }

    .contents {
        padding-left: 10px;
        font-size: 12px;
    }

    .no-left-padding {
        padding-left: 0;
    }

    .standard-font-size {
        font-size: 12px;
    }

    .signature-padding {
        padding-left: 20px;
    }
    
    .new-page {
        page-break-before: always;
    }

    .bolded {
        font-weight: bold;
    }

    .root-clause-spacing {
        padding-top: 14px;
        padding-bottom: 10px;
    }

    .child-clause-spacing {
        padding-bottom: 10px;
    }
</style>
@endsection

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
            <i class="fa fa-file-code"></i> {{{ trans('general.letterOfAppointment') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <?php
                switch($letterOfAward->status)
                {
                    case PCK\ConsultantManagement\LetterOfAward::STATUS_DRAFT:
                        $bgColor = 'bg-color-red';
                        break;
                    case PCK\ConsultantManagement\LetterOfAward::STATUS_APPROVAL:
                        $bgColor = 'bg-color-yellow';
                        break;
                    default:
                        $bgColor = 'bg-color-green';
                }
                ?>
                <h2>{{{ $vendorCategoryRfp->vendorCategory->name }}} <span class="label {{$bgColor}}">{{ $letterOfAward->getStatusText() }}</span></h2>
            </header>
            <div>
                <div class="widget-body">
                    @if($letterOfAward->status == PCK\ConsultantManagement\LetterOfAward::STATUS_APPROVAL && $letterOfAward->needApprovalFromUser($user))
                    {{ Form::open(['route' => ['consultant.management.loa.verify', $vendorCategoryRfp->id], 'class' => 'smart-form']) }}
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
                        {{ Form::hidden('id', $letterOfAward->id) }}
                        {{ HTML::decode(link_to_route('consultant.management.loa.preview', '<i class="fa fa-eye"></i> '.trans('general.preview'), [$vendorCategoryRfp->id], ['class' => 'btn btn-info', 'target'=>"_blank"])) }}
                        {{ Form::button('<i class="fa fa-times-circle"></i> '.trans('forms.reject'), ['type' => 'submit', 'name'=>'reject', 'value'=>0, 'class' => 'btn btn-danger'] )  }}
                        {{ Form::button('<i class="fa fa-check-circle"></i> '.trans('forms.approve'), ['type' => 'submit', 'name'=>'approve', 'value'=>1, 'class' => 'btn btn-success'] )  }}
                    </footer>
                    {{ Form::close() }}
                    @endif
                    
                    <div class="row">
                        <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <ul id="consultant-management-loa-tabs" class="nav nav-tabs bordered">
                                <li class="active">
                                    <a href="#consultant-management-loa-tab-letter-head" data-toggle="tab"><i class="fa fa-fw fa-lg fa-certificate"></i> {{{ trans('letterOfAward.letterHead') }}}</a>
                                </li>
                                <li>
                                    <a href="#consultant-management-loa-tab-clauses" data-toggle="tab"><i class="fa fa-fw fa-lg fa-align-left"></i> {{{ trans('letterOfAward.clauses') }}}</a>
                                </li>
                                <li>
                                    <a href="#consultant-management-loa-tab-signatory" data-toggle="tab"><i class="fa fa-fw fa-lg fa-signature"></i> {{{ trans('letterOfAward.signatory') }}}</a>
                                </li>
                            </ul>
                            <div id="consultant-management-load-tab-content" class="tab-content padding-10">
                                <div class="tab-pane fade in active " id="consultant-management-loa-tab-letter-head">
                                    @if(strlen($letterOfAward->letterhead) > 0)
                                    <div class="well">
                                    {{ $letterOfAward->letterhead }}
                                    </div>
                                    @else
                                    <div class="alert text-middle text-center alert-warning">Header is empty</div>
                                    @endif
                                </div>
                                <div class="tab-pane fade in" id="consultant-management-loa-tab-clauses">
                                    @if($letterOfAward->clauses->count() > 0)
                                    <div class="well">
                                        <table>{{$clauseHtml}}</table>
                                    </div>
                                    @else
                                    <div class="alert text-middle text-center alert-warning">Clauses is empty</div>
                                    @endif
                                </div>
                                <div class="tab-pane fade in" id="consultant-management-loa-tab-signatory">
                                    @if(strlen($letterOfAward->signatory) > 0)
                                    <div class="well">
                                    {{ $letterOfAward->signatory }}
                                    </div>
                                    @else
                                    <div class="alert text-middle text-center alert-warning">Signatory is empty</div>
                                    @endif
                                </div>
                            </div>
                        </section>
                    </div>

                    @if($letterOfAward->status == PCK\ConsultantManagement\LetterOfAward::STATUS_APPROVED)
                    <br />
                    <div class="row">
                        <div class="col col-lg-12">
                            <div class="pull-right">
                                {{ HTML::decode(link_to_route('consultant.management.loa.print', '<i class="fa fa-print"></i> '.trans('general.print'), [$vendorCategoryRfp->id], ['class' => 'btn btn-success', 'target'=>"_blank"])) }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget">
            <header>
                <h2><i class="fa fa-paperclip"></i> {{{ trans('general.attachments') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(['route' => ['consultant.management.loa.attachment.store', $vendorCategoryRfp->id], 'id'=>'loa_attachment-form', 'class' => 'smart-form']) }}
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{{ trans('general.title') }}} <span class="required">*</span>:</label>
                            <label class="input {{{ $errors->has('title') ? 'state-error' : null }}}">
                                {{ Form::text('title', Input::old('title'), ['id'=>'loa_attachment-title', 'required' => 'required', 'autofocus' => 'autofocus']) }}
                            </label>
                            {{ $errors->first('title', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                    <footer>
                        {{ Form::hidden('id', -1, ['id'=>'loa_attachment-id']) }}
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                    </footer>
                    {{ Form::close() }}
                    <hr class="simple">
                    <div class="row">
                        <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <div id="attachments-table"></div>
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
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="loaAttachmentUploadModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => ['consultant.management.loa.attachment.upload', $vendorCategoryRfp->id], 'id' => 'loa_attachment-upload-form', 'method' => 'post', 'enctype' => "multipart/form-data"]) }}
            <div class="modal-header">
                <h6 class="modal-title"><i class="fa fa-upload"></i> {{trans('forms.upload')}}</h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body smart-form" style="padding:4px;">
                <div class="well">
                    <section>
                        <label class="label" for="loa_attachment-upload-file">Attachment <span class="required">*</span>:</label>
                        <input type="file" name="loa_attachment-upload-file" id="loa_attachment-upload-file" required>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="loa_attachment-upload-button" type="submit"><i class="fa fa-upload"></i> {{trans('forms.upload')}}</button>
            </div>
            {{ Form::hidden('id', -1, ['id'=>'loa_attachment-upload-attachment_id']) }}
            {{ Form::close() }}
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="<?php echo asset('js/app/app.restfulDelete.js'); ?>"></script>
<script type="text/javascript">
$(document).ready(function () {
    var verifierLogTable = new Tabulator('#verifier_logs-table', {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.loa.verifier.ajax.log', [$vendorCategoryRfp->id]) }}",
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

    new Tabulator('#attachments-table', {
        height:320,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.loa.attachment.list', [$vendorCategoryRfp->id]) }}",
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
            {title:"Uploaded Document", field: "attachment_filename", width:320, cssClass:"text-left", hozAlign:"left", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        return (rowData.attachment_filename && rowData.attachment_filename.length) ? '<a href="'+rowData['route:download']+'" class="plain">'+rowData.attachment_filename+'</a>' : '';
                    }
                }]
            }}
            ,{title:"{{ trans('general.actions') }}", field:"id", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        var content = '<button type="button" class="btn btn-xs btn-primary" data-id="'+rowData.id+'" onclick="editAttachment('+rowData.id+')" title="{{{ trans("general.edit") }}}"><i class="fa fa-edit"></i></button>';
                            content += '&nbsp;<button type="button" class="btn btn-xs btn-success" data-id="'+rowData.id+'" onclick="uploadAttachment('+rowData.id+')" title="{{{ trans("forms.upload") }}}"><i class="fa fa-upload"></i></button>';
                            content += '&nbsp;<a href="'+rowData['route:delete']+'" class="btn btn-xs btn-danger" data-id="'+rowData.id+'" data-id="'+rowData.id+'" data-method="delete" data-csrf_token="{{csrf_token()}}" title="{{{ trans("forms.delete") }}}"><i class="fa fa-trash"></i></a>';

                        return content;
                    }
                }]
            }}
        ]
    });
});

function editAttachment(id){
    var url = "{{route('consultant.management.loa.attachment.info', [$vendorCategoryRfp->id, ':id'])}}";
    url = url.replace(':id', parseInt(id));

    $.get(url)
    .done(function(data){
        $('#loa_attachment-form').find('#loa_attachment-id').val(data.id);
        $('#loa_attachment-form').find('#loa_attachment-title').focus().val(data.title);
    })
    .fail(function(data){
        console.error('failed');
    });
}
function uploadAttachment(id){
    $('#loaAttachmentUploadModal').modal('show');
    $('#loa_attachment-upload-form').find('#loa_attachment-upload-attachment_id').val(id);
}
</script>
@endsection