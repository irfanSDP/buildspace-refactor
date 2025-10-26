@extends('layout.main')

@section('css')
    <style>
		/*custom styling since table fully occupied container*/
        .tabulator .tabulator-tableHolder {
			border: none;
		}

        .button-row {
            display: flex;
            gap: 5px;
            justify-content: flex-end;
        }
    </style>
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{ trans('projectReport.templates') }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-7 col-md-7 col-lg-7">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ trans('projectReport.templates') }}
			</h1>
		</div>

		<div class="col-xs-12 col-sm-5 col-md-5 col-lg-5 button-row">
			<a href="{{ route('projectReport.type.index') }}" class="btn btn-primary btn-md pull-right header-btn">
				<i class="fas fa-map"></i> {{ trans('projectReport.templateMappings') }}
			</a>
            <a id="btnCreateNewReportTemplate" href="#" class="btn btn-primary btn-md pull-right header-btn" data-target="#editorModal" data-toggle="modal">
				<i class="fa fa-plus"></i> {{ trans('projectReport.newReportTemplate') }}
			</a>
		</div>
	</div>

	<div class="jarviswidget ">
		<header>
			<h2> {{ trans('projectReport.templates') }} </h2>
		</header>
		<div>
			<div class="widget-body no-padding">
				<div id="project-report-templates-list-table"></div>
			</div>
		</div>
	</div>
	
	@include('project_report.template.partials.report_template_editor_modal', [
        'modalId' => 'editorModal',
        'label'   => trans('general.title'),
    ])
	@include('templates.yesNoModal', [
        'modalId'   => 'yesNoModal',
        'titleId'   => 'yesNoModalTitle',
        'title'     => trans('general.confirmation'),
        'messageId' => 'yesNoModalMessage',
    ])
    @include('templates.yesNoModal', [
        'modalId'   => 'lockRevisionYesNoModal',
        'titleId'   => 'lockRevisionYesNoModalTitle',
        'title'     => trans('general.confirmation'),
        'messageId' => 'lockRevisionYesNoModalMessage',
    ])
	@include('templates.yesNoModal', [
        'modalId'   => 'newRevisionYesNoModal',
        'titleId'   => 'newRevisionYesNoModalTitle',
        'title'     => trans('general.confirmation'),
        'messageId' => 'newRevisionYesNoModalMessage',
    ])
	@include('templates.warning_modal', [
        'modalId'          => 'warningModal',
        'warningMessageId' => 'txtWarningMessage',
    ])
@endsection

@section('js')
<script>
    $(document).ready(function() {
        let projectReportTemplatesListTable = null;

        const templateLinkFormatter = function(cell, formatterParams, onRendered) {
            const rowData = cell.getRow().getData();

            let templateLink = document.createElement('a');
            templateLink.href = rowData['route:show'];
            templateLink.innerHTML = rowData.title;
            templateLink.dataset.toggle = 'tooltip';
            templateLink.title = "{{ trans('projectReport.designTemplate') }}";
            templateLink.style['user-select'] = 'none';

            return templateLink;
        }

        const revisionFormatter = function(cell, formatterParams, onRendered) {
            const rowData = cell.getRow().getData();

            return rowData.revision == 0 ? "{{ trans('projectReport.original') }}" : rowData.revision;
        }

        const actionsFormatter = function(cell, formatterParams, onRendered) {
            const rowData = cell.getRow().getData();

            let container = document.createElement('div');
            container.style.textAlign = "left";

            if(rowData.hasOwnProperty('route:update')) {
                const editTitleButton = document.createElement('a');
                editTitleButton.dataset.action = 'update_title';
                editTitleButton.dataset.id = rowData.id;
                editTitleButton.dataset.title = rowData.title;
                editTitleButton.dataset.url = rowData['route:update'];
                editTitleButton.dataset.toggle = 'tooltip';
                editTitleButton.title = "{{ trans('projectReport.editTemplateTitle') }}";
                editTitleButton.className = 'btn btn-xs btn-warning';
                editTitleButton.innerHTML = '<i class="fa fa-pencil-alt"></i>';
                editTitleButton.style['margin-right'] = '5px';
    
                container.appendChild(editTitleButton);
            }

            if(rowData.hasOwnProperty('route:newRevision')) {
                const newRevisionButton = document.createElement('a');
                newRevisionButton.dataset.action = 'new_revision';
                newRevisionButton.dataset.id = rowData.id;
                newRevisionButton.dataset.title = rowData.title;
                newRevisionButton.dataset.url = rowData['route:newRevision'];
                newRevisionButton.title = "{{ trans('projectReport.newRevision') }}";
                newRevisionButton.className = 'btn btn-xs btn-success';
                newRevisionButton.innerHTML = '<i class="fas fa-file-medical"></i>';
                newRevisionButton.style['margin-right'] = '5px';

                container.appendChild(newRevisionButton);
            }

            const cloneFormButton = document.createElement('a');
            cloneFormButton.title = "{{ trans('projectReport.cloneTemplate') }}";
            cloneFormButton.className = "btn btn-xs btn-primary";
            cloneFormButton.innerHTML = '<i class="far fa-clone"></i>';
            cloneFormButton.style['margin-right'] = '5px';
            cloneFormButton.dataset.action = 'cloneTemplate';
            cloneFormButton.dataset.url = rowData['route:clone'];
            cloneFormButton.dataset.target = "#editorModal";
            cloneFormButton.dataset.toggle = "modal";

            container.appendChild(cloneFormButton);

            if(rowData.hasOwnProperty('route:lockRevision')) {
                const lockRevisionButton = document.createElement('a');
                lockRevisionButton.title = "{{ trans('projectReport.lockRevision') }}";
                lockRevisionButton.className = "btn btn-xs btn-success";
                lockRevisionButton.innerHTML = '<i class="fa fa-check"></i>';
                lockRevisionButton.style['margin-right'] = '5px';
                lockRevisionButton.dataset.action = 'lockRevision';
                lockRevisionButton.dataset.url = rowData['route:lockRevision'];
                lockRevisionButton.dataset.target = "#lockRevisionYesNoModal";
                lockRevisionButton.dataset.toggle = "modal";

                lockRevisionButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    $('#lockRevisionYesNoModalMessage').html("{{ trans('projectReport.allContentsWillBeLocked') . ' ' . trans('general.sureToProceed') }}");
                    $('#lockRevisionYesNoModal [data-action="actionYes"]').data('route_lock_revision', rowData['route:lockRevision']);
                });
                
                container.appendChild(lockRevisionButton);
            }

            if(rowData.hasOwnProperty('route:delete')) {
                let title = "{{ trans('projectReport.deleteTemplate') }}";
		        let text  = '<i class="fa fa-trash"></i>';

				if(rowData.revision > 0) {
					title = "{{ trans('projectReport.revertToPreviousRevision') }}";
					text  = '<i class="fas fa-undo"></i>';
				}

                const deleteButton = document.createElement('a');
                deleteButton.id = 'btnDeleteForm_' + rowData.id;
                deleteButton.dataset.url = rowData['route:delete'];
                deleteButton.dataset.toggle = 'tooltip';
                deleteButton.title = title;
                deleteButton.className = 'btn btn-xs btn-danger';
                deleteButton.innerHTML = text;
                deleteButton.style['margin-right'] = '5px';
                deleteButton.dataset.toggle = 'modal';
                deleteButton.dataset.target = '#yesNoModal';

                deleteButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    $('#yesNoModalMessage').html("{{ trans('projectReport.allContentsWillBeDeleted') . ' ' . trans('general.sureToProceed') }}");
                    $('#yesNoModal [data-action="actionYes"]').data('route_delete', rowData['route:delete']);
                });
                
                container.appendChild(deleteButton);
            }

            return container;
        }

        projectReportTemplatesListTable = new Tabulator('#project-report-templates-list-table', {
            fillHeight: true,
            pagination: "local",
            paginationSize: 30,
            columns: [
                { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                { title:"{{ trans('projectReport.title') }}", field: 'title', headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter: templateLinkFormatter },
                { title:"{{ trans('projectReport.revision') }}", field: 'revision', width: 100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", formatter: revisionFormatter },
                { title:"{{ trans('general.status') }}", field: 'status_text', width: 160, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", formatter: null },
                { title:"{{ trans('general.actions') }}", width: 120, hozAlign: 'left', cssClass:"text-center text-middle", headerSort:false, formatter: actionsFormatter },
            ],
            layout: "fitColumns",
            ajaxURL: "{{ route('projectReport.template.list') }}",
            placeholder: "{{ trans('projectReport.noTemplatesAvailable') }}",
            columnHeaderSortMulti: false,
        });

        const submit = async (e) => {
            e.preventDefault();

            const url = getSubmitButtonURL();
            const title = getTemplateNameInputValue();

            disableSubmit(true);

            app_progressBar.toggle();

            try {
                const options = {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        title: title.trim(),
                        _token: '{{{ csrf_token() }}}'
                    }),
                };

                const promise = await fetch(url, options);
                const response = await promise.json();

                if(!promise.ok || (promise.status !== 200) || !response.success) {
                    if(response.errors !== null && response.errors.hasOwnProperty('title')) {
                        $('#template-name-error').text(response.errors.title[0]);
                    }

                    throw new Error("{{ trans('general.anErrorHasOccured') }}");
                }

                $('#template-name-error').text('');
                $('#editorModal').modal('hide');
                projectReportTemplatesListTable.setData();
            } catch(err) {
                console.error(err.message);
                disableSubmit(false);
            } finally {
                app_progressBar.maxOut();
                app_progressBar.hide();
            }
        }

        $('#editorModal').on('shown.bs.modal', function (e) {
            selectInputField();
            disableSubmit(false);
        });

        function selectInputField() {
            $('#template-name-input').select();
        }

        function disableSubmit(disable) {
            $('#submit-button').prop('disabled', disable);
        }
        
        $(document).on('click', '#submit-button', submit);

        function changeEditorModalTitle(title) {
            $('#editorLabel').text(title);
        }

        function setTemplateNameInputValue(name) {
            $('#template-name-input').val(name);
        }

        function getTemplateNameInputValue() {
            return $('#template-name-input').val();
        }

        function setSubmitButtonURL(url) {
            $('#submit-button').data('url', url);
        }

        function getSubmitButtonURL() {
            return $('#submit-button').data('url');
        }

        function showEditorModal() {
            $('#editorModal').modal('show');
        }

        function hideEditorModal() {
            $('#editorModal').modal('hide');
        }

        /* Errors */
        function setTemplateNameError(error) {
            $('#template-name-error').text(error);
        }

        /* Create */
        $(document).on('click', '#btnCreateNewReportTemplate', function (e) {
            e.preventDefault();

            changeEditorModalTitle("{{ trans('projectReport.newReportTemplate') }}");
            setTemplateNameInputValue('');
            setTemplateNameError('');
            setSubmitButtonURL("{{ route('projectReport.template.store') }}");
        });

        /* Edit */
        $(document).on('click', '[data-action="update_title"]', function(e) {
            e.preventDefault();

            changeEditorModalTitle("{{ trans('projectReport.editTemplateTitle') }}");
            setTemplateNameInputValue($(this).data('title'));
            setSubmitButtonURL($(this).data('url'));
            setTemplateNameError('');
            showEditorModal();
        });

        /* New Revision */
        $(document).on('click', '[data-action="new_revision"]', function(e) {
            e.preventDefault();

            changeEditorModalTitle("{{ trans('projectReport.newRevision') }}");
            setTemplateNameInputValue($(this).data('title'));
            setSubmitButtonURL($(this).data('url'));
            setTemplateNameError('');
            showEditorModal();
        });

        /* Clone */
        $(document).on('click', '[data-action="cloneTemplate"]', function(e) {
            e.preventDefault();
            e.stopPropagation();

            changeEditorModalTitle("{{ trans('projectReport.cloneTemplate') }}");
            setSubmitButtonURL($(this).data('url'));
            setTemplateNameError('');
            showEditorModal();
        });

        $(document).on('click', '#yesNoModal [data-action="actionYes"]', deleteTemplateHandler);
        $(document).on('click', '#lockRevisionYesNoModal [data-action="actionYes"]', lockRevisionHander);

        async function lockRevisionHander(e) {
            e.preventDefault();

            const url = $(this).data('route_lock_revision');

            app_progressBar.toggle();

            try {
                const url = $(this).data('route_lock_revision');
                const options = {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        _token: '{{{ csrf_token() }}}'
                    }),
                };

                const promise = await fetch(url, options);
                const response = await promise.json();

                if(!promise.ok || (promise.status !== 200) || !response.success) {
                    throw new Error("{{ trans('general.anErrorHasOccured') }}");
                }

                $('#lockRevisionYesNoModal').modal('hide');
                projectReportTemplatesListTable.setData();
            } catch(err) {
                console.error(err.message);
                SmallErrorBox.refreshAndRetry();
            } finally {
                app_progressBar.maxOut();
                app_progressBar.hide();
            }
        };

        async function deleteTemplateHandler(e) {
            e.preventDefault();
            e.stopPropagation();

            app_progressBar.toggle();

            try {
                const url = $(this).data('route_delete');
                const options = {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        _token: '{{{ csrf_token() }}}'
                    }),
                };

                const promise = await fetch(url, options);
                const response = await promise.json();

                if(!promise.ok || (promise.status !== 200) || !response.success) {
                    throw new Error("{{ trans('general.anErrorHasOccured') }}");
                }

                $('#yesNoModal').modal('hide');
                projectReportTemplatesListTable.setData();
            } catch(err) {
                console.error(err.message);
                SmallErrorBox.refreshAndRetry();
            } finally {
                app_progressBar.maxOut();
                app_progressBar.hide();
            }
        };
    });
</script>	
@endsection