<div class="modal" id="ai_number_modal" tabindex="-1" role="dialog" aria-labelledby="aiNumberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="aiNumberModalLabel">{{ trans('requestForVariation.updateAINumber') }}</h4>
            </div>

            <div class="modal-body">
                <div class="form-group col-12">
                    <label class="control-label">{{ trans('requestForVariation.aiNumber') }}</label>
                    <input id="ai-number-input" class="form-control" placeholder="{{ trans('requestForVariation.aiNumber') }}" maxlength="100" required />
                    <em id="ai-number-error" class="color-bootstrap-danger"></em>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ trans('requestForVariation.close') }}</button>
                <h4 class="pull-right">&nbsp</h4>
                <input type="submit" data-action="submit-ai-number" class="btn btn-primary pull-right" value="{{trans('forms.save')}}" data-intercept="confirmation" data-confirmation-message="{{ trans('general.sureToProceed') }}" />
                <input type="hidden" name="rfv_id" id="ai_number-rfv_id" value="">
            </div>
        </div>
    </div>
</div>
