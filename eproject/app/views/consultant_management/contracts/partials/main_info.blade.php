<div class="row">
    <div class="col col-lg-12">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('projects.title') }}:</dt>
            <dd><div class="well">{{ nl2br($consultantManagementContract->title) }}</div></dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
<div class="row">
    <div class="col col-lg-3">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.referenceNo') }}:</dt>
            <dd>{{{ $consultantManagementContract->reference_no }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col col-lg-9">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('general.subsidiaryTownship') }}:</dt>
            <dd>{{{ $consultantManagementContract->subsidiary->name }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
<div class="row">
    <div class="col col-lg-12">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('general.description') }}:</dt>
            <dd>{{ nl2br($consultantManagementContract->description) }}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
<div class="row">
    <div class="col col-lg-12">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.address') }}:</dt>
            <dd>{{ nl2br($consultantManagementContract->address) }}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
<div class="row">
    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.state') }}:</dt>
            <dd>{{{ $consultantManagementContract->state->name }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('companies.country') }}:</dt>
            <dd>{{{ $consultantManagementContract->country->country }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3"></div>
    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3"></div>
</div>
<hr class="simple">
<div class="row">
    <div class="col-xs-6 col-sm-6 col-md-4 col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('general.createdBy') }}:</dt>
            <dd>{{{ $consultantManagementContract->createdBy->name }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col-xs-6 col-sm-6 col-md-4 col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('general.updatedBy') }}:</dt>
            <dd>{{{ $consultantManagementContract->updatedBy->name }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
    <div class="col-xs-6 col-sm-6 col-md-4 col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('general.createdAt') }}:</dt>
            <dd>{{{ date('d/m/Y', strtotime($consultantManagementContract->created_at)) }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>