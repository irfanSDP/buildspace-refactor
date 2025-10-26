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
		<li>{{ trans('formBuilder.formsLibrary') }}</li>
	</ol>
@endsection
<?php use PCK\FormBuilder\DynamicForm; ?>
@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ trans('formBuilder.formsLibrary') }}
			</h1>
		</div>

		<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
			<a id="btnCreateNewForm" href="#" class="btn btn-primary btn-md pull-right header-btn" data-target="#editorModal" data-toggle="modal">
				<i class="fa fa-plus"></i> {{ trans('formBuilder.newForm') }}
			</a>
		</div>
	</div>

	<div class="jarviswidget ">
		<header>
			<h2> {{ trans('formBuilder.formTemplates') }} </h2>
		</header>
		<div>
			<div class="widget-body no-padding">
				<div id="form-list-table"></div>
			</div>
		</div>
	</div>

	@include('form_builder.partials.form_editor_modal')
	@include('templates.yesNoModal', [
        'modalId'   => 'yesNoModal',
        'titleId'   => 'yesNoModalTitle',
        'title'     => trans('general.confirmation'),
        'messageId' => 'yesNoModalMessage',
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
	@include('templates.generic_table_modal', [
		'modalId'    => 'previousRevisionFormModal',
		'title'      => trans('formBuilder.previousRevisionForms'),
		'tableId'    => 'previousRevisionFormTable',
		'showCancel' => true,
		'cancelText' => trans('forms.close'),
	])
@endsection

@section('js')
	<script>
		$(document).ready(function() {
			var formListTable = null;
			var previousRevisionFormTable = null;

			var templateLinkFormatter = function(cell, formatterParams, onRendered) {
				var templateLink = document.createElement('a');
				templateLink.id = 'btnShowFormContents_' + cell.getRow().getData().id;
				templateLink.href = cell.getRow().getData().route_show;
				templateLink.innerHTML = cell.getRow().getData().name;
				templateLink.dataset.toggle = 'tooltip';
				templateLink.title = "{{ trans('formBuilder.editFormContents') }}";
				templateLink.style['user-select'] = 'none';

				return templateLink;
			}

			var actionsFormatter = function(cell, formatterParams, onRendered) {
				var container = document.createElement('div');
				container.style.textAlign = "left";

				if(cell.getRow().getData().status == "{{ DynamicForm::STATUS_OPEN }}") {
					var editNameButton = document.createElement('a');
					editNameButton.id = 'btnEditFormName_' + cell.getRow().getData().id;
					editNameButton.dataset.id = cell.getRow().getData().id;
					editNameButton.dataset.name = cell.getRow().getData().name;
					editNameButton.dataset.url = cell.getRow().getData().route_update;
					editNameButton.dataset.toggle = 'tooltip';
					editNameButton.title = "{{ trans('formBuilder.editFormName') }}";
					editNameButton.className = 'btn btn-xs btn-warning';
					editNameButton.innerHTML = '<i class="fa fa-pencil-alt"></i>';
					editNameButton.style['margin-right'] = '5px';

					container.appendChild(editNameButton);
				}

				if(cell.getRow().getData().status == "{{ DynamicForm::STATUS_DESIGN_APPROVED }}") {
					var newRevisionButton = document.createElement('a');
					newRevisionButton.id = 'btnNewRevisionButton_' + cell.getRow().getData().id;
					newRevisionButton.title = "{{ trans('formBuilder.newRevision') }}";
					newRevisionButton.className = 'btn btn-xs btn-success';
					newRevisionButton.innerHTML = '<i class="fas fa-file-medical"></i>';
					newRevisionButton.style['margin-right'] = '5px';
					newRevisionButton.dataset.toggle = 'modal';
					newRevisionButton.dataset.target = '#newRevisionYesNoModal';
					
					newRevisionButton.addEventListener('click', function(e) {
						e.preventDefault();

						$('#newRevisionYesNoModalMessage').html("{{ trans('formBuilder.createNewRevisionWarning') . ' ' . trans('general.sureToProceed') }}");
						$('#newRevisionYesNoModal [data-action="actionYes"]').data('route_new_revision', cell.getRow().getData().route_new_revision);
					});

					container.appendChild(newRevisionButton);
				}

				if(cell.getRow().getData().revision > 0) {
					var viewPreviousRevisionFormButton = document.createElement('a');
					viewPreviousRevisionFormButton.id = 'btnViewPreviousRevisionForm_' + cell.getRow().getData().id;
					viewPreviousRevisionFormButton.title = "{{ trans('formBuilder.previousRevisionForms') }}";
					viewPreviousRevisionFormButton.className = "btn btn-xs btn-success";
					viewPreviousRevisionFormButton.innerHTML = '<i class="fa fa-eye"></i>';
					viewPreviousRevisionFormButton.style['margin-right'] = '5px';
					viewPreviousRevisionFormButton.href = '#';
					viewPreviousRevisionFormButton.dataset.toggle = 'modal';
					viewPreviousRevisionFormButton.dataset.target = '#previousRevisionFormModal';

					viewPreviousRevisionFormButton.addEventListener('click', function(e) {
						e.preventDefault();

						$('#previousRevisionFormModal').data('url', cell.getRow().getData().route_previous_revision_forms);
					});

					container.appendChild(viewPreviousRevisionFormButton);
				}

				var cloneFormButton = document.createElement('a');
				cloneFormButton.id = 'btnCloneForm_' + cell.getRow().getData().id;
				cloneFormButton.title = "{{ trans('formBuilder.cloneForm') }}";
				cloneFormButton.className = "btn btn-xs btn-primary";
				cloneFormButton.innerHTML = '<i class="far fa-clone"></i>';
				cloneFormButton.style['margin-right'] = '5px';
				cloneFormButton.dataset.action = 'cloneForm';
				cloneFormButton.dataset.url = cell.getRow().getData().route_clone;
				cloneFormButton.dataset.target = "#editorModal";
				cloneFormButton.dataset.toggle = "modal";

				container.appendChild(cloneFormButton);

				var title   = "{{ trans('formBuilder.deleteForm') }}";
				var text    = '<i class="fa fa-trash"></i>';

				if(cell.getRow().getData().revision > 0) {
					title = "{{ trans('formBuilder.revertToPreviousRevision') }}";
					text  = '<i class="fas fa-undo"></i>';
				}

				if(cell.getRow().getData().status == "{{ DynamicForm::STATUS_OPEN }}") {
					var deleteButton = document.createElement('a');
					deleteButton.id = 'btnDeleteForm_' + cell.getRow().getData().id;
					deleteButton.dataset.url = cell.getRow().getData().route_delete;
					deleteButton.dataset.toggle = 'tooltip';
					deleteButton.title = title;
					deleteButton.className = 'btn btn-xs btn-danger';
					deleteButton.innerHTML = text;
					deleteButton.style['margin-right'] = '5px';
					deleteButton.dataset.toggle = 'modal';
					deleteButton.dataset.target = '#yesNoModal';

					deleteButton.addEventListener('click', function(e) {
						e.preventDefault();

						$('#yesNoModalMessage').html("{{ trans('formBuilder.allContentsWillBeDeleted') . ' ' . trans('general.sureToProceed') }}");
						$('#yesNoModal [data-action="actionYes"]').data('route_delete', cell.getRow().getData().route_delete);
					});
					
					container.appendChild(deleteButton);
				}

				return container;
			}

			var revisionFormatter = function(cell, formatterParams, onRendered) {
				if(cell.getRow().getData().revision == 0)
				{
					return "{{ trans('formBuilder.original') }}";
				}

				return cell.getRow().getData().revision;
			}

			var statusFormatter = function(cell, formatterParams, onRendered) {
				var status = cell.getRow().getData().status.toString();
				var text   = null;

				switch(status) {
					case "{{ DynamicForm::STATUS_OPEN }}":
						text = "{{ trans('formBuilder.open') }}";
						break;
					case "{{ DynamicForm::STATUS_DESIGN_PENDING_FOR_APPROVAL }}":
						text = "{{ trans('formBuilder.pendingForApproval') }}";
						break;
					case "{{ DynamicForm::STATUS_DESIGN_APPROVED }}":
						text = "{{ trans('formBuilder.approved') }}";
						break;
				}

				return text;
			}

			formListTable = new Tabulator('#form-list-table', {
                height:500,
				pagination:"local",
                columns: [
					{ title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
					{ title:"{{ trans('general.name') }}", field: 'name', headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:templateLinkFormatter },
					{ title:"{{ trans('formBuilder.revision') }}", field: 'revision', width: 100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", formatter: revisionFormatter },
					{ title:"{{ trans('general.status') }}", field: 'status', width: 160, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", formatter: statusFormatter },
					{ title:"{{ trans('general.actions') }}", width: 120, hozAlign: 'left', cssClass:"text-center text-middle", headerSort:false, formatter:actionsFormatter },
				],
                layout:"fitColumns",
				ajaxURL: "{{ $getFormsRoute }}",
                movableColumns:true,
                placeholder:"{{ trans('formBuilder.noFormsAvailable') }}",
                columnHeaderSortMulti:false,
            });

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

			$(document).on('click', '#submit-button', function () {
                disableSubmit(true);
                submit($(this).data('url'), getTemplateNameInputValue());
            });

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
            $(document).on('click', '#btnCreateNewForm', function (e) {
				e.preventDefault();

                changeEditorModalTitle("{{ trans('formBuilder.createNewForm') }}");
				setTemplateNameInputValue('');
				setTemplateNameError('');
				setSubmitButtonURL("{{ route('new.form.template.store') }}");
            });

			/* Edit */
			$(document).on('click', '[id^=btnEditFormName_]', function(e) {
				e.preventDefault();

				changeEditorModalTitle("{{ trans('formBuilder.editFormName') }}");
				setTemplateNameInputValue($(this).data('name'));
				setSubmitButtonURL($(this).data('url'));
				setTemplateNameError('');
				showEditorModal();
			});

			/* Clone */
			$(document).on('click', '[data-action="cloneForm"]', function(e) {
				e.preventDefault();
				e.stopPropagation();

				var url = $(this).data('url');

				changeEditorModalTitle("{{ trans('formBuilder.cloneForm') }}");
				setTemplateNameInputValue('');
				setTemplateNameError('');
				setSubmitButtonURL(url);
			});

			function submit(url, formName) {
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
						moduleIdentifier: "{{ $moduleIdentifier }}",
                        name: formName.trim(),
                        _token: '{{{ csrf_token() }}}',
                    },
                    success: function (data) {
                        if (data['success']) {
                            hideEditorModal();
							formListTable.setData();
                        }
                        else {
                            setTemplateNameError(data['errors']['name']);
                            disableSubmit(false);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            }

			$(document).on('click', '#newRevisionYesNoModal [data-action="actionYes"]', function(e) {
				e.preventDefault();
				e.stopPropagation();

                var url = $(this).data('route_new_revision');

				$.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: _csrf_token,
                    },
                    success: function (data) {
                        if (data.success) {
							formListTable.setData();
                            $('#newRevisionYesNoModal').modal('hide');
                        } else {
                            $('#newRevisionYesNoModal').modal('hide');
                            displayWarning(data.errors);
                        }
                    },
                    error: function (request, status, error) {
                        // error
                    }
                });
            });

			$(document).on('click', '#yesNoModal [data-action="actionYes"]', function(e) {
				e.preventDefault();
				e.stopPropagation();

                var url = $(this).data('route_delete');

				$.ajax({
                    url: url,
                    method: 'DELETE',
                    data: {
                        _token: _csrf_token,
                    },
                    success: function (data) {
                        if (data.success) {
							formListTable.setData();
                            $('#yesNoModal').modal('hide');
                        } else {
                            displayWarning(data.errors);
                            $('#yesNoModal').modal('hide');
                        }
                    },
                    error: function (request, status, error) {
                        // error
                    }
                });
            });

			var viewFormButtonFormatter = function(cell, formatterParams, onRendered) {
				var viewFormButton = document.createElement('a');
				viewFormButton.id = 'btnCloneForm_' + cell.getRow().getData().id;
				viewFormButton.title = "{{ trans('formBuilder.cloneForm') }}";
				viewFormButton.className = "btn btn-xs btn-success";
				viewFormButton.innerHTML = '<i class="fas fa-eye"></i>';
				viewFormButton.href = cell.getRow().getData().route_show;
				viewFormButton.target = '_blank';

				return viewFormButton;
			}

			$(document).on('shown.bs.modal', '#previousRevisionFormModal', function(e) {
				e.preventDefault();

				var url = $(this).data('url');

				previousRevisionFormTable = new Tabulator('#previousRevisionFormTable', {
                    height:300,
                    columns: [
                        { title: "{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                        { title: "{{ trans('formBuilder.formName') }}", field:"name", cssClass:"text-left", align: 'left', headerSort: false, headerFilter: 'input' },
						{ title: "{{ trans('formBuilder.revision') }}", field: 'revision', width: 100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", formatter: revisionFormatter },
						{ title:"{{ trans('general.actions') }}", width: 80, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter:viewFormButtonFormatter },
                    ],
                    layout:"fitColumns",
                    ajaxURL: url,
                    ajaxConfig: "GET",
                    pagination:"local",
                    placeholder:"{{{ trans('general.noRecordsFound') }}}",
                    columnHeaderSortMulti:false,
                });
			})

			function displayWarning(message) {
                $('#txtWarningMessage').html(message);
                $('#warningModal').modal('show');
            }
		});
	</script>
@endsection