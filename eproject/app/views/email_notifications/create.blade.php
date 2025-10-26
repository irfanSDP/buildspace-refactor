<?php $formId = str_random(); ?>

<div class="modal fade in scrollable-modal" id="sendNewEmailNotificationModalBox" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {{ Form::open(['id' => 'sendNewNotificationEmailForm-'.$formId, 'data-submit-url' => route('email_notifications.create', $project->id)]) }}
            <div class="modal-header">
                <h4 class="modal-title">
                    {{ trans('email.sendNewMessage') }}
                </h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                            class="sr-only">{{ trans('forms.close') }}</span>
                </button>
            </div>

            @include('email_notifications.partials.email_form', ['formId' => $formId])

            <div class="modal-footer">
                {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.saveAsDraft'), ['type' => 'submit', 'class' => 'btn btn-success', 'name' => 'draft'] ) }}

                {{ Form::button('<i class="fa fa-paper-plane"></i> '.trans('general.send'), ['type' => 'submit', 'class' => 'btn btn-primary'] ) }}

                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('forms.close') }}</button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>

@include('layout.partials.javascript_variable_holder')

<script src="{{ asset('js/app/app.datePicker.js') }}"></script>

<script type="text/javascript">
$(document).ready(function () {
    $('#sendNewEmailNotificationModalBox').modal({show: true});
    
    $('#sendNewEmailNotificationModalBox').on('shown.bs.modal', function () {
        $('#sendNewNotificationEmailForm-{{$formId}} textarea').autosize();
    });

    $('#sendNewNotificationEmailForm-' + '{{{ $formId }}}').submit(function (e) {
        e.preventDefault();
        app_progressBar.toggle();

        var form = $(this);
        var btn = form.find("button[type=submit]:focus");
        var targetURL = form.data('submitUrl');
        var dataString = form.serialize();
        var isDraft = false;

        if (btn.get(0).name === 'draft') {
            dataString = dataString + '&draft=draft';
            isDraft = true;
        }

        $.ajax({
            type: "POST",
            url: targetURL,
            data: dataString,
            success: function () {
                $('#sendNewEmailNotificationModalBox').modal('toggle');
                app_progressBar.maxOut();
                app_progressBar.toggle();

                if(isDraft){
                    $("#email_inbox_menu .email-draft-load").click();
                }else{
                    $("#email_inbox_menu .email-sent-load").click();
                }
            },
            error: function (jqXHR) {
                app_progressBar.toggle();
                var data = JSON.parse(jqXHR.responseText);
                for (var fieldName in data.errors) {
                    var message = data.errors[fieldName];
                    $("#email_notification-input-" + fieldName).html(message).show();
                }
            }
        });
    });
});
</script>