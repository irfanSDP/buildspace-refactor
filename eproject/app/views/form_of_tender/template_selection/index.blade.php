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
		<li>{{ trans('formOfTender.formOfTender') }}</li>
		<li>{{ trans('formOfTender.listOfTemplates') }}</li>
	</ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-list-alt"></i> {{ trans('formOfTender.listOfTemplates') }}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <a id="btnCreateNewTemplate" href="#" class="btn btn-primary btn-md pull-right header-btn" data-target="#editorModal" data-toggle="modal">
                <i class="fa fa-plus"></i> {{ trans('formOfTender.newTemplate') }}
            </a>
        </div>
    </div>

    <div class="jarviswidget ">
		<header>
			<h2> {{ trans('formOfTender.listOfTemplates') }} </h2>
		</header>
		<div>
			<div class="widget-body no-padding">
				<div id="template-list-table"></div>
			</div>
		</div>
    </div>
    
    @include('form_of_tender.partials.template_editor_modal')

@endsection

@section('js')
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script>
        $(document).ready(function() {
            var templateListTable = null;

            var templateLinkFormatter = function(cell, formatterParams, onRendered) {
				var templateLink = document.createElement('a');
				templateLink.id = 'btnShowTemplateLink_' + cell.getRow().getData().id;
				templateLink.href = cell.getRow().getData().route_edit_template;
				templateLink.innerHTML = cell.getRow().getData().name;
				templateLink.dataset.toggle = 'tooltip';
				templateLink.title = "{{ trans('letterOfAward.editTemplateContents') }}";
				templateLink.style['user-select'] = 'none';

				return templateLink;
			}

			var actionsFormatter = function(cell, formatterParams, onRendered) {
                var templateButton = document.createElement('a');
				templateButton.id = 'btnViewTemplateLink_' + cell.getRow().getData().id;
				templateButton.href = cell.getRow().getData().route_edit_template;
				templateButton.innerHTML = cell.getRow().getData().name;
				templateButton.dataset.toggle = 'tooltip';
                templateButton.title = "{{ trans('letterOfAward.editTemplateContents') }}";
                templateButton.className = 'btn btn-xs btn-default';
                templateButton.innerHTML = '<i class="fa fa-th-list"></i>';
                templateButton.style['margin-right'] = '5px';
                templateButton.style['user-select'] = 'none';
                
				var editNameButton = document.createElement('a');
                editNameButton.id = 'btnEditTemplateName_' + cell.getRow().getData().id;
				editNameButton.dataset.id = cell.getRow().getData().id;
				editNameButton.dataset.name = cell.getRow().getData().name;
				editNameButton.dataset.url = cell.getRow().getData().route_update_name;
				editNameButton.dataset.toggle = 'tooltip';
				editNameButton.title = "{{ trans('formOfTender.editTemplateName') }}";
                editNameButton.className = 'btn btn-xs btn-default';
                editNameButton.innerHTML = '<i class="fa fa-pencil-alt"></i>';
                editNameButton.style['margin-right'] = '5px';

				var deleteButton = document.createElement('a');
                deleteButton.id = 'btnDeleteTemplate_' + cell.getRow().getData().id;
                deleteButton.href = cell.getRow().getData().route_delete_template;
				deleteButton.dataset.toggle = 'tooltip';
				deleteButton.title = "{{ trans('formOfTender.deleteTemplate') }}";
                deleteButton.className = 'btn btn-xs btn-danger';
                deleteButton.innerHTML = '<i class="fa fa-trash"></i>';
                deleteButton.dataset.method = 'delete';
                deleteButton.dataset.csrf_token = cell.getRow().getData().csrf_token;
                deleteButton.style['margin-right'] = '5px';

                var container = document.createElement('div');
                container.appendChild(templateButton);
				container.appendChild(editNameButton);
                container.appendChild(deleteButton);

				return container;
			}

            var columns = [
                { title: "id", field: 'id', visible:false },
				{ title: '<div class="text-center">{{ trans('general.no') }}</div>', field: 'indexNo', width: 60, align: 'center', headerSort:false },
				{ title: '<div class="text-left">{{ trans('general.name') }}</div>', field: 'name', headerSort:false, formatter:templateLinkFormatter },
				{ title: '<div class="text-center">{{ trans('general.actions') }}</div>', width: 120, 'align': 'center', headerSort:false, formatter:actionsFormatter },
            ];

            templateListTable = new Tabulator('#template-list-table', {
                height:400,
				pagination:"local",
                columns: columns,
                layout:"fitColumns",
                ajaxURL: "{{ route('form_of_tender.templates.all.get') }}",
                movableColumns:true,
                placeholder:"No Data Available",
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
            $(document).on('click', '#btnCreateNewTemplate', function () {
                changeEditorModalTitle("{{ trans('letterOfAward.createNewTemplate') }}");
				setTemplateNameInputValue('');
				setTemplateNameError('');
				setSubmitButtonURL("{{ route('form_of_tender.template.store') }}");
            });

			/* Edit */
			$(document).on('click', '[id^=btnEditTemplateName_]', function(e) {
				e.preventDefault();
				changeEditorModalTitle("{{ trans('letterOfAward.editTemplateName') }}");
				setTemplateNameInputValue($(this).data('name'));
				setSubmitButtonURL($(this).data('url'));
				setTemplateNameError('');
				showEditorModal();
			});

			function submit(url, templateName) {
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}',
                        name: templateName.trim(),
                    },
                    success: function (data) {
                        if (data['success']) {
                            hideEditorModal();
							templateListTable.setData();
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
		});
    </script>
@endsection