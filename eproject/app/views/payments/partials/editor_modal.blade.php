<div class="modal fade" id="paymentSettingEditorModal" tabindex="-1" role="dialog" aria-labelledby="editorLabel" aria-hidden="false">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header bg-grey-e">
                <h2 class="modal-title" id="editorLabel"></h2>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            <div class="modal-body">
                <div class="form-group col-6">
                    <label class="control-label">{{ trans('general.name') }}:</label>
                    <input id="setting-name-input" class="form-control"/>
                    <em id="setting-name-error" style="color:#F00;"></em>
                </div>
                <div class="form-group col-6">
                    <label class="control-label">{{ trans('payment.accountNumber') }}:</label>
                    <input id="account-number-input" class="form-control"/>
                    <em id="account-number-error" style="color:#F00;"></em>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" id="submit-button" data-id="" data-url=""><i class="fa fa-save"></i> {{{ trans('forms.save') }}}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->