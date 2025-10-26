@extends('layout.main')
<?php use PCK\ProjectReport\ProjectReportColumn; ?>
<?php use PCK\Projects\Project; ?>
@section('css')
    <style>
		/*custom styling since table fully occupied container*/
        .tabulator .tabulator-tableHolder {
          border: none;
        }

        .text-wrap {
            white-space: normal;
        }
    </style>
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
	    <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
        <li>{{ link_to_route('projectReport.dashboard.index', trans('projectReport.projectReportDashboard')) }}</li>
        <li>{{ link_to_route('projectReport.dashboard.projectReport.show', $mapping->projectReportType->title, [$mapping->id]) }}</li>
        <li>{{ trans('projectReport.subPackageReports') }}</li>
	</ol>
@endsection

@section('content')
    <div class="row">
		<div class="col-xs-12 col-sm-7 col-md-7 col-lg-7">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ trans('projectReport.subPackageReports') }}
			</h1>
		</div>
	</div>

    @foreach($templates as $templateId => $template)
	<div class="jarviswidget">
        <header>
            <h2> {{ $template['template_title'] }} </h2>
        </header>
        <div>
            <div class="widget-body">
                <div>
                    <a href="{{ route('projectReport.dashboard.excel.export', [$subPackageMapping->id, $template['template_id'], Project::TYPE_SUB_PACKAGE]) }}" target="_blank" class="btn btn-success"><i class="far fa-file-excel fa-lg"></i>&nbsp;&nbsp;{{ trans('general.export') }}</a>
                </div>
                <br/>
                <div id="template_{{ $templateId }}_table"></div>
            </div>
        </div>
	</div>
    @endforeach
    @include('templates.generic_table_modal', [
        'modalId'    => 'previousRevisionsModal',
        'title'      => trans('projectReport.previousRevisions'),
        'tableId'    => 'previousRevisionsTable',
        'showCancel' => true,
        'cancelText' => trans('forms.close'),
    ])
    @include('templates.generic_table_modal', [
        'modalId'    => 'attachmentsModal',
        'title'      => trans('general.attachments'),
        'tableId'    => 'attachmentsTable',
        'showCancel' => true,
        'cancelText' => trans('forms.close'),
    ])
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            @foreach($templates as $templateId => $template)
            new Tabulator('#template_{{ $templateId }}_table', {
                fillHeight: true,
                columnHeaderVertAlign: 'bottom',
                placeholder: "{{ trans('general.noRecordsFound') }}",
                columnHeaderSortMulti:false,
                ajaxConfig: "GET",
                pagination: 'local',
                paginationSize: 50,
                layout:"fitData",
                columns: [],
                data: [],
            });
            @endforeach

            const approvedDateFormatter = (cell, formatterParams, onRendered) => {
                const data = cell.getRow().getData();

                return data.approvedDate !== null ? data.approvedDate : "{{ trans('general.notAvailable') }}";
            };

            const allRevisionsButtonFormatter = (cell, formatterParams, onRendered) => {
                const data = cell.getRow().getData();

                const viewAllRevisionsButton = document.createElement('a');
                viewAllRevisionsButton.className = 'btn btn-xs btn-success';
                viewAllRevisionsButton.innerHTML = '<i class="fa fa-list"></i>';

                viewAllRevisionsButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    $('#previousRevisionsModal').data('url', data['route:listAllReportsInLine']);
                    $('#previousRevisionsModal').modal('show');
                });

                return viewAllRevisionsButton;
            };

            const listAttachmentsFormatter = (cell, formatterParams, onRendered) => {
                const data = cell.getRow().getData();
                
                if(data.attachmentCount < 1) return null;

                const listAttachmentsButton = document.createElement('button');
                listAttachmentsButton.className = 'btn btn-xs btn-warning';
                listAttachmentsButton.innerHTML = `<i class="fa fa-paperclip"></i> (${data.attachmentCount})`;
                listAttachmentsButton.dataset.action = 'list_attachments';

                listAttachmentsButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    const url = data['route:attachments'];
                    const downloadAttachmentsAsZipUrl = data['route:downloadAttachmentsAsZip'];

                    $('#attachmentsModal').data('url', url);
                    $('#attachmentsModal').data('downloadAttachmentsAsZipUrl', downloadAttachmentsAsZipUrl);
                    $('#attachmentsModal').modal('show');
                });

                return listAttachmentsButton;
            };

            const attachmentDownloadButtonFormatter = (cell, formatterParams, onRendered) => {
                const data = cell.getRow().getData();

                const downloadButton = document.createElement('a');
                downloadButton.dataset.toggle = 'tooltip';
                downloadButton.className = 'btn btn-xs btn-primary';
                downloadButton.innerHTML = '<i class="fas fa-download"></i>';
                downloadButton.style['margin-right'] = '5px';
                downloadButton.href = data.download_url;
                downloadButton.download = data.filename;

                return downloadButton;
            }

            const columnContentFormatter = (cell, formatterParams, onRendered) => {
                const data = cell.getRow().getData();

                const container = document.createElement('div');
                container.classList.add('text-wrap');
                container.innerHTML = data[cell.getColumn().getField()];

                cell.getColumn().setWidth(true);
                
                return container;
            };

            const widthProps = {
                minWidth: 100,
                maxWidth: 500,
            };

            const standardColumnProps = {
                align: 'center',
                cssClass: 'text-center text-middle',
            };

            const columnFilterProps = {
                headerFilter:"input",
                headerFilterPlaceholder: "{{ trans('general.filter') }}",
            };

            const fetchData = async (url, options = {}) => {
                try {
                    const request = await fetch(url, options);

                    if(!request.ok || request.status !== 200) {
                        throw new Error(`An error has occured at: ${url}`);
                    }

                    return await request.json();
                } catch(err) {
                    throw new Error(err.message);
                }
            };

            const constructColumnsRecursively = columnDefinition => {
                const column = {
                    title: columnDefinition.title,
                    field: columnDefinition.identifier,
                    formatter: columnContentFormatter,
                    ...standardColumnProps,
                    ...((columnDefinition.type !== {{ ProjectReportColumn::COLUMN_GROUP }}) && widthProps),
                    ...((columnDefinition.type !== {{ ProjectReportColumn::COLUMN_GROUP }}) && columnFilterProps),
                    ...(columnDefinition?.children?.length > 0) && { columns: columnDefinition.children.map(childColumnDefinition => constructColumnsRecursively(childColumnDefinition)) },
                };

                return column;
            };

            const constructColumns = async (templateId, columnDefinitions) => {
                return new Promise((resolve, reject) => {
                    const columns = [];

                    columnDefinitions.forEach(columnDefinition => {
                        const column = constructColumnsRecursively(columnDefinition);
                        columns.push(column);
                    });

                    columns.unshift({
                        title: "{{ trans('general.no') }}",
                        width: 80,
                        align:'center',
                        cssClass: 'text-center',
                        formatter: 'rownum',
                        ...standardColumnProps,
                    });

                    columns.push({
                        width: 250,
                        title: "{{ trans('projectReport.approvedDate') }}",
                        field: 'approvedDate',
                        headerFilter:"input",
                        headerFilterPlaceholder: "{{ trans('general.filter') }}",
                        formatter: approvedDateFormatter,
                        ...standardColumnProps,
                    });

                    columns.push({
                        width: 120,
                        title: "{{ trans('general.attachments') }}",
                        field: 'attachments',
                        formatter: allRevisionsButtonFormatter,
                        ...standardColumnProps,
                    });

                    columns.push({
                        width: 350,
                        hozAlign: 'center',
                        cssClass: 'text-left text-middle',
                        title: "{{ trans('general.remarks') . ' (' . trans('general.clickToEdit') . ')' }}",
                        field: 'remarks',
                        headerFilter:"input",
                        headerFilterPlaceholder: "{{ trans('general.filter') }}",
                        formatter: 'textarea',
                        editor: 'textarea',
                        cellEdited: remarksCellEditedCallback,
                    });

                    Tabulator.prototype.findTable(`#template_${templateId}_table`)[0].setColumns(columns);

                    resolve(true);
                });
            };

            const remarksCellEditedCallback = async cell => {
                app_progressBar.show();

                try {
                    const row = cell.getRow();
                    const url = row.getData()['route:updateRemarks']
                    const updatedRemarks = DOMPurify.sanitize(cell.getValue()).trim();

                    const response = await fetchData(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            remarks: updatedRemarks,
                            _token: '{{{ csrf_token() }}}'
                        }),
                    });

                    if(response.success) {
                        row.update({'remarks': updatedRemarks});
                        row.reformat();
                    } else {
                        throw new Error(response.errors);
                    }
                } catch(err) {
                    cell.cancelEdit();
                    console.error(err.message);
                } finally {
                    app_progressBar.maxOut(null, function(){ app_progressBar.hide(); });
                }
            };

            const constructTableContents = async (templateId, columnContents) => {
                return new Promise((resolve, reject) => {
                    columnContents.forEach(columnData => {
                        const rowData = {
                            id: columnData.projectReportId,
                            'route:show': columnData['route:show'],
                            subProjectProjectReportsCount: columnData.subProjectProjectReportsCount,
                            approvedDate: columnData.approvedDate,
                            remarks: columnData.remarks,
                            'route:getRemarks': columnData['route:getRemarks'],
                            'route:updateRemarks': columnData['route:updateRemarks'],
                            'route:listAllReportsInLine': columnData['route:listAllReportsInLine'],
                            ...columnData.rowData,
                        };

                        Tabulator.prototype.findTable(`#template_${templateId}_table`)[0].addRow(rowData, false);
                    });

                    resolve(true);
                });
            };

            const renderTables = async () => {
                try {
                    app_progressBar.show();

                    const columnDefinitions = await fetchData("{{ route('projectReport.dashboard.column.definitions.get', [$subPackageMapping->id, $firstTemplateId]) }}");

                    await constructColumns("{{ $firstTemplateId }}", columnDefinitions);

                    const columnContents = await fetchData("{{ route('projectReport.dashboard.column.contents.get', [$subPackageMapping->id, $firstTemplateId, Project::TYPE_SUB_PACKAGE]) }}");

                    await constructTableContents("{{ $firstTemplateId }}", columnContents);
                } catch(err) {
                    console.error(err.message);
                } finally {
                    app_progressBar.maxOut(null, function(){ app_progressBar.hide(); });
                }
            };

            $('#previousRevisionsModal').on('shown.bs.modal', function(e) {
                e.preventDefault();

                const url = $(this).data('url');

                const previousRevisionsTable = new Tabulator('#previousRevisionsTable', {
                    height:500,
                    pagination:"local",
                    columns: [
                        { title:"{{ trans('projectReport.revision') }}", field: 'revision', width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('projectReport.title') }}", field: 'title', headerSort:false, headerFilter:"input" },
                        { title:"{{ trans('general.actions') }}", width: 80, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter: listAttachmentsFormatter },
                    ],
                    layout:"fitColumns",
                    ajaxURL: url,
                    movableColumns:true,
                    placeholder:"{{ trans('general.noAttachments') }}",
                    columnHeaderSortMulti:false,
                });
            });

            $('#attachmentsModal').on('shown.bs.modal', function(e) {
                e.preventDefault();

                const url = $(this).data('url');
                const downloadAttachmentsAsZipUrl = $(this).data('downloadAttachmentsAsZipUrl');

                const attachmentsTable = new Tabulator('#attachmentsTable', {
                    height:500,
                    pagination:"local",
                    columns: [
                        { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('general.attachments') }}", field: 'filename', headerSort:false, headerFilter:"input" },
                        { title:"{{ trans('files.uploadedBy') }}", field: 'uploader', width:250, headerSort:false, headerFilter:"input" },
                        { title:"{{ trans('general.download') }}", width: 120, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter: attachmentDownloadButtonFormatter },
                    ],
                    layout:"fitColumns",
                    ajaxURL: url,
                    movableColumns:true,
                    placeholder:"{{ trans('general.noAttachments') }}",
                    columnHeaderSortMulti:false,
                });

                // remove if exists
                if($(this).find('[data-action="downloadAttachmentsAsZip"]')[0] !== undefined)
                {
                    $(this).find('[data-action="downloadAttachmentsAsZip"]').remove();
                }

                const downloadAsZipButton = document.createElement('a');
                downloadAsZipButton.dataset.action = 'downloadAttachmentsAsZip';
                downloadAsZipButton.href = downloadAttachmentsAsZipUrl;
                downloadAsZipButton.target = '_blank';
                downloadAsZipButton.className = 'btn btn-primary pull-right';
                downloadAsZipButton.innerHTML = `<i class="fa fa-download"></i> {{ trans('general.downloadAll') }}`;
                downloadAsZipButton.style['margin-right'] = '15px';
                downloadAsZipButton.style['margin-bottom'] = '15px';
                
                $('#attachmentsTable')[0].parentNode.insertBefore(downloadAsZipButton, $('#attachmentsTable')[0].nextSibling);
            });

            @if(count($templates) > 0)
            renderTables();
            @endif
        });
    </script>
@endsection