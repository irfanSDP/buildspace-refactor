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
		<li>{{ trans('emailNotificationSettings.emailNotificationSettings') }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> {{ trans('emailNotificationSettings.emailNotificationSettings') }}
			</h1>
		</div>
	</div>

	<div class="jarviswidget ">
		<header>
			<h2> {{ trans('emailNotificationSettings.emailNotificationSettings') }} </h2>
		</header>
		<div>
			<div class="widget-body no-padding">
			<ul id="tabPanes" class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#externalUsersEmailNotificationSettingsTab" aria-controls="home" role="tab" data-toggle="tab" id="externalUsersTabHeader"><i class="far fa-envelope"></i>&nbsp;{{ trans('emailNotificationSettings.externalUsers') }}</a></li>
                    <li role="presentation"><a href="#internalUsersEmailNotificationSettingsTab" aria-controls="profile" role="tab" data-toggle="tab" id="internalUsersTabHeader"><i class="far fa-envelope"></i>&nbsp;{{ trans('emailNotificationSettings.internalUsers') }}</a></li>
                </ul>
                <div id="navigationTabs" class="tab-content padding-10">
                    <div role="tabpanel" class="tab-pane fade in active" id="externalUsersEmailNotificationSettingsTab">
						<div id="externalUsersEmailNotificationSettingsTable"></div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade in" id="internalUsersEmailNotificationSettingsTab">
						<div id="internalUsersEmailNotificationSettingsTable"></div>
                    </div>
                </div>
			</div>
		</div>
	</div>

	@include('module_parameters.email_notification_settings.partials.modifiable_contents_modal', [
		'modalId' => 'modifiableContentsModal',
	])
	@include('module_parameters.email_notification_settings.partials.email_contents_preview_modal', [
		'modalId' => 'emailPreviewModal',
		'title'	  => trans('vendorManagement.emailPreview'),
	])
@endsection

@section('js')
	<script>
		$(document).ready(function(e) {
			var externalUsersEmailNotificationSettingsTable = null;
			var internalUsersEmailNotificationSettingsTable = null;

			$('#tabPanes').on('shown.bs.tab', function(e) {
                var activeTabId = e.target.id;

                switch(activeTabId) {
                    case 'externalUsersTabHeader':
                        renderExternalUserEmailNotificationSettingsTable();
                        break;
                    case 'internalUsersTabHeader':
                        renderInternalUserEmailNotificationSettingsTable();
                        break;
                    default:
                        // nothing here
                }
            });

			var activatedStatusFormatter = function(cell, formatterParams, onRendered) {
				var table 			 = cell.getTable();
				var row				 = cell.getRow();
                var rowData 	     = row.getData();
				var buttonText       = rowData.activated ? "{{ trans('emailNotificationSettings.activated') }}" : "{{ trans('emailNotificationSettings.deactivated') }}";
				var buttonColorClass = rowData.activated ? 'btn-success' : 'btn-warning';
				var buttonPopupTitle = rowData.activated ? "{{ trans('emailNotificationSettings.clickToDeactivate') }}" : "{{ trans('emailNotificationSettings.clickToActivate') }}";

				var toggleActivationButton 		 = document.createElement('a');
				toggleActivationButton.id 		 = 'btnToggleActivation_' + rowData.id;
				toggleActivationButton.className = 'btn btn-xs ' + buttonColorClass;
				toggleActivationButton.innerHTML = buttonText;
				toggleActivationButton.title 	 = buttonPopupTitle;

				toggleActivationButton.addEventListener('click', function(e) {
					e.preventDefault();

					table.modules.ajax.showLoader();

					$.ajax({
                        type: 'POST',
                        url: rowData.route_update_status,
                        dataType: "json",
						data: { _token:_csrf_token },
                        success: function(response) {
                            if(response.success) {
								table.updateRow(rowData.id, response.row);
                                row.reformat();
                                table.modules.ajax.hideLoader();
                            }
                        },
                        error: function(){
                            table.modules.ajax.hideLoader();
                        }
                    });
				});

				return toggleActivationButton;
            }

			var editContentsActionFormatter = function(cell, formatterParams, onRendered) {
				var rowData = cell.getRow().getData();

				var editContentsButton = document.createElement('a');
				editContentsButton.id  = 'btnEditContents_' + rowData.id;
				editContentsButton.innerHTML = '<i class="fa fa-edit"></i>';
				editContentsButton.className = 'btn btn-xs btn-warning';
				editContentsButton.title = "{{ trans('emailNotificationSettings.editContents') }}";
				editContentsButton.style['margin-right'] = '5px';
				editContentsButton.dataset.toggle = 'modal';
				editContentsButton.dataset.target = '#modifiableContentsModal';

				editContentsButton.addEventListener('click', function(e) {
					e.preventDefault();

					$.ajax({
                        type: 'GET',
                        url: rowData.route_get_content,
                        dataType: "json",
                        success: function(contents) {
							var cleansedContents = contents;

							$('#email_contents').val(cleansedContents);
                        },
                        error: function(){
                        }
                    });

					$('#modifiableContentsModal').find('[data-action="saveContent"]').data('url', rowData.route_update_content);
				});

				var previewButton = document.createElement('a');
				previewButton.id  = 'btnPreviewEmail_' + rowData.id;
				previewButton.innerHTML = '<i class="fas fa-eye"></i>';
				previewButton.className = 'btn btn-xs btn-success';
				previewButton.title = "{{ trans('emailNotificationSettings.editContents') }}";
				previewButton.style['margin-right'] = '5px';
				previewButton.dataset.toggle = 'modal';
				previewButton.dataset.target = '#emailPreviewModal';

				previewButton.addEventListener('click', function(e) {
					e.preventDefault();

					$('#emailPreviewModal div[data-control="contents"]').html('');
					$('#emailPreviewModal [data-control="title"]').text(rowData.emailSubject);

					$.ajax({
                        type: 'GET',
                        url: rowData.route_contents_preview,
                        dataType: "json",
                        success: function(response) {
							response.contents.forEach(function(item, index) {
								var paragraph = document.createElement('p');
								paragraph.innerHTML = item;

								$('#emailPreviewModal div[data-control="contents"]').append(paragraph);
							});
                        },
                        error: function(){
                        },
                    });
				});

				var container = document.createElement('div');
				container.appendChild(editContentsButton);
				container.appendChild(previewButton);

				return container
			}

			function renderExternalUserEmailNotificationSettingsTable() {
				externalUsersEmailNotificationSettingsTable = new Tabulator("#externalUsersEmailNotificationSettingsTable", {
                    layout:"fitColumns",
                    height: 450,
                    ajaxURL: "{{ route('external.users.email.notification.settings.get') }}",
                    ajaxConfig: "GET",
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    pagination: 'local',
                    columns:[
                        { title:"{{ trans('general.no') }}", cssClass:"text-center text-middle", width: 30, headerSort:false, formatter:"rownum" },
                        { title:"{{ trans('emailNotificationSettings.description') }}", field: 'description', hozAlign:"left", headerFilter: "input", headerSort:false },
                        { title:"{{ trans('emailNotificationSettings.status') }}", field: 'activated', hozAlign:"center", width:"100", cssClass:"text-center text-middle", formatter:activatedStatusFormatter, headerSort:false },
						{ title:"{{ trans('emailNotificationSettings.contents') }}", hozAlign:"center", width:"100", cssClass:"text-center text-middle", formatter:editContentsActionFormatter, headerSort:false }
                    ],
				});
			}

			function renderInternalUserEmailNotificationSettingsTable() {
				internalUsersEmailNotificationSettingsTable = new Tabulator("#internalUsersEmailNotificationSettingsTable", {
                    layout:"fitColumns",
                    height: 450,
                    ajaxURL: "{{ route('internal.users.email.notification.settings.get') }}",
                    ajaxConfig: "GET",
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    pagination: 'local',
                    columns:[
                        { title:"{{ trans('general.no') }}", cssClass:"text-center text-middle", width: 30, headerSort:false, formatter:"rownum" },
                        { title:"{{ trans('emailNotificationSettings.description') }}", field: 'description', hozAlign:"left", headerFilter: "input", headerSort:false },
                        { title:"{{ trans('emailNotificationSettings.status') }}", field: 'project_title', hozAlign:"left", width:"100", cssClass:"text-center text-middle", formatter:activatedStatusFormatter, headerSort:false },
                    ],
                });
			}

			// remove and reconfigure textarea styles
			// bootstrap adds it's own stylings for unknown reasons
			$('#modifiableContentsModal').on('show.bs.modal', function() {
				$('#email_contents').removeAttr('style');
				$('#email_contents').css('height', '200px');
				$('#email_contents').css('overflow-y', 'scroll');
			});

			$('#modifiableContentsModal').on('shown.bs.modal', function() {
				$('#email_contents').focus();
			});

			$('#modifiableContentsModal [data-action="saveContent"]').on('click', function(e) {
				e.preventDefault();

				var url 	 = $(this).data('url');
				var contents = $('#email_contents').val();

				$.ajax({
					type: 'POST',
					url: url,
					dataType: "json",
					data: { 
						contents: DOMPurify.sanitize(contents.trim()),
						_token: _csrf_token,
					},
					success: function(response) {
						if(response.success) {
							$('#modifiableContentsModal').modal('hide');
						}
					},
					error: function(){
						table.modules.ajax.hideLoader();
					}
				});
			});

			renderExternalUserEmailNotificationSettingsTable();
		});
	</script>
@endsection