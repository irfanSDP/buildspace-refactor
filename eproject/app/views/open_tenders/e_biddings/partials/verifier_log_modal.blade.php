<div class="modal" id="eBiddingLogModal" tabindex="-1" role="dialog" aria-labelledby="actionLogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="verifierLogModalLabel">E-Bidding Logs</h4>
            </div>

            <div class="modal-body">
                @include('verifiers.verifier_status_overview', [
                    'verifierRecords' => $verifierLogsWithTrashed,
                ])
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>