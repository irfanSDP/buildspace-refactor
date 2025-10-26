<div class="row">
    <div class="col col-lg-12">
        <h4>Section D - Details of Consultant Services Procurement</h4>
    </div>
</div>
<hr class="simple">
<div class="row">
    <div class="col col-lg-12">
        <dl class="dl-horizontal no-margin">
            <dt style="width:180px;text-align:left;">Consultant Category:</dt>
            <dd>{{{ $vendorCategoryRfp->vendorCategory->name }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
<div class="row">
    <div class="col col-lg-12">
        <dl class="dl-horizontal no-margin">
            <dt style="width:180px;text-align:left;">No. of Consultant Shortlisted:</dt>
            <dd>{{ $openRfp->shortlistedCompanies()->get()->count() }}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
<div class="row">
    <div class="col col-lg-12">
        <dl class="dl-horizontal no-margin">
            <dt style="width:180px;text-align:left;">Interview Date:</dt>
            <dd>@if($latestRfpInterview) {{{ Carbon\Carbon::parse($latestRfpInterview->interview_date)->format('d-M-Y') }}} @else - @endif</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
<hr class="simple">
<div class="well">
    <div class="row">
        <div class="col col-lg-12">
            <h5>{{ trans('vendorProfile.vendorProfile') }}</h5>
        </div>
    </div>
    <div class="row">
        <div class="col col-lg-12">
            <dl class="dl-horizontal no-margin">
                <dt style="width:180px;text-align:left;">Pre-Qualification Rating:</dt>
                <dd>
                    <table class="table table-bordered table-hover" style="text-align: center;">
                        <thead>
                            <tr>
                                <th style="text-align: center;width:38px;">No.</th>
                                <th style="text-align: left;">{{ trans('vendorManagement.vendorWorkCategories') }}</th>
                                <th style="text-align: center;width:280px;">{{ trans('vendorManagement.rating') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($vendorPreQualificationData))
                                @foreach($vendorPreQualificationData as $idx => $preQData)
                                <tr>
                                    <td>{{ ($idx+1) }}</td>
                                    <td style="text-align:left;">{{{ $preQData['vendor_work_category'] }}}</td>
                                    <td>{{{ $preQData['grade'] }}}</td>
                                </tr>
                                @endforeach
                            @else
                            <tr>
                                <td colspan="3" style="text-align: center;">{{ trans('general.noRecordsFound') }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </dd>
                <dt>&nbsp;</dt>
                <dd>&nbsp;</dd>
            </dl>
        </div>
    </div>
    <div class="row">
        <div class="col col-lg-4">
            <dl class="dl-horizontal no-margin">
                <dt>{{ trans('vendorManagement.bumiputeraEquity') }}:</dt>
                <dd>{{{ $awardedConsultant->company->bumiputera_equity }}}</dd>
                <dt>&nbsp;</dt>
                <dd>&nbsp;</dd>
            </dl>
        </div>
        <div class="col col-lg-4">
            <dl class="dl-horizontal no-margin">
                <dt>{{ trans('vendorManagement.nonBumiputeraEquity') }}:</dt>
                <dd>{{{ $awardedConsultant->company->non_bumiputera_equity }}}</dd>
                <dt>&nbsp;</dt>
                <dd>&nbsp;</dd>
            </dl>
        </div>
        <div class="col col-lg-4">
            <dl class="dl-horizontal no-margin">
                <dt>{{ trans('vendorManagement.foreignerEquity') }}:</dt>
                <dd>{{{ $awardedConsultant->company->foreigner_equity }}}</dd>
                <dt>&nbsp;</dt>
                <dd>&nbsp;</dd>
            </dl>
        </div>
    </div>
    <div class="row">
        <div class="col col-lg-12">
            <dl class="dl-horizontal no-margin">
                <dt>{{ trans('vendorManagement.bumiputera') }}:</dt>
                <dd>@if($awardedConsultant->company->is_bumiputera) {{ trans('forms.yes') }} @else {{ trans('forms.no') }} @endif</dd>
                <dt>&nbsp;</dt>
                <dd>&nbsp;</dd>
            </dl>
        </div>
    </div>
    <div class="row">
        <div class="col col-xs-12 col-sm-12 col-md-6 col-lg-6">
            <dl class="dl-horizontal no-margin">
                <dt>{{ trans('tenders.registrationStatus') }}:</dt>
                <dd>@if($latestVendorRegistration) {{ $latestVendorRegistration->statusText }} @else - @endif</dd>
                <dt>&nbsp;</dt>
                <dd>&nbsp;</dd>
            </dl>
        </div>
        <div class="col col-xs-12 col-sm-12 col-md-6 col-lg-6">
            <dl class="dl-horizontal no-margin">
                <dt>{{ trans('vendorManagement.expiryDate') }}:</dt>
                <dd>{{{ ($awardedConsultant->company->expiry_date) ? Carbon\Carbon::parse($awardedConsultant->company->expiry_date)->format('d/m/Y') : '-'}}}</dd>
                <dt>&nbsp;</dt>
                <dd>&nbsp;</dd>
            </dl>
        </div>
    </div>
    <hr class="simple">
    <?php $gradingSystem = PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter::first()->vendorManagementGrade ?>
    @if($gradingSystem && $awardedConsultant->company->hasLatestPerformanceEvaluationScore())
    <div class="row">
        <div class="col col-lg-12">
            <dl class="dl-horizontal no-margin">
                <dt style="width:280px;text-align:left;">Latest Performance Evaluation Rating (Overall):</dt>
                <dd>{{{ $gradingSystem->getGrade($awardedConsultant->company->getLatestPerformanceEvaluationAverageDeliberatedScore())->description }}}</dd>
                <dt>&nbsp;</dt>
                <dd>&nbsp;</dd>
            </dl>
        </div>
    </div>
    <div class="row">
        <div class="col col-lg-12">
            <dl class="dl-horizontal no-margin">
                <dt style="width:360px;text-align:left;">Latest Performance Evaluation Rating (Work Categories):</dt>
                <dd></dd>
                <dt>&nbsp;</dt>
                <dd>&nbsp;</dd>
            </dl>
        </div>
        <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <table class="table table-bordered table-hover" style="text-align: center;">
                <thead>
                    <tr>
                        <th style="text-align:left;">{{ trans('vendorManagement.vendorWorkCategories') }}</th>
                        <th style="text-align:center;width:280px;">{{{ trans('vendorManagement.rating') }}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($awardedConsultant->company->vendors as $vendor)
                    <tr>
                        <td style="text-align:left;">{{ $vendor->vendorWorkCategory->name }}</td>
                        <?php
                        $vendorCycleScore = $vendor->getLatestPerformanceEvaluationCycleScore();
                        ?>
                        <td>@if($vendorCycleScore) {{{ $gradingSystem->getGrade($vendorCycleScore->deliberated_score)->description }}} @else - @endif</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
            @include('consultant_management.partials.vendor_profile.project_track_record')
        </div>
    </div>
    @else
    <div class="row">
        <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="alert alert-warning text-center">
                <i class="fa-fw fa fa-info"></i>
                <strong>Info!</strong> There is no Performance Evaluation Score for <strong>{{{ $awardedConsultant->company->name }}}</strong>.
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
            @include('consultant_management.partials.vendor_profile.project_track_record')
        </div>
    </div>
    @endif
</div>
<hr class="simple">
<div class="well">
    <?php
    $sectionDDetails = $approvalDocument->sectionD->details()->where('company_id', '=', $awardedConsultant->company_id)->first();
    ?>
    @if($approvalDocument->status == PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT)
    {{ Form::open(['route' => ['consultant.management.approval.document.section.d.details.store', $vendorCategoryRfp->id], 'class' => 'smart-form']) }}
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <label class="label">Scope of Services :</label>
            <label class="textarea {{{ $errors->has('scope_of_services') ? 'state-error' : null }}}">
                {{ Form::textarea('scope_of_services', Input::old('scope_of_services', ($sectionDDetails) ? $sectionDDetails->scope_of_services : null), ['rows' => '1', 'autofocus' => 'autofocus']) }}
            </label>
            {{ $errors->first('scope_of_services', '<em class="invalid">:message</em>') }}
        </section>
    </div>
    <footer>
        {{ Form::hidden('cid', $awardedConsultant->company_id) }}
        {{ Form::hidden('open_rfp_id', $openRfp->id) }}
        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
    </footer>
    {{ Form::close() }}
    @else
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <dl class="dl-horizontal no-margin">
                <dt>Scope of Services:</dt>
                <dd>
                    <div class="well">
                        @if($sectionDDetails && mb_strlen($sectionDDetails->scope_of_services) > 0)
                        {{ nl2br($sectionDDetails->scope_of_services) }}
                        @endif
                    </div>
                </dd>
                <dt>&nbsp;</dt>
                <dd>&nbsp;</dd>
            </dl>
        </section>
    </div>
    @endif
</div>
<hr class="simple">
<div class="well">
    <div class="row">
        <div class="col col-lg-12">
            <h5>Consultant Service Fee</h5>
        </div>
    </div>
    @foreach($consultantManagementContract->consultantManagementSubsidiaries as $consultantManagementSubsidiary)

    <?php
    $sectionDServiceFee = $approvalDocument->sectionD->consultantServiceFees()->where('consultant_management_subsidiary_id', '=', $consultantManagementSubsidiary->id)->where('company_id', '=', $awardedConsultant->company_id)->first();
    ?>

    @if($approvalDocument->status == PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT)
    {{ Form::open(['route' => ['consultant.management.approval.document.section.d.service.fee.store', $vendorCategoryRfp->id], 'class' => 'smart-form']) }}
    @endif
    <div class="well">
        <div class="row">
            <div class="row">
                <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{{trans('general.phase')}}}:</dt>
                        <dd>{{{ $consultantManagementSubsidiary->subsidiary->full_name }}}</dd>
                        <dt>&nbsp;</dt>
                        <dd>&nbsp;</dd>
                    </dl>
                </div>
            </div>
            <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <dl class="dl-horizontal no-margin">
                    <dt>{{trans('general.projectBudget')}}:</dt>
                    <dd>{{{$currencyCode}}} {{{ number_format($consultantManagementSubsidiary->project_budget, 2, '.', ',')}}}</dd>
                    <dt>&nbsp;</dt>
                    <dd>&nbsp;</dd>
                </dl>
            </div>
        </div>
        @if($approvalDocument->status == PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT)
        <div class="row">
            <section class="col col-xs-12 col-md-6 col-lg-6">
                <label class="label">Board Scale of Fee :</label>
                <label class="input {{{ $errors->has($consultantManagementSubsidiary->id.'-'.$awardedConsultant->company_id.'-board_scale_of_fee') ? 'state-error' : null }}}">
                    {{ Form::text($consultantManagementSubsidiary->id.'-'.$awardedConsultant->company_id.'-board_scale_of_fee', Input::old($consultantManagementSubsidiary->id.'-'.$awardedConsultant->company_id.'-board_scale_of_fee', ($sectionDServiceFee) ? $sectionDServiceFee->board_scale_of_fee : null), ['autofocus' => 'autofocus']) }}
                </label>
                {{ $errors->first($consultantManagementSubsidiary->id.'-'.$awardedConsultant->company_id.'-board_scale_of_fee', '<em class="invalid">:message</em>') }}
            </section>
        </div>
        @else
        <div class="row">
            <section class="col col-xs-12 col-md-12 col-lg-12">
                <dl class="dl-horizontal no-margin">
                    <dt>Board Scale of Fee:</dt>
                    <dd>
                        <div class="well">
                            @if($sectionDServiceFee) {{ nl2br($sectionDServiceFee->board_scale_of_fee) }} @endif
                        </div>
                    </dd>
                    <dt>&nbsp;</dt>
                    <dd>&nbsp;</dd>
                </dl>
            </section>
        </div>
        @endif

        <div class="row">
            <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <table class="table table-bordered" style="text-align: center;">
                    <thead>
                        <tr>
                            <th style="text-align: left;">{{ trans('general.name') }}</th>
                            <th style="text-align: right;width:160px;">{{{ trans('tenders.amount') }}} ({{{ $currencyCode }}})</th>
                            <th style="text-align: center;width:120px;">{{{ trans('general.proposedFee') }}} %</th>
                            <th style="text-align: center;width:160px;">{{ trans('tenders.submittedDate') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($proposedFeeList as $consultantProposedFee)
                        @if($consultantProposedFee['company_id'] == $awardedConsultant->company_id && $consultantProposedFee['subsidiary_id'] == $consultantManagementSubsidiary->id)
                        <tr>
                            <td style="text-align:left;">{{ $awardedConsultant->company->name }}</td>
                            <td style="text-align:right;">{{ number_format($consultantProposedFee['proposed_fee_amount'], 2, '.', ',') }}</td>
                            <td>{{ number_format($consultantProposedFee['proposed_fee_percentage'], 2, '.', ',') }}</td>
                            <td>@if($consultantProposedFee['submitted_at']) {{ $consultantManagementContract->getAppTimeZoneTime(Carbon\Carbon::parse($consultantProposedFee['submitted_at'])->format(\Config::get('dates.created_and_updated_at_formatting'))) }} @endif</td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if($approvalDocument->status == PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT)
        <hr class="simple">
        <div class="row">
            <section class="col col-xs-12 col-md-12 col-lg-12">
                <label class="label">{{ trans('costData.notes') }} :</label>
                <label class="textarea {{{ $errors->has($consultantManagementSubsidiary->id.'-'.$awardedConsultant->company_id.'-notes') ? 'state-error' : null }}}">
                    {{ Form::textarea($consultantManagementSubsidiary->id.'-'.$awardedConsultant->company_id.'-notes', Input::old($consultantManagementSubsidiary->id.'-'.$awardedConsultant->company_id.'-notes', ($sectionDServiceFee) ? $sectionDServiceFee->notes : null), ['rows' => '1', 'autofocus' => 'autofocus']) }}
                </label>
                {{ $errors->first($consultantManagementSubsidiary->id.'-'.$awardedConsultant->company_id.'-notes', '<em class="invalid">:message</em>') }}
            </section>
        </div>
        @elseif($approvalDocument->status != PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT && $sectionDServiceFee && mb_strlen($sectionDServiceFee->notes) > 0)
        <div class="row">
            <section class="col col-xs-12 col-md-12 col-lg-12">
                <dl class="dl-horizontal no-margin">
                    <dt>{{ trans('costData.notes') }}:</dt>
                    <dd>
                        <div class="well">
                            {{ nl2br($sectionDServiceFee->notes) }}
                        </div>
                    </dd>
                    <dt>&nbsp;</dt>
                    <dd>&nbsp;</dd>
                </dl>
            </section>
        </div>
        @endif

        @if($approvalDocument->status == PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT)
        <footer>
            {{ Form::hidden('cid', $awardedConsultant->company_id) }}
            {{ Form::hidden('sid', $consultantManagementSubsidiary->id) }}
            {{ Form::hidden('open_rfp_id', $openRfp->id) }}
            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
        </footer>
        {{ Form::close() }}
        @endif
    </div>
    <div class="pb-4"></div>
    @endforeach
</div>
