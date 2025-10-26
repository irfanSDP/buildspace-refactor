<?php $modalId = isset($modalId) ? $modalId : 'claimCertificatePaymentsModal'; ?>
<?php $tableId = isset($tableId) ? $tableId : 'claimCertificatePaymentsTable'; ?>
<div class="modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="width: 90%; max-width: 1300px;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{{ trans('finance.claimCertificate') . ' ' . trans('finance.payments') }}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="widget-body">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td class="text-right text-nowrap">{{{ trans('finance.balance') }}}: <strong id="claimCertPaymentAmountBalance" class="txt-color-yellow"></strong> (<span class="currencyCode"></span>)</td>
                                        <td class="text-left squeeze text-nowrap">{{ trans('finance.paidAmount') }}: <strong id="claimCertPaymentPaidAmount" class="txt-color-greenDark"></strong> (<span class="currencyCode"></span>)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <form class="smart-form">
                    <fieldset>
                        <div class="row">
                            <section class="col col-3">
                                <label class="label">{{ trans('finance.bank') }}:</label>
                                <label class="input {{ $errors->has('bank') ? 'state-error' : null }}">
                                    {{ Form::text('bank', Input::old('bank'), array('class'=> 'form-control', 'autofocus' => 'true')) }}
                                </label>
                                <div name="error-bank" class="txt-color-red"></div>
                            </section>
                            <section class="col col-3">
                                <label class="label">{{ trans('finance.reference') }}:</label>
                                <label class="input {{{ $errors->has('reference') ? 'state-error' : null }}}">
                                    {{ Form::text('reference', Input::old('reference'), array('class'=> 'form-control')) }}
                                </label>
                                <div name="error-reference" class="txt-color-red"></div>
                            </section>
                            <section class="col col-3">
                                <label class="label">{{ trans('finance.amount') }}:</label>
                                <label class="input {{{ $errors->has('amount') ? 'state-error' : null }}}">
                                    {{ Form::number('amount', Input::old('amount'), array('class'=> 'form-control', 'step' => '.01')) }}
                                </label>
                                <div name="error-amount" class="txt-color-red"></div>
                            </section>
                            <section class="col col-2">
                                <label class="label">{{ trans('general.date') }}:</label>
                                <label class="input {{{ $errors->has('date') ? 'state-error' : null }}}">
                                    {{ Form::text('date', Input::old('date') ?? \Carbon\Carbon::now()->format('d-M-Y'), array('class'=> 'form-control datetimepicker')) }}
                                </label>
                                <div name="error-date" class="txt-color-red"></div>
                            </section>
                            <section class="col col-1">
                                <label class="label">&nbsp;</label>
                                <label>
                                    <button id="btnSubmitClaimCertPayment" type="submit" class="btn btn-primary" data-intercept="confirmation"><i class="fa fa-save"></i>&nbsp;{{ trans('forms.save') }}</button>
                                </label>
                            </section>
                        </div>
                    </fieldset>
                </form>
                <div id="{{{ $tableId }}}"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">{{{ trans('forms.close') }}}</button>
            </div>
        </div>
    </div>
</div>