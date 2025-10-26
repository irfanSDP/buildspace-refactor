<?php $formId = str_random(); ?>

<div class="modal fade in scrollable-modal" id="createMessageModalBox" tabindex="-1" role="dialog" aria-labelledby="createMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {{ Form::open(['id' => 'createMessageForm-'.$formId, 'data-submit-url' => route('message.create', $project->id)]) }}
            <div class="modal-header">
                <h4 class="modal-title" id="createMessageModalLabel">
                    {{ trans('email.sendNewMessage') }}
                </h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                            class="sr-only">{{ trans('forms.close') }}</span>
                </button>
            </div>

            @include('messages.partials.conversation_form', ['formId' => $formId])

            <div class="modal-footer">
                @if ( $user->isEditor($project) )
                {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.saveAsDraft'), ['type' => 'submit', 'class' => 'btn btn-success', 'name' => 'draft'] ) }}

                {{ Form::button('<i class="fa fa-paper-plane"></i> '.trans('general.send'), ['type' => 'submit', 'class' => 'btn btn-primary'] ) }}
                @endif

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
    $('#createMessageModalBox').modal({show: true});

    $('#createMessageModalBox').on('shown.bs.modal', function () {
        $('#createMessageForm-{{$formId}} textarea').autosize();
    });

    $('#createMessageForm-' + '{{{ $formId }}}').submit(function (e) {
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

        var fieldsInputs = [
            'to_viewer', 'subject', 'deadline_to_reply', 'message'
        ];

        for (var key in fieldsInputs) {
            var fieldName = fieldsInputs[key];

            $("#project_message-input-" + fieldName).hide();
        }

        $.ajax({
            type: "POST",
            url: targetURL,
            data: dataString,
            success: function () {
                $('#createMessageModalBox').modal('toggle');
                app_progressBar.maxOut();
                app_progressBar.toggle();
                if(isDraft){
                    $("#message_inbox_menu .draft-load").click();
                }else{
                    $("#message_inbox_menu .sent-load").click();
                }
            },
            error: function (jqXHR) {
                app_progressBar.toggle();
                var data = JSON.parse(jqXHR.responseText);

                for (var fieldName in data.errors) {
                    var message = data.errors[fieldName];

                    $("#project_message-input-" + fieldName).html(message).show();
                }
            }
        });
    });
});
</script>