<div class="modal" id="instructionToContractorVerifierApproveRemarksModal" tabindex="-1" role="dialog" aria-labelledby="verifierApproveRemarksModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-color-green txt-color-white">
                <h4 class="modal-title" id="verifierApproveRemarksModalLabel">Approve Instruction to Contractor</h4>
            </div>
            <div class="modal-body">
                <fieldset>
                    <div class="row smart-form">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{ trans('forms.remarks') }} :</label>
                            <label class="textarea ">
                                <textarea id="approve_verifier_remarks" rows="5" name="verifier_remark" cols="50"></textarea>
                            </label>
                        </section>
                    </div>
                </fieldset>
            </div>
            <div class="modal-footer">
                <button id="verifier_approve_instruction_to_contractor-submit_btn" type="submit" class="btn btn-success">{{trans('forms.approve')}}</button>
                <h4 class="pull-right">&nbsp</h4>
                <button class="btn btn-default" data-dismiss="modal" aria-hidden="true">{{ trans('forms.close') }}</button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="instructionToContractorVerifierRejectRemarksModal" tabindex="-1" role="dialog" aria-labelledby="verifierRejectRemarksModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-color-redLight txt-color-white">
                <h4 class="modal-title" id="verifierRejectRemarksModalLabel">Reject Instruction to Contractor</h4>
            </div>
            <div class="modal-body">
                <fieldset>
                    <div class="row smart-form">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{ trans('forms.remarks') }} :</label>
                            <label class="textarea ">
                                <textarea id="reject_verifier_remarks" rows="5" name="verifier_remark" cols="50"></textarea>
                            </label>
                        </section>
                    </div>
                </fieldset>
            </div>
            <div class="modal-footer">
                <button id="verifier_reject_instruction_to_contractor-submit_btn" type="submit" class="btn btn-danger">{{trans('forms.reject')}}</button>
                <h4 class="pull-right">&nbsp</h4>
                <button class="btn btn-default" data-dismiss="modal" aria-hidden="true">{{ trans('forms.close') }}</button>
            </div>
        </div>
    </div>
</div>
