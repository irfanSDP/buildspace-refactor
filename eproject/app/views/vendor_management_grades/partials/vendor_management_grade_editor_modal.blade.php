<div class="modal fade" id="editorModal" tabindex="-1" role="dialog" aria-labelledby="editorLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header bg-grey-e">
                <h6 class="modal-title" id="editorLabel">
                    <!-- Title -->
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            <div class="modal-body">
                <div class="form-group col-6">
                    <label class="control-label">{{ trans('vendorManagement.gradeName') }}:</label>
                    <input id="template-name-input" class="form-control"/>
                    <em id="template-name-error" style="color:#F00;"></em>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" id="submit-button" data-id="" data-url=""><i class="fa fa-save" aria-hidden="true"></i> {{{ trans('forms.save') }}}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->