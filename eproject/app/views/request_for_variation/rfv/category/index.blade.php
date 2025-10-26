@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{ trans('modulePermissions.rfvCategories') }}</li>
	</ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-table" aria-hidden="true"></i> {{ trans('modulePermissions.rfvCategories') }}
            </h1>
        </div>
		<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
			<a id="btnCreateNewRfvCatogory" href="#" class="btn btn-primary btn-md pull-right header-btn" data-target="#editorModal" data-toggle="modal">
				<i class="fa fa-plus"></i> {{ trans('general.new') . ' ' . trans('requestForVariation.categoryOfRfv')  }}
			</a>
		</div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <div>
                    <div class="widget-body no-padding">
                        <div id="rfvCategoryTable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	@include('templates.yesNoModal', [
		'title'   => trans('requestForVariation.deleteRfvCategory'),
		'message' => trans('general.sureToProceed'),
	])
	@include('templates.warning_modal', [
		'message' => trans('requestForVariation.rfvCategoryInUse'),
	])
	@include('request_for_variation.rfv.partials.rfv_category_editor_modal')
	@include('request_for_variation.rfv.partials.kpiLimitUpdateLogModal')
@endsection

@section('js')
	<script>
		$(document).ready(function() {
			var kpiLimitFormatter = function(cell, formatterParams, onRendered) {
				if(cell.getRow().getData().kpi_limit == null) {
					return 'N/A';
				}

				return cell.getRow().getData().kpi_limit;
			};

			var kpiLimitActionsFormatter = function(cell, formatterParams, onRendered) {
				var editRfvKpiButton = document.createElement('a');
                editRfvKpiButton.id = 'btnEditRfvKpiLimit_' + cell.getRow().getData().id;
                editRfvKpiButton.className = 'btn btn-xs btn-warning';
				editRfvKpiButton.innerHTML = "{{ trans('general.edit') }}";
				editRfvKpiButton.href = cell.getRow().getData().route_kpi_limit_update;
				editRfvKpiButton.style['margin-right'] = '5px';

				var viewKpiUpdateLogButton = document.createElement('a');
                viewKpiUpdateLogButton.id = 'btnViewKpiUpdateLogButton_' + cell.getRow().getData().id;
                viewKpiUpdateLogButton.className = 'btn btn-xs btn-success';
				viewKpiUpdateLogButton.innerHTML = "{{ trans('general.logs') }}";

				viewKpiUpdateLogButton.addEventListener('click', function(e) {
					e.preventDefault();
					$('#kpiLimitUpdateLogModal [name=rfvCategoryId]').val(cell.getRow().getData().id);
					$('#kpiLimitUpdateLogModal').modal('show');
				});

				var container = document.createElement('div');
				container.appendChild(editRfvKpiButton);
				container.appendChild(viewKpiUpdateLogButton);

				return container;
			}

			var actionsFormatter = function(cell, formatterParams, onRendered) {
				var editRfvCategoryDescriptionButton = document.createElement('a');
                editRfvCategoryDescriptionButton.id = 'btnEditRfvCategoryDescription_' + cell.getRow().getData().id;
                editRfvCategoryDescriptionButton.className = 'btn btn-xs btn-warning';
				editRfvCategoryDescriptionButton.innerHTML = "{{ trans('general.edit') }}";
				editRfvCategoryDescriptionButton.style['margin-right'] = '5px';
				editRfvCategoryDescriptionButton.dataset.id = cell.getRow().getData().id;
				editRfvCategoryDescriptionButton.dataset.description = cell.getRow().getData().description;
				editRfvCategoryDescriptionButton.dataset.url = cell.getRow().getData().route_category_update;
				editRfvCategoryDescriptionButton.dataset.editable_check_url = cell.getRow().getData().route_editable_check;

				var deleteRfvCategoryButton = document.createElement('a');
				deleteRfvCategoryButton.id = 'btnDeleteRfvCategory_' + cell.getRow().getData().id;
				deleteRfvCategoryButton.className = 'btn btn-xs btn-danger';
				deleteRfvCategoryButton.innerHTML = "{{ trans('general.remove') }}";
                deleteRfvCategoryButton.dataset.csrf_token = "{{ csrf_token() }}";
				deleteRfvCategoryButton.style['margin-right'] = '5px';

				deleteRfvCategoryButton.addEventListener('click', function(e) {
                    e.preventDefault();

					$('[data-action=actionYes]').attr('data-deleteRoute', cell.getRow().getData().route_category_delete);
					
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
				container.appendChild(editRfvCategoryDescriptionButton);
				container.appendChild(deleteRfvCategoryButton);

				return container;
			}

			var rfvCategoryTable = new Tabulator("#rfvCategoryTable", {
				height: 400,
				layout:"fitColumns",
				columns:[
					{ title:"{{ trans('requestForVariation.categoryOfRfv') }}", field: 'description', align:"left", resizable:false, headerSort:false },
					{ title:"{{ trans('requestForVariation.kpiLimit') }} (%)", field: 'kpi_limit', align:"center", cssClass: 'text-center', width: 100, resizable:false, headerSort:false, formatter: kpiLimitFormatter },
					{ title:"{{ trans('requestForVariation.kpiLimit') . ' ' . trans('general.actions') }}", align:"center", cssClass: 'text-center', width: 120, resizable:false, headerSort:false, formatter: kpiLimitActionsFormatter },
					{ title:"{{ trans('general.actions') }}", align:"center", cssClass: 'text-center', width: 110, resizable:false, headerSort:false, formatter: actionsFormatter },
				],
				ajaxURL: "{{route('requestForVariation.categories.get')}}",
				ajaxConfig: 'GET',
				pagination: 'local',
			});

			var currentKpiLimitFormatter = function(cell, formatterParams, onRendered) {
				if(cell.getRow().getData().current_kpi_limit == null) {
					return 'N/A';
				}

				return cell.getRow().getData().current_kpi_limit;
            };

            var previouskpiLimitFormatter = function(cell, formatterParams, onRendered) {
				if(cell.getRow().getData().previous_kpi_limit == null) {
					return 'N/A';
				}

				return cell.getRow().getData().previous_kpi_limit;
            };

            $('#kpiLimitUpdateLogModal').on('shown.bs.modal', function() {
				var categoryId = $('#kpiLimitUpdateLogModal [name=rfvCategoryId]').val();
				var partialUrl = "{{route('requestForVariation.category.kpi.update.logs.get')}}";
				var url = partialUrl.replace('%7BrfvCategoryId%7D', categoryId);

                var rfvCategoryKpiLimitUpdateLogTable = new Tabulator("#rfvCategoryKpiLimitUpdateLogTable", {
                    height: 400,
                    layout:"fitColumns",
                    columns:[
                        { title:"{{ trans('general.previous') }} (%)", field: 'previous_kpi_limit', align:"center", cssClass: 'text-center', width: 100, resizable:false, headerSort:false, formatter: previouskpiLimitFormatter },
                        { title:"{{ trans('general.current') }} (%)", field: 'kpi_limit', align:"center", cssClass: 'text-center', width: 100, resizable:false, headerSort:false, formatter: currentKpiLimitFormatter },
                        { title:"{{ trans('users.name') }}", field: 'updated_by', width: 220, align:"left", resizable:false, headerSort:false },
						{ title:"{{ trans('general.date') . ' & ' . trans('general.time')  }}", field: 'updated_at', width: 220, align:"left", resizable:false, headerSort:false },
						{ title:"{{ trans('general.remarks') }}", field: 'remarks', align:"left", resizable:true, headerSort:false },
                    ],
                    ajaxURL: url,
                    ajaxConfig: 'GET',
                    pagination: 'local',
                });
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
			$(document).on('click', '[id^=btnCreateNewRfvCatogory]', function(e) {
				e.preventDefault();
				changeEditorModalTitle("{{ trans('requestForVariation.createNewRfvCategory') }}");
				setTemplateNameInputValue('');
				setTemplateNameError('');
				setSubmitButtonURL("{{ route('requestForVariation.category.store') }}");
			});

			/* Edit */
			$(document).on('click', '[id^=btnEditRfvCategoryDescription_]', function(e) {
				e.preventDefault();
				var self = $(this);

				$.ajax({
					url: self.attr('data-editable_check_url'),
					method: 'GET',
					success: function (response) {
						if(response.editable) {
							changeEditorModalTitle("{{ trans('requestForVariation.editRfvCategory') }}");
							setTemplateNameInputValue(self.data('description'));
							setSubmitButtonURL(self.data('url'));
							setTemplateNameError('');
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

			function submit(url, description) {
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}',
                        description: description.trim(),
                    },
                    success: function (data) {
						
						// return;
                        if (data['success']) {
                            hideEditorModal();
							rfvCategoryTable.setData();
                        } else {
                            setTemplateNameError(data['error']['description']);
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
                    url: $(this).attr('data-deleteRoute'),
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (data) {
                        if (data.success) {
                            $('#yesNoModal').modal('hide');
							rfvCategoryTable.setData();
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