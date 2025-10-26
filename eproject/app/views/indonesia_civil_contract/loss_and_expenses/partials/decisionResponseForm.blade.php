{{ Form::open(array('route' => array('indonesiaCivilContract.lossAndExpenses.response.decision.submit', $project->id, $le->id),'method' => 'POST', 'id'=> 'responseForm')) }}
    <fieldset class="border-top">
        <section>
            <h3>{{ trans('lossAndExpenses.responseForm') }}</h3>
            <hr/>
        </section>
        <section>
            <strong>{{ trans('lossAndExpenses.lossAndExpensesReference') }}:</strong><br>
            <label class="input">
                {{{ $le->reference }}}
            </label>
        </section>
        <section>
            <label class="label">{{ trans('lossAndExpenses.subject') }}<span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('subject') ? 'state-error' : null }}}">
                {{ Form::text('subject', Input::old('subject'), array('required' => 'required')) }}
            </label>
            {{ $errors->first('subject', '<em class="invalid">:message</em>') }}
        </section>
        <section>
            <label class="label">{{ trans('lossAndExpenses.reason') }}<span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('content') ? 'state-error' : null }}}">
                {{ Form::textArea('content', Input::old('content'), array('required' => 'required', 'rows' => "4", 'class' => 'fill-horizontal')) }}
            </label>
            {{ $errors->first('content', '<em class="invalid">:message</em>') }}
        </section>
        <section>
            <label class="label">{{ trans('lossAndExpenses.decision') }}<span class="required">*</span>:</label>
            <table>
                <tr>
                    <td>
                        <label class="radio">
                            <input type="radio" name="type" value="{{{ \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_AGREE_ON_PROPOSED_VALUE }}}"
                                {{{ (Input::old('type') == \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_AGREE_ON_PROPOSED_VALUE) ? 'checked' : '' }}}>
                            <i></i>{{ trans('lossAndExpenses.'.\PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_AGREE_ON_PROPOSED_VALUE_TEXT) }}</label>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td>
                        <label class="radio">
                            <input type="radio" name="type" value="{{{ \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_REJECT_PROPOSED_VALUE }}}"
                                {{{ (Input::old('type') == \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_REJECT_PROPOSED_VALUE) ? 'checked' : '' }}}>
                            <i></i>{{ trans('lossAndExpenses.'.\PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_REJECT_PROPOSED_VALUE_TEXT) }}</label>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td>
                        <label class="radio">
                            <input type="radio" name="type" value="{{{ \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_GRANT }}}"
                                {{{ (Input::old('type') == \PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_GRANT) ? 'checked' : '' }}}>
                            <i></i>{{ trans('lossAndExpenses.'.\PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse::TYPE_GRANT_TEXT, array('currencyCode' => $project->modified_currency_code)) }}
                        </label>
                    </td>
                    <td>
                        <label class="input {{{ $errors->has('proposed_value') ? 'state-error' : null }}}">
                            {{ Form::number('proposed_value', Input::old('proposed_value'), array('step' => '0.01', 'placeholder' => trans('lossAndExpenses.claimAmount'))) }}
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

        <button type="submit" class="btn btn-primary">{{ trans('forms.submit') }}</button>
    </footer>
{{ Form::close() }}