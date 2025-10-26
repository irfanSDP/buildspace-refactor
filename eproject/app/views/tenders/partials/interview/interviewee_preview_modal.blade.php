<div class="modal scrollable-modal fade" id="intervieweePreviewModal" tabindex="-1" role="dialog" aria-labelledby="intervieweePreviewLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-grey-e">
                <h6 class="modal-title" id="intervieweePreviewLabel">
                    Interviewee Preview
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                @include('notifications.email.tender_interview_request', array(
                    "recipientName" => "[Recipient's Name]",
                    "companyName" => "[Company Name]",
                    "projectTitle" => $project->title,
                    "date" => $project->getProjectTimeZoneTime($tender->tenderInterviewInfo->date_and_time)->format(\Config::get('dates.full_format')),
                    "time" => "[Specified time]",
                    "venue" => $tender->tenderInterviewInfo->getVenue(),
                    "link" => "[uniqueLink]",
                    'disableLink' => true,
                    'recipientLocale' => null,
                ))
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal" aria-hidden="true" data-toggle="modal" data-target="#tenderInterviewModal">{{ trans('forms.back') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->