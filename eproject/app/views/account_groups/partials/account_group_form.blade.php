<div class="modal fade" id="account_group_form-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => ['account.group.store'], 'id' =>'account_group_form', 'method' => 'post']) }}
            <div class="modal-header">
                <h6 class="modal-title" id="account_group-form_title"></h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body smart-form">
                <div class="row">
                    <section class="col col-xs-12 col-md-12 col-lg-12">
                        <label class="label">{{{ trans('general.name') }}} <span class="required">*</span>:</label>
                        <label data-field="form_error_label-name" class="input">
                            {{ Form::text('name', Input::old('name'), ['id'=>'name-input', 'required'=>'required', 'autofocus' => 'autofocus']) }}
                        </label>
                        <em class="invalid" data-field="form_error-name"></em>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="account_group_form_submit-btn" type="submit"><i class="fa fa-save"></i> {{trans('forms.save')}}</button>
            </div>
            <input type="hidden" value="" name="id" id="account_group_id-hidden">
            {{ Form::close() }}
        </div>
    </div>
</div>