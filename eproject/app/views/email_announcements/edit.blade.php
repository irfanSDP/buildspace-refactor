<?php $formId = str_random(); ?>

<div class="modal fade in scrollable-modal" id="editEmailAnnouncementModalBox" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {{ Form::model($emailAnnouncement, ['id' => 'editEmailAnnouncementForm-'.$formId, 'data-submit-url' => route('email_announcements.edit', [$emailAnnouncement->id])]) }}
                <div class="modal-header">
                    <h4 class="modal-title">
                        {{ trans('email.editMessage') }}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                </div>

                @include('email_announcements.partials.email_form')

                <div class="modal-footer">
                    @if($emailAnnouncement->created_by == $user->id)

                    {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.saveAsDraft'), ['type' => 'submit', 'class' => 'btn btn-success', 'name' => 'draft'] ) }}

                    {{ Form::button('<i class="fa fa-paper-plane"></i> '.trans('general.send'), ['type' => 'submit', 'class' => 'btn btn-primary'] ) }}

                    {{ HTML::decode(link_to_route('email_announcements.delete', '<i class="fa fa-trash"></i> '.trans('forms.delete'), [$emailAnnouncement->id], ['class' => 'btn btn-danger', 'data-method'=>'delete', 'data-csrf_token' => csrf_token()])) }}
                    
                    @endif

                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('forms.close') }}</button>
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>

@include('layout.partials.javascript_variable_holder')

<script src="{{ asset('js/app/app.datePicker.js') }}"></script>
<script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>

<script type="text/javascript">
$(document).ready(function () {
    $('#editEmailAnnouncementModalBox').modal({ show: true });

    $('#editEmailAnnouncementModalBox').on('shown.bs.modal', function () {
        autosize($('#editEmailAnnouncementForm-{{$formId}} textarea'));
    });

    $('#editEmailAnnouncementForm-' + '{{{ $formId }}}').submit(function(e) {
        e.preventDefault();
        app_progressBar.toggle();

        var form = $(this);
        var btn = form.find("button[type=submit]:focus" );
        var targetURL = form.data('submitUrl');
        var dataString = form.serialize();
        var isDraft = false;

        if ( btn.get(0).name === 'draft' ) {
            dataString = dataString + '&draft=draft';
            isDraft = true;
        }

        $.ajax({
            type: "PUT",
            url: targetURL,
            data: dataString,
            success: function() {
                $('#editEmailAnnouncementModalBox').modal('toggle');
                app_progressBar.maxOut();
                app_progressBar.toggle();

                if(isDraft){
                    $("#email_inbox_menu .email-draft-load").click();
                }else{
                    $("#email_inbox_menu .email-sent-load").click();
                }
            },
            error: function(jqXHR) {
                app_progressBar.toggle();
                var data = JSON.parse(jqXHR.responseText);

                for (var fieldName in data.errors) {
                    var message = data.errors[fieldName];

                    $("#email_announcement-input-" + fieldName).html(message).show();
                }
            }
        });
    });
});
</script>
