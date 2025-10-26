@extends('layout.main')

@section('css')
    <!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
    <link rel="stylesheet" href="{{ asset('css/jquery.fileupload.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery.fileupload-ui.css') }}">
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('email_announcements.main', trans('navigation/mainnav.emailAnnouncement'), array()) }}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-5 col-md-5 col-lg-4">
            <h1 class="page-title txt-color-blueDark">
                {{{ trans('email.emailAnnouncement') }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <article class="col-sm-12">
            <div class="jarviswidget">
                <div class="no-padding">
                    <div class="widget-body">
                        @include('email_announcements.partials.email_announcements_view')
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

    <script type="text/javascript">
    $(document).ready(function() {

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

        function showEmail(dom)
        {
            var url = dom.data('email-url');
            
            loadURL(url, $('#email-announcements-contents > #email-announcements-list'));
        }

        var global_conversationType = "{{{ PCK\Conversations\StatusType::INBOX }}}";
        var currentEmailType = "{{{ PCK\EmailAnnouncement\EmailAnnouncement::SENT }}}";
        loadInbox();
        loadEmailAnnouncementsBox(currentEmailType);

        var messageMenus = $('.inbox-menu-lg li');
        var emailMessageMenus = $('#email_inbox_menu li');

        $(document).on('click', '.sent-load, .draft-load', function () {
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

        $('#messages-filter').on('keyup', 'input', function(){
            loadInbox(global_conversationType, getFilters());
        });

        function loadEmailAnnouncementsBox(status, filters = []) {
            url = webClaim.getEmailAnnouncementsURL + '/?status=' + status;

            for(var key in filters)
            {
                url = url + '&' + key + '=' + filters[ key ];
            }

            loadURL(url, $('#email-announcements-contents > #email-announcements-list'));
        }

        $(document).on('click', '#compose-announcement-email', function () {
            loadURL(webClaim.createEmailAnnouncementURL, $('#emailAnnouncementsModalBoxContainer'));
        });

        $(document).on('click', '.email-sent-load, .email-draft-load', function () {
            var conversationType = currentEmailType = $(this).data('conversationsType');

            // remove active's class from last selection
            emailMessageMenus.removeClass('active');

            // add active's class into current selection
            $(this).parent().addClass('active');

            loadEmailAnnouncementsBox(conversationType);
        });

        $(document).on('click', '#email_inbox-table .inbox-email-subject, #email_inbox-table .inbox-email-message, #email_inbox-table .inbox-email-author, #email_inbox-table .inbox-email-datetime', function () {
            if ($(this).data('isDraft')) {
                // open modal box for editing draft
                loadURL($(this).data('edit-url'), $('#emailAnnouncementsModalBoxContainer'));
            } else {
                // open conversation content
                showEmail($(this));
            }
        });

        function getEmailSearchFilters()
        {
            return {
                subject: $('#email-announcement-filter input[name=subject]').val(),
                author: $('#email-announcement-filter input[name=author]').val(),
                message: $('#email-announcement-filter input[name=message]').val()
            };
        }

        $('#email-announcement-filter').on('keyup', 'input', function(){
            loadEmailAnnouncementsBox(currentEmailType, getEmailSearchFilters());
        });
    });
    </script>
@endsection