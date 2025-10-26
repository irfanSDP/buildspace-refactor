<div class="modal" id="letterOfAwardLogModal" tabindex="-1" role="dialog" aria-labelledby="actionLogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="actionLogModalLabel">{{ trans('letterOfAward.letterOfAwardEditLogs') }}</h4>
            </div>

            <div class="modal-body">
                <div class="row" id="action_log-content">
                    <span class="message"></span>
                    <ol style="padding-left:28px;margin: 0 0 0.5rem 0;"></ol>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('letterOfAward.close') }}</button>
            </div>
        </div>
    </div>
</div>