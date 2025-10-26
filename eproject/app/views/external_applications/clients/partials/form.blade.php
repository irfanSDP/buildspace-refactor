<div class="modal fade" id="{{ $formPrefix }}-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => ['api.v2.clients.store'], 'id' => $formPrefix.'-form', 'method' => 'post']) }}
            <div class="modal-header">
                <h6 class="modal-title">@if(isset($client)) {{trans('general.editClient')}} @else {{trans('general.newClient')}} @endif</h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body smart-form">
                <div class="row">
                    <section class="col col-xs-12 col-md-12 col-lg-12">
                        <label class="label">{{{ trans('general.name') }}} <span class="required">*</span>:</label>
                        <label data-field="form_error_label-name" class="input">
                            {{ Form::text('name', Input::old('name', isset($client) ? $client->name : ''), ['id'=>'name-input', 'required'=>'required', 'autofocus' => 'autofocus']) }}
                        </label>
                        <em class="invalid" data-field="form_error-name"></em>
                    </section>
                </div>
                <div class="row">
                    <section class="col col-xs-12 col-md-12 col-lg-12">
                        <label class="label">{{{ trans('general.remarks') }}} :</label>
                        <label data-field="form_error_label-remarks" class="textarea">
                            {{ Form::textarea('remarks', Input::old('remarks', isset($client) ? $client->remarks : ''), ['id'=>'remarks-input', 'rows' => 3]) }}
                        </label>
                        <em class="invalid" data-field="form_error-remarks"></em>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="{{ $formPrefix }}_submit-btn" type="submit"><i class="fa fa-save"></i> {{trans('forms.save')}}</button>
            </div>
            {{ Form::hidden('id', isset($client) ? $client->id : -1) }}
            {{ Form::close() }}
        </div>
    </div>
</div>