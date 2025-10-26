{{ Form::open(['route' => ['api.v2.clients.module.settings.store', $selectedModule->id], 'class' => 'smart-form', 'method' => 'post']) }}
<div class="row">
    <section class="col col-xs-12 col-md-4 col-lg-4">
        <label class="label">Downstream Permission <span class="required">*</span>:</label>
        <label data-field="form_error_label-name" class="input {{{ ($errors->has('downstream_permission') || $errors->has('id')) ? 'state-error' : null }}}">
            <select class="select2 fill-horizontal" name="downstream_permission" id="client_module_downstream_permission-select" placeholder="Please Select Downstream Permission">
                <option value=""></option>
                @foreach($downstreamPermissions as $val => $txt)
                <option value="{{ $val }}" @if($selectedModule->downstream_permission == $val) selected @endif>{{ $txt }}</option>
                @endforeach
            </select>
        </label>
        {{ $errors->first('downstream_permission', '<em class="invalid">:message</em>') }}
        {{ $errors->first('id', '<em class="invalid">:message</em>') }}
    </section>
</div>
@if(!empty($attributes))
<hr class="simple">
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <table class="table table-bordered table-condensed table-striped table-hover">
            <thead>
                <tr>
                    <th style="width:32px;text-align:center;">No.</th>
                    <th style="min-width:200px;width:auto;">Attributes</th>
                    <th style="width:200px;text-align:center;">Internal Attributes</th>
                    <th style="width:100px;text-align:center;">Types</th>
                    <th style="width:80px;text-align:center;">Required</th>
                    <th style="width:80px;text-align:center;">Is Identifier</th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 1; ?>
                @foreach($attributes as $key => $attribute)
                <?php
                $externalAttributeOptions = ['id'=>'external_attribute_{{ $key }}-input', 'autofocus' => 'autofocus'];
                if(array_key_exists('required', $attribute) && $attribute['required'])
                {
                    $externalAttributeOptions['required'] = 'required';
                }
                $moduleAttribute = $selectedModule->getAttributeByInternalAttribute($key);
                ?>
                <tr>
                    <td style="text-align:center;">{{ $count }}</td>
                    <td>
                        <label class="input {{{ $errors->has('external_attribute_'.$key) ? 'state-error' : null }}}">
                            {{ Form::text('external_attribute_'.$key, Input::old('external_attribute_'.$key, ($moduleAttribute) ? $moduleAttribute->external_attribute : ''), $externalAttributeOptions) }}
                        </label>
                        {{ $errors->first('external_attribute_'.$key, '<em class="invalid">:message</em>') }}
                    </td>
                    <td style="text-align:center;">{{ $attribute['name'] }}</td>
                    <td style="text-align:center;">{{ PCK\ExternalApplication\Module\Base::getAttributeTypeText($attribute['type']) }}</td>
                    <td style="text-align:center;">
                        @if(array_key_exists('required', $attribute) && $attribute['required']) Yes @else Optional @endif
                    </td>
                    <td style="text-align:center;">
                        @if(array_key_exists('is_identifier', $attribute) && $attribute['is_identifier']) {{ trans('general.yes') }} @else {{ trans('general.no') }} @endif
                    </td>
                </tr>
                <?php $count++ ?>
                @endforeach
            </tbody>
        </table>
    </section>
</div>
@endif
<footer>
    {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
</footer>
{{ Form::close() }}