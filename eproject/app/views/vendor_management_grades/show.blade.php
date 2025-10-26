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
        <li>{{ link_to_route('vendor.management.grade.index', trans('vendorManagement.vendorManagementGrades'), []) }}</li>
		<li>{{{ $grade->name }}}</li>
        <li>{{ trans('vendorManagement.gradeLevels') }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ trans('vendorManagement.gradeLevels') }}
			</h1>
		</div>

		<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
			<a id="btnCreateNewLevel" href="#" class="btn btn-primary btn-md pull-right header-btn" data-target="#gradeLevelEditorModal" data-toggle="modal">
				<i class="fa fa-plus"></i> {{ trans('vendorManagement.newLevel') }}
			</a>
		</div>
	</div>

	<div class="jarviswidget ">
		<header>
			<h2> {{ $grade->name }} </h2>
		</header>
		<div>
			<div class="widget-body no-padding">
				<div id="vendor-management-grade-levels-table"></div>
			</div>
		</div>
	</div>

    @include('vendor_management_grades.partials.vendor_management_grade_level_editor_modal', [
        'modalId' => 'gradeLevelEditorModal',
    ])
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
			var vendorManagementGradeLevelsTable = null;

            var actionsFormatter = function(cell, formatterParams, onRendered) {
                var editGradeLevelButton = document.createElement('a');
                editGradeLevelButton.id = 'btnEditGradeLevel_' + cell.getRow().getData().id;
                editGradeLevelButton.className = 'btn btn-xs btn-warning';
				editGradeLevelButton.innerHTML = '<i class="fa fa-pencil-alt"></i>';
				editGradeLevelButton.style['margin-right'] = '5px';
                editGradeLevelButton.dataset.description = cell.getRow().getData().description;
                editGradeLevelButton.dataset.score = cell.getRow().getData().score_upper_limit;
                editGradeLevelButton.dataset.definition = cell.getRow().getData().definition;
                editGradeLevelButton.dataset.url = cell.getRow().getData().route_update;

				var deleteButton = document.createElement('a');
				deleteButton.dataset.toggle = 'tooltip';
				deleteButton.title = "{{ trans('vendorManagement.deleteLevel') }}";
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
				container.appendChild(editGradeLevelButton);
                container.appendChild(deleteButton);

				return container;
			}

            var columns = [
                { title:"{{ trans('vendorManagement.level') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                { title:"{{ trans('vendorManagement.rating') }}", field:"description", hozAlign:'left', cssClass:"text-middle text-left", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", headerSort:false, editable:true },
                { title:"{{ trans('vendorManagement.definition') }}", field:"definition", hozAlign:'center', cssClass:"text-middle text-center", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", headerSort:false, editable:true },
                { title:"{{ trans('vendorManagement.upperLimit') }}", field:"score_upper_limit", width:170, hozAlign:'center', headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", cssClass:"text-center text-middle" },
                { title: '{{ trans('general.actions') }}</div>', width: 80, hozAlign: 'center', headerSort:false, cssClass:"text-center text-middle", formatter:actionsFormatter },
            ];

            vendorManagementGradeLevelsTable = new Tabulator('#vendor-management-grade-levels-table', {
                height:400,
				pagination:"local",
                columns: columns,
                layout:"fitColumns",
                ajaxURL: "{{ route('vendor.management.grade.levels.get', [$grade->id]) }}",
                movableColumns:true,
                placeholder:"{{ trans('general.noRecordsFound') }}",
                columnHeaderSortMulti:false,
            });

            $('#gradeLevelEditorModal').on('shown.bs.modal', function (e) {
                selectInputField();
                disableSubmit(false);
            });

			function selectInputField() {
                $('#level-description-input').select();
            }

			function disableSubmit(disable) {
                $('#submit-button').prop('disabled', disable);
            }

			$(document).on('click', '#submit-button', function () {
                disableSubmit(true);
                submit($(this).data('url'), $('#level-description-input').val(), $('#score-input').val(), $('#definition-input').val());
            });

			function getSubmitButtonURL() {
				return $('#submit-button').data('url');
			}

			function showEditorModal() {
                $('#gradeLevelEditorModal').modal('show');
            }

			function hideEditorModal() {
				$('#gradeLevelEditorModal').modal('hide');
			}

			/* Create */
            $(document).on('click', '#btnCreateNewLevel', function (e) {
				e.preventDefault();

                $('#editorLabel').text("{{ trans('vendorManagement.newLevel') }}");
                $('#level-description-input').val('');
                $('#score-input').val('');
                $('#definition-input').val('');
                $('#level-description-error').text('');
                $('#score-error').text('');
                $('#submit-button').data('url', "{{ route('vendor.management.grade.level.store', [$grade->id]) }}");
            });

			/* Edit */
			$(document).on('click', '[id^=btnEditGradeLevel_]', function(e) {
				e.preventDefault();

				$('#editorLabel').text("{{ trans('vendorManagement.editLevel') }}");
                $('#level-description-input').val($(this).data('description'));
                $('#score-input').val($(this).data('score'));
                $('#definition-input').val($(this).data('definition'));
                $('#submit-button').data('url', $(this).data('url'));
                $('#level-description-error').text('');
                $('#score-error').text('');
				showEditorModal();
			});

			function submit(url, description, score, definition) {
                $('#level-description-error').text('');
                $('#score-error').text('');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        description: description.trim(),
                        score_upper_limit: score.trim(),
                        definition: definition.trim(),
                        _token: '{{{ csrf_token() }}}',
                    },
                    success: function (data) {
                        if (data.success) {
                            hideEditorModal();
							vendorManagementGradeLevelsTable.setData();
                        }
                        else {
                            $('#level-description-error').text(data['errors']['description']);
                            $('#score-error').text(data['errors']['score_upper_limit']);
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
							vendorManagementGradeLevelsTable.setData();
                            $('#yesNoModal').modal('hide');
                        } else {
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