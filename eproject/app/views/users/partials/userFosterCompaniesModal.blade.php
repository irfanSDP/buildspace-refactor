<?php $modalId = isset($modalId) ? $modalId : 'userFosterCompaniesModal' ?>

<div class="modal" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="logModal" aria-hidden="true">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ trans('users.fosterCompanies') }}</h4>
            </div>
            <div class="modal-body">
                <div id="userFosterCompaniesTable"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-dismiss="modal" aria-hidden="true">{{ trans('forms.close') }}</button>
            </div>
        </div>
    </div>
</div>