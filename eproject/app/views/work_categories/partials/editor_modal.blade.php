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
                    <label for="workCategory-name-input" class="control-label">{{{ trans('workCategories.name') }}}:</label>
                    <input id="workCategory-name-input" v-model="name" v-on="keyup: generateIdentifier" class="form-control" placeholder="{{{ trans('workCategories.workCategory') }}}"/>
                    <em id="workCategory-name-error" class="color-bootstrap-danger"></em>
                </div>

                <div class="form-group col-6">
                    <label for="workCategory-identifier-input" class="control-label">{{{ trans('workCategories.identifier') }}}:</label><i class="fa fa-question-circle pull-right" data-toggle="tooltip" data-placement="left" title="{{{ trans('workCategories.identifierTooltip') }}}"></i>
                    <input id="workCategory-identifier-input" v-model="identifier" class="form-control" placeholder="{{{ trans('workCategories.identifier') }}}"/>
                    <em id="workCategory-identifier-error" class="color-bootstrap-danger"></em>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" id="submit-button" data-id=""><i class="fa fa-save" aria-hidden="true"></i> {{{ trans('forms.save') }}}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->