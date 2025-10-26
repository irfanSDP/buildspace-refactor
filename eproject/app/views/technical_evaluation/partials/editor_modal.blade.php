<div class="modal fade" id="editorModal" tabindex="-1" role="dialog" aria-labelledby="editorLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-lg">
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
                    <label for="item-name-input" class="control-label">{{{ trans('technicalEvaluation.name') }}}:</label>
                    <input id="item-name-input" v-model="name" class="form-control" placeholder="{{{ trans('technicalEvaluation.name') }}}"/>
                    <em id="item-name-error" class="color-bootstrap-danger"></em>
                </div>

                <div class="form-group col-6">
                    <label for="item-value-input" class="control-label">{{{ trans('technicalEvaluation.value') }}}:</label>
                    <input id="item-value-input" v-model="value" class="form-control" placeholder="{{{ trans('technicalEvaluation.value') }}}"/>
                    <em id="item-value-error" class="color-bootstrap-danger"></em>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" id="submit-button" data-id=""><i class="fa fa-save" aria-hidden="true"></i> {{{ trans('forms.save') }}}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->