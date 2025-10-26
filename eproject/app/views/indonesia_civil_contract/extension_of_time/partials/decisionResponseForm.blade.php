{{ Form::open(array('route' => array('indonesiaCivilContract.extensionOfTime.response.decision.submit', $project->id, $eot->id),'method' => 'POST', 'id'=> 'responseForm')) }}
    <fieldset class="border-top">
        <section>
            <h3>{{ trans('extensionOfTime.responseForm') }}</h3>
            <hr/>
        </section>
        <section>
            <strong>{{ trans('extensionOfTime.extensionOfTimeReference') }}:</strong><br>
            <label class="input">
                {{{ $eot->reference }}}
            </label>
        </section>
        <section>
            <label class="label">{{ trans('extensionOfTime.subject') }}<span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('subject') ? 'state-error' : null }}}">
                {{ Form::text('subject', Input::old('subject'), array('required' => 'required')) }}
            </label>
            {{ $errors->first('subject', '<em class="invalid">:message</em>') }}
        </section>
        <section>
            <label class="label">{{ trans('extensionOfTime.reason') }}<span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('content') ? 'state-error' : null }}}">
                {{ Form::textArea('content', Input::old('content'), array('required' => 'required', 'rows' => "4", 'class' => 'fill-horizontal')) }}
            </label>
            {{ $errors->first('content', '<em class="invalid">:message</em>') }}
        </section>
        <section>
            <label class="label">{{ trans('extensionOfTime.decision') }}<span class="required">*</span>:</label>
            <table>
                <tr>
                    <td>
                        <label class="radio">
                            <input type="radio" name="type" value="{{{ \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_AGREE_ON_PROPOSED_VALUE }}}"
                                {{{ (Input::old('type') == \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_AGREE_ON_PROPOSED_VALUE) ? 'checked' : '' }}}>
                            <i></i>{{ trans('extensionOfTime.'.\PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_AGREE_ON_PROPOSED_VALUE_TEXT) }}</label>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td>
                        <label class="radio">
                            <input type="radio" name="type" value="{{{ \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_REJECT_PROPOSED_VALUE }}}"
                                {{{ (Input::old('type') == \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_REJECT_PROPOSED_VALUE) ? 'checked' : '' }}}>
                            <i></i>{{ trans('extensionOfTime.'.\PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_REJECT_PROPOSED_VALUE_TEXT) }}</label>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td>
                        <label class="radio">
                            <input type="radio" name="type" value="{{{ \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_GRANT }}}"
                                {{{ (Input::old('type') == \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_GRANT) ? 'checked' : '' }}}>
                            <i></i>{{ trans('extensionOfTime.'.\PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_GRANT_TEXT, array('currencyCode' => $project->modified_currency_code)) }}
                        </label>
                    </td>
                    <td>
                        <label class="input {{{ $errors->has('proposed_value') ? 'state-error' : null }}}">
                            {{ Form::number('proposed_value', Input::old('proposed_value'), array('placeholder' => trans('extensionOfTime.days'))) }}
                        </label>
                        {{ $errors->first('proposed_value', '<em class="invalid">:message</em>') }}
                    </td>
                </tr>
            </table>
            {{ $errors->first('type', '<em class="invalid">:message</em>') }}
        </section>
        <section>
            <label class="label">{{ trans('general.attachments') }}:</label>

            @include('file_uploads.partials.upload_file_modal')
        </section>
    </fieldset>
    <footer>

        <button type="submit" class="btn btn-primary">{{ trans('forms.reply') }}</button>
    </footer>
{{ Form::close() }}