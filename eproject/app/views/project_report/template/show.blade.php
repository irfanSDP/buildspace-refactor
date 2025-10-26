@extends('layout.main')

@section('css')
    <style>
		/*custom styling since table fully occupied container*/
        .tabulator .tabulator-tableHolder {
			border: none;
		}

        .hidden {
            display: none;
        }

        .buttons-row {
            display: flex;
            gap: 5px;
            justify-content: flex-end;
        }

        .sortable-chosen {
            background-color: #dee2e6;
            opacity: 20%
        }

        .sortable-dragging {
            background-color: #74c0fc;
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
<?php use PCK\ProjectReport\ProjectReportColumn; ?>
@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{ link_to_route('projectReport.template.index', trans('projectReport.templates'), []) }}</li>
		<li>{{ $template->title }}</li>
	</ol>
@endsection

@section('content')
    <div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">&nbsp;</h1>
		</div>
        @if($canEditTemplate)
		<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
			<a id="btnAddColumn" href="#" class="btn btn-primary btn-md pull-right header-btn" data-target="#columnSelectionModal" data-toggle="modal" data-depth="0">
				<i class="fa fa-plus"></i> {{ trans('projectReport.addColumn') }}
			</a>
		</div>
        @endif
	</div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header>
                    <h2><i class="fa fa-list"></i> {{ $template->title }}</h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <form action="demo-contacts.php" method="post" class="smart-form">
                            <fieldset id="columns_container">
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('project_report.partials.project_report_components', ['canEditTemplate' => $canEditTemplate])
    @if($canEditTemplate)
        @include('project_report.template.partials.report_column_modal', [
            'modalId'       => 'columnSelectionModal',
            'selectOptions' => $columnSelections,

        ])
        @include('templates.yesNoModal', [
            'modalId'   => 'deleteColumnModal',
            'titleId'   => 'deleteColumnModalTitle',
            'title'     => trans('general.confirmation'),
            'message'   => trans('projectReport.allContentsWillBeDeleted') . ' ' . trans('general.sureToProceed'),
        ])
    @endif
@endsection

@section('js')
<script>
    $(document).ready(function() {
        const columnsContainer = document.getElementById('columns_container');
        const columnTemplate = document.getElementById('column-template');
        const columnGroupContainerTemplate = document.getElementById('column-group-container-template');
        const emptyColumnGroupTemplate = document.getElementById('empty_column_group_template');
        const textareaTemplate = document.getElementById('textarea-template');
        const addSubColumnTemplate = document.getElementById('add_sub_column_template');
        const singleEntryColumnList = {{ json_encode(ProjectReportColumn::isSingleEntryColumnList()) }};
        const systemColumnList = {{ json_encode(ProjectReportColumn::isSystemColumnList()) }}

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

            @if($canEditTemplate)
            column.dataset.swap_url = el['route:swap'];

            column.querySelector('[data-action="edit_column"]').dataset.id = el.id;
            column.querySelector('[data-action="edit_column"]').dataset.depth = el.depth;
            column.querySelector('[data-action="edit_column"]').dataset.url = el['route:update'];
            column.querySelector('[data-action="edit_column"]').dataset.type = el.type;
            column.querySelector('[data-action="edit_column"]').dataset.typeLabel = el.typeLabel;
            column.querySelector('[data-action="edit_column"]').dataset.singleEntry = el.singleEntry;
            column.querySelector('[data-action="edit_column"]').dataset.title = el.title;

            column.querySelector('[data-action="delete_column"]').dataset.url = el['route:delete'];
            @endif

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

        const constructTextarea = (type, typeLabel, singleEntry) => {
            const label = textareaTemplate.cloneNode(true);
            label.removeAttribute('id');
            label.classList.remove('hidden');

            // Ensure we target the textarea inside the label
            const textarea = label.querySelector('textarea');
            if (! textarea) {
                //console.error('No textarea element found inside the label.');
                return label;
            }

            @if($canEditTemplate)
                // Check if the textarea is readonly (both attribute and property)
                /*const isReadOnly = textarea.hasAttribute('readonly');
                if (isReadOnly) {
                    textarea.removeAttribute('readonly');
                }*/

                // Add value to the textarea
                if (singleEntryColumnList.includes(type)) {
                    textarea.value = singleEntry ? `${typeLabel} ({{ trans('projectReport.singleEntry') }})` : typeLabel;
                } else {
                    if (systemColumnList.includes(type)) {
                        textarea.value = '{{ trans('projectReport.systemGenerated') }}';
                    }
                }
            @endif

            return label;
        };

        const constructColumnRecursively = el => {
            let column = constructColumn(el);
            column.querySelector('.row').dataset.id = el.id;
            column.querySelector('.row').dataset.swap_url = el['route:swap'];

            if(el.type === {{ ProjectReportColumn::COLUMN_GROUP }}) {
                const addSubColumnButton = constructAddSubColumnButton(el);

                @if($canEditTemplate)
                column.querySelector('[data-component="parent_buttons_container"]').prepend(addSubColumnButton);
                @endif

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
                const textarea = constructTextarea(el.type, el.typeLabel, el.singleEntry);
                column.querySelector('[data-component="content_container"]').appendChild(textarea);
            }

            @if($canEditTemplate)
            if(el.hasOwnProperty('parent')) {
                const sortable = new Sortable(column, {
                    group: `column_group_${el.parent.id}`,
                    swap: true,
                    swapClass: 'sortable-dragging',
                    chosenClass: "sortable-chosen",
                    handle: `.handle`,
                    animation: 300,
                    easing: "cubic-bezier(1, 0, 0, 1)",
                    onEnd: swapCallback,
                });
            }
            @endif

            return column;
        };

        const renderColumns = async () => {
            app_progressBar.toggle();

            try {
                clearChildNodes(columnsContainer);

                const responseData = await fetchData("{{ route('projectReport.template.columns.get', [$template->id]) }}");

                responseData.forEach(el => {
                    const column = constructColumnRecursively(el);

                    columnsContainer.appendChild(column);

                    @if($canEditTemplate)
                    Sortable.create(columnsContainer, {
                        group: 'group',
                        swap: true,
                        swapClass: 'sortable-dragging',
                        chosenClass: "sortable-chosen",
                        handle: '.handle',
                        animation: 300,
                        easing: "cubic-bezier(1, 0, 0, 1)",
                        onEnd: swapCallback,
                    });
                    @endif
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

        @if($canEditTemplate)
        $('.select').select2();

        const submit = async (e) => {
            e.preventDefault();

            setTemplateNameError('');
            setColumnTypeError('');

            const url = getSubmitButtonURL();
            const columnType = getColumnTypeSelectValue();
            const title = ['{{ ProjectReportColumn::COLUMN_CUSTOM }}', '{{ ProjectReportColumn::COLUMN_GROUP }}', '{{ ProjectReportColumn::COLUMN_DATE }}', '{{ ProjectReportColumn::COLUMN_NUMBER }}'].includes(columnType) ? getTemplateNameInputValue() : '';
            const id = getData('id');
            const depth = getData('depth');
            const singleEntry = ['{{ ProjectReportColumn::COLUMN_CUSTOM }}', '{{ ProjectReportColumn::COLUMN_DATE }}', '{{ ProjectReportColumn::COLUMN_NUMBER }}'].includes(columnType) ? getTemplateSingleEntry() : false;

            disableSubmit(true);

            app_progressBar.toggle();

            try {
                const options = {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        title: title.trim(),
                        columnId: id,
                        columnType: columnType,
                        singleEntry: singleEntry,
                        depth: depth,
                        _token: '{{{ csrf_token() }}}'
                    }),
                };

                const promise = await fetch(url, options);
                const response = await promise.json();

                if(!promise.ok || (promise.status !== 200) || !response.success) {
                    if(response.errors !== null) {
                        const { title:titleError, columnType:columnTypeError } = response.errors;
   
                        (titleError !== undefined) && setTemplateNameError(titleError[0]);
                        (columnTypeError !== undefined) && setColumnTypeError(columnTypeError[0]);
                    }

                    throw new Error("{{ trans('general.anErrorHasOccured') }}");
                }

                hideEditorModal();
                renderColumns();
            } catch(err) {
                console.error(err.message);
                disableSubmit(false);
                SmallErrorBox.refreshAndRetry();
            } finally {
                app_progressBar.maxOut();
                app_progressBar.hide();
            }
        }

        $('#columnSelectionModal').on('shown.bs.modal', function (e) {
            disableSubmit(false);
        });

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

        function setTemplateSingleEntry(value) {
            if (value) {
                $('#template-single-entry').prop('checked', true);
            } else {
                $('#template-single-entry').prop('checked', false);
            }
        }

        function getTemplateNameInputValue() {
            return $('#template-name-input').val();
        }

        function getTemplateSingleEntry() {
            return $('#template-single-entry').is(':checked');
        }

        function setColumnTypeSelectValue(value) {
            return $('#column_type').val(value).trigger('change');
        }

        function getColumnTypeSelectValue() {
            return $('#column_type').val();
        }

        function setSubmitButtonURL(url) {
            $('#submit-button').data('url', url);
        }

        function getSubmitButtonURL() {
            return $('#submit-button').data('url');
        }

        function showEditorModal() {
            $('#columnSelectionModal').modal('show');
        }

        function hideEditorModal() {
            $('#columnSelectionModal').modal('hide');
        }

        function setData(key, value) {
            $('#columnSelectionModal').data(key, value);
        }

        function getData(key) {
            return $('#columnSelectionModal').data(key);
        }

        function clearData(key) {
            $('#columnSelectionModal').removeData(key);
        }

        /* Errors */
        function setTemplateNameError(error) {
            $('#template-name-error').text(error);
        }

        function setColumnTypeError(error) {
            $('#column-type-error').text(error);
        }

        /* Create */
        $(document).on('click', '#btnAddColumn', function (e) {
            e.preventDefault();

            const depth = this.dataset.depth;

            clearData('id');
            clearData('depth');

            setData('depth', depth);

            changeEditorModalTitle("{{ trans('projectReport.addColumn') }}");
            setTemplateNameInputValue('');
            setTemplateNameError('');
            setColumnTypeError('');
            setColumnTypeSelectValue('');
            setTemplateSingleEntry(false);
            setSubmitButtonURL("{{ route('projectReport.template.column.store', [$template->id]) }}");
        });

        /* Create sub-column */
        $(document).on('click', '[data-action="add_sub_column"]', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            const depth = this.dataset.depth;

            clearData('id');
            clearData('depth');

            setData('id', id);
            setData('depth', depth);

            changeEditorModalTitle("{{ trans('projectReport.addSubColumn') }}");
            setTemplateNameInputValue('');
            setTemplateNameError('');
            setColumnTypeError('');
            setColumnTypeSelectValue('');
            setTemplateSingleEntry(false);
            setSubmitButtonURL("{{ route('projectReport.template.column.store', [$template->id]) }}");
            showEditorModal();
        });

        /* Edit */
        $(document).on('click', '[data-action="edit_column"]', function(e) {
            e.preventDefault();

            const id = this.dataset.id;
            const depth = this.dataset.depth;

            clearData('id');
            clearData('depth');

            setData('id', id);
            setData('depth', depth);

            changeEditorModalTitle("{{ trans('projectReport.editColumn') }}");
            setColumnTypeSelectValue(this.dataset.type);
            setTemplateSingleEntry(this.dataset.singleEntry === 'true');
            setTemplateNameInputValue(this.dataset.title);
            setSubmitButtonURL(this.dataset.url);
            setTemplateNameError('');
            setColumnTypeError('');
            showEditorModal();
        });

        /* Delete */
        $(document).on('click', '[data-action="delete_column"]', function(e) {
            e.preventDefault();

            const url = this.dataset.url;

            $('#deleteColumnModal button[data-action="actionYes"]').data('url', url);
            $('#deleteColumnModal').modal('show');
        });

        /* Delete */
        $(document).on('click', '[data-action="delete_column"]', function(e) {
            e.preventDefault();

            const url = this.dataset.url;

            $('#deleteColumnModal button[data-action="actionYes"]').data('url', url);
            $('#deleteColumnModal').modal('show');
        });

        $(document).on('click', '#deleteColumnModal button[data-action="actionYes"]', deleteColumnHandler);

        async function deleteColumnHandler() {
            app_progressBar.toggle();

            try {
                const url = $(this).data('url');
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

                $('#deleteColumnModal').modal('hide');
                renderColumns();
            } catch (err) {
                SmallErrorBox.refreshAndRetry();
                console.error(err.message);
            } finally {
                app_progressBar.maxOut();
                app_progressBar.hide();
            }
        };

        $(document).on('change', '#column_type', function() {
            const columnType = getColumnTypeSelectValue();

            if(['{{ ProjectReportColumn::COLUMN_CUSTOM }}', '{{ ProjectReportColumn::COLUMN_GROUP }}', '{{ ProjectReportColumn::COLUMN_DATE }}', '{{ ProjectReportColumn::COLUMN_NUMBER }}' ].includes(columnType)) {
                $('#template-name-input').closest('.form-group').show(200);

                if(['{{ ProjectReportColumn::COLUMN_CUSTOM }}', '{{ ProjectReportColumn::COLUMN_DATE }}', '{{ ProjectReportColumn::COLUMN_NUMBER }}'].includes(columnType)) {
                    $('#template-single-entry').closest('.form-group').show(200);
                } else {
                    $('#template-single-entry').closest('.form-group').hide(200);
                }
            } else {
                $('#template-name-input').closest('.form-group').hide(200);
                $('#template-single-entry').closest('.form-group').hide(200);
            }
        });

        const swapCallback = async e => {
            const draggedColumnId = e.item.dataset.id;
            const swappedColumnId = e.swapItem.dataset.id;
            const url = e.item.dataset.swap_url;

            if(draggedColumnId === swappedColumnId) return;

            app_progressBar.toggle();

            try {
                const options = {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        draggedColumnId: draggedColumnId,
                        swappedColumnId: swappedColumnId,
                        _token: '{{{ csrf_token() }}}'
                    }),
                };

                const promise = await fetch(url, options);
                const response = await promise.json();

                if(!promise.ok || (promise.status !== 200) || !response.success) {
                    throw new Error("{{ trans('general.anErrorHasOccured') }}");
                }
            } catch (err) {
                SmallErrorBox.refreshAndRetry();
                console.error(err.message);
            } finally {
                app_progressBar.maxOut();
                app_progressBar.hide();
            }
        };
        @endif
    });
    
</script>
@endsection