<?php $modalId = $modalId ?? "verifierStatusOverviewModal" ?>
<?php $verifierRecords = $verifierRecords ?? array() ?>
<div class="modal" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="actionLogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="verifierLogModalLabel">{{ trans('verifiers.verifiers') }}</h4>
            </div>

            <div class="modal-body">
                @include('verifiers.verifier_status_overview', [
                    'verifierRecords' => $verifierRecords,
                ])
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('general.close') }}</button>
            </div>
        </div>
    </div>
</div>