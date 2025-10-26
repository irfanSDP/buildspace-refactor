<div align="center">
    <div class="modal fade" id="award_recommendation_verifier_remark_modal" tabindex="-1" role="dialog" aria-labelledby="verifier_remark_modal" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header alert-danger">
                    <h4 class="modal-title">{{ trans('general.confirmation') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        &times;
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label for="remark"><strong>{{ trans('verifiers.remarks') }}:</strong> <div id="verification-lbl"></div></label>
                            <textarea class="form-control" rows="5" placeholder="{{ trans('verifiers.addRemarksOptional') }}" name="verifier_remark" id="verifier_remark"></textarea>
                        </section>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="remark" type="submit" name="" class="btn btn-lg btn-primary">{{ trans('forms.yes') }}</button>
                    <button class="btn btn-lg btn-default" data-dismiss="modal" aria-hidden="true">{{ trans('forms.no') }}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    </div>