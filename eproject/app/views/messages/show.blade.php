<div id="replyMessagesContainer">
    <div class="row">
        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12" >
            <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false">
                <header>
                    <span class="widget-icon"> <i class="fa fa-list"></i> </span>
                    <h2 class="hidden-mobile">{{{ \PCK\Helpers\StringOperations::shorten($conversation->subject, 50) }}}</h2>
                </header>
                <div>
                    <div class="widget-body smart-form">
                        <section>
                            <strong>{{ trans('messaging.subject') }}:</strong><br>
                            {{{ $conversation->subject }}}
                        </section>

                        <section>
                            @foreach($conversation->viewerGroups as $group)
                                @if ( $conversation->send_by_contract_group_id != $group->id )
                                    <?php $groups[] = $project->getRoleName($group->group); ?>
                                @endif
                            @endforeach

                            <?php asort($groups); ?>

                            <strong>{{ trans('email.to') }}:</strong><br>
                            {{{ implode(', ', $groups) }}}
                        </section>

                        <section>
                            <strong>{{ trans('email.dateIssued') }}:</strong><br>
                            <span class="dateSubmitted">{{{ $conversation->project->getProjectTimeZoneTime($conversation->created_at) }}}</span>
                        </section>

                        <section>
                            <strong>{{ trans('messaging.author') }}:</strong><br>
                            <span class="color-blue">
                                {{{ $conversation->createdBy->name }}}
                                ({{{ $conversation->createdBy->getProjectCompanyName($conversation->project, $conversation->created_at) }}})
                            </span>
                        </section>

                        <section>
                            <strong>{{ trans('messaging.purposeOfIssue') }}:</strong><br>
                            {{{ $conversation->purpose_of_issued }}}
                        </section>

                        @if ( $conversation->deadline_to_reply )
                            <section>
                                <strong>{{ trans('messaging.deadlineToReply') }}:</strong><br>
                                {{{ $conversation->project->getProjectTimeZoneTime($conversation->deadline_to_reply) }}}
                            </section>
                        @endif

                        <section>
                            <strong>{{ trans('messaging.message') }}:</strong><br>
                            {{ nl2br($conversation->message) }}
                        </section>

                        @if ( ! $conversation->attachments->isEmpty() )
                            <p>
                                <strong>{{ trans('forms.attachments') }}:</strong><br>

                                @include('file_uploads.partials.uploaded_file_show_only', ['files' => $conversation->attachments, 'projectId' => $conversation->project_id])
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </article>
    </div>

    @foreach ( $conversation->replyMessages as $replyMessage )
    <div class="row">
        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                @include('messages.partials.reply')
        </article>
    </div>
    @endforeach
</div>

@if ( $user->isEditor($project) )
    <?php $formId = str_random(); ?>

    <div class="row">
        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget well boxShadow" style="margin-bottom: 18px;">
                <div class="widget-body">
                    {{ Form::open(array('id' => 'sendNewReplyForm-'.$formId, 'class' => 'smart-form', 'data-submit-url' => route('message.reply', array($conversation->project_id, $conversation->id)))) }}
                    <h2>{{ trans('email.replyForm') }}</h2>

                    <section>
                        <label class="label">{{ trans('email.message') }}<span class="required">*</span>:</label>
                        <label class="textarea {{{ $errors->has('message') ? 'state-error' : null }}}">
                            {{ Form::textarea('message', Input::old('message'), array('required', 'rows' => 3, 'id' => 'messageInput')) }}
                        </label>
                        <em class="required" id="project_message_reply-input-message" style="display:none;font-size:11px;color:#D56161;">&nbsp;</em>
                    </section>

                    <section>
                        <label class="label">{{ trans('general.attachments') }}:</label>

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
                                        <span>{{ trans('files.startUpload') }}</span>
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

                    <div class="modal-footer" style="padding-top:8px;">
                        {{ Form::button('<i class="fa fa-paper-plane"></i> '.trans('forms.reply'), ['type' => 'submit', 'class' => 'btn btn-primary'] ) }}
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </article>
    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            autosize($('#sendNewReplyForm-{{$formId}} textarea'));

            $('#sendNewReplyForm-' + '{{{ $formId }}}').submit(function (e) {
                e.preventDefault();

                var form = $(this);
                var targetURL = form.data('submitUrl');
                var dataString = form.serialize();
                var container = $("#replyMessagesContainer");

                var fieldsInputs = ['message'];

                for (var key in fieldsInputs) {
                    var fieldName = fieldsInputs[key];

                    $("#project_message_reply-input-" + fieldName).hide();
                }

                app_progressBar.show();
                app_progressBar.maxOut(0, function(){
                    $.ajax({
                        type: "POST",
                        url: targetURL,
                        data: dataString,
                        success: function (data) {
                            container.append(data);

                            app_progressBar.hide();

                            $.smallBox({
                                title: "Message Posted !",
                                content: "<i class='fa fa-clock-o'></i> <i>Message Posted Successfully...</i>",
                                color: "#008000",
                                iconSmall: "fa fa-reply bounce animated",
                                timeout: 4000
                            });

                            // set the input to null after submission
                            $("#messageInput").val(null);

                            // remove uploaded file's reference
                            $(".template-download").each(function () {
                                $(this).remove();
                            });
                        },
                        error: function (jqXHR) {
                            var data = JSON.parse(jqXHR.responseText);

                            for (var fieldName in data.errors) {
                                var message = data.errors[fieldName];

                                $("#project_message_reply-input-" + fieldName).html(message).show();
                            }
                        }
                    });
                });
            });

            var token = $('meta[name=_token]').attr("content");

            // Initialize the jQuery File Upload widget:
            $('#fileupload-' + '{{{ $formId }}}').fileupload({
                url: '{{ route("moduleUploads.upload", array($project->id)) }}',
                formData: {_token: token},
                maxFileSize: '{{{ Config::get('uploader.max_file_size') }}}',
                // Enable image resizing, except for Android and Opera,
                // which actually support image resizing, but fail to
                // send Blob objects via XHR requests:
                disableImageResize: /Android(?!.*Chrome)|Opera/
                        .test(window.navigator.userAgent)
            });
        });

        function deleteUpload(url) {
            $.post(url, {
                '_token': $('meta[name=_token]').attr("content")
            })
            .done(function (data) {
                // code for result
            });
        }
    </script>
@endif