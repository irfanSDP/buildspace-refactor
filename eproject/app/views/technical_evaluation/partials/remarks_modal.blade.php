<?php $modalId = 'remarksModal' ?>

<div class="modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    {{{ trans('technicalEvaluation.remarks') }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            </div>

            <div class="modal-body">
                <div class="form-group col-6">
                    <label for="remarks-input" class="control-label">{{{ trans('technicalEvaluation.remarks') }}}:</label>
                    <textarea id="remarks-input" v-model="remarks" class="form-control text-indent" placeholder="{{{ trans('technicalEvaluation.remarks') }}}" maxlength="100"></textarea>
                    <em id="remarks-error" class="color-bootstrap-danger"></em>
                </div>
            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{{ trans('forms.close') }}}</button>

                <h4 class="pull-right">&nbsp</h4>

                <button type="button" data-action="update-remarks" class="btn btn-primary pull-right" v-on="click:updateRemarks"><i class='fa fa-save'></i> {{trans('forms.save')}}</button>

                <button type="button" class="btn btn-info pull-left" data-dismiss="modal" data-toggle="modal" data-target="#@{{ remarkLogModalId }}"><i class="fa fa-search"></i> {{ trans('technicalEvaluation.viewLog') }}</button>

            </div>
        </div>
    </div>
</div>