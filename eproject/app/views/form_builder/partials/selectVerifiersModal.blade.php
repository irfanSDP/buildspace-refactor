<?php $modalId   = isset($modalId) ? $modalId : 'selectVerifiersModal'; ?>
<?php $verifiers = isset($verifiers) ? $verifiers : []; ?>

<div class="modal fade" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    {{{ trans('formBuilder.submitFormDesignforApproval') }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <div class="widget-body">
                    <form action="{{ route('form.submit.for.approval', [$form->id]) }}" method="POST" class="smart-form">
                        <input type="hidden" name="_token" value="{{{  csrf_token() }}}">
                        @include('verifiers.select_verifiers', [
                            'verifiers' => $verifiers,
                        ])
                        <div class="footer">
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                <button type="submit" id="btnSubmitFormDesignForApproval" class="btn btn-primary btn-md pull-right header-btn" data-intercept-condition="noVerifier" data-confirmation-message="{{{ trans('general.stillWantToProceed') }}}" data-confirmation-title="{{{ trans('general.warning') }}}">{{ trans('forms.submit') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>