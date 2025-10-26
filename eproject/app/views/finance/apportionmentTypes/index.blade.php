@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{ trans('accountCodes.apportionmentTypes') }}</li>
	</ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-list-alt" aria-hidden="true"></i> {{ trans('accountCodes.apportionmentTypes') }}
            </h1>
        </div>
		<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
			<a id="btnCreateNewApportionmentType" href="#" class="btn btn-primary btn-md pull-right header-btn" data-target="#editorModal" data-toggle="modal">
				<i class="fa fa-plus"></i> {{ trans('general.new') . ' ' . trans('accountCodes.apportionmentType')  }}
			</a>
		</div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <div>
                    <div class="widget-body no-padding">
                        <div id="apportionmentTypesTable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('templates.yesNoModal', [
		'title'   => trans('accountCodes.deleteApportionmentType'),
		'message' => trans('general.sureToProceed'),
	])
    @include('templates.warning_modal', [
		'message' => trans('accountCodes.apportionemtnTypeInUse'),
	])
	@include('finance.apportionmentTypes.partials.apportionment_type_editor_modal')
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            var apportionmentTypesTable = null;

            var actionsFormatter = function(cell, formatterParams, onRendered) {
				var editApportionmentTypeButton = document.createElement('a');
                editApportionmentTypeButton.id = 'btnEditApportionmentType_' + cell.getRow().getData().id;
                editApportionmentTypeButton.className = 'btn btn-xs btn-warning';
				editApportionmentTypeButton.innerHTML = '<i class="fa fa-pencil-alt"></i>';
				editApportionmentTypeButton.style['margin-right'] = '5px';
				editApportionmentTypeButton.dataset.id = cell.getRow().getData().id;
				editApportionmentTypeButton.dataset.name = cell.getRow().getData().name;
				editApportionmentTypeButton.dataset.url = cell.getRow().getData().route_update;
				editApportionmentTypeButton.dataset.editable_check_url = cell.getRow().getData().route_editable_check;

				var deleteApportionmentTypeButton = document.createElement('a');
				deleteApportionmentTypeButton.id = 'btnDeleteApportionmentType_' + cell.getRow().getData().id;
				deleteApportionmentTypeButton.className = 'btn btn-xs btn-danger';
				deleteApportionmentTypeButton.innerHTML = '<i class="fa fa-trash"></i>';
                deleteApportionmentTypeButton.dataset.csrf_token = "{{ csrf_token() }}";
				deleteApportionmentTypeButton.style['margin-right'] = '5px';

				deleteApportionmentTypeButton.addEventListener('click', function(e) {
                    e.preventDefault();

					$('[data-action=actionYes]').data('delete_route', cell.getRow().getData().route_delete);
	
					$.ajax({
						url: cell.getRow().getData().route_editable_check,
						method: 'GET',
						success: function (response) {
							if(response.editable) {
								$('#yesNoModal').modal('show');
							} else {
								$('#warningModal').modal('show');
							}
						},
						error: function (request, status, error) {
							// error
						}
					});
                });
			
				var container = document.createElement('div');
				container.appendChild(editApportionmentTypeButton);
				container.appendChild(deleteApportionmentTypeButton);

				return container;
			}

            var apportionmentTypesTable = new Tabulator("#apportionmentTypesTable", {
				height: 400,
				layout:"fitColumns",
				columns:[
                    { title:"{{ trans('general.no') }}", field: 'count', width: 30, align:"center", cssClass: 'text-center', resizable:false, headerSort:false },
					{ title:"{{ trans('accountCodes.apportionmentType') }}", field: 'name', align:"left", resizable:false, headerSort:false },
					{ title:"{{ trans('general.actions') }}", align:"center", cssClass: 'text-center', width: 80, resizable:false, headerSort:false, formatter: actionsFormatter },
				],
				ajaxURL: "{{ route('apportionment.types.table.data.get') }}",
				ajaxConfig: 'GET',
				pagination: 'local',
			});

            $('#editorModal').on('shown.bs.modal', function (e) {
                selectInputField();
                disableSubmit(false);
            });

			function selectInputField() {
                $('#name-input').select();
            }

			function disableSubmit(disable) {
                $('#submit-button').prop('disabled', disable);
            }

			$(document).on('click', '#submit-button', function () {
                disableSubmit(true);
                submit($(this).data('url'), getApportionmentTypeNameInputValue());
            });

			function changeEditorModalTitle(title) {
                $('#editorLabel').text(title);
            }

			function setApportionmentTypeNameInputValue(name) {
                $('#name-input').val(name);
            }

			function getApportionmentTypeNameInputValue() {
                return $('#name-input').val();
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
			function setApportionmentTypeNameError(error) {
                $('#name-error').text(error);
            }

			/* Create */
			$('#btnCreateNewApportionmentType').on('click', function(e) {
				e.preventDefault();
				changeEditorModalTitle("{{ trans('general.new') . ' ' . trans('accountCodes.apportionmentType')  }}");
				setApportionmentTypeNameInputValue('');
				setApportionmentTypeNameError('');
				setSubmitButtonURL("{{ route('apportionment.type.store') }}");
			});

			/* Edit */
			$(document).on('click', '[id^=btnEditApportionmentType_]', function(e) {
				e.preventDefault();
				var self = $(this);

				$.ajax({
					url: self.data('editable_check_url'),
					method: 'GET',
					success: function (response) {
						if(response.editable) {
							changeEditorModalTitle("{{ trans('general.edit') . ' ' . trans('accountCodes.apportionmentType')  }}");
							setApportionmentTypeNameInputValue(self.data('name'));
							setSubmitButtonURL(self.data('url'));
							setApportionmentTypeNameError('');
							showEditorModal();
						} else {
							$('#warningModal').modal('show');
						}
					},
					error: function (request, status, error) {
						// error
					}
				});
			});

            function submit(url, name) {
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}',
                        name: name.trim(),
                    },
                    success: function (data) {
						
						// return;
                        if (data['success']) {
                            hideEditorModal();
							apportionmentTypesTable.setData();
                        } else {
                            setApportionmentTypeNameError(data['error']['name']);
                            disableSubmit(false);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            }

            $(document).on('click', '[data-action="actionYes"]', function(e) {
				e.preventDefault();
				e.stopPropagation();

				$.ajax({
                    url: $(this).data('delete_route'),
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (data) {
                        if (data.success) {
                            $('#yesNoModal').modal('hide');
							apportionmentTypesTable.setData();
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