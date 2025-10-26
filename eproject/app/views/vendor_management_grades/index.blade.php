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
		<li>{{ trans('vendorManagement.vendorManagementGrades') }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ trans('vendorManagement.vendorManagementGrades') }}
			</h1>
		</div>

		<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
			<a id="btnCreateNewGrade" href="#" class="btn btn-primary btn-md pull-right header-btn" data-target="#editorModal" data-toggle="modal">
				<i class="fa fa-plus"></i> {{ trans('vendorManagement.newGrade') }}
			</a>
		</div>
	</div>

	<div class="jarviswidget ">
		<header>
			<h2> {{ trans('formBuilder.formTemplates') }} </h2>
		</header>
		<div>
			<div class="widget-body no-padding">
				<div id="vendor-management-grades-table"></div>
			</div>
		</div>
	</div>

	@include('vendor_management_grades.partials.vendor_management_grade_editor_modal')
	@include('templates.yesNoModal', [
        'modalId'   => 'yesNoModal',
        'titleId'   => 'yesNoModalTitle',
        'title'     => trans('general.confirmation'),
        'messageId' => 'yesNoModalMessage',
    ])
	@include('templates.warning_modal', [
        'modalId'          => 'warningModal',
        'warningMessageId' => 'txtWarningMessage',
    ])
@endsection

@section('js')
	<script>
		$(document).ready(function() {
			var vendorManagementGradesTable = null;

			var templateLinkFormatter = function(cell, formatterParams, onRendered) {
				var templateLink = document.createElement('a');
				templateLink.id = 'btnShowGradeLevels_' + cell.getRow().getData().id;
				templateLink.href = cell.getRow().getData().route_show;
				templateLink.innerHTML = cell.getRow().getData().name;
				templateLink.dataset.toggle = 'tooltip';
				templateLink.title = "{{ trans('vendorManagement.editGradeLevels') }}";
				templateLink.style['user-select'] = 'none';

				return templateLink;
			}

			var actionsFormatter = function(cell, formatterParams, onRendered) {
				var editNameButton = document.createElement('a');
                editNameButton.id = 'btnEditGradeName_' + cell.getRow().getData().id;
				editNameButton.dataset.id = cell.getRow().getData().id;
				editNameButton.dataset.name = cell.getRow().getData().name;
				editNameButton.dataset.url = cell.getRow().getData().route_update;
				editNameButton.dataset.toggle = 'tooltip';
				editNameButton.title = "{{ trans('vendorManagement.editGradeName') }}";
                editNameButton.className = 'btn btn-xs btn-warning';
                editNameButton.innerHTML = '<i class="fa fa-pencil-alt"></i>';
                editNameButton.style['margin-right'] = '5px';

				var deleteButton = document.createElement('a');
				deleteButton.dataset.toggle = 'tooltip';
				deleteButton.title = "{{ trans('vendorManagement.deleteGrade') }}";
                deleteButton.className = 'btn btn-xs btn-danger';
                deleteButton.innerHTML = '<i class="fa fa-trash"></i>';
                deleteButton.style['margin-right'] = '5px';
				deleteButton.dataset.toggle = 'modal';
				deleteButton.dataset.target = '#yesNoModal';

				deleteButton.addEventListener('click', function(e) {
					e.preventDefault();

					$('#yesNoModalMessage').html("{{ trans('formBuilder.allContentsWillBeDeleted') . ' ' . trans('general.sureToProceed') }}");
					$('[data-action=actionYes]').data('route_delete', cell.getRow().getData().route_delete);
				});

				var container = document.createElement('div');
				container.appendChild(editNameButton);
				container.appendChild(deleteButton);

				return container;
			}

			var columns = [
				{ title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
				{ title: '<div class="text-center">{{ trans('general.name') }}</div>', field: 'name', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}",  formatter:templateLinkFormatter },
				{ title: '<div class="text-center">{{ trans('general.createdAt') }}</div>', field: 'created_at', hozAlign: 'center', width: 200, headerSort:false, headerFilter:"input" },
				{ title: '<div class="text-center">{{ trans('general.actions') }}</div>', width: 80, hozAlign: 'center', headerSort:false, formatter:actionsFormatter },
            ];

			vendorManagementGradesTable = new Tabulator('#vendor-management-grades-table', {
                height:400,
				pagination:"local",
                columns: columns,
                layout:"fitColumns",
                ajaxURL: "{{ route('vendor.management.grades.get') }}",
                movableColumns:true,
                placeholder:"{{ trans('vendorManagement.noGradesAvailable') }}",
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
            $(document).on('click', '#btnCreateNewGrade', function () {
                changeEditorModalTitle("{{ trans('vendorManagement.createNewGrade') }}");
				setTemplateNameInputValue('');
				setTemplateNameError('');
				setSubmitButtonURL("{{ route('vendor.management.grade.store') }}");
            });

			/* Edit */
			$(document).on('click', '[id^=btnEditGradeName_]', function(e) {
				e.preventDefault();
				changeEditorModalTitle("{{ trans('vendorManagement.editGradeName') }}");
				setTemplateNameInputValue($(this).data('name'));
				setSubmitButtonURL($(this).data('url'));
				setTemplateNameError('');
				showEditorModal();
			});

			function submit(url, gradeName) {
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}',
                        name: gradeName.trim(),
                    },
                    success: function (response) {
                        if (response['success']) {
                            hideEditorModal();
							vendorManagementGradesTable.setData();
                        } else {
                            setTemplateNameError(response['errors']['name']);
                            disableSubmit(false);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            }

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
							vendorManagementGradesTable.setData();
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

			function displayWarning(message) {
                $('#txtWarningMessage').html(message);
                $('#warningModal').modal('show');
            }
		});
	</script>
@endsection