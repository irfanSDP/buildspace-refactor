<div id="interestToTender">
    <button type="button" class="btn btn-light-primary interest" data-lnk="{{ $interestUrl }}" data-pid="{{ $projectId }}" data-tid="{{ $tenderId }}" data-co="{{ $companyId }}">{{ trans('projectOpenTenderBM.interestToTender') }}</button>
    @include('templates.yesNoModal', [
        'modalId'   => 'interestToTenderModal',
        'titleId'   => 'yesNoModalTitle',
        'title'     => trans('projectOpenTenderBM.confirmation'),
        'message'   => trans('projectOpenTenderBM.interestToTenderConfirmation'),
        'modalHeaderClass' => 'alert-primary',
        'modalTitleClass' => 'text-default',
        'modalHeaderIcon' => 'fas fa-exclamation-triangle text-primary',
        'yesBtnClass' => 'btn-primary',
        'yesBtnText' => trans('general.yes', [], 'messages', 'ms'),
        'noBtnClass' => 'btn-light',
        'noBtnText' => trans('general.no', [], 'messages', 'ms'),
    ])
</div>