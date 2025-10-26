@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{ trans('formBuilder.formsLibrary') }}</li>
	</ol>
@endsection

@section('content')
    <div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">
                <i class="fas fa-building"></i> {{ trans('buildingInformationModelling.bimLevels') }}
			</h1>
		</div>

		<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
			<a id="btnCreateNewBimLevel" href="#" class="btn btn-primary btn-md pull-right header-btn" data-target="#bimLevelEditorModal" data-toggle="modal">
				<i class="fa fa-plus"></i> {{ trans('general.add') }}
			</a>
		</div>
	</div>

	<div class="jarviswidget ">
		<header>
			<h2> {{ trans('buildingInformationModelling.listofBimLevels') }} </h2>
		</header>
		<div>
			<div class="widget-body no-padding">
				<div id="bim-levels-table"></div>
			</div>
		</div>
	</div>

    @include('building_information_modelling.level.partials.bim_editor_modal', [
        'modalId' => 'bimLevelEditorModal'
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
            var bimLevelsTable = null;

            var actionsFormatter = function(cell, formatterParams, onRendered) {
                if(!cell.getRow().getData().canBeEdited) return null;

                var container = document.createElement('div');

                var editBimLevelNameButton = document.createElement('a');
                editBimLevelNameButton.id = 'btnEditBimLevelName_' + cell.getRow().getData().id;
                editBimLevelNameButton.dataset.id = cell.getRow().getData().id;
                editBimLevelNameButton.dataset.name = cell.getRow().getData().name;
                editBimLevelNameButton.dataset.url = cell.getRow().getData().route_update;
                editBimLevelNameButton.dataset.toggle = 'tooltip';
                editBimLevelNameButton.title = "{{ trans('buildingInformationModelling.editBimLevel') }}";
                editBimLevelNameButton.className = 'btn btn-xs btn-warning';
                editBimLevelNameButton.innerHTML = '<i class="fa fa-pencil-alt"></i>';
                editBimLevelNameButton.style['margin-right'] = '5px';

                container.appendChild(editBimLevelNameButton);

                var deleteButton = document.createElement('a');
                deleteButton.id = 'btnDeleteForm_' + cell.getRow().getData().id;
                deleteButton.dataset.url = cell.getRow().getData().route_delete;
                deleteButton.dataset.toggle = 'tooltip';
                deleteButton.title = "{{ trans('buildingInformationModelling.deleteBimLevel') }}";
                deleteButton.className = 'btn btn-xs btn-danger';
                deleteButton.innerHTML = '<i class="fa fa-trash"></i>';
                deleteButton.style['margin-right'] = '5px';
                deleteButton.dataset.toggle = 'modal';
                deleteButton.dataset.target = '#yesNoModal';

                deleteButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    $('#yesNoModalMessage').html("{{ trans('formBuilder.allContentsWillBeDeleted') . ' ' . trans('general.sureToProceed') }}");
                    $('#yesNoModal [data-action="actionYes"]').data('route_delete', cell.getRow().getData().route_delete);
                });
                
                container.appendChild(deleteButton);
                
                return container;
            }

            bimLevelsTable = new Tabulator('#bim-levels-table', {
                height:500,
				pagination:"local",
                columns: [
					{ title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
					{ title:"{{ trans('general.name') }}", field: 'name', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
					{ title:"{{ trans('general.actions') }}", width: 80, hozAlign: 'left', cssClass:"text-center text-middle", headerSort:false, formatter:actionsFormatter },
				],
                layout:"fitColumns",
				ajaxURL: "{{ route('buildingInformationModellingLevel.list') }}",
                movableColumns:true,
                placeholder:"{{ trans('general.noRecordsFound') }}",
                columnHeaderSortMulti:false,
            });

            $('#bimLevelEditorModal').on('shown.bs.modal', function (e) {
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
                $('#bimLevelEditorModal').modal('show');
            }

			function hideEditorModal() {
				$('#bimLevelEditorModal').modal('hide');
			}

			/* Errors */
			function setTemplateNameError(error) {
                $('#template-name-error').text(error);
            }

            /* Create */
            $(document).on('click', '#btnCreateNewBimLevel', function (e) {
				e.preventDefault();

                changeEditorModalTitle("{{ trans('buildingInformationModelling.createNewBimLevel') }}");
				setTemplateNameInputValue('');
				setTemplateNameError('');
				setSubmitButtonURL("{{ route('buildingInformationModellingLevel.store') }}");
            });

			/* Edit */
			$(document).on('click', '[id^=btnEditBimLevelName_]', function(e) {

				changeEditorModalTitle("{{ trans('buildingInformationModelling.editBimLevel') }}");
				setTemplateNameInputValue($(this).data('name'));
				setSubmitButtonURL($(this).data('url'));
				setTemplateNameError('');
				showEditorModal();
			});

            function submit(url, bimName) {
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        name: bimName.trim(),
                        _token: '{{{ csrf_token() }}}',
                    },
                    success: function (data) {
                        if (data['success']) {
                            hideEditorModal();
							bimLevelsTable.setData();
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
							bimLevelsTable.setData();
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
        });
    </script>
@endsection