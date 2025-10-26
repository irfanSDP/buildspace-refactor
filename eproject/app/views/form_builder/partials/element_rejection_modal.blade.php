<?php $modalId = isset($modalId) ? $modalId : 'formRejectionModal'; ?>
<?php $canEditRejection = isset($canEditRejection) ? $canEditRejection : false; ?>
<div class="modal fade warning" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="editorLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-lg">
        <div class="modal-content  panel-warning">
            <div class="modal-header bg-color-redLight txt-color-white">
                <h6 class="modal-title" id="editorLabel">{{ trans('formBuilder.rejectElement') }}</h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <form class="smart-form" data-control="form_body">
                    <fieldset>
                        <section>
                            <label class="label">{{ trans('forms.remarks') }}</label>
                            <label class="textarea">
                                <?php $class = $canEditRejection ? null : 'disabled'; ?>
                                <textarea rows="10" name="remarks" {{ $class }}></textarea>
                                <em class="invalid" data-component="error_message"></em>
                                <div data-component="updator_container" style="display:none;">
                                    <label style="color:#000;">{{ trans('formBuilder.rejectedBy') }}:</label>&nbsp;<label style="color:#00F;" data-component="updator_name"></label>
                                </div>
                            </label>
                        </section>
                    </fieldset>
                </form>
            </div>
            @if($canEditRejection)
            <div class="modal-footer">
                <button class="btn btn-primary" data-action="save_rejection"><i class="fa fa-save" aria-hidden="true"></i> {{{ trans('forms.save') }}}</button>
                <button class="btn btn-success" data-action="resolve_rejection"><i class="far fa-check-circle"></i> {{{ trans('formBuilder.resolve') }}}</button>
            </div>
            @endif
        </div>
    </div>
</div>