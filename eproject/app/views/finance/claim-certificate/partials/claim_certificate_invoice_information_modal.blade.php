<?php $modalId = isset($modalId) ? $modalId : 'claimCertificateInvoiceInformationModal'; ?>
<div class="modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="width: 90%; max-width: 1300px;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{{ trans('finance.claimCertificate') . ' ' . trans('finance.invoiceInformation') }}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="smart-form">
                    <fieldset>
                        <div class="row">
                            <section class="col col-3">
                                <label class="label">{{ trans('finance.invoiceNumber') }}:</label>
                                <label class="input {{{ $errors->has('invoiceNumber') ? 'state-error' : null }}}">
                                    <input class="form-control" autofocus="true" name="invoiceNumber" type="text" value="">
                                </label>
                                <div name="error-invoiceNumber" class="txt-color-red"></div>
                            </section>
                            <section class="col col-2">
                                <label class="label">{{ trans('finance.invoiceDate') }}:</label>
                                <label class="input {{{ $errors->has('invoiceDate') ? 'state-error' : null }}}">
                                    <input class="form-control datetimepicker" autofocus="true" name="invoiceDate" type="text" value="">
                                </label>
                                <div name="error-invoiceDate" class="txt-color-red"></div>
                            </section>
                            <section class="col col-3">
                                <label class="label">{{ trans('finance.postMonth') }}:</label>
                                <label class="input {{{ $errors->has('postMonth') ? 'state-error' : null }}}">
                                    <input class="form-control" autofocus="true" name="postMonth" type="text" value="">
                                </label>
                                <div name="error-postMonth" class="txt-color-red"></div>
                            </section>
                            <section class="col col-1">
                                <label class="label">&nbsp;</label>
                                <label>
                                    <button id="btnSubmitClaimCertificateInvoiceInformation" type="submit" class="btn btn-success padded-less"><i class="fa fa-save"></i>&nbsp;{{ trans('forms.save') }}</button>
                                </label>
                            </section>
                        </div>
                    </fieldset>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">{{{ trans('forms.close') }}}</button>
            </div>
        </div>
    </div>
</div>