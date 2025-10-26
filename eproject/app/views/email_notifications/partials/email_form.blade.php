<ul id="project_tenderer_email-tabs" class="nav nav-tabs bordered">
    <li class="active">
        <a href="#project_tenderer_email_content-tab" data-toggle="tab">{{{ trans('emailNotificationSettings.contents') }}}</a>
    </li>
    <li>
        <a href="#project_tenderer_email_recipients-tab" data-toggle="tab">{{{ trans('email.recipientNamePlaceholder') }}} <em class="invalid" id="email_notification-input-to_viewer" style="display:none;font-size:11px;color:#D56161;">&nbsp;</em></a>
    </li>
</ul>
<div id="project_tenderer_email-tab-content" class="tab-content no-padding smart-form">
    <div class="tab-pane fade in active" id="project_tenderer_email_content-tab">
        <fieldset>
            <section>
                <label class="label">{{ trans('messaging.subject') }}<span class="required">*</span>:</label>
                <label class="input">
                    {{ Form::text('subject', Input::old('subject'), array('required')) }}
                </label>
                <em class="invalid" id="email_notification-input-subject" style="display:none;font-size:11px;color:#D56161;">&nbsp;</em>
            </section>

            <section>
                <label class="label">{{ trans('messaging.message') }}<span class="required">*</span>:</label>
                <label class="textarea">
                    {{ Form::textarea('message', Input::old('message'), array('required', 'rows' => 3)) }}
                </label>
                <em class="invalid" id="email_notification-input-message" style="display:none;font-size:11px;color:#D56161;">&nbsp;</em>
            </section>

            <section>
                <label class="label">{{ trans('forms.attachments') }}:</label>

                <div id="fileupload-{{{ $formId }}}">
                    <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
                    <div class="row fileupload-buttonbar" style="margin: 0;">
                        <div>
                            <!-- The fileinput-button span is used to style the file input field as button -->
                            <span class="btn btn-sm btn-success fileinput-button">
                                <i class="glyphicon glyphicon-plus"></i>
                                <span>{{ trans('files.addFiles') }}</span>
                                <input type="file" name="file" multiple>
                            </span>
                            <button class="btn btn-sm btn-primary start">
                                <i class="glyphicon glyphicon-upload"></i>
                                <span>{{ trans('forms.startUpload') }}</span>
                            </button>
                            <button class="btn btn-sm btn-warning cancel">
                                <i class="glyphicon glyphicon-ban-circle"></i>
                                <span>{{ trans('files.cancelUpload') }}</span>
                            </button>

                            <!-- The global file processing state -->
                            <span class="fileupload-process"></span>
                        </div>
                    </div>
                    <!-- The global progress state -->
                    <div class="fileupload-progress fade">
                        <!-- The global progress bar -->
                        <div class="progress progress-striped active" role="progressbar" aria-valuemin="0"
                            aria-valuemax="100">
                            <div class="progress-bar progress-bar-success" style="width:0;"></div>
                        </div>
                        <!-- The extended global progress state -->
                        <div class="progress-extended">&nbsp;</div>
                    </div>
                    <!-- The table listing the files available for upload/download -->
                    <table role="presentation" class="table  table-bordered table-hover" id="uploadFileTable">
                        <thead>
                        <tr>
                            <th style="width:64px;" class="text-center">{{ trans('documentManagementFolders.preview') }}</th>
                            <th style="width:40%;">{{ trans('documentManagementFolders.filename') }}</th>
                            <th style="width:64px;" class="text-center">{{ trans('documentManagementFolders.size') }}</th>
                            <th style="width:28%;" class="text-center">{{ trans('documentManagementFolders.actions') }}</th>
                            <th style="width:98px;" class="text-center">{{ trans('documentManagementFolders.uploaded') }}</th>
                        </tr>
                        </thead>
                        <tbody class="files" style="font-size:11px!important;">
                        @if (isset($uploadedFiles))
                            @foreach ( $uploadedFiles as $uploadedFile )
                                @include('file_uploads.partials.uploaded_file_row', ['file' => $uploadedFile, 'projectId' => $project->id])
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </section>
        </fieldset>
    </div>
    <div class="tab-pane fade in" id="project_tenderer_email_recipients-tab">
        @include('form_partials.select_tenderers', array(
            'usersGroupedByCompany' => $usersGroupedByCompany,
            'hideLabel' => true,
            'defaultChecked' => false,
            'checkboxName' => 'to_viewer',
            'selectedRecipientIds' => $selectedRecipientIds ?? array(),
        ))
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    var token = $('meta[name=_token]').attr("content");

    // Initialize the jQuery File Upload widget:
    $('#fileupload-' + '{{{ $formId }}}').fileupload({
        url: '{{ route("moduleUploads.upload", array($project->id)) }}',
        formData: {_token :token},
        maxFileSize: '{{{ Config::get('uploader.max_file_size') }}}',
        // Enable image resizing, except for Android and Opera,
        // which actually support image resizing, but fail to
        // send Blob objects via XHR requests:
        disableImageResize: /Android(?!.*Chrome)|Opera/
            .test(window.navigator.userAgent)
    });
});

function deleteUpload(url){
    $.post(url, {
        '_token': $('meta[name=_token]').attr("content")
    })
    .done(function(data) {
        // code for result
    });
}
</script>