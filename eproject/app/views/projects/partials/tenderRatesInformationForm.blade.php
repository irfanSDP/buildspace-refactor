<?php
if(isset($tenderAlternative) && !empty($tenderAlternative))
{
    $discountedPercentageFormInputName = "discounted_percentage[".$tenderAlternative->tender_alternative_id."]";
    $discountedPercentageFormName      = "discounted_percentage.".$tenderAlternative->tender_alternative_id;
    $vueModelDiscountPercentageInput   = "discountPercentageInput".$tenderAlternative->tender_alternative_id;

    $discountedAmountFormInputName = "discounted_amount[".$tenderAlternative->tender_alternative_id."]";
    $discountedAmountFormName      = "discounted_amount.".$tenderAlternative->tender_alternative_id;
    $vueModelDiscountAmountInput   = "discountAmountInput".$tenderAlternative->tender_alternative_id;
}
else
{
    $discountedPercentageFormInputName = "discounted_percentage";
    $discountedPercentageFormName      = "discounted_percentage";
    $vueModelDiscountPercentageInput   = "discountPercentageInput";

    $discountedAmountFormInputName = "discounted_amount";
    $discountedAmountFormName      = "discounted_amount";
    $vueModelDiscountAmountInput   = "discountAmountInput";
}
?>
<div class="col col-xs-12 col-md-6 col-lg-6">
    <div class="well padded">
        <section>
            <h3>{{ trans("tenders.projectDiscount") }}</h3>
        </section>

        <section>
            <label class="label">{{ trans("tenders.discountPercentage") }}:</label>
            <label class="input-group {{{ $errors->has($discountedPercentageFormName) ? 'state-error' : null }}}">
                <span class="input-group-addon">%</span>
                <input class="form-control" name="{{$discountedPercentageFormInputName}}" type="text" v-model="{{$vueModelDiscountPercentageInput}}" v-attr="disabled: {{$vueModelDiscountAmountInput}}.length > 0" value="{{{ Input::old($discountedPercentageFormName) }}}" style="text-indent: 10px;">
            </label>
            {{ $errors->first($discountedPercentageFormName, '<em class="invalid">:message</em>') }}
        </section>

        <section>
            <label class="label">{{ trans("tenders.discountAmount") }} :</label>
            <label class="input-group {{{ $errors->has($discountedAmountFormName) ? 'state-error' : null }}}">
                <span class="input-group-addon">{{{ $project->modified_currency_code }}}</span>
                <input class="form-control" name="{{$discountedAmountFormInputName}}" type="text" v-model="{{$vueModelDiscountAmountInput}}" v-attr="disabled: {{$vueModelDiscountPercentageInput}}.length > 0" value="{{{ Input::old($discountedAmountFormName) }}}" style="text-indent: 10px;">
            </label>
            {{ $errors->first($discountedAmountFormName, '<em class="invalid">:message</em>') }}
        </section>
    </div>
</div>

@if ( $tender->callingTenderInformation->allowContractorProposeOwnCompletionPeriod() )
<?php
if(isset($tenderAlternative) && !empty($tenderAlternative))
{
    $completionPeriodFormInputName = "completion_period[".$tenderAlternative->tender_alternative_id."]";
    $completionPeriodFormName      = "completion_period.".$tenderAlternative->tender_alternative_id;

    $adjustmentPercentageFormInputName = "contractor_adjustment_percentage[".$tenderAlternative->tender_alternative_id."]";
    $adjustmentPercentageFormName      = "contractor_adjustment_percentage.".$tenderAlternative->tender_alternative_id;
    $vueModelAdjustmentPercentageInput = "adjustmentPercentageInput".$tenderAlternative->tender_alternative_id;

    $adjustmentAmountFormInputName = "contractor_adjustment_amount[".$tenderAlternative->tender_alternative_id."]";
    $adjustmentAmountFormName      = "contractor_adjustment_amount.".$tenderAlternative->tender_alternative_id;
    $vueModelAdjustmentAmountInput = "adjustmentAmountInput".$tenderAlternative->tender_alternative_id;
}
else
{
    $completionPeriodFormInputName = "completion_period";
    $completionPeriodFormName      = "completion_period";

    $adjustmentPercentageFormInputName = "contractor_adjustment_percentage";
    $adjustmentPercentageFormName      = "contractor_adjustment_percentage";
    $vueModelAdjustmentPercentageInput = "adjustmentPercentageInput";

    $adjustmentAmountFormInputName = "contractor_adjustment_amount";
    $adjustmentAmountFormName      = "contractor_adjustment_amount";
    $vueModelAdjustmentAmountInput = "adjustmentAmountInput";
}
?>
    <div class="col col-xs-12 col-md-6 col-lg-6">
        <div class="well padded">
            <section>
                <h3>{{ trans("tenders.contractorProposal") }}</h3>
            </section>

            <section>
                <label class="label">{{ trans("tenders.proposedCompletionPeriod") }} :</label>
                <label class="input-group {{{ $errors->has($completionPeriodFormName) ? 'state-error' : null }}}">
                    <span class="input-group-addon">{{{ $project->completion_period_metric }}}</span>
                    {{ Form::text($completionPeriodFormInputName, Input::old($completionPeriodFormName), array('class' => 'form-control', 'style' => 'text-indent: 10px;')) }}
                </label>
                {{ $errors->first($completionPeriodFormName, '<em class="invalid">:message</em>') }}
            </section>

            <section>
                <label class="label">{{ trans("tenders.adjustmentPercentage") }} :</label>
                <label class="input-group {{{ $errors->has($adjustmentPercentageFormName) ? 'state-error' : null }}}">
                    <span class="input-group-addon">%</span>
                    <input class="form-control" name="{{$adjustmentPercentageFormInputName}}" type="text" v-model="{{$vueModelAdjustmentPercentageInput}}" v-attr="disabled: {{$vueModelAdjustmentAmountInput}}.length > 0" value="{{{ Input::old($adjustmentPercentageFormName) }}}" style="text-indent: 10px;">
                </label>
                {{ $errors->first($adjustmentPercentageFormName, '<em class="invalid">:message</em>') }}
            </section>

            <section>
                <label class="label">{{ trans("tenders.adjustmentAmount") }} :</label>
                <label class="input-group {{{ $errors->has($adjustmentAmountFormName) ? 'state-error' : null }}}">
                    <span class="input-group-addon">{{{ $project->modified_currency_code }}}</span>
                    <input class="form-control" name="{{$adjustmentAmountFormInputName}}" type="text" v-model="{{$vueModelAdjustmentAmountInput}}" v-attr="disabled: {{$vueModelAdjustmentPercentageInput}}.length > 0" value="{{{ Input::old($adjustmentAmountFormName) }}}" style="text-indent: 10px;">
                </label>
                {{ $errors->first($adjustmentAmountFormName, '<em class="invalid">:message</em>') }}
            </section>
        </div>
    </div>
@endif