<div class="modal" id="letterOfAwardVerifierApproveRemarksModal" tabindex="-1" role="dialog" aria-labelledby="verifierApproveRemarksModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="verifierApproveRemarksModalLabel">{{ trans('letterOfAward.approveLetterOfAward') }}</h4>
            </div>
            <div class="modal-body">
                <fieldset>
                    <div class="row smart-form">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{ trans('letterOfAward.verifierRemarks') }} :</label>
                            <label class="textarea ">
                                <textarea id="approve_verifier_remarks" rows="5" placeholder="{{ trans('letterOfAward.addRemarks') }} ({{ trans('letterOfAward.optional') }})" name="verifier_remark" cols="50"></textarea>
                            </label>
                        </section>
                    </div>
                </fieldset>
            </div>
            <div class="modal-footer">
                <button id="verifier_approve_letter_of_award-submit_btn" type="submit" class="btn btn-success">{{trans('forms.approve')}}</button>
                <h4 class="pull-right">&nbsp</h4>
                <button class="btn btn-default" data-dismiss="modal" aria-hidden="true">{{ trans('letterOfAward.close') }}</button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="letterOfAwardVerifierRejectRemarksModal" tabindex="-1" role="dialog" aria-labelledby="verifierRejectRemarksModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="verifierRejectRemarksModalLabel">{{ trans('letterOfAward.rejectLetterOfAward') }}</h4>
            </div>
            <div class="modal-body">
                <fieldset>
                    <div class="row smart-form">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{ trans('letterOfAward.verifierRemarks') }} :</label>
                            <label class="textarea ">
                                <textarea id="reject_verifier_remarks" rows="5" placeholder="{{ trans('letterOfAward.addRemarks') }} ({{ trans('letterOfAward.optional') }})" name="verifier_remark" cols="50"></textarea>
                            </label>
                        </section>
                    </div>
                </fieldset>
            </div>
            <div class="modal-footer">
                <button id="verifier_reject_letter_of_award-submit_btn" type="submit" class="btn btn-danger">{{trans('forms.reject')}}</button>
                <h4 class="pull-right">&nbsp</h4>
                <button class="btn btn-default" data-dismiss="modal" aria-hidden="true">{{ trans('letterOfAward.close') }}</button>
            </div>
        </div>
    </div>
</div>
