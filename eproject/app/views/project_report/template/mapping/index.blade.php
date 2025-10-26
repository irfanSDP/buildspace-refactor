@extends('layout.main')

@section('css')
    <style>
		/*custom styling since table fully occupied container*/
        .tabulator .tabulator-tableHolder {
			border: none;
		}
    </style>
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
        <li>{{ link_to_route('projectReport.template.index', trans('projectReport.templates'), []) }}</li>
		<li>{{ trans('projectReport.reportTypes') }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-7 col-md-7 col-lg-7">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ trans('projectReport.reportTypes') }}
			</h1>
		</div>

		<div class="col-xs-12 col-sm-5 col-md-5 col-lg-5 button-row">
            <a id="btnCreateNewReportTemplate" href="#" class="btn btn-primary btn-md pull-right header-btn" data-target="#createReportTypesModal" data-toggle="modal">
				<i class="fa fa-plus"></i> {{ trans('projectReport.newReportType') }}
			</a>
		</div>
	</div>

	<div class="jarviswidget ">
		<header>
			<h2> {{ trans('projectReport.reportTypes') }} </h2>
		</header>
		<div>
			<div class="widget-body no-padding">
				<div id="project-report-types-list-table"></div>
			</div>
		</div>
	</div>
    @include('templates.generic_input_modal', [
        'modalId'      => 'createReportTypesModal',
        'title'        => trans('projectReport.newReportType'),
        'label'        => trans('projectReport.title'),
        'labelId'      => 'createReportTypesModalLabel',
        'inputId'      => 'mappingNameInput',
        'inputErrorId' => 'createReportTypeNameError',
    ])
    @include('templates.generic_table_modal', [
        'modalId'    => 'selectTemplatesModal',
        'title'      => trans('projectReport.templates'),
        'tableId'    => 'selectTemplateTable',
        'showSubmit' => true,
        'showCancel' => true,
        'cancelText' => trans('forms.close'),
    ])
    @include('templates.yesNoModal', [
        'modalId'   => 'yesNoModal',
        'titleId'   => 'yesNoModalTitle',
        'title'     => trans('general.confirmation'),
        'messageId' => 'yesNoModalMessage',
    ])
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            const mappingBindingLinkFormatter = function(cell, formatterParams, onRendered) {
                const rowData = cell.getRow().getData();

                let mappingBindingShowLink = document.createElement('a');
                mappingBindingShowLink.href = rowData['route:show'];
                mappingBindingShowLink.innerHTML = rowData.title;
                mappingBindingShowLink.dataset.toggle = 'tooltip';
                mappingBindingShowLink.title = "{{ trans('projectReport.templateBindings') }}";
                mappingBindingShowLink.style['user-select'] = 'none';

                return mappingBindingShowLink;
            }

            const actionsFormatter = function(cell, formatterParams, onRendered) {
                const rowData = cell.getRow().getData();

                const container = document.createElement('div');
                container.style.textAlign = "left";

                if(rowData.hasOwnProperty('route:update')) {
                    const editTitleButton = document.createElement('a');
                    editTitleButton.dataset.action = 'update_title';
                    editTitleButton.dataset.id = rowData.id;
                    editTitleButton.dataset.title = rowData.title;
                    editTitleButton.dataset.url = rowData['route:update'];
                    editTitleButton.dataset.toggle = 'tooltip';
                    editTitleButton.title = "{{ trans('projectReport.editMappingTitle') }}";
                    editTitleButton.className = 'btn btn-xs btn-warning';
                    editTitleButton.innerHTML = '<i class="fa fa-pencil-alt"></i>';
                    editTitleButton.style['margin-right'] = '5px';
        
                    container.appendChild(editTitleButton);
                }

                if(rowData.hasOwnProperty('route:delete')) {
                    const deleteButton = document.createElement('a');
                    deleteButton.dataset.url = rowData['route:delete'];
                    deleteButton.dataset.toggle = 'tooltip';
                    deleteButton.title = "{{ trans('projectReport.deleteMapping') }}";
                    deleteButton.className = 'btn btn-xs btn-danger';
                    deleteButton.innerHTML = '<i class="fa fa-trash"></i>';;
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

            let container = document.createElement('div');
            container.style.textAlign = "left";

            const projectReportTypesListTable = new Tabulator('#project-report-types-list-table', {
                fillHeight: true,
                pagination: "local",
                paginationSize: 30,
                columns: [
                    { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                    { title:"{{ trans('projectReport.title') }}", field: 'title', headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter: mappingBindingLinkFormatter },
                    { title:"{{ trans('general.actions') }}", width: 80, hozAlign: 'left', cssClass:"text-center text-middle", headerSort:false, formatter: actionsFormatter },
                ],
                layout: "fitColumns",
                ajaxURL: "{{ route('projectReport.types.list') }}",
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
                            setTemplateNameError(response.errors.title[0]);
                        }

                        throw new Error("{{ trans('general.anErrorHasOccured') }}");
                    }

                    setTemplateNameInputValue('');
                    hideEditorModal();
                    projectReportTypesListTable.setData();
                } catch(err) {
                    console.error(err.message);
                    disableSubmit(false);
                } finally {
                    app_progressBar.maxOut();
                    app_progressBar.hide();
                }
            }

            $('#createReportTypesModal').on('shown.bs.modal', function (e) {
                selectInputField();
                disableSubmit(false);
            });

            function selectInputField() {
                $('#mappingNameInput').select();
            }

            function disableSubmit(disable) {
                $('#submit-button').prop('disabled', disable);
            }
            
            $(document).on('click', '#submit-button', submit);

            function changeEditorModalTitle(title) {
                $('#createReportTypesModalLabel').text(title);
            }

            function setTemplateNameInputValue(name) {
                $('#mappingNameInput').val(name);
            }

            function getTemplateNameInputValue() {
                return $('#mappingNameInput').val();
            }

            function setSubmitButtonURL(url) {
                $('#submit-button').data('url', url);
            }

            function getSubmitButtonURL() {
                return $('#submit-button').data('url');
            }

            function showEditorModal() {
                $('#createReportTypesModal').modal('show');
            }

            function hideEditorModal() {
                $('#createReportTypesModal').modal('hide');
            }

            /* Errors */
            function setTemplateNameError(error) {
                $('#createReportTypeNameError').text(error);
            }

            /* Create */
            $(document).on('click', '#btnCreateNewReportTemplate', function (e) {
                e.preventDefault();

                changeEditorModalTitle("{{ trans('projectReport.newMapping') }}");
                setTemplateNameInputValue('');
                setTemplateNameError('');
                setSubmitButtonURL("{{ route('projectReport.type.store') }}");
            });

            /* Edit */
            $(document).on('click', '[data-action="update_title"]', function(e) {
                e.preventDefault();

                changeEditorModalTitle("{{ trans('projectReport.editMappingTitle') }}");
                setTemplateNameInputValue($(this).data('title'));
                setSubmitButtonURL($(this).data('url'));
                setTemplateNameError('');
                showEditorModal();
            });

            $(document).on('click', '#yesNoModal [data-action="actionYes"]', deleteMappingHandler);

            async function deleteMappingHandler(e) {
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
                    projectReportTypesListTable.setData();
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