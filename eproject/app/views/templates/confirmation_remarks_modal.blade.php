<div align="center">
    <div class="modal fade" id="confirmation_remarks_modal" tabindex="-1" role="dialog" aria-labelledby="verifier_remark_modal" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-grey-e">
                    <h4 class="modal-title">{{ trans('verifiers.confirm') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        &times;
                    </button>
                </div>
                <div class="modal-body">
                  <fieldset>
                    <section>
                      <label for="remark" style="padding-left:5px;"><strong>{{ trans('verifiers.remarks') }}:</strong></label>
                      <textarea class="form-control" rows="5" placeholder="{{ trans('verifiers.addRemarksOptional') }}" name="verifier_remark" id="verifier_remark"></textarea>
                    </section>
                    <section>
                      <div class="form-group">
                        <br>
                        <button class="btn btn-default btn-lg" data-dismiss="modal" aria-hidden="true">{{ trans('forms.no') }}</button>
                        <button id="remark" type="submit" name="" value="" class="btn btn-primary btn-lg">{{ trans('forms.yes') }}</button>
                      </div>
                    </section>
                  </fieldset>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
  </div>