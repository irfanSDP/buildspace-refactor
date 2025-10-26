<?php use \PCK\RequestForVariation\RequestForVariation as RFV; ?>
<fieldset>
    @if ($requestForVariation)
        <div class="row">
            <section class="col col-xs-12 col-md-8 col-lg-10">
                <label class="label">{{ trans('requestForVariation.rfvNumber') }} : {{{ $requestForVariation->rfv_number }}}</label>
                <input type="hidden" name="rfvNumber" value="{{{ $requestForVariation->rfv_number }}}">
            </section>
            @if ($requestForVariation->isApproved())
            <section class="col col-xs-12 col-md-4 col-lg-2">
                <a href="{{ route('requestForVariation.print', [$project->id, $requestForVariation->id]) }}" target="_blank" class="btn btn-success pull-right"><i class="fa fa-print"></i> {{ trans('general.print') . ' ' . trans('general.summary') }}</a>
            </section>
            @endif
        </div>
    @endif
    @if ($requestForVariation && $requestForVariation->isApproved())
        <div class="row">
            <section class="col col-xs-12 col-md-12 col-lg-12">
                <label class="label">{{ trans('requestForVariation.aiNumber') }} : {{{ $requestForVariation->ai_number }}}</label>
            </section>
        </div>
    @endif
    @if(count($userPermissionGroups) == 1)
    <input type="hidden" name="request_for_variation_user_permission_group_id" value="@if($requestForVariation) {{{$requestForVariation->request_for_variation_user_permission_group_id}}} @else {{{$userPermissionGroups[0]->id}}} @endif">
    @else
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            @if (!$requestForVariation)
                <label class="label">{{ trans('requestForVariation.userPermissionGroup') }}<span class="required">*</span></label>
                <label class="fill-horizontal">
                    <select name="request_for_variation_user_permission_group_id" id="request_for_variation_user_permission_group_id" class="input-sm select2 fill-horizontal">
                        @foreach ($userPermissionGroups as $userPermissionGroup)
                        <option value="{{{ $userPermissionGroup->id }}}">{{{ $userPermissionGroup->name }}}</option>
                        @endforeach
                    </select>
                    <i></i>
                </label>
            @else
                <label class="label">{{ trans('requestForVariation.userPermissionGroup') }}</label>
                {{{ $requestForVariation->userPermissionGroup->name }}}
            @endif
        </section>
    </div>
    @endif

    <div class="row">
        <section class="col col-xs-12 col-md-6 col-lg-6">
            @if (!$requestForVariation || $canApprovePendingVerification)
                <label class="label">{{ trans('requestForVariation.descriptionForProposedVariationWork') }}<span class="required">*</span></label>
                <label class="textarea">
                    <textarea name="decription_of_proposed_variation" id="" rows="1" required>{{{ $requestForVariation->description ?? null }}}</textarea>
                </label>
            @else
                <label class="label">{{ trans('requestForVariation.descriptionForProposedVariationWork') }}</label>
                {{{ $requestForVariation->description ?? null }}}
            @endif
        </section>
        <section class="col col-xs-12 col-md-6 col-lg-6">
            @if (!$requestForVariation || $canApprovePendingVerification)
                <label class="label">{{ trans('requestForVariation.reasonForVariation') }}<span class="required">*</span></label>
                <label class="textarea">
                    <textarea name="reasons_for_variation" id="" rows="1" required>{{{ $requestForVariation->reasons_for_variation ?? null }}}</textarea>
                </label>
            @else
                <label class="label">{{ trans('requestForVariation.reasonForVariation') }}</label>
                {{{ $requestForVariation->reasons_for_variation ?? null }}}
            @endif
        </section>
    </div>
    <hr class="simple"/>
    <div class="row">
        <?php
        $colSize = $requestForVariation ? 4 : 6;
        ?>
        <section class="col col-xs-12 col-md-{{{$colSize}}} col-lg-{{{$colSize}}}">
            @if (!$requestForVariation || $canApprovePendingVerification)
                <label class="label">{{ trans('requestForVariation.categoryOfRfv') }}<span class="required">*</span>:</label>
                <label class="fill-horizontal">
                    <select name="rfv_category" id="rfv_category" class="input-sm select2 fill-horizontal">
                        @foreach ($rfvCategories as $category)
                        <option value="{{{ $category->id }}}" @if ($requestForVariation && $requestForVariation->request_for_variation_category_id == $category->id) selected="selected" @endif>{{{ $category->name }}}</option>
                        @endforeach
                    </select>
                    <i></i>
                </label>
            @else
                <label class="label">{{ trans('requestForVariation.categoryOfRfv') }}</label>
                {{{ $requestForVariation->requestForVariationCategory->name }}}
            @endif
        </section>
        @if ($requestForVariation)
        <section class="col col-xs-12 col-md-{{{$colSize}}} col-lg-{{{$colSize}}}">
            <label class="label">{{ trans('requestForVariation.estimateCostOfProposedVariationWork') }}</label>
            @if((int)$requestForVariation->nett_omission_addition < 0)
                <span style="color:red;" class="rfv_nett_omission_addition-txt">{{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) . ' (' . number_format(abs($requestForVariation->nett_omission_addition), 2, '.', ',')}}})</span>
            @else
                <span class="rfv_nett_omission_addition-txt">{{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) . ' ' . number_format($requestForVariation->nett_omission_addition, 2, '.', ',') }}}</span>
            @endif
        </section>
        @endif
        <section class="col col-xs-12 col-md-{{{$colSize}}} col-lg-{{{$colSize}}}">
            <label class="label">{{ trans('requestForVariation.timeImplication') }}</label>
            @if (!$requestForVariation || $canApprovePendingVerification)
                <label class="textarea">
                    <textarea name="time_implication" rows="1">{{{ $requestForVariation->time_implication ?? null }}}</textarea>
                </label>
            @else
                {{{ $requestForVariation->time_implication ?? null}}}
            @endif
        </section>
    </div>
    <hr class="simple"/>
    <div class="row">
        <section class="col col-xs-12 col-md-6 col-lg-6">
            @if ($canUserUploadDeleteFiles)
            <a class="btn btn-primary btn-sm" id="btnUploadFiles">
                <i class="fa fa-upload"></i> {{ trans('requestForVariation.upload') }}
            </a>
            @endif
        </section>
    </div>
    @include('request_for_variation.rfv.partials.rfvDocumentUpload')
    @if($requestForVariation && $requestForVariation->showFinancialStanding())
        <hr class="simple"/>
        <div class="row">
            <section class="col col-xs-12 col-md-12 col-lg-12">
                @include('request_for_variation.rfv.partials.rfv_financialStandingData')
            </section>
        </div>
    @endif
    @if($showKpiLimitTable)
        <hr class="simple"/>
        <div class="row">
            <section class="col col-xs-12 col-md-12 col-lg-12">
                @include('request_for_variation.rfv.partials.kpiLimitTable')
            </section>
        </div>
    @endif
    @if ($canAssignVerifiers)
        <hr class="simple"/>
        <div class="row">
            <section class="col col-xs-12 col-md-12 col-lg-12">
                @include('verifiers.select_verifiers', ['verifiers'=>$submitForApprovalPermissionUsers])
            </section>
        </div>
    @endif
    @if($requestForVariation && ($requestForVariation->status == \PCK\RequestForVariation\RequestForVariation::STATUS_PENDING_APPROVAL || $requestForVariation->status == \PCK\RequestForVariation\RequestForVariation::STATUS_APPROVED))
    <hr class="simple"/>
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <table class="table table-bordered table-condensed table-striped table-hover smallFont">
                <thead>
                    <tr>
                        <th style="width:32px;text-align:center;">No.</th>
                        <th>{{ trans('requestForVariation.verifiers') }}</th>
                        <th style="width:100px;text-align:center;">{{ trans('requestForVariation.status') }}</th>
                        <th style="width:150px;text-align:center;">{{ trans('general.date') . ' & ' . trans('general.time') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if($verifiers->count())
                        @foreach($verifiers as $idx => $verifier)
                        <tr>
                            <td style="text-align:center;">{{{$idx+1}}}</td>
                            <td>
                                <?php
                                $verifierUser = $verifier->verifier;
                                ?>
                                {{{$verifierUser->name}}}
                                <br />
                                <div style="display:inline;padding: .2em .6em .3em;font-size: 75%;font-weight: 700;line-height: 1;color: #fff;text-align: center;white-space: nowrap;vertical-align: baseline;border-radius: .25em;" class="bg-color-teal">
                                @if($verifierUser->hasCompanyProjectRole($project, \PCK\ContractGroups\Types\Role::PROJECT_OWNER))
                                    {{{$project->subsidiary->name}}}
                                @else
                                    {{{ ($company = $verifierUser->getAssignedCompany($project)) ? $company->name : '-' }}}
                                @endif
                                </div>
                            </td>
                            <td style="text-align:center;">@if($verifier->approved) {{ trans('requestForVariation.approved') }} @else {{ trans('requestForVariation.pending') }} @endif</td>
                            <td style="text-align:center;">{{ Carbon\Carbon::parse($verifier->verified_at)->format(\Config::get('dates.readable_timestamp')) }}</td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td style="text-align:center;" colspan="4">{{ trans('requestForVariation.noVerifierAssigned') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </section>
    </div>
    @endif
</fieldset>
