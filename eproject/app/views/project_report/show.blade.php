@extends('layout.main')
<?php use PCK\ProjectReport\ProjectReportColumn; ?>
<?php use PCK\ObjectField\ObjectField; ?>
@section('css')
    <link rel="stylesheet" href="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}"/>
    <style>
        .hidden {
            display: none;
        }

        .column-group {
            padding: 5px 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .empty-block {
            height: 75px;
            background-color: #f1f3f5;

            display: flex;
            justify-content: center;
            align-items: center;
        }

        .empty-block span {
            font-size: 1.2em;
        }

        #columns_container textarea:disabled,
        #columns_container textarea[readonly],
        #columns_container input[readonly] {
            background-color: #efefef;
        }
    </style>
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), [$project->id]) }}</li>
		<li>{{ link_to_route('projectReport.index', trans('projectReport.reportTypes'), [$project->id]) }}</li>
        <li>{{ link_to_route('projectReport.showAll', $projectReportType->title, [$project->id, $latestProjectReport->project_report_type_mapping_id]) }}</li>
	</ol>
@endsection

@section('content')
    <div class="row">
		<div class="col-xs-12 col-sm-7 col-md-7 col-lg-7">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ $projectReportType->title }}
			</h1>
		</div>
        @if($canCreateNewRevision)
        <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5">
			<form action="{{ route('projectReport.newRevision.create', [$project->id, $mapping->id]) }}" method="POST" data-submit-loading="1">
                <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
                <button type="submit" class="btn btn-primary pull-right">
                    <i class="fa fa-plus"></i> {{ ($mapping->latest_rev) ? trans('projectReport.newRevision') : trans('projectReport.newRecord') }}
                </button>
            </form>
		</div>
        @endif
        @if($canEditReport)
        <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5">
			<form action="{{ route('projectReport.delete', [$project->id, $latestProjectReport->id]) }}" method="POST" id="deleteReportForm">
                <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
                <button type="submit" class="btn btn-danger pull-right">
                    <i class="fa fa-trash"></i> {{ trans('forms.delete') }}
                </button>
            </form>
		</div>
        @endif
	</div>

    @if($latestProjectReport)
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header>
                    <h2><i class="fa fa-list"></i> {{ trans('projectReport.revision') }}: {{ $latestProjectReport && $mapping->latest_rev ? $latestProjectReport->getRevisionText() : 'N/A' }}</h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        @if($canEditReport)
                        <form action="{{ $saveColumnContentRoute }}" method="POST" class="smart-form" data-submit-loading="1">
                        @else
                        <div class="smart-form">
                        @endif
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
                            <fieldset id="columns_container"></fieldset>
                            @if($canEditReport)
                                @include('verifiers.select_verifiers', [
                                    'verifiers' => $verifiers,
                                ])
                            @endif
                            <footer>
                                @if($canEditReport)
                                <button type="submit" 
                                    class="btn btn-success" 
                                    data-intercept="confirmation"
                                    data-intercept-condition="noVerifier"
                                    data-confirmation-message="{{ trans('general.submitWithoutVerifier') }}"
                                    name="send_to_verify"><i class="fa fa-file-upload"></i> {{ trans('forms.submit') }}
                                </button>
                                <button type="submit" class="btn btn-primary">{{ trans('forms.save') }}</button>
                                @endif
                                <a href="{{ route('projectReport.showAll', [$project->id, $latestProjectReport->project_report_type_mapping_id]) }}" class="btn btn-default">{{ trans('general.back') }}</a>

                                @if($isCurrentVerifier)
                                    @include('verifiers.approvalForm', [
                                        'formId' => 'verifierForm',
                                        'object' => $latestProjectReport,
                                    ])
                                @endif
                                @if($latestProjectReport)
                                <button type="button" class="btn btn-warning pull-left" data-toggle="modal" data-target="#verifierStatusOverviewModal"><i class="fa fa-users"></i> {{ trans('verifiers.verifiers') }}</button>
                                <?php $action = $canEditReport ? 'upload' : 'download'; ?>
                                <button type="button" class="btn btn-info pull-left" data-action="{{ $action }}-item-attachments" 
                                    data-route-get-attachments-list="{{ route('projectReport.attachements.get', [$project->id, $latestProjectReport->id, ObjectField::PROJECT_REPORT]) }}"
                                    data-route-update-attachments="{{ route('projectReport.attachements.update', [$project->id, $latestProjectReport->id, ObjectField::PROJECT_REPORT]) }}"
                                    data-route-get-attachments-count="{{ route('projectReport.attachements.count.get', [$project->id, $latestProjectReport->id, ObjectField::PROJECT_REPORT]) }}"
                                    data-field="{{ ObjectField::PROJECT_REPORT }}">
                                    <?php 
                                        $record = ObjectField::findRecord($latestProjectReport, ObjectField::PROJECT_REPORT);
                                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                    ?>
                                    <i class="fas fa-paperclip fa-lg"></i> {{ trans('general.attachments') }}&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                </button>
                                <button type="button" class="btn btn-default pull-left" data-toggle="modal" data-target="#actionLogsModal"><i class="fas fa-list"></i> {{ trans('projectReport.actionLogs') }}</button>
                                @endif
                            </footer>
                        @if($canEditReport)
                        </form>
                        @else
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($canEditReport)
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
    @endif

    @if($latestProjectReport)
        @include('verifiers.verifier_status_overview_modal', array(
            'verifierRecords' => $assignedVerifierRecords
        ))
        @include('templates.generic_table_modal', [
            'modalId'    => 'attachmentsModal',
            'title'      => trans('general.attachments'),
            'tableId'    => 'attachmentsTable',
            'showCancel' => true,
            'cancelText' => trans('forms.close'),
        ])
        @include('templates.verifier_remarks_modal', [
            'verifierApproveModalId' => 'projectReportVerifierApproveModal',
            'verifierRejectModalId'  => 'projectReportVerifierRejectModal',
        ])
        @include('templates.yesNoModal', [
            'modalId' => 'deleteReportConfirmModal',
            'message' => trans('projectReport.areYouSureToDelete'),
        ])
        @include('templates.generic_table_modal', [
            'modalId'    => 'actionLogsModal',
            'title'      => trans('projectReport.actionLogs'),
            'tableId'    => 'actionLogsTable',
            'showCancel' => true,
            'cancelText' => trans('forms.close'),
        ])
    @endif
    @include('project_report.partials.project_report_components', ['canEditReport' => $canEditReport])
@endsection

@section('js')
    <script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            const columnsContainer = document.getElementById('columns_container');
            const columnTemplate = document.getElementById('column-template');
            const columnGroupContainerTemplate = document.getElementById('column-group-container-template');
            const emptyColumnGroupTemplate = document.getElementById('empty_column_group_template');
            const textareaTemplate = document.getElementById('textarea-template');
            const numberInputTemplate = document.getElementById('number-input-template');
            const dateInputTemplate = document.getElementById('date-input-template');
            const selectOptTemplate = document.getElementById('select-opt-template');
            const addSubColumnTemplate = document.getElementById('add_sub_column_template');
            //const customColumnList = {{ json_encode(ProjectReportColumn::isCustomColumnList()) }};

            @if($latestProjectReport)
            const clearChildNodes = (node, includeCurrentNode = false) => {
                if(includeCurrentNode) {
                    node.remove();
                } else {
                    while(node.firstChild) {
                        node.removeChild(node.lastChild);
                    }
                }
            }

            const fetchData = async url => {
                try {
                    const request = await fetch(url);

                    if(!request.ok || request.status !== 200) {
                        throw new Error(`An error has occured at: ${url}`);
                    }

                    return await request.json();
                } catch(err) {
                    throw new Error(err.message);
                }
            };

            const constructColumn = el => {
                const column = columnTemplate.cloneNode(true);
                column.classList.remove('hidden');
                column.removeAttribute('id');
                column.dataset.id = el.id;
                column.querySelector('[data-component="column-title"]').textContent = el.title;

                return column;
            };

            const constructAddSubColumnButton = el => {
                const addSubColumnButton = addSubColumnTemplate.cloneNode(true);
                addSubColumnButton.removeAttribute('id');
                addSubColumnButton.classList.remove('hidden');
                addSubColumnButton.dataset.id = el.id;
                addSubColumnButton.dataset.depth = el.depth;

                return addSubColumnButton;
            }

            const constructTextarea = el => {
                const node = textareaTemplate.cloneNode(true);
                node.removeAttribute('id');
                node.classList.remove('hidden');

                const columnContent = node.querySelector('[data-component="column-content"]');

                columnContent.value = el.content;

                columnContent.style.overflow = 'scroll';
                columnContent.style.height = 'auto';

                if(el.type == '{{ ProjectReportColumn::COLUMN_CUSTOM }}') {
                    columnContent.name = el.name;

                    if (el.doneSingleEntry) {
                        columnContent.disabled = true;
                    }
                } else {
                    columnContent.disabled = true;
                }

                return node;
            };

            const constructNumberInput = el => {
                const node = numberInputTemplate.cloneNode(true);
                node.removeAttribute('id');
                node.classList.remove('hidden');

                const columnContent = node.querySelector('[data-component="column-content"]');

                columnContent.value = el.content;

                if(el.type == '{{ ProjectReportColumn::COLUMN_NUMBER }}') {
                    columnContent.name = el.name;

                    if (el.doneSingleEntry) {
                        columnContent.disabled = true;
                    }
                } else {
                    columnContent.disabled = true;
                }

                return node;
            };

            const constructDateInput = el => {
                const node = dateInputTemplate.cloneNode(true);
                node.removeAttribute('id');
                node.classList.remove('hidden');

                const columnContent = node.querySelector('[data-component="column-content"]');

                columnContent.value = el.content;

                if (el.type == '{{ ProjectReportColumn::COLUMN_DATE }}') {
                    columnContent.name = el.name;

                    if (el.doneSingleEntry) {
                        columnContent.disabled = true;
                    }
                } else {
                    columnContent.disabled = true;
                }

                return node;
            };

            const constructSelectOpt = el => {
                const node = selectOptTemplate.cloneNode(true);
                node.removeAttribute('id');
                node.classList.remove('hidden');

                const columnContent = node.querySelector('[data-component="column-content"]');

                // Set the value of the select element, this does not change the visual selection
                columnContent.value = el.content;

                // Loop over each option in the select element
                const options = columnContent.options;
                for (let i = 0; i < options.length; i++) {
                    if (options[i].text === el.content) {
                        // If the option text matches el.content, set it as selected
                        options[i].selected = true;
                        break; // Stop the loop once the matching option is found
                    }
                }

                if (el.type == '{{ ProjectReportColumn::COLUMN_PROJECT_PROGRESS }}') {
                    columnContent.name = el.name;

                    if (el.doneSingleEntry) {
                        columnContent.disabled = true;
                    }
                } else {
                    columnContent.disabled = true;
                }

                return node;
            };

            const constructColumnRecursively = el => {
                let column = constructColumn(el);

                switch (el.type) {
                    case {{ ProjectReportColumn::COLUMN_GROUP }}:
                        const addSubColumnButton = constructAddSubColumnButton(el);

                        if(el.children.length > 0) {
                            const columnGroupContainer = columnGroupContainerTemplate.cloneNode(true);
                            columnGroupContainer.removeAttribute('id');
                            columnGroupContainer.classList.remove('hidden');

                            el.children.forEach(child => {
                                const childColumnContents = constructColumnRecursively(child);

                                if(childColumnContents != null) {
                                    columnGroupContainer.appendChild(childColumnContents);
                                }
                            });

                            column.querySelector('[data-component="content_container"]').appendChild(columnGroupContainer);
                        } else {
                            const emptyColumnGroup = emptyColumnGroupTemplate.cloneNode(true);
                            emptyColumnGroup.removeAttribute('id');
                            emptyColumnGroup.classList.remove('hidden');

                            column.querySelector('[data-component="content_container"]').appendChild(emptyColumnGroup);
                        }
                        break;

                    case {{ ProjectReportColumn::COLUMN_NUMBER }}:
                        const numberInput = constructNumberInput(el);
                        column.querySelector('[data-component="content_container"]').appendChild(numberInput);
                        break;

                    case {{ ProjectReportColumn::COLUMN_DATE }}:
                        const dateInput = constructDateInput(el);
                        column.querySelector('[data-component="content_container"]').appendChild(dateInput);
                        break;

                    case {{ ProjectReportColumn::COLUMN_PROJECT_PROGRESS }}:
                        const selectOpt = constructSelectOpt(el);
                        column.querySelector('[data-component="content_container"]').appendChild(selectOpt);
                        break;

                    default:    // COLUMN_CUSTOM (text)
                        const textarea = constructTextarea(el);
                        column.querySelector('[data-component="content_container"]').appendChild(textarea);
                }
                return column;
            };

            const renderColumns = async () => {
                app_progressBar.toggle();

                try {
                    clearChildNodes(columnsContainer);

                    const responseData = await fetchData("{{ route('projectReport.columns.get', [$project->id, $latestProjectReport->id]) }}");

                    responseData.forEach(el => {
                        const column = constructColumnRecursively(el);

                        columnsContainer.appendChild(column);
                    });
                } catch (err) {
                    SmallErrorBox.refreshAndRetry();
                    console.error(err.message);
                } finally {
                    app_progressBar.maxOut();
                    app_progressBar.hide();
                }
            };

            renderColumns();

            $('#verifierForm button[name="approve"], #verifierForm button[name="reject"]').on('click', function(e) {
				e.preventDefault();

				if(this.name == 'reject') {
					$('#projectReportVerifierRejectModal').modal('show');
				}

				if(this.name == 'approve') {
					$('#projectReportVerifierApproveModal').modal('show');           
                } 
			});

            $('#projectReportVerifierApproveModal button[type="submit"]').on('click', function(e) {
                e.preventDefault();

                app_progressBar.toggle();

                var input = $("<input>").attr("type", "hidden").attr("name", "approve").val(1);
                $('#verifierForm').append(input);

                var remarks = $('#verifierForm').append($("<input>").attr("type", "hidden").attr("name", "verifier_remarks").val($('#projectReportVerifierApproveModal [name="verifier_remarks"]').val()));

                $('#verifierForm').submit();
            });

            $('#projectReportVerifierRejectModal button[type="submit"]').on('click', function(e) {
                e.preventDefault();

                app_progressBar.toggle();

                var remarks = $('#verifierForm').append($("<input>").attr("type", "hidden").attr("name", "verifier_remarks").val($('#projectReportVerifierRejectModal [name="verifier_remarks"]').val()));
                $('#verifierForm').append(remarks);

                $('#verifierForm').submit();
            });

            $('#deleteReportForm').on('submit', function(e) {
                e.preventDefault();
                
                $('#deleteReportConfirmModal').modal('show');
            });

            $('#deleteReportConfirmModal [data-action="actionYes"]').on('click', function(e) {
                e.preventDefault();

                app_progressBar.toggle();

                $('#deleteReportForm')[0].submit();
            });
            @endif

            $('[data-submit-loading="1"]').on('submit', function(e) {
                app_progressBar.toggle();

                return true;
            });

            @if($canEditReport)
            /* 
            * attachments upload codes
            */
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
                            $(document).find('[data-component="attachment_upload_count"]').text(resp.attachmentCount);
                        });

                        app_progressBar.maxOut(null, function(){ app_progressBar.hide(); });
                    }
                })
                .fail(function(data){
                    console.error('failed');
                });
            });
            @endif

            @if($latestProjectReport && !$canEditReport)
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
                    placeholder:"{{ trans('general.noAttachments') }}",
                    columnHeaderSortMulti:false,
                });
            });
            @endif

            @if($latestProjectReport)
            let submissionLogsTable = null;

            $(document).on('show.bs.modal', '#actionLogsModal', function(e) {
                submissionLogsTable = new Tabulator('#actionLogsTable', {
                    height:350,
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    ajaxURL: "{{ route('projectReport.actionLogs.get', [$project->id, $latestProjectReport->id]) }}",
                    ajaxConfig: "GET",
                    layout:"fitColumns",
                    pagination: "local",
                    paginationSize:10,
                    columns:[
                        {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('general.user') }}", field:"user", hozAlign:"left", headerSort:false},
                        {title:"{{ trans('general.actions') }}", field:"action", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('general.dateAndTime') }}", field:"dateTime", width: 250, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    ],
                });
            });
            @endif
        });

        function noVerifier(e){
            var form = $(e.target).closest('form');
            var input = form.find(':input[name="verifiers[]"]').serializeArray();

            return !input.some(function(element){
                return (element.value > 0);
            });
        }
    </script>
@endsection