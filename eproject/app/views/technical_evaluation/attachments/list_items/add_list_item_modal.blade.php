<div class="modal fade" id="addListItemModal" tabindex="-1" role="dialog" aria-labelledby="addListItemLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            {{ Form::open(array('route' => array('technicalEvaluation.attachments.listItem.save', $setReference->id))) }}
                <div class="modal-header bg-grey-e">
                    <h6 class="modal-title" id="addListItemLabel">
                        {{{ trans('technicalEvaluation.addNewListItem') }}}
                    </h6>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        &times;
                    </button>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="form-group col-md-10">
                            <label class="control-label" for="attachment_item-description">{{ trans('technicalEvaluation.name') }} :</label>
                            <textarea type="" id="attachment_item-description" name="description" class="form-control" v-model="description" maxlength="200" required></textarea>
                        </div>

                        <div class="form-group col-md-2">
                            <label class="control-label" for="attachment_item-description">&nbsp;</label>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="attachment_item-compulsory" name="compulsory" checked>
                                    {{ trans('technicalEvaluation.mandatory') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="list_item_id" v-model="listItemId"/>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary submit-button"><i class='fa fa-save'></i> {{{ trans('forms.save') }}}</button>
                </div>
            {{ Form::close() }}

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->