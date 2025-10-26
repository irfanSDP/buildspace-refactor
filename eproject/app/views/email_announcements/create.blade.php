<?php $formId = str_random(); ?>

<div class="modal fade in scrollable-modal" id="sendNewEmailAnnouncementModalBox" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        {{ Form::open(['id' => 'sendNewAnnouncementEmailForm-'.$formId, 'data-submit-url' => route('email_announcements.create')]) }}
            <div class="modal-header">
                <h4 class="modal-title">
                    {{ trans('email.sendNewMessage') }}
                </h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                            class="sr-only">{{ trans('forms.close') }}</span>
                </button>
            </div>
            
            @include('email_announcements.partials.email_form', ['formId' => $formId])

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

<script type="text/javascript">
$(document).ready(function () {
    $('#sendNewEmailAnnouncementModalBox').modal({show: true});
    
    $('#sendNewEmailAnnouncementModalBox').on('shown.bs.modal', function () {
        autosize($('#sendNewAnnouncementEmailForm-{{$formId}} textarea'));
    });

    $('#sendNewAnnouncementEmailForm-' + '{{{ $formId }}}').submit(function (e) {
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
                $('#sendNewEmailAnnouncementModalBox').modal('toggle');
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

                // Clear previous errors
                $('.invalid').html('').hide();
                for (var fieldName in data.errors) {
                    var message = data.errors[fieldName];
                    $("#email_announcement-input-" + fieldName).html(message).show();

                    if ($('a[href="#announcement_email_recipients-tab"]').parent().hasClass('active')) {
                        $("#email_announcement-"+ fieldName).html(message).show();
                        break;
                    }
                }
            }
        });
    });
});
</script>