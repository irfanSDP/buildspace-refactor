<?php $verifierApproveModalId = isset($verifierApproveModalId) ? $verifierApproveModalId : 'verifierApproveModal'; ?>
<?php $verifierRejectModalId  = isset($verifierRejectModalId) ? $verifierRejectModalId : 'verifierRejectModal'; ?>
<?php $verifierApproveTitle   = isset($verifierApproveTitle) ? $verifierApproveTitle : trans('forms.approve'); ?>
<?php $verifierRejectTitle    = isset($verifierRejectTitle) ? $verifierRejectTitle : trans('forms.reject'); ?>
<?php $remarksTitle           = isset($remarksTitle) ? $remarksTitle : trans('general.remarks'); ?>

<div class="modal" id="{{ $verifierApproveModalId }}" tabindex="-1" role="dialog" aria-labelledby="myLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-color-green txt-color-white">
                <h4 class="modal-title">{{ $verifierApproveTitle }}</h4>
            </div>
            <div class="modal-body">
                <fieldset>
                    <div class="row smart-form">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{ $remarksTitle }} :</label>
                            <label class="textarea ">
                                <textarea rows="5" name="verifier_remarks" cols="50"></textarea>
                            </label>
                        </section>
                    </div>
                </fieldset>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">{{trans('forms.approve')}}</button>
                <h4 class="pull-right">&nbsp</h4>
                <button class="btn btn-default" data-dismiss="modal" aria-hidden="true">{{ trans('forms.close') }}</button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="{{ $verifierRejectModalId }}" tabindex="-1" role="dialog" aria-labelledby="myLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-color-redLight txt-color-white">
                <h4 class="modal-title">{{ $verifierRejectTitle }}</h4>
            </div>
            <div class="modal-body">
                <fieldset>
                    <div class="row smart-form">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{ $remarksTitle }} :</label>
                            <label class="textarea ">
                                <textarea rows="5" name="verifier_remarks" cols="50"></textarea>
                            </label>
                        </section>
                    </div>
                </fieldset>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger">{{trans('forms.reject')}}</button>
                <h4 class="pull-right">&nbsp</h4>
                <button class="btn btn-default" data-dismiss="modal" aria-hidden="true">{{ trans('forms.close') }}</button>
            </div>
        </div>
    </div>
</div>
