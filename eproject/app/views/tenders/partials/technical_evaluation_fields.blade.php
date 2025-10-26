<div class="row">
    <section class="col col-md-6">
        <?php
        $disabled = false; $checkBoxValue = false;

        if ( $isTechnicalEvaluationReadOnly )
        {
            $disabled = true;

            if ( $withModel )
                $checkBoxValue = $isChecked;
        }
        ?>

        <label class="checkbox {{{ $errors->has('technical_evaluation_required') ? 'state-error' : null }}}">
            @if($disabled)
                @if($withModel && $checkBoxValue)
                    {{ Form::hidden('technical_evaluation_required', $checkBoxValue) }}
                @endif
                <input type="checkbox" value="1" name="technical_evaluation_required_chkbox" disabled {{{ $checkBoxValue ? "checked" : '' }}}/>
            @else
            {{ Form::checkbox('technical_evaluation_required', 1, Input::old('technical_evaluation_required', $checkBoxValue)) }}
            @endif
            <i></i>{{ trans("technicalEvaluation.technicalEvaluation") }}
        </label>
        {{ $errors->first('technical_evaluation_required', '<em class="invalid">:message</em>') }}
    </section>
    <?php
        $selectedContractLimit = (!empty($selectedContractLimitId)) ? PCK\ContractLimits\ContractLimit::find($selectedContractLimitId) : null;
    ?>
    <section class="col col-md-7" data-id="contract-limit-section">
        <label class="label" for="contract-limit">{{ trans("technicalEvaluation.contractLimit") }}:</label>
            @if($selectedContractLimit && $isTechnicalEvaluationReadOnly)
                <p>{{ ($selectedContractLimit) ? $selectedContractLimit->limit : trans('forms.none') }}</p>
                <input type="hidden" name="contract_limit_id" value="{{$selectedContractLimit->id}}">
            @else
                <select id="contract-limit" name="contract_limit_id" class="form-control">
                    @foreach($contractLimits as $contractLimit)
                        @if(!$contractLimit)
                            <option value="">
                                {{ trans("general.none") }}
                            </option>
                        @else
                            <option value="{{{ $contractLimit->id }}}" {{{ ($selectedContractLimitId == $contractLimit->id) ? 'selected' : '' }}}>{{{ $contractLimit->limit }}}</option>
                        @endif
                    @endforeach
                </select>
            @endif
    </section>
</div>