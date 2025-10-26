<div align="center">
  <div class="modal fade" id="verifier_remark_modal" tabindex="-1" role="dialog" aria-labelledby="verifier_remark_modal" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
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
                    <label for="remark" class="label"><strong>{{ trans('verifiers.remarks') }}:</strong></label>
                    <label class="textarea ">
                      <textarea class="form-control" rows="5" placeholder="{{ trans('verifiers.addRemarksOptional') }}" name="verifier_remark" id="verifier_remark"></textarea>
                    </label>
                  </section>
                  <section>
                    <div class="form-group">
                      <button class="btn btn-default" data-dismiss="modal" aria-hidden="true">{{ trans('forms.no') }}</button>
                      <button id="remark" type="submit" name="" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('forms.yes') }}</button>
                    </div>
                  </section>
                </fieldset>
              </div>
          </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
</div>