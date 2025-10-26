<header>
    {{ trans('projects.postContractInformation') }}
</header>

<fieldset>
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <div class="well">
                <label class="label">{{ trans('projects.selectedContractor') }}:</label>
                <label class="input" id="contractor-name">
                    {{{ isset($contractor) ? mb_strtoupper($contractor->name) : null }}}
                </label>
                {{ Form::hidden('contractorId', isset($contractor) ? $contractor->id : -1, ['id'=>'contractor_id-hidden']) }}
            </div>
        </section>
    </div>

    <div class="row">
        <section class="col col-xs-6 col-md-6 col-lg-6">
            <label class="label">{{ trans('projects.commencementDate') }} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('commencement_date') ? 'state-error' : null }}}">
                <i class="icon-append fa fa-calendar"></i>
                {{ Form::text('commencement_date', Input::old('commencement_date'), array('required' => 'required', 'id' => 'commencement_date', 'class' => 'commencement_date')) }}
            </label>
            {{ $errors->first('commencement_date', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-6 col-md-6 col-lg-6">
            <label class="label">{{ trans('projects.completionDate') }} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('completion_date') ? 'state-error' : null }}}">
                <i class="icon-append fa fa-calendar"></i>
                {{ Form::text('completion_date', Input::old('completion_date'), array('required' => 'required', 'id' => 'completion_date', 'class' => 'completion_date')) }}
            </label>
            {{ $errors->first('completion_date', '<em class="invalid">:message</em>') }}
        </section>
    </div>

    <div class="row">
        <section class="col col-xs-12 col-md-3 col-lg-3">
            <label class="label">{{ trans('projects.contractSum') }} (
                @if(empty($project->modified_currency_code))
                    {{{ mb_strtoupper($project->country->currency_code) }}}
                @else
                    {{{ mb_strtoupper($project->modified_currency_code) }}}
                @endif
            ) :</label>
            <label class="input {{{ $errors->has('contract_sum') ? 'state-error' : null }}}">
                {{ Form::number('contract_sum', Input::old('contract_sum', '0.00'), ['step'=>'0.01']) }}
            </label>
            {{ $errors->first('contract_sum', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-12 col-md-3 col-lg-3">
            <label class="label">{{ trans('projects.liquidateDamages') }} (
                @if(empty($project->modified_currency_code))
                    {{{ mb_strtoupper($project->country->currency_code) }}}
                @else
                    {{{ mb_strtoupper($project->modified_currency_code) }}}
                @endif
            ) :</label>
            <label class="input {{{ $errors->has('liquidate_damages') ? 'state-error' : null }}}">
                {{ Form::number('liquidate_damages', Input::old('liquidate_damages', '0.00'), ['step'=>'0.01']) }}
            </label>
            {{ $errors->first('liquidate_damages', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-12 col-md-3 col-lg-3">
            <label class="label">{{ trans('projects.amountOfPerformanceBond') }} (
                @if(empty($project->modified_currency_code))
                    {{{ mb_strtoupper($project->country->currency_code) }}}
                @else
                    {{{ mb_strtoupper($project->modified_currency_code) }}}
                @endif
            ) :</label>
            <label class="input {{{ $errors->has('amount_performance_bond') ? 'state-error' : null }}}">
                {{ Form::number('amount_performance_bond', Input::old('amount_performance_bond', '0.00'), ['step'=>'0.01']) }}
            </label>
            {{ $errors->first('amount_performance_bond', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-12 col-md-3 col-lg-3">
            <label class="label">{{ trans('dailyLabourReports.trade') }} <span class="required">*</span>:</label>
            <label class="fill-horizontal {{{ $errors->has('trade') ? 'state-error' : null }}}">
                <select name="trade" id="trade" class="select2 fill-horizontal" style="width:100%;" required>
                    <option selected disabled>Select</option>
                    @foreach($trades as $trade)
                        @if(Input::old('trade') == $trade->id)
                            <option selected value="{{{ $trade->id }}}">
                                {{{ $trade->name }}}
                            </option>
                        @else
                            <option value="{{{ $trade->id }}}">
                                {{{ $trade->name }}}
                            </option>
                        @endif
                    @endforeach
                </select>
            </label>
            {{ $errors->first('trade', '<em class="invalid">:message</em>') }}
        </section>
    </div>

    <div class="row">
        <section class="col col-xs-12 col-md-3 col-lg-3">
            <label class="label">{{ trans('projects.cpcDate') }} :</label>
            <label class="input {{{ $errors->has('cpc_date') ? 'state-error' : null }}}">
                <i class="icon-append fa fa-calendar"></i>
                {{ Form::text('cpc_date', Input::old('cpc_date'), ['id' => 'cpc_date', 'class' => 'cpc_date']) }}
            </label>
            {{ $errors->first('cpc_date', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-12 col-md-3 col-lg-3">
            <label class="label">{{ trans('projects.extensionOfTimeDate') }} :</label>
            <label class="input {{{ $errors->has('extension_of_time_date') ? 'state-error' : null }}}">
                <i class="icon-append fa fa-calendar"></i>
                {{ Form::text('extension_of_time_date', Input::old('extension_of_time_date'), ['id' => 'extension_of_time_date', 'class' => 'extension_of_time_date']) }}
            </label>
            {{ $errors->first('extension_of_time_date', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-12 col-md-3 col-lg-3">
            <label class="label">{{ trans('projects.dlpPeriod') }} :</label>
            <label class="input {{{ $errors->has('defect_liability_period') ? 'state-error' : null }}}">
                {{ Form::number('defect_liability_period', Input::old('defect_liability_period', 24), ['required'=>'required']) }}
            </label>
            {{ $errors->first('defect_liability_period', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-12 col-md-3 col-lg-3">
            <label class="label">&nbsp;</label>
            <label class="fill-horizontal {{{ $errors->has('defect_liability_period_unit') ? 'state-error' : null }}}">
                <select name="defect_liability_period_unit" id="defect_liability_period_unit" class="select2 fill-horizontal" style="width:100%;" required>
                    <option value="{{\PCK\ProjectDetails\PAM2006ProjectDetail::DLP_PERIOD_UNIT_MONTH}}" selected>{{{trans('tenders.months')}}}</option>
                    <option value="{{\PCK\ProjectDetails\PAM2006ProjectDetail::DLP_PERIOD_UNIT_WEEK}}">{{{trans('tenders.weeks')}}}</option>
                    <option value="{{\PCK\ProjectDetails\PAM2006ProjectDetail::DLP_PERIOD_UNIT_DAY}}">{{{trans('projects.days')}}}</option>
                </select>
            </label>
            {{ $errors->first('defect_liability_period_unit', '<em class="invalid">:message</em>') }}
        </section>
    </div>

    <div class="row">
        <section class="col col-xs-12 col-md-3 col-lg-3">
            <label class="label">{{ trans('projects.cmgdDate') }} :</label>
            <label class="input {{{ $errors->has('certificate_of_making_good_defect_date') ? 'state-error' : null }}}">
                <i class="icon-append fa fa-calendar"></i>
                {{ Form::text('certificate_of_making_good_defect_date', Input::old('certificate_of_making_good_defect_date'), ['id' => 'certificate_of_making_good_defect_date', 'class' => 'certificate_of_making_good_defect_date']) }}
            </label>
            {{ $errors->first('certificate_of_making_good_defect_date', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-12 col-md-3 col-lg-3">
            <label class="label">{{ trans('projects.cncDate') }} :</label>
            <label class="input {{{ $errors->has('cnc_date') ? 'state-error' : null }}}">
                <i class="icon-append fa fa-calendar"></i>
                {{ Form::text('cnc_date', Input::old('cnc_date'), ['id' => 'cnc_date', 'class' => 'cnc_date']) }}
            </label>
            {{ $errors->first('cnc_date', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-12 col-md-3 col-lg-3">
            <label class="label">{{ trans('projects.performanceBondValidityDate') }} :</label>
            <label class="input {{{ $errors->has('performance_bond_validity_date') ? 'state-error' : null }}}">
                <i class="icon-append fa fa-calendar"></i>
                {{ Form::text('performance_bond_validity_date', Input::old('performance_bond_validity_date'), ['id' => 'performance_bond_validity_date', 'class' => 'performance_bond_validity_date']) }}
            </label>
            {{ $errors->first('performance_bond_validity_date', '<em class="invalid">:message</em>') }}
        </section>
        <section class="col col-xs-12 col-md-3 col-lg-3">
            <label class="label">{{ trans('projects.insurancePolicyCoverageDate') }} :</label>
            <label class="input {{{ $errors->has('insurance_policy_coverage_date') ? 'state-error' : null }}}">
                <i class="icon-append fa fa-calendar"></i>
                {{ Form::text('insurance_policy_coverage_date', Input::old('insurance_policy_coverage_date'), ['id' => 'insurance_policy_coverage_date', 'class' => 'insurance_policy_coverage_date']) }}
            </label>
            {{ $errors->first('insurance_policy_coverage_date', '<em class="invalid">:message</em>') }}
        </section>
    </div>

    <div class="row">
        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label">{{ trans('projects.interimClaimInterval') }} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('interim_claim_interval') ? 'state-error' : null }}}">
                {{ Form::number('interim_claim_interval', Input::old('interim_claim_interval', 1), array('required' => 'required')) }}
            </label>
            {{ $errors->first('interim_claim_interval', '<em class="invalid">:message</em>') }}
        </section>

        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label">{{ trans('projects.periodOfHonouringCertificates') }} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('period_of_honouring_certificate') ? 'state-error' : null }}}">
                {{ Form::number('period_of_honouring_certificate', Input::old('period_of_honouring_certificate', 21), array('required' => 'required')) }}
            </label>
            {{ $errors->first('period_of_honouring_certificate', '<em class="invalid">:message</em>') }}
        </section>
    </div>
    <div class="row">
        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label" style="white-space: normal;">{{ trans('projects.minimumDaysToComplyWithAI') }} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('min_days_to_comply_with_ai') ? 'state-error' : null }}}">
                {{ Form::number('min_days_to_comply_with_ai', Input::old('min_days_to_comply_with_ai', 7), array('required' => 'required')) }}
            </label>
            {{ $errors->first('min_days_to_comply_with_ai', '<em class="invalid">:message</em>') }}
        </section>

        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label" style="white-space: normal;">{{ trans('projects.eotClaimNoticeDeadline') }} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('deadline_submitting_notice_of_intention_claim_eot') ? 'state-error' : null }}}">
                {{ Form::number(
                    'deadline_submitting_notice_of_intention_claim_eot',
                    Input::old('deadline_submitting_notice_of_intention_claim_eot',
                    28
                ), array('required' => 'required')) }}
            </label>
            {{ $errors->first('deadline_submitting_notice_of_intention_claim_eot', '<em class="invalid">:message</em>') }}
        </section>
    </div>

    <div class="row">
        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label" style="white-space: normal;">{{ trans('projects.eotFinalClaimDeadline') }} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('deadline_submitting_final_claim_eot') ? 'state-error' : null }}}">
                {{ Form::number(
                    'deadline_submitting_final_claim_eot',
                    Input::old('deadline_submitting_final_claim_eot',
                    28
                ), array('required' => 'required')) }}
            </label>
            {{ $errors->first('deadline_submitting_final_claim_eot', '<em class="invalid">:message</em>') }}
        </section>

        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label" style="white-space: normal;">{{ trans('projects.architectEotParticularsRequest') }} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('deadline_architect_request_info_from_contractor_eot_claim') ? 'state-error' : null }}}">
                {{ Form::number(
                    'deadline_architect_request_info_from_contractor_eot_claim',
                    Input::old('deadline_architect_request_info_from_contractor_eot_claim',
                    28
                ), array('required' => 'required')) }}
            </label>
            {{ $errors->first('deadline_architect_request_info_from_contractor_eot_claim', '<em class="invalid">:message</em>') }}
        </section>
    </div>

    <div class="row">
        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label" style="white-space: normal;">{{ trans('projects.eotClaimDecisionDeadline') }} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('deadline_architect_decide_on_contractor_eot_claim') ? 'state-error' : null }}}">
                {{ Form::number(
                    'deadline_architect_decide_on_contractor_eot_claim',
                    Input::old('deadline_architect_decide_on_contractor_eot_claim',
                    6
                ), array('required' => 'required')) }}
            </label>
            {{ $errors->first('deadline_architect_decide_on_contractor_eot_claim', '<em class="invalid">:message</em>') }}
        </section>

        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label" style="white-space: normal;">{{ trans('projects.lossAndExpenseNoticeDeadline') }} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('deadline_submitting_note_of_intention_claim_l_and_e') ? 'state-error' : null }}}">
                {{ Form::number(
                    'deadline_submitting_note_of_intention_claim_l_and_e',
                    Input::old('deadline_submitting_note_of_intention_claim_l_and_e',
                    28
                ), array('required' => 'required')) }}
            </label>
            {{ $errors->first('deadline_submitting_note_of_intention_claim_l_and_e', '<em class="invalid">:message</em>') }}
        </section>
    </div>

    <div class="row">
        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label" style="white-space: normal;">{{ trans('projects.finalLeClaimDeadline') }} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('deadline_submitting_final_claim_l_and_e') ? 'state-error' : null }}}">
                {{ Form::number(
                    'deadline_submitting_final_claim_l_and_e',
                    Input::old('deadline_submitting_final_claim_l_and_e',
                    28
                ), array('required' => 'required')) }}
            </label>
            {{ $errors->first('deadline_submitting_final_claim_l_and_e', '<em class="invalid">:message</em>') }}
        </section>

        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label" style="white-space: normal;">{{ trans('projects.leClaimNoticeDeadline') }} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('deadline_submitting_note_of_intention_claim_ae') ? 'state-error' : null }}}">
                {{ Form::text(
                    'deadline_submitting_note_of_intention_claim_ae',
                    Input::old('deadline_submitting_note_of_intention_claim_ae',
                    28
                ), array('required' => 'required')) }}
            </label>
            {{ $errors->first('deadline_submitting_note_of_intention_claim_ae', '<em class="invalid">:message</em>') }}
        </section>
    </div>

    <div class="row">
        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label" style="white-space: normal;">{{ trans('projects.finalAeClaimDeadline') }} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('deadline_submitting_final_claim_ae') ? 'state-error' : null }}}">
                {{ Form::number(
                    'deadline_submitting_final_claim_ae',
                    Input::old('deadline_submitting_final_claim_ae',
                    28
                ), array('required' => 'required')) }}
            </label>
            {{ $errors->first('deadline_submitting_final_claim_ae', '<em class="invalid">:message</em>') }}
        </section>

        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label" style="white-space: normal;">{{ trans('projects.retainedCertifiedValuePercentage') }} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('percentage_of_certified_value_retained') ? 'state-error' : null }}}">
                {{ Form::number(
                    'percentage_of_certified_value_retained',
                    Input::old('percentage_of_certified_value_retained',
                    '10.00'
                ), ['step'=>'0.01', 'required' => 'required']) }}
            </label>
            {{ $errors->first('percentage_of_certified_value_retained', '<em class="invalid">:message</em>') }}
        </section>
    </div>

    <div class="row">
        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label" style="white-space: normal;">{{ trans('projects.limitOfRetentionFund') }} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('limit_retention_fund') ? 'state-error' : null }}}">
                {{ Form::number(
                    'limit_retention_fund',
                    Input::old('limit_retention_fund',
                    '5.00'
                ), ['step'=>'0.01', 'required' => 'required']) }}
            </label>
            {{ $errors->first('limit_retention_fund', '<em class="invalid">:message</em>') }}
        </section>

        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label" style="white-space: normal;">{{ trans('projects.percentageOfCertificateGoods') }} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('percentage_value_of_materials_and_goods_included_in_certificate') ? 'state-error' : null }}}">
                {{ Form::number(
                    'percentage_value_of_materials_and_goods_included_in_certificate',
                    Input::old('percentage_value_of_materials_and_goods_included_in_certificate',
                    '100.00'
                ), ['step'=>'0.01', 'required' => 'required']) }}
            </label>
            {{ $errors->first('percentage_value_of_materials_and_goods_included_in_certificate', '<em class="invalid">:message</em>') }}
        </section>
    </div>

    <div class="row">
        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label" style="white-space: normal;">{{ trans('projects.periodOfArchitectIssueInterimCertificate') }} <span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('period_of_architect_issue_interim_certificate') ? 'state-error' : null }}}">
                {{ Form::number(
                    'period_of_architect_issue_interim_certificate',
                    Input::old('period_of_architect_issue_interim_certificate',
                    21
                ), array('required' => 'required')) }}
            </label>
            {{ $errors->first('period_of_architect_issue_interim_certificate', '<em class="invalid">:message</em>') }}
        </section>
    </div>
</fieldset>