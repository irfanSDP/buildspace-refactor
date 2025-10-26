<div class="modal fade" id="account_code_form-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => ['account.group.account.codes.store', '-1'], 'id' =>'account_code_form', 'method' => 'post']) }}
            <div class="modal-header">
                <h6 class="modal-title" id="account_code-form_title"></h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body smart-form">
                <div class="row">
                    <section class="col col-xs-12 col-md-12 col-lg-12">
                        <label class="label">{{{ trans('contractGroupCategories.code') }}} <span class="required">*</span>:</label>
                        <label data-field="form_error_label-code" class="input">
                            {{ Form::text('code', Input::old('code'), ['id'=>'code-input', 'required'=>'required', 'autofocus' => 'autofocus']) }}
                        </label>
                        <em class="invalid" data-field="form_error-code"></em>
                    </section>
                </div>
                <div class="row">
                    <section class="col col-xs-12 col-md-6 col-lg-6">
                        <label class="label">{{{ trans('accountCodes.taxCode') }}} <span class="required">*</span>:</label>
                        <label data-field="form_error_label-tax_code" class="input">
                            {{ Form::text('tax_code', Input::old('tax_code'), ['id'=>'tax_code-input', 'required'=>'required', 'autofocus' => 'autofocus']) }}
                        </label>
                        <em class="invalid" data-field="form_error-tax_code"></em>
                    </section>
                    <section class="col col-xs-12 col-md-6 col-lg-6">
                        <label class="label">{{{ trans('general.type') }}} <span class="required">*</span>:</label>
                        <label data-field="form_error_label-type">
                            <select class="select2 fill-horizontal" name="type" id="account_code_type">
                                <option value="">{{{ trans('forms.none') }}}</option>
                                @foreach($accountCodeTypes as $id => $name)
                                <option value="{{$id}}">{{{ $name }}}</option>
                                @endforeach
                            </select>
                        </label>
                        <em class="invalid" data-field="form_error-type"></em>
                    </section>
                </div>
                <div class="row">
                    <section class="col col-xs-12 col-md-12 col-lg-12">
                        <label class="label">{{{ trans('general.description') }}} :</label>
                        <label data-field="form_error_label-description" class="textarea">
                            {{ Form::textarea('description', Input::old('description'), ['id'=>'description-input', 'rows' => 3]) }}
                        </label>
                        <em class="invalid" data-field="form_error-description"></em>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="account_code_submit-btn" type="submit"><i class="fa fa-save"></i> {{trans('forms.save')}}</button>
            </div>
            <input type="hidden" value="" name="id" id="account_code_id-hidden">
            {{ Form::close() }}
        </div>
    </div>
</div>