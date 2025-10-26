<div class="row">
    <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h4>Section C - Summary of Recommendation</h4>
    </div>
</div>
<hr class="simple">
@foreach($consultantManagementContract->consultantManagementSubsidiaries as $consultantManagementSubsidiary)
<div class="well">
    <div class="row">
        <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <dl class="dl-horizontal no-margin">
                <dt>{{{trans('general.phase')}}}:</dt>
                <dd>{{{ $consultantManagementSubsidiary->subsidiary->name }}}</dd>
                <dt>&nbsp;</dt>
                <dd>&nbsp;</dd>
            </dl>
        </div>
        <div class="col col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <dl class="dl-horizontal no-margin">
                <dt>{{{trans('general.consultantCategories')}}}:</dt>
                <dd>{{{ $vendorCategoryRfp->vendorCategory->name }}}</dd>
                <dt>&nbsp;</dt>
                <dd>&nbsp;</dd>
            </dl>
        </div>
        <div class="col col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <dl class="dl-horizontal no-margin">
                <dt>{{{trans('general.developmentType')}}}:</dt>
                <dd>{{{ $consultantManagementSubsidiary->developmentType->title }}}</dd>
                <dt>&nbsp;</dt>
                <dd>&nbsp;</dd>
            </dl>
        </div>
    </div>
    <div class="row">
        <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div id="consultant-{{$consultantManagementSubsidiary->id}}-table"></div>
        </div>
    </div>
</div>
<hr class="simple">
@endforeach
<div class="row mt-10">
    <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h4>{{ trans('accountCodes.accountCode') }}</h4>
    </div>
</div>
<hr class="simple">
<div class="well mb-5" id="item-code-settings-amount-information">
    <div class="row">
        <div class="col col-sm-3">
            <dl class="dl-horizontal no-margin">
                <dt>{{{ trans('accountCodes.totalAmount') }}}:</dt>
                <dd><strong>{{ number_format($proposedFeeAmount, 2, '.', ',') }}</strong></dd>
            </dl>
        </div>
        <div class="col col-sm-3">
            <dl class="dl-horizontal no-margin">
                <dt>{{{ trans('accountCodes.assignedAmount') }}}:</dt>
                <dd class="@{{ labelClass }}"><strong>@{{ assignedAmount }}</strong></dd>
            </dl>
        </div>
        <div class="col col-sm-3">
            <dl class="dl-horizontal no-margin">
                <dt>{{{ trans('accountCodes.balance') }}}:</dt>
                <dd class="@{{ labelClass }}"><strong>@{{ balance }}</strong></dd>
            </dl>
        </div>
        <div class="col col-sm-3">
            <div class="text-right @{{ labelClass }}">
                <strong>
                    @{{ saveStatusLabel }}
                </strong>
            </div>
        </div>
    </div>
</div>
<div id="account-codes-proportion-table"></div>