@extends('layout.main')

@section('css')
    <!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
    <link rel="stylesheet" href="{{ asset('css/jquery.fileupload.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery.fileupload-ui.css') }}">
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ str_limit($project->title, 50) }}}</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-5 col-md-5 col-lg-4">
            <h1 class="page-title txt-color-blueDark">
                {{{ trans('projects.dashboard') }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-7 col-md-7 col-lg-8 mb-4">
            <!-- Header buttons -->
            <div class="btn-group pull-right header-btn">
                @include('projects.partials.actions_menu', array('classes' => 'pull-right'))
            </div>
        </div>
    </div>

    @if((!$user->isSuperAdmin()) && (!$user->hasCompanyRoles([\PCK\ContractGroups\Types\Role::CONTRACTOR])))
        <?php $totalPendingItemsCount = count($pendingTenderProcesses) + $pendingUserReviews->count() + $pendingSiteModuleProcesses->count(); ?>
        @if($totalPendingItemsCount > 0)
            @include('projects.partials.dashboard_contract_management_review_list')
        @endif
    @endif

    @if(!$user->isSuperAdmin())
        @if($project->isDesignStage() && $user->getAssignedCompany($project) && $user->getAssignedCompany($project)->contractGroupCategory->hasPrivilege(\PCK\ContractGroupCategory\ContractGroupCategoryPrivilege::DASHBOARD_PROJECT_DESIGN_STAGE))
            @include('projects.partials.dashboard_design')
        @endif
        @if($project->inRecommendationOfTenderer() || $project->inListOfTenderer() || $project->inCallingTender() || $project->inClosedTender())
            @if($user->getAssignedCompany($project) && $user->getAssignedCompany($project)->contractGroupCategory && $user->getAssignedCompany($project)->contractGroupCategory->hasPrivilege(\PCK\ContractGroupCategory\ContractGroupCategoryPrivilege::DASHBOARD_PROJECT_TENDERING))
                @include('projects.partials.dashboard_tendering')
            @endif
        @endif
        @if($project->isPostContract() && $user->getAssignedCompany($project) && $user->getAssignedCompany($project)->contractGroupCategory->hasPrivilege(\PCK\ContractGroupCategory\ContractGroupCategoryPrivilege::DASHBOARD_PROJECT_POST_CONTRACT))
            @include('projects.partials.dashboard_post_contract')
        @endif
    @endif

    @include('projects.partials.progress_checklist_widget', array('project' => $project))

    <div class="row">
        <article class="col-sm-12">
            <div class="jarviswidget" data-widget-togglebutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false">
                <header>
                    <ul id="myTab1" class="nav nav-tabs pull-right in">
                        @if ( $user->isSuperAdmin() )
                            <li class="active">
                                <a href="#s2" data-toggle="tab"><i class="fa fa-fw fa-lg fa-info-circle"></i> <span class="hidden-mobile hidden-tablet">{{{ trans('projects.projectInformation') }}}</span></a>
                            </li>
                        @else
                            <li class="active">
                                <a href="#s2" data-toggle="tab"><i class="fa fa-fw fa-lg fa-info-circle"></i> <span class="hidden-mobile hidden-tablet">{{{ trans('projects.projectInformation') }}}</span></a>
                            </li>
                            <li>
                                <a href="#s1" data-toggle="tab"><i class="fa fa-fw fa-lg fa-comments"></i> <span class="hidden-mobile hidden-tablet">{{{ trans('projects.messages') }}}</span></a>
                            </li>
                            @if ($canSendEmailNotifications)
                                <li>
                                    <a href="#s3" data-toggle="tab"><i class="fa fa-fw fa-lg fa-envelope"></i> <span class="hidden-mobile hidden-tablet">{{{ trans('projects.emailToTenderers') }}}</span></a>
                                </li>
                            @endif
                        @endif
                    </ul>
                </header>

                <div class="no-padding">
                    <div class="widget-body">
                        <div id="myTabContent1" class="tab-content" style="height: 100%;">
                            @if ( $user->isSuperAdmin() )
                                <div class="tab-pane fade active in padding-10 no-padding-bottom" id="s2">
                                    @include('projects.partials.project_information')
                                </div>
                            @else
                                <div class="tab-pane fade active in padding-10 no-padding-bottom" id="s2">
                                    @include('projects.partials.project_information')
                                </div>
                                <div class="tab-pane fade in padding-10 no-padding-bottom" id="s1">
                                    @include('messages.partials.messaging_view')
                                </div>
                                @if ($canSendEmailNotifications)
                                    <div class="tab-pane fade in padding-10 no-padding-bottom" id="s3">
                                        @include('email_notifications.partials.email_notifications_view')
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </article>
        
    </div>

    <!-- The template to display files available for upload -->
    <script id="template-upload" type="text/x-tmpl">
	{% for (var i=0, file; file=o.files[i]; i++) { %}
		<tr class="template-upload fade">
			<td>
				<span class="preview"></span>
			</td>
			<td>
				<p class="name">{%=file.name%}</p>
				<strong class="error text-danger"></strong>
			</td>
			<td>
				<p class="size">{{ trans('files.processing') }}...</p>
				<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
			</td>
			<td>
				{% if (!i && !o.options.autoUpload) { %}
					<button class="btn btn-xs btn-primary start" disabled>
						<i class="glyphicon glyphicon-upload"></i>
						<span>{{ trans('files.start') }}</span>
					</button>
				{% } %}
				{% if (!i) { %}
					<button class="btn btn-xs btn-warning cancel">
						<i class="glyphicon glyphicon-ban-circle"></i>
						<span>{{ trans('files.cancel') }}</span>
					</button>
				{% } %}
			</td>
		</tr>
	{% } %}
    </script>

    <!-- The template to display files available for download -->
    <script id="template-download" type="text/x-tmpl">
	{% for (var i=0, file; file=o.files[i]; i++) { %}
		<tr class="template-download fade">
			<td>
				<span class="preview">
					{% if (file.thumbnailUrl) { %}
						<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
					{% } %}
				</span>
			</td>
			<td>
				<p class="name">
					{% if (file.url) { %}
						<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
					{% } else { %}
						<span>{%=file.name%}</span>
					{% } %}
					{% if (file.fileID) { %}
						<input class="upload-field-ids" type="hidden" name="uploaded_files[]" value="{%=file.fileID%}">
					{% } %}
				</p>
				{% if (file.error) { %}
					<div><span class="label label-danger">{{ trans('files.error') }}</span> {%=file.error%}</div>
				{% } %}
			</td>
			<td>
				<span class="size">{%=o.formatFileSize(file.size)%}</span>
			</td>
			<td>
				{% if (file.deleteUrl) { %}
					<button class="btn btn-xs btn-danger delete" onclick="deleteUpload('{%=file.deleteUrl%}')">
						<i class="glyphicon glyphicon-trash"></i>
						<span>{{ trans('files.delete') }}</span>
					</button>
				{% } else { %}
					<button class="btn btn-xs btn-warning cancel">
						<i class="glyphicon glyphicon-ban-circle"></i>
						<span>{{ trans('files.cancel') }}</span>
					</button>
				{% } %}
			</td>
		</tr>
	{% } %}
    </script>
    @if($isBuOrGcdEditor)
        @include('projects.partials.editor_modal', [
            'modalId' => 'editorModal',
            'label'   => trans('general.date'),
        ])
        @include('templates.yesNoModal', [
            'modalId'   => 'yesNoModal',
            'titleId'   => 'yesNoModalTitle',
            'title'     => trans('general.confirmation'),
            'messageId' => 'yesNoModalMessage',
        ])
    @endif
@endsection

@section('js')
    <script src="{{ asset('js/jquery_file_upload/tmpl.min.js') }}"></script>
    <script src="{{ asset('js/jquery_file_upload/load-image.all.min.js') }}"></script>
    <script src="{{ asset('js/jquery_file_upload/canvas-to-blob.min.js') }}"></script>
    <script src="{{ asset('js/jquery_file_upload/jquery.iframe-transport.js') }}"></script>
    <script src="{{ asset('js/jquery_file_upload/jquery.fileupload.js') }}"></script>
    <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-process.js') }}"></script>
    <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-image.js') }}"></script>
    <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-audio.js') }}"></script>
    <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-video.js') }}"></script>
    <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-validate.js') }}"></script>
    <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-ui.js') }}"></script>
    <script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>

    @if(!$user->isSuperAdmin())
        @if($project->isDesignStage() && $user->getAssignedCompany($project) && $user->getAssignedCompany($project)->contractGroupCategory->hasPrivilege(\PCK\ContractGroupCategory\ContractGroupCategoryPrivilege::DASHBOARD_PROJECT_DESIGN_STAGE))
            @include('projects.partials.js_partials.js_dashboard_design')
        @endif
        @if($project->inRecommendationOfTenderer() || $project->inListOfTenderer() || $project->inCallingTender() || $project->inClosedTender())
            @if($user->getAssignedCompany($project) && $user->getAssignedCompany($project)->contractGroupCategory->hasPrivilege(\PCK\ContractGroupCategory\ContractGroupCategoryPrivilege::DASHBOARD_PROJECT_TENDERING))
                @include('projects.partials.js_partials.js_dashboard_tendering')
            @endif
        @endif
        @if($project->isPostContract() && $user->getAssignedCompany($project) && $user->getAssignedCompany($project)->contractGroupCategory->hasPrivilege(\PCK\ContractGroupCategory\ContractGroupCategoryPrivilege::DASHBOARD_PROJECT_POST_CONTRACT))
            @include('projects.partials.js_partials.js_dashboard_post_contract')
        @endif
    @endif

    <script type="text/javascript">

        $(document).ready(function() {

        $('[data-id="checkList"]').hide();

        $('input[name="skip-steps[]"]').each(function (){
            console.log($(this).is(':checked'));

            if($(this).is(':checked'))
            {
                $('[data-id="checkList"]').show();

                return;
            }
        });

        $('input[name="skip-steps[]"]').on('change', function(e) {
            const field = $(this).data('id');

            console.log(field);

            $.ajax({
                url: "{{{ route('projects.progress-checklist', $project->id) }}}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    stage: $(this).data('id'),
                    skip : $(this).is(':checked')
                },
                success: function (data) {
                    console.log(data);
                    window.location.reload();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log('Error:', textStatus, errorThrown);
                }
            });

            // $("#checkList").load(location.href + " #checkList");
        });

        function loadUnreadMessagesCount() {
            var inboxCounter = $("#inboxUnreadMessageCounter");
            var sentCounter = $("#sentUnreadMessageCounter");

            $.get(webClaim.getUnreadMessagesCount, function (data) {
                inboxCounter.html(data.inboxCounts > 0 ? ' (' + data.inboxCounts + ')' : null);
                sentCounter.html(data.sendCounts > 0 ? ' (' + data.sendCounts + ')' : null);
            });
        }

        function loadInbox(type, filters, url) {
            if(!url)
            {
                if (!type) {
                    type = "{{{ PCK\Conversations\StatusType::INBOX }}}";
                }

                url = webClaim.getMessagesURL + '/?messageType=' + type;
            }

            for(var key in filters)
            {
                url = url + '&' + key + '=' + filters[ key ];
            }

            loadURL(url, $('#inbox-content > #message-list'));

            loadUnreadMessagesCount();
        }

        function getConversation($this) {
            var url = $this.data('messageUrl');

            loadURL(url, $('#inbox-content > #message-list'));

            loadUnreadMessagesCount();
        }

        function showEmail(dom)
        {
            var url = dom.data('email-url');
            
            loadURL(url, $('#email-notifications-contents > #email-notifications-list'));
        }

        @if ( ! $user->isSuperAdmin() )
            var global_conversationType = "{{{ PCK\Conversations\StatusType::INBOX }}}";
            var currentEmailType = "{{{ PCK\EmailNotification\EmailNotification::SENT }}}";
            loadInbox();
            loadEmailNotificationsBox(currentEmailType);

            var messageMenus = $('.inbox-menu-lg li');
            var emailMessageMenus = $('#email_inbox_menu li');
            var container = $("#myTabContent1");

            container.on('click', '.inbox-load, .sent-load, .draft-load', function () {
                var conversationType = global_conversationType = $(this).data('conversationsType');

                // remove active's class from last selection
                messageMenus.removeClass('active');

                // add active's class into current selection
                $(this).parent().addClass('active');

                loadInbox(conversationType);
            });

            $(document).on('click', '#inbox-pagination-links>ul>li>a', function(e){
                // Prevents redirecting.
                e.preventDefault();
                var url = $(this).prop('href');
                url = url.replace('messageTypePlaceholder', global_conversationType);
                loadInbox(global_conversationType, getFilters(), url);
            });

            container.on('click', '#compose-message', function () {
                loadURL(webClaim.createMessageURL, $('#modalBoxContainer'));
            });

            container.on('click', '#message_inbox-table .inbox-data-subject, #message_inbox-table .inbox-data-from', function () {
                if ($(this).data('isDraft')) {
                    // open modal box for editing draft
                    loadURL($(this).data('isDraft'), $('#modalBoxContainer'));
                } else {
                    // open conversation content
                    getConversation($(this));
                }
            });

            function getFilters()
            {
                return {
                    subject: $('#messages-filter input[name=subject]').val(),
                    author: $('#messages-filter input[name=author]').val(),
                    purpose_of_issue: $('#messages-filter input[name=purpose_of_issue]').val()
                };
            }

            $('#messages-filter').on('keyup', 'input', function(){
                loadInbox(global_conversationType, getFilters());
            });

            /**
            Email Notification codes
            */
            function loadEmailNotificationsBox(status, filters = []) {
                url = webClaim.getEmailNotificationsURL + '/?status=' + status;

                for(var key in filters)
                {
                    url = url + '&' + key + '=' + filters[ key ];
                }

                loadURL(url, $('#email-notifications-contents > #email-notifications-list'));
            }

            container.on('click', '#compose-notification-email', function () {
                    loadURL(webClaim.createEmailNotificationURL, $('#emailNotificationsModalBoxContainer'));
                });

            container.on('click', '.email-sent-load, .email-draft-load', function () {
                var conversationType = currentEmailType = $(this).data('conversationsType');

                // remove active's class from last selection
                emailMessageMenus.removeClass('active');

                // add active's class into current selection
                $(this).parent().addClass('active');

                loadEmailNotificationsBox(conversationType);
            });

            container.on('click', '#email_inbox-table .inbox-email-subject, #email_inbox-table .inbox-email-message, #email_inbox-table .inbox-email-author, #email_inbox-table .inbox-email-datetime', function () {
                if ($(this).data('isDraft')) {
                    // open modal box for editing draft
                    loadURL($(this).data('edit-url'), $('#emailNotificationsModalBoxContainer'));
                } else {
                    // open conversation content
                    showEmail($(this));
                }
            });

            function getEmailSearchFilters()
            {
                return {
                    subject: $('#email-notification-filter input[name=subject]').val(),
                    author: $('#email-notification-filter input[name=author]').val(),
                    message: $('#email-notification-filter input[name=message]').val()
                };
            }

            $('#email-notification-filter').on('keyup', 'input', function(){
                loadEmailNotificationsBox(currentEmailType, getEmailSearchFilters());
            });

            const actionsFormatter = function(cell, formatterParams, onRendered) {
                const data = cell.getRow().getData();

                const container = document.createElement('div');

                const editDateButton = document.createElement('a');
                editDateButton.dataset.id = data.id;
                editDateButton.dataset.date = data.date;
                editDateButton.dataset.description = data.description;
                editDateButton.dataset.url = data['route:update'];
                editDateButton.dataset.action = 'editSectionalCompletionDate';
                editDateButton.title = "{{ trans('general.edit') }}";
                editDateButton.className = 'btn btn-xs btn-warning';
                editDateButton.innerHTML = '<i class="fa fa-pencil-alt"></i>';
                editDateButton.style['margin-right'] = '5px';

                container.appendChild(editDateButton);

                const deleteButton = document.createElement('a');
                deleteButton.dataset.toggle = 'tooltip';
                deleteButton.title = "{{ trans('general.delete') }}";
                deleteButton.className = 'btn btn-xs btn-danger';
                deleteButton.innerHTML = '<i class="fa fa-trash"></i>';
                deleteButton.dataset.toggle = 'modal';
                deleteButton.dataset.target = '#yesNoModal';

                deleteButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    $('#yesNoModalMessage').html("{{ trans('formBuilder.allContentsWillBeDeleted') . ' ' . trans('general.sureToProceed') }}");
                    $('#yesNoModal [data-action="actionYes"]').data('route_delete', data['route:delete']);
                });
                
                container.appendChild(deleteButton);

                return container;
            };

            const sectionalCompletionDateTable = new Tabulator('#sectionalCompletionDateTable', {
                height: 350,
				pagination: 'local',
                paginationSize: 20,
                columns: [
					{ title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
					{ title:"{{ trans('projects.sectionalCompletionDate') }}", width:250, field: 'date_display', headerSort:false, headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                    { title:"{{ trans('general.description') }}", field: 'description', headerSort:false, headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter: 'textarea', },
					@if($isBuOrGcdEditor)
                    { title:"{{ trans('general.actions') }}", width: 80, hozAlign: 'left', cssClass:"text-center text-middle", headerSort:false, formatter:actionsFormatter },
                    @endif
				],
                layout:"fitColumns",
				ajaxURL: "{{ route('project.sectionalCompletionDate.records.get', $project->id) }}",
                ajaxConfig: 'GET',
                placeholder:"{{ trans('general.noRecordsFound') }}",
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
                submit($(this).data('url'), getTemplateNameInputValue(), getDescription());
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

            function setDescription(description)
            {
                $('#description-input').val(description);
            }

            function getDescription(description)
            {
                return $('#description-input').val();
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

            function setDescriptionError(error) {
                $('#description-error').text(error);
            }

            /* Create */
            $(document).on('click', '#btnAddSectionalCompletionDate', function (e) {
				e.preventDefault();

                changeEditorModalTitle("{{ trans('buildingInformationModelling.createNewBimLevel') }}");
				setTemplateNameInputValue('');
                setDescription('');
				setTemplateNameError('');
                setDescriptionError('');
				setSubmitButtonURL("{{ route('project.sectionalCompletionDate.record.add', $project->id) }}");
            });

			/* Edit */
			$(document).on('click', '[data-action="editSectionalCompletionDate"]', function(e) {
                e.preventDefault();

				changeEditorModalTitle("{{ trans('buildingInformationModelling.editBimLevel') }}");
				setTemplateNameInputValue($(this).data('date'));
                setDescription($(this).data('description'));
				setSubmitButtonURL($(this).data('url'));
				setTemplateNameError('');
                setDescriptionError('');
				showEditorModal();
			});

            function submit(url, date, description) {
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        date: date.trim(),
                        description: description,
                        _token: '{{{ csrf_token() }}}',
                    },
                    success: function (data) {
                        if (data['success']) {
                            hideEditorModal();
							sectionalCompletionDateTable.setData();
                        }
                        else {
                            setTemplateNameError(data['errors']['date']);
                            setDescriptionError(data['errors']['description']);
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

                const url = $(this).data('route_delete');

				$.ajax({
                    url: url,
                    method: 'DELETE',
                    data: {
                        _token: _csrf_token,
                    },
                    success: function (data) {
                        if (data.success) {
							sectionalCompletionDateTable.setData();
                            $('#yesNoModal').modal('hide');
                        } else {
                            $.smallBox({
                                title : "{{ trans('general.error') }}",
                                content : "<i class='fa fa-check'></i> <i>{{ trans('general.anErrorHasOccured') }}</i>",
                                color : "#C46A69",
                                sound: true,
                                iconSmall : "fa fa-exclamation-triangle",
                                timeout : 5000
                            });
                            $('#yesNoModal').modal('hide');
                        }
                    },
                    error: function (request, status, error) {
                        // error
                    }
                });
            });

            $('.date_picker').datepicker({
                dateFormat : 'yy-mm-dd',
                prevText : '<i class="fa fa-chevron-left"></i>',
                nextText : '<i class="fa fa-chevron-right"></i>'
            });
        @endif
        });
    </script>
@endsection