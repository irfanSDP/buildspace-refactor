@extends('layout.main')
<?php use PCK\ObjectField\ObjectField; ?>
@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorManagement') }}}</li>
        <li>{{ link_to_route('vendorManagement.approval.registrationAndPreQualification', trans('vendorManagement.registrationAndPreQualification'), array()) }}</li>
        <li>{{ link_to_route('vendorManagement.approval.registrationAndPreQualification.show', $vendorRegistration->company->name, array($vendorRegistration->id)) }}</li>
        <li>{{{ trans('vendorManagement.preQualification') }}}</li>
    </ol>
@endsection

@section('css')
<style>
    .spaced {
        margin-right: 5px;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.preQualification') }}}
        </h1>
    </div>
</div>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        @if(!empty($instructionSettings->vendor_pre_qualifications))
        <div class="padded label-success text-white"><strong>{{ nl2br($instructionSettings->vendor_pre_qualifications) }}</strong></div>
        <br>
        @endif
    </div>
</div>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ $vendorRegistration->company->name }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="main-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget padded">
            <footer>
                @include('vendor_management.partials.link_to_next_registration_approval_section', ['vendorRegistration' => $vendorRegistration, 'currentSection' => 'preQualification'])
                <a href="{{ route('vendorManagement.approval.registrationAndPreQualification.show', [$vendorRegistration->id]) }}" class="btn btn-default pull-right spaced">{{ trans('forms.back') }}</a>
                @if($canUploadProcessorAttachments)
                <button type="button" class="btn btn-info pull-left spaced" data-action="upload-item-attachments" 
                        data-route-get-attachments-list="{{ route('vendorManagement.approval.processor.attachments.list', [$vendorRegistration->id, ObjectField::PROCESSOR_ATTACHMENTS_VENDOR_PREQUALIFICATIONS]) }}"
                        data-route-update-attachments="{{ route('vendorManagement.approval.processor.attachments.upload', [$vendorRegistration->id, ObjectField::PROCESSOR_ATTACHMENTS_VENDOR_PREQUALIFICATIONS]) }}"
                        data-route-get-attachments-count="{{ route('vendorManagement.approval.processor.attachments.count', [$vendorRegistration->id, ObjectField::PROCESSOR_ATTACHMENTS_VENDOR_PREQUALIFICATIONS]) }}"
                        data-field="{{ ObjectField::PROCESSOR_ATTACHMENTS_VENDOR_PREQUALIFICATIONS }}">
                    <?php 
                        $record = ObjectField::findRecord($vendorRegistration, ObjectField::PROCESSOR_ATTACHMENTS_VENDOR_PREQUALIFICATIONS);
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;{{ trans('vendorManagement.processorsAttachments') }}&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                </button>
                <button type="button" id="viewActionLogsButton" class="btn btn-primary pull-left spaced">{{ trans('general.editLogs') }}</button>
                @endif
            </footer>
        </div>
    </div>
</div>

@include('templates.generic_table_modal', [
    'modalId'    => 'actionLogsModal',
    'title'      => trans('general.editLogs'),
    'tableId'    => 'actionLogsTable',
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])

<div data-type="template" hidden>
    <table>
        @include('file_uploads.partials.uploaded_file_row_template')
    </table>
</div>

<div class="modal fade" id="uploadAttachmentModal">
    <div class="modal-dialog">
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

                        @include('file_uploads.partials.upload_file_modal', array('id' => 'invoice-upload'))
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
    <script>
        $(document).ready(function () {
            var actionLogsTable = null;

            var mainTable = new Tabulator('#main-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: {{ json_encode($data) }},
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.form') }}", field:"form", minWidth: 200, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"vendorCategory", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendorWorkCategory", width: 200, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.status') }}", field:"status", width: 150, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.score') }}", field:"score", width: 150, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.grade') }}", field:"grade", width: 150, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.remarks') }}", field:"remarks", width: 150, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [
                            @if($editable)
                            {
                                tag: 'a',
                                rowAttributes: {href:'route:edit'},
                                attributes: {class:'btn btn-xs btn-warning', title: '{{ trans("general.edit") }}'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-edit'}
                                }
                            },
                            {
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },
                            @endif
                            {
                                tag: 'a',
                                rowAttributes: {href:'route:view'},
                                attributes: {class:'btn btn-xs btn-default', title: '{{ trans("general.view") }}'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-arrow-right'}
                                }
                            }
                        ]
                    }}
                ],
            });

            function addRowToUploadModal(fileAttributes){
                var clone = $('[data-type=template] tr.template-download').clone();
                var target = $('#uploadFileTable tbody.files');

                $(clone).find("a[data-category=link]").prop('href', fileAttributes['download_url']);
                $(clone).find("a[data-category=link]").prop('title', fileAttributes['filename']);
                $(clone).find("a[data-category=link]").prop('download', fileAttributes['filename']);
                $(clone).find("a[data-category=link]").html(fileAttributes['filename']);
                $(clone).find("input[name='uploaded_files[]']").val(fileAttributes['id']);
                $(clone).find("[data-category=size]").html(fileAttributes['size']);
                $(clone).find("button[data-action=delete]").prop('data-route', fileAttributes['deleteRoute']);
                $(clone).find("[data-category=created-at]").html(fileAttributes['createdAt']);

                target.append(clone);
            }

            $(document).on('click', '[data-action="upload-item-attachments"]', function(e) {
                e.preventDefault();

                var target = $('#uploadFileTable tbody.files').empty();
                var data   = $.get($(this).data('route-get-attachments-list'), function(data){
                    for(var i in data){
                        addRowToUploadModal({
                            download_url: data[i]['download_url'],
                            filename: data[i]['filename'],
                            imgSrc: data[i]['imgSrc'],
                            id: data[i]['id'],
                            size: data[i]['size'],
                            deleteRoute: data[i]['deleteRoute'],
                            createdAt: data[i]['createdAt'],
                        });
                    }
                });

                $('[data-action=submit-attachments]').data('updated-attachment-count-url', $(this).data('route-get-attachments-count'));
                $('#uploadAttachmentModal').modal('show');
                $('#attachment-upload-form').prop('action',$(this).data('route-update-attachments'));
            });

            $(document).on('click', '[data-action=submit-attachments]', function(){
                var updatedAttachmentCountUrl = $(this).data('updated-attachment-count-url');
                var uploadedFilesInput        = [];

                $('form#attachment-upload-form input[name="uploaded_files[]"]').each(function(index){
                    uploadedFilesInput.push($(this).val());
                });

                app_progressBar.show();

                $.post($('form#attachment-upload-form').prop('action'),{
                    _token: _csrf_token,
                    uploaded_files: uploadedFilesInput
                })
                .done(function(data){
                    if(data.success){
                        $('#uploadAttachmentModal').modal('hide');

                        $.get(updatedAttachmentCountUrl, {},function(resp) {
                            $(document).find('[data-field="' + resp.name + '"]').find('[data-component="attachment_upload_count"]').text(resp.attachmentCount);
                        });

                        app_progressBar.maxOut(null, function(){ app_progressBar.hide(); });
                    }
                })
                .fail(function(data){
                    console.error('failed');
                });
            });

            $('#viewActionLogsButton').on('click', function(e) {
                $('#actionLogsModal').modal('show');
            });

            $('#actionLogsModal').on('shown.bs.modal', function(e) {
                e.preventDefault();

                actionLogsTable = new Tabulator('#actionLogsTable', {
                    height:400,
                    pagination:"local",
                    columns: [
                        { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('users.name') }}", field: 'user', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input" },
                        { title:"{{ trans('general.action') }}", field: 'action', width: 180, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('general.date') }}", field: 'datetime', width: 180, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                    ],
                    layout:"fitColumns",
                    ajaxURL: "{{ route('vendorManagement.approval.vendorPreQualification.action.logs.get', [$vendorRegistration->id]) }}",
                    movableColumns:true,
                    placeholder:"{{ trans('formBuilder.noFormsAvailable') }}",
                    columnHeaderSortMulti:false,
                });
            });
        });
    </script>
@endsection