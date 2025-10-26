<div class="modal fade scrollable-modal" id="emailComposerPreviewModal" tabindex="-1" role="dialog" aria-labelledby="emailComposerPreviewLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-grey-e">
                <h6 class="modal-title" id="emailComposerPreviewLabel">
                    {{ trans('general.messagePreview') }}
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                @include('notifications.email.tender_confirm_commitment_status', array(
                    'recipientName' => trans('email.recipientNamePlaceholder'),
                    'link' => trans('email.uniqueLinkPlaceholder'),
                    'companyName' => trans('email.companyNamePlaceholder'),
                    'projectTitle' => $project->title,
                    'tenderCallingDate' => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($tender->getTenderInformationCallingTime()))->format(\Config::get('dates.full_format')),
                    'tenderClosingDate' => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($tender->getTenderInformationClosingTime()))->format(\Config::get('dates.full_format')),
                    'employerName' => null,
                    'emailMessage' => null,
                    'tenderStage' => $tender->getTenderStage(),
                    'disableLink' => true,
                    'recipientLocale' => null,
                ))
            </div>
            <div class="modal-footer">
                <?php $targetId = "" ?>
                @if ($tender->getTenderStage() == PCK\Tenders\TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER)
                    <?php $targetId = "#emailComposerSelectRecipientROTModal"; ?>
                @elseif ($tender->getTenderStage() == PCK\Tenders\TenderStages::TENDER_STAGE_LIST_OF_TENDERER)
                    <?php $targetId = "#emailComposerSelectRecipientLOTModal"; ?>
                @endif

                @if ($tender->getTenderStage() == PCK\Tenders\TenderStages::TENDER_STAGE_CALLING_TENDER)
                    <button class="btn btn-primary" id="compose-email-send-button-stage-{{{PCK\Tenders\TenderStages::TENDER_STAGE_CALLING_TENDER}}}" data-original-id="compose-email-send-button">{{ trans('general.send') }}</button>
                @else
                    <button class="btn btn-success" id="select-recipients" data-toggle="modal" data-target="{{{$targetId}}}">{{ trans('forms.selectRecipients') }}</button>
                @endif
                
                <button class="btn btn-info" id="save-as-draft" v-on="click:saveAsDraft">{{ trans('forms.saveAsDraft') }}</button>
                <button class="btn btn-default" data-dismiss="modal" aria-hidden="true" data-toggle="modal" data-target="#emailComposerModal">{{ trans('forms.back') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@include('templates.log_modal', array(
    'log' => \PCK\TenderReminder\SentTenderRemindersLog::getLog($tender),
    'modalId' => 'sentReminderLogModal',
    'title' => 'Sent Tender Reminders',
    'logAction' => 'Sent by',
))