<div class="modal fade in scrollable-modal" id="editEmailReminderModalBox" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 1px solid #e5e5e5">
                <h4 class="modal-title">
                    {{ trans('email.editEmailReminder') }}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body padding-0">
                {{ Form::model($emailReminder, ['class' => 'padding-0', 'id' => 'editEmailReminderForm-'.$emailReminder->id, 'data-submit-url' => route('projects.e_bidding.email_reminders.update', ['projectId' => $project->id, 'emailId' => $emailReminder->id])]) }}
                    @include('open_tenders.e_biddings.email_reminders.partials.email_form')

                    <div class="modal-footer">
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-success'] ) }}

                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('forms.close') }}</button>
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>