<div class="modal" id="sendNotificationFeedbackModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">

                <label class="message"></label>
                <label class="sent-to-label">{{ trans('tenders.sendNotificationSuccessful') }}:</label>
                <ol class="sent-to-list"></ol>
                <label class="not-sent-to-label"><br/>{{ trans('tenders.sendNotificationFailed') }}:</label>
                <ol class="not-sent-to-list" style="color:red"></ol>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('forms.close') }}</button>
            </div>
        </div>
    </div>
</div>