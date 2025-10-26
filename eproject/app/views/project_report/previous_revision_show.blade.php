@extends('layout.main')
<?php use PCK\ProjectReport\ProjectReportColumn; ?>
<?php use PCK\ObjectField\ObjectField; ?>
@section('css')
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

            display:flex;
            justify-content: center;
            align-items: center;
        }

        .empty-block span {
            font-size: 1.2em;
        }
    </style>
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), [$project->id]) }}</li>
		<li>{{ link_to_route('projectReport.index', trans('projectReport.projectReports'), [$project->id]) }}</li>
        <li>{{ $projectReportType->title }}</li>
	</ol>
@endsection

@section('content')
    <div class="row">
		<div class="col-xs-12 col-sm-7 col-md-7 col-lg-7">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ $projectReportType->title }}
			</h1>
		</div>
	</div>

     <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header>
                    <h2><i class="fa fa-list"></i> {{ trans('projectReport.revision') }}: {{ $projectReport->getRevisionText() }}</h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="smart-form">
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
                            <fieldset id="columns_container"></fieldset>
                            <footer>
                                <button type="button" class="btn btn-warning pull-left" data-toggle="modal" data-target="#verifierStatusOverviewModal"><i class="fa fa-users"></i> {{ trans('verifiers.verifiers') }}</button>
                                <button type="button" class="btn btn-info pull-left" data-action="download-item-attachments" 
                                    data-route-get-attachments-list="{{ route('projectReport.attachements.get', [$project->id, $projectReport->id, ObjectField::PROJECT_REPORT]) }}"
                                    data-route-update-attachments="{{ route('projectReport.attachements.update', [$project->id, $projectReport->id, ObjectField::PROJECT_REPORT]) }}"
                                    data-route-get-attachments-count="{{ route('projectReport.attachements.count.get', [$project->id, $projectReport->id, ObjectField::PROJECT_REPORT]) }}"
                                    data-field="{{ ObjectField::PROJECT_REPORT }}">
                                    <?php 
                                        $record = ObjectField::findRecord($projectReport, ObjectField::PROJECT_REPORT);
                                        $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                                    ?>
                                    <i class="fas fa-paperclip fa-lg"></i> {{ trans('general.attachments') }}&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
                                </button>
                            </footer>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
    @include('project_report.partials.project_report_components')
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $(document).ready(function() {
                const columnsContainer = document.getElementById('columns_container');
                const columnTemplate = document.getElementById('column-template');
                const columnGroupContainerTemplate = document.getElementById('column-group-container-template');
                const emptyColumnGroupTemplate = document.getElementById('empty_column_group_template');
                const textareaTemplate = document.getElementById('textarea-template');
                const addSubColumnTemplate = document.getElementById('add_sub_column_template');

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
                    const textarea = textareaTemplate.cloneNode(true);
                    textarea.removeAttribute('id');
                    textarea.classList.remove('hidden');
                    textarea.querySelector('[data-component="column-content"]').value = el.content;

                    textarea.querySelector('[data-component="column-content"]').style.overflow = 'scroll';
                    textarea.querySelector('[data-component="column-content"]').style.height = 'auto';

                    if(el.type == '{{ ProjectReportColumn::COLUMN_CUSTOM }}') {
                        textarea.querySelector('[data-component="column-content"]').name = el.name;
                    } else {
                        textarea.querySelector('[data-component="column-content"]').disabled = true;
                    }

                    return textarea;
                };

                const constructColumnRecursively = el => {
                    let column = constructColumn(el);

                    if(el.type === {{ ProjectReportColumn::COLUMN_GROUP }}) {
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
                    } else {
                        const textarea = constructTextarea(el);

                        column.querySelector('[data-component="content_container"]').appendChild(textarea);
                    }

                    return column;
                };

                const renderColumns = async () => {
                    app_progressBar.toggle();

                    try {
                        clearChildNodes(columnsContainer);

                        const responseData = await fetchData("{{ route('projectReport.columns.get', [$project->id, $projectReport->id]) }}");

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
            });
        });
    </script>
@endsection