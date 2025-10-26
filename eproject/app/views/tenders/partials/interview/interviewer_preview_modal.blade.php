<div class="modal scrollable-modal fade" id="interviewerPreviewModal" tabindex="-1" role="dialog" aria-labelledby="interviewerPreviewLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-grey-e">
                <h6 class="modal-title" id="interviewerPreviewLabel">
                    Interviewer Preview
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                @include('notifications.email.tender_interview_request_for_meeting', array(
                    "recipientName" => "[Recipient's Name]",
                    "companyName" => "[Company Name]",
                    "projectTitle" => $project->title,
                    "date" => $project->getProjectTimeZoneTime($tender->tenderInterviewInfo->date_and_time)->format(\Config::get('dates.standard_spaced_date_and_day')),
                    "discussionTime" => $project->getProjectTimeZoneTime($tender->tenderInterviewInfo->date_and_time)->format(\Config::get('dates.time_only')),
                    "venue" => $tender->tenderInterviewInfo->getVenue(),
                    'tenderInterviews' => $tender->tenderInterviewInfo->getCompanyInterviews(),
                    'recipientLocale' => null,
                ))
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal" aria-hidden="true" data-toggle="modal" data-target="#tenderInterviewModal">{{ trans('forms.back') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->