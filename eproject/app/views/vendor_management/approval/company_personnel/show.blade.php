@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorManagement') }}}</li>
        <li>{{ link_to_route('vendorManagement.approval.registrationAndPreQualification', trans('vendorManagement.registrationAndPreQualification'), array()) }}</li>
        <li>{{ link_to_route('vendorManagement.approval.registrationAndPreQualification.show', $vendorRegistration->company->name, array($vendorRegistration->id)) }}</li>
        <li>{{ trans('vendorManagement.companyPersonnel') }}</li>
    </ol>
@endsection
<?php use PCK\ObjectField\ObjectField; ?>

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
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.companyPersonnel') }}}
        </h1>
    </div>
    @if($canReject)
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <a href="{{ route('vendorManagement.approval.companyPersonnel.create', [$vendorRegistration->id]) }}" class="btn btn-primary btn-md pull-right header-btn">
            <i class="fa fa-plus"></i> {{{ trans('forms.add') }}}
        </a>
    </div>
    @endif
</div>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        @if(!empty($instructionSettings->company_personnel))
        <div class="padded label-success text-white"><strong>{{ nl2br($instructionSettings->company_personnel) }}</strong></div>
        <br>
        @endif
        @if(!empty($section->amendment_remarks))
        <div class="well @if($section->amendmentsRequired()) border-danger @elseif($section->amendmentsMade()) border-warning @endif">
            {{ nl2br($section->amendment_remarks) }}
        </div>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('vendorManagement.directors') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="directors-table"></div>
                    @if($canReject && $setting->has_attachments)
                    {{ Form::open(array('route' => array('vendorManagement.approval.uploads.directors', $vendorRegistration->id), 'class' => 'smart-form')) }}
                        <section>
                            <label class="label">{{{ trans('forms.attachments') }}}:</label>

                            @include('file_uploads.partials.upload_file_modal', array('id' => 'directors-upload', 'tableId' => 'directors-upload-table', 'uploadedFiles' => $directorUploadedFiles))
                        </section>
                        <footer>
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                    @endif
                    @if((!$canReject) && $setting->has_attachments)
                    <button type="button" class="btn btn-primary" data-action="download-item-attachments"
                        data-route-get-attachments-list="{{ route('company.personnel.approval.attachments.get', [$vendorRegistration->id, ObjectField::COMPANY__COMPANY_PERSONNEL_DIRECTOR]) }}"
                        >
                        <?php 
                            $record = ObjectField::findRecord($vendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_DIRECTOR);
                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                        ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('vendorManagement.shareholders') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="shareholders-table"></div>
                    @if($canReject && $setting->has_attachments)
                    {{ Form::open(array('route' => array('vendorManagement.approval.uploads.shareholders', $vendorRegistration->id), 'class' => 'smart-form')) }}
                        <section>
                            <label class="label">{{{ trans('forms.attachments') }}}:</label>

                            @include('file_uploads.partials.upload_file_modal', array('id' => 'shareholders-upload', 'tableId' => 'shareholders-upload-table', 'uploadedFiles' => $shareholderUploadedFiles))
                        </section>
                        <footer>
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                    @endif
                    @if((!$canReject) && $setting->has_attachments)
                    <button type="button" class="btn btn-primary" data-action="download-item-attachments"
                        data-route-get-attachments-list="{{ route('company.personnel.approval.attachments.get', [$vendorRegistration->id, ObjectField::COMPANY__COMPANY_PERSONNEL_SHAREHOLDER]) }}"
                        >
                        <?php 
                            $record = ObjectField::findRecord($vendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_SHAREHOLDER);
                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                        ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('vendorManagement.headOfCompany') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="head-of-company-table"></div>
                    @if($canReject && $setting->has_attachments)
                    {{ Form::open(array('route' => array('vendorManagement.approval.uploads.companyHeads', $vendorRegistration->id), 'class' => 'smart-form')) }}
                        <section>
                            <label class="label">{{{ trans('forms.attachments') }}}:</label>

                            @include('file_uploads.partials.upload_file_modal', array('id' => 'company-heads-upload', 'tableId' => 'company-heads-upload-table', 'uploadedFiles' => $headOfCompanyUploadedFiles))
                        </section>
                        <footer>
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                    @endif
                    @if((!$canReject) && $setting->has_attachments)
                    <button type="button" class="btn btn-primary" data-action="download-item-attachments"
                        data-route-get-attachments-list="{{ route('company.personnel.approval.attachments.get', [$vendorRegistration->id, ObjectField::COMPANY__COMPANY_PERSONNEL_COMPANY_HEAD]) }}"
                        >
                        <?php 
                            $record = ObjectField::findRecord($vendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_COMPANY_HEAD);
                            $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                        ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget padded">
            <footer>
                <a href="{{ route('vendorManagement.approval.projectTrackRecord', [$vendorRegistration->id]) }}" class="btn btn-info pull-right">{{ trans('forms.next') }}</a>
                @if($canReject)
                    <form action="{{ route('vendorManagement.approval.companyPersonnel.reject', [$vendorRegistration->id])}}" method="POST">
                        <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
                        <button type="submit" class="btn btn-danger pull-right spaced" data-intercept="confirmation" data-confirmation-with-remarks="amendment_remarks" data-confirmation-with-remarks-required="true" data-confirmation-with-remarks-required-message="{{ trans('forms.remarksRequired') }}"><i class="fa fa-times"></i> {{ trans('forms.reject') }}</button>
                    </form>
                    @if($section->amendmentsMade() || $section->amendmentsRequired())
                        <button type="button" data-action="form-submit" data-target-id="resolve-form" class="btn btn-warning pull-right spaced"><i class="fa fa-check"></i> {{ trans('forms.markAsResolved') }}</button>
                    @endif
                @endif
                {{ link_to_route('vendorManagement.approval.registrationAndPreQualification.show', trans('forms.back'), array($vendorRegistration->id), array('class' => 'btn btn-default pull-right spaced')) }}
                @if($canUploadProcessorAttachments)
                <button type="button" class="btn btn-info pull-left spaced" data-action="upload-item-attachments" 
                        data-route-get-attachments-list="{{ route('vendorManagement.approval.processor.attachments.list', [$vendorRegistration->id, ObjectField::PROCESSOR_ATTACHMENTS_COMPANY_PERSONNELS]) }}"
                        data-route-update-attachments="{{ route('vendorManagement.approval.processor.attachments.upload', [$vendorRegistration->id, ObjectField::PROCESSOR_ATTACHMENTS_COMPANY_PERSONNELS]) }}"
                        data-route-get-attachments-count="{{ route('vendorManagement.approval.processor.attachments.count', [$vendorRegistration->id, ObjectField::PROCESSOR_ATTACHMENTS_COMPANY_PERSONNELS]) }}"
                        data-field="{{ ObjectField::PROCESSOR_ATTACHMENTS_COMPANY_PERSONNELS }}">
                    <?php 
                        $record = ObjectField::findRecord($vendorRegistration, ObjectField::PROCESSOR_ATTACHMENTS_COMPANY_PERSONNELS);
                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                    ?>
                    <i class="fas fa-paperclip fa-lg"></i>&nbsp;{{ trans('vendorManagement.processorsAttachments') }}&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                </button>
                <button type="button" id="viewActionLogsButton" class="btn btn-primary pull-left spaced">{{ trans('general.editLogs') }}</button>
                @endif
            </footer>
            @if($section->amendmentsMade() || $section->amendmentsRequired())
            <form action="{{ route('vendorManagement.approval.companyPersonnel.resolve', [$vendorRegistration->id])}}" method="POST" id="resolve-form">
                <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
            </form>
            @endif
        </div>
    </div>
</div>

@if($setting->has_attachments)
    @include('templates.generic_table_modal', [
        'modalId'    => 'attachmentsModal',
        'title'      => trans('general.attachments'),
        'tableId'    => 'attachmentsTable',
        'showCancel' => true,
        'cancelText' => trans('forms.close'),
    ])
@endif
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
    <script src="<?php echo asset('js/app/app.restfulDelete.js'); ?>"></script>
    <script>
        $(document).ready(function () {
            var actionLogsTable = null;

            var defaultColumns = [
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                {title:"{{ trans('vendorManagement.identificationNumber') }}", field:"identification_number", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.emailAddress') }}", field:"email_address", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.contactNumber') }}", field:"contact_number", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.yearsOfExperience') }}", field:"years_of_experience", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                @if($canReject)
                {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    show: function(cell){
                        return cell.getData().hasOwnProperty('id');
                    },
                    innerHtml: [
                        {
                            tag: 'a',
                            rowAttributes: {href:'route:edit'},
                            attributes: {class:'btn btn-xs btn-warning', title: '{{ trans("vendorPreQualification.updateItem") }}'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fa fa-edit'}
                            }
                        },{
                            innerHtml: function(){
                                return '&nbsp;';
                            }
                        },{
                            innerHtml: function(rowData){
                                if(rowData['deletable'])
                                {
                                    return '<a href="'+rowData['route:delete']+'" class="btn btn-xs btn-danger" data-id="'+rowData['id']+'" data-method="delete" data-csrf_token="{{{ csrf_token() }}}"><i class="fa fa-trash"></i></a>';
                                }

                                return '<button type="button" class="btn btn-xs invisible"><i class="fa fa-trash"></i></button>';
                            }
                        },
                    ]
                }}
                @endif
            ];
            var shareholdersColumns = [
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                {title:"{{ trans('vendorManagement.identificationNumber') }}", field:"identification_number", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.designation') }}", field:"designation", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.amountOfShare') }}", field:"amount_of_share", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.holdingPercentage') }}", field:"holding_percentage", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                @if($canReject)
                {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    show: function(cell){
                        return cell.getData().hasOwnProperty('id');
                    },
                    innerHtml: [
                        {
                            tag: 'a',
                            rowAttributes: {href:'route:edit'},
                            attributes: {class:'btn btn-xs btn-warning', title: '{{ trans("vendorPreQualification.updateItem") }}'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fa fa-edit'}
                            }
                        },{
                            innerHtml: function(){
                                return '&nbsp;';
                            }
                        },{
                            innerHtml: function(rowData){
                                if(rowData['deletable'])
                                {
                                    return '<a href="'+rowData['route:delete']+'" class="btn btn-xs btn-danger" data-id="'+rowData['id']+'" data-method="delete" data-csrf_token="{{{ csrf_token() }}}"><i class="fa fa-trash"></i></a>';
                                }

                                return '<button type="button" class="btn btn-xs invisible"><i class="fa fa-trash"></i></button>';
                            }
                        },
                    ]
                }}
                @endif
            ];
            new Tabulator('#directors-table', {
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: {{ json_encode($directorsData) }},
                layout:"fitColumns",
                columns:defaultColumns,
            });
            new Tabulator('#shareholders-table', {
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: {{ json_encode($shareholdersData) }},
                layout:"fitColumns",
                columns:shareholdersColumns,
            });
            new Tabulator('#head-of-company-table', {
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: {{ json_encode($headOfCompanyData) }},
                layout:"fitColumns",
                columns:defaultColumns,
            });

            @if($setting->has_attachments)
            $(document).on('click', '[data-action="download-item-attachments"]', function(e) {
                e.preventDefault();

                $('#attachmentsModal').data('url', $(this).data('route-get-attachments-list'));
                $('#attachmentsModal').modal('show');
            });

            var attachmentDownloadButtonFormatter = function(cell, formatterParams, onRendered) {
                var data = cell.getRow().getData();

                var downloadButton = document.createElement('a');
                downloadButton.dataset.toggle = 'tooltip';
                downloadButton.className = 'btn btn-xs btn-primary';
                downloadButton.innerHTML = '<i class="fas fa-download"></i>';
                downloadButton.style['margin-right'] = '5px';
                downloadButton.href = data.download_url;
                downloadButton.download = data.filename;

                return downloadButton;
            }

            $('#attachmentsModal').on('shown.bs.modal', function(e) {
                e.preventDefault();

                var url = $(this).data('url');

                var attachmentsTable = new Tabulator('#attachmentsTable', {
                    height:500,
                    pagination:"local",
                    columns: [
                        { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('general.attachments') }}", field: 'filename', headerSort:false, headerFilter:"input" },
                        { title:"{{ trans('general.download') }}", width: 120, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter: attachmentDownloadButtonFormatter },
                    ],
                    layout:"fitColumns",
                    ajaxURL: url,
                    movableColumns:true,
                    placeholder:"{{ trans('formBuilder.noFormsAvailable') }}",
                    columnHeaderSortMulti:false,
                });
            });
            @endif

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
                    ajaxURL: "{{ route('vendorManagement.approval.companyPersonnel.action.logs.get', [$vendorRegistration->id]) }}",
                    movableColumns:true,
                    placeholder:"{{ trans('formBuilder.noFormsAvailable') }}",
                    columnHeaderSortMulti:false,
                });
            });
        });
    </script>
@endsection