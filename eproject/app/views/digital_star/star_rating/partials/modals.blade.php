@if(\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR))
    @include('templates.generic_table_modal', [
        'modalId'          => 'ds-company-score-modal',
        'title'            => '',
        'tableId'          => 'ds-company-score-table',
        'modalDialogClass' => 'modal-xl',
    ])
    @include('templates.generic_table_modal', [
        'modalId'          => 'ds-project-score-modal',
        'title'            => '',
        'tableId'          => 'ds-project-score-table',
        'modalDialogClass' => 'modal-xl',
    ])
    @include('templates.generic_table_modal', [
        'modalId'          => 'ds-form-evaluation-log-modal',
        'title'            => trans('digitalStar/digitalStar.evaluationLog'),
        'tableId'          => 'ds-form-evaluation-log-table',
    ])
    @include('templates.generic_table_modal', [
        'modalId'          => 'ds-form-verifier-log-modal',
        'title'            => trans('verifiers.verifierLog'),
        'tableId'          => 'ds-form-verifier-log-table',
    ])
    @include('uploads.downloadModal')

    <div class="modal fade" id="ds-form-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        {{{ trans('digitalStar/digitalStar.form') }}}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        &times;
                    </button>
                </div>
                <div class="modal-body">
                    <div class="well">
                        <div class="row">
                            <div class="col col-lg-12 project-info">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('projects.reference') }}:</dt>
                                    <dd data-name="project-reference"></dd>
                                </dl>
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('projects.project') }}:</dt>
                                    <dd data-name="project"></dd>
                                </dl>
                            </div>
                            <div class="col col-lg-6">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('companies.companyName') }}:</dt>
                                    <dd data-name="company"></dd>
                                    <dt>{{ trans('digitalStar/digitalStar.vendorGroup') }}:</dt>
                                    <dd data-name="vendor_group"></dd>
                                    <dt>{{ trans('digitalStar/digitalStar.evaluator') }}:</dt>
                                    <dd data-name="evaluator"></dd>
                                    <dt>{{ trans('digitalStar/digitalStar.rating') }}:</dt>
                                    <dd data-name="rating"></dd>
                                </dl>
                            </div>
                            <div class="col col-lg-6">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('digitalStar/digitalStar.form') }}:</dt>
                                    <dd data-name="form_name"></dd>
                                    <dt>{{ trans('digitalStar/digitalStar.status') }}:</dt>
                                    <dd data-name="status"></dd>
                                    <dt>&nbsp;</dt>
                                    <dd></dd>
                                    <dt>{{ trans('digitalStar/digitalStar.score') }}:</dt>
                                    <dd data-name="score"></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div id="ds-form-table"></div>
                    <div class="row">
                        <div class="col col-lg-12">
                            <dl>
                                <dt>{{ trans('general.remarks') }}:</dt>
                                <dd data-name="remarks"></dd>
                            </dl>
                            <dl>
                                <dt>{{ trans('general.logs') }}:</dt>
                                <dd>
                                    <button class="btn btn-xs btn-primary" data-action='show-evaluation-log'>{{ trans('digitalStar/digitalStar.evaluationLog') }}</button>
                                    <button class="btn btn-xs btn-primary" data-action='show-verifier-log'>{{ trans('verifiers.verifierLog') }}</button>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif