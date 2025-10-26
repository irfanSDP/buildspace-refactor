<div class="modal" id="accountCodeSettingVerifierLogModal" tabindex="-1" role="dialog" aria-labelledby="actionLogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="verifierLogModalLabel">{{ trans('accountCodes.accountCodeSettings') }} {{ trans('accountCodes.verifierLogs') }}</h4>
            </div>

            <div class="modal-body">
                @include('verifiers.verifier_status_overview', [
                    'verifierRecords' => $verifierLogs,
                ])
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('letterOfAward.close') }}</button>
            </div>
        </div>
    </div>
</div>