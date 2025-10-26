<div class="modal fade" id="my-processes-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title txt-color-white">
                    <i class="fa fa-info-circle"></i>
                    {{{ trans('general.myProcesses') }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <div>
                    <ul class="nav nav-pills">
                        <li class="nav-item active">
                            <a class="nav-link" href="#tendering-processes" data-toggle="tab">
                                <i class="fa fa-book"></i> {{ trans('toDoLists.tendering') }}
                                <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#post-contract-processes" data-toggle="tab">
                                <i class="fa fa-folder"></i> {{ trans('toDoLists.postContract') }}
                                <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#site-module-processes" data-toggle="tab">
                                <i class="fa fa-map"></i> {{ trans('toDoLists.siteModule') }}
                                <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#vendor-management-processes" data-toggle="tab">
                                <i class="fa fa-users"></i> {{ trans('toDoLists.vendorManagement') }}
                                <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#consultant-management-processes" data-toggle="tab">
                                <i class="fa fa-fw fa-th-list"></i> {{ trans('toDoLists.consultantManagement') }}
                                <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="tab-content" style="padding-top:1rem!important;">
                    <div class="tab-pane fade in active" id="tendering-processes">
                        <div class="well">
                            <div class="row">
                                <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2" style="height:450px; overflow-x: scroll;">
                                    <ul class="nav flex-column nav-pills" style="padding-bottom:4px;">
                                        <li class="nav-item active">
                                            <a class="nav-link" href="#recommendation-of-tenderer-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.recommendationOfTenderer') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#list-of-tenderer-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.listOfTenderer') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#calling-tender-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.callingTender') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#open-tender-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.openTender') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#technical-evaluation-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.technicalOpening') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#technical-assessment-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.technicalAssessment') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#award-recommendation-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.awardRecommendation') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#letter-of-award-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.letterOfAward') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#tender-resubmission-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.tenderResubmission') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#request-for-information-message-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.requestForInformation') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#risk-register-message-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.riskRegister') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10">
                                    <div class="tab-content">
                                        <div class="tab-pane fade in active" id="recommendation-of-tenderer-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="recommendation-of-tenderer-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="list-of-tenderer-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="list-of-tenderer-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="calling-tender-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="calling-tender-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="open-tender-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="open-tender-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="technical-evaluation-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="technical-evaluation-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="technical-assessment-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="technical-assessment-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="award-recommendation-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="award-recommendation-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="letter-of-award-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="letter-of-award-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="tender-resubmission-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="tender-resubmission-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="request-for-information-message-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="request-for-information-message-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="risk-register-message-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="risk-register-message-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="post-contract-processes">
                        <div class="well">
                            <div class="row">
                                <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2" style="height:450px; overflow-x: scroll;">
                                    <ul class="nav flex-column nav-pills" style="padding-bottom:4px;">
                                        <li class="nav-item active">
                                            <a class="nav-link" href="#publish-to-post-contract-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.publishToPostContract') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#water-deposit-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.waterDeposit') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#deposit-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.deposit') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#out-of-contract-item-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.outOfContractItems') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#purchase-on-behalf-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.purchaseOnBehalf') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#advanced-payment-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.advancedPayment') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#work-on-behalf-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.workOnBehalf') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#work-on-behalf-back-charge-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.workOnBehalfBackCharge') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#penalty-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.penalty') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#permit-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.permit') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#variation-order-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.variationOrder') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#material-on-site-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.materialOnSite') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#claim-certificate-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.claimCertificate') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#request-for-variation-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.requestForVariation') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#account-code-setting-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.accountCodeSettings') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#site-management-defect-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.siteManagementDefects') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10">
                                    <div class="tab-content">
                                        <div class="tab-pane fade in active" id="publish-to-post-contract-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="publish-to-post-contract-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="water-deposit-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="water-deposit-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="deposit-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="deposit-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="out-of-contract-item-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="out-of-contract-item-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="purchase-on-behalf-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="purchase-on-behalf-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="advanced-payment-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="advanced-payment-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="work-on-behalf-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="work-on-behalf-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="work-on-behalf-back-charge-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="work-on-behalf-back-charge-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="penalty-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="penalty-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="permit-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="permit-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="variation-order-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="variation-order-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="material-on-site-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="material-on-site-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="claim-certificate-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="claim-certificate-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="request-for-variation-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="request-for-variation-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="account-code-setting-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="account-code-setting-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="site-management-defect-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="site-management-defect-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="site-module-processes">
                        <div class="well">
                            <div class="row">
                                <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2" style="height:450px; overflow-x: scroll;">
                                    <ul class="nav flex-column nav-pills" style="padding-bottom:4px;">
                                        <li class="nav-item active">
                                            <a class="nav-link" href="#request-for-inspection-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.requestForInspection') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#site-diary-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.siteDiary') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#instruction-to-contractor-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.instructionToContractor') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#daily-report-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.dailyReport') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10">
                                    <div class="tab-content">
                                        <div class="tab-pane fade in active" id="request-for-inspection-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="request-for-inspection-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="site-diary-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="site-diary-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="instruction-to-contractor-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="instruction-to-contractor-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="daily-report-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="daily-report-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="vendor-management-processes">
                        <div class="well">
                            <div class="row">
                                <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2" style="height:450px; overflow-x: scroll;">
                                    <ul class="nav flex-column nav-pills" style="padding-bottom:4px;">
                                        <li class="nav-item active">
                                            <a class="nav-link" href="#vendor-registration-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.vendorRegistration') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#vendor-evaluation-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.vendorEvaluation') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10">
                                    <div class="tab-content">
                                        <div class="tab-pane fade in active" id="vendor-registration-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="vendor-registration-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="vendor-evaluation-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="vendor-evaluation-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="consultant-management-processes">
                        <div class="well">
                            <div class="row">
                                <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2" style="height:450px; overflow-x: scroll;">
                                    <ul class="nav flex-column nav-pills" style="padding-bottom:4px;">
                                        <li class="nav-item active">
                                            <a class="nav-link" href="#recommendation-of-consultant-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.recommendationOfConsultant') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#list-of-consultant-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.listOfConsultant') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#calling-rfp-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.callingRfp') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#open-rfp-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.openRfp') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#rfp-resubmission-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.rfpResubmission') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#approval-documents-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.approvalDocument') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#consultant-management-letter-of-award-processes-tab" data-toggle="tab">
                                                <table class="width-100">
                                                    <td>
                                                        {{{ trans('toDoLists.letterOfAward') }}}
                                                    </td>
                                                    <td class="ps-2 text-right">
                                                        <span class="badge bg-color-red inbox-badge txt-color-white" data-category="count"></span>
                                                    </td>
                                                </table>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10">
                                    <div class="tab-content">
                                        <div class="tab-pane fade in active" id="recommendation-of-consultant-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="recommendation-of-consultant-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="list-of-consultant-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="list-of-consultant-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="calling-rfp-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="calling-rfp-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="open-rfp-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="open-rfp-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="rfp-resubmission-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="rfp-resubmission-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="approval-documents-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="approval-documents-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="consultant-management-letter-of-award-processes-tab">
                                            <div class="row">
                                                <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                    <div id="consultant-management-letter-of-award-processes-table"></div>
                                                </section>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default btn-md" >{{{ trans('general.back') }}}</button>
            </div>
        </div>
    </div>
</div>
<div id="my-processes-modal-templates" hidden>
    <div data-id="project-process-info">
        <div class="well">
            <div class="row">
                <div class="col col-lg-12">
                    <dl class="dl-horizontal no-margin">
                        <dt><strong>{{ trans('toDoLists.process') }}:</strong></dt>
                        <dd><strong><span data-id="process"></span></strong></dd>
                    </dl>
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('projects.reference') }}:</dt>
                        <dd data-id="project-reference"></dd>
                    </dl>
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('projects.project') }}:</dt>
                        <dd data-id="project-title"></dd>
                    </dl>
                </div>
            </div>
            <div class="row">
                <div class="col col-lg-6">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('forms.submittedAt') }}:</dt>
                        <dd data-id="submitted-at"></dd>
                    </dl>
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('verifiers.daysFromSubmission') }}:</dt>
                        <dd data-id="days-since-start"></dd>
                    </dl>
                </div>
                <div class="col col-lg-6">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('forms.submittedBy') }}:</dt>
                        <dd data-id="submitted-by"></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    <div data-id="vendor-registration-process-info">
        <div class="well">
            <div class="row">
                <div class="col col-lg-12">
                    <dl class="dl-horizontal no-margin">
                        <dt><strong>{{ trans('toDoLists.process') }}:</strong></dt>
                        <dd><strong><span data-id="process"></span></strong></dd>
                    </dl>
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('companies.company') }}:</dt>
                        <dd data-id="company-name"></dd>
                    </dl>
                </div>
            </div>
            <div class="row">
                <div class="col col-lg-6">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('forms.submittedAt') }}:</dt>
                        <dd data-id="submitted-at"></dd>
                    </dl>
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('verifiers.daysFromSubmission') }}:</dt>
                        <dd data-id="days-since-start"></dd>
                    </dl>
                </div>
                <div class="col col-lg-6">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('forms.submittedBy') }}:</dt>
                        <dd data-id="submitted-by"></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    <div data-id="vendor-evaluation-process-info">
        <div class="well">
            <div class="row">
                <div class="col col-lg-12">
                    <dl class="dl-horizontal no-margin">
                        <dt><strong>{{ trans('toDoLists.process') }}:</strong></dt>
                        <dd><strong><span data-id="process"></span></strong></dd>
                    </dl>
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('companies.company') }}:</dt>
                        <dd data-id="company-name"></dd>
                    </dl>
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('projects.reference') }}:</dt>
                        <dd data-id="project-reference"></dd>
                    </dl>
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('projects.project') }}:</dt>
                        <dd data-id="project-title"></dd>
                    </dl>
                </div>
            </div>
            <div class="row">
                <div class="col col-lg-6">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('forms.submittedAt') }}:</dt>
                        <dd data-id="submitted-at"></dd>
                    </dl>
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('verifiers.daysFromSubmission') }}:</dt>
                        <dd data-id="days-since-start"></dd>
                    </dl>
                </div>
                <div class="col col-lg-6">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('forms.submittedBy') }}:</dt>
                        <dd data-id="submitted-by"></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    <div data-id="consultant-management-process-info">
        <div class="well">
            <div class="row">
                <div class="col col-lg-12">
                    <dl class="dl-horizontal no-margin">
                        <dt><strong>{{ trans('toDoLists.process') }}:</strong></dt>
                        <dd><strong><span data-id="process"></span></strong></dd>
                    </dl>
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('companies.referenceNo') }}:</dt>
                        <dd data-id="reference-no"></dd>
                    </dl>
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('projects.contract') }}:</dt>
                        <dd data-id="title"></dd>
                    </dl>
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('vendorManagement.vendorCategory') }}:</dt>
                        <dd data-id="vendor-category"></dd>
                    </dl>
                </div>
            </div>
            <div class="row">
                <div class="col col-lg-6">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('forms.submittedAt') }}:</dt>
                        <dd data-id="submitted-at"></dd>
                    </dl>
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('verifiers.daysFromSubmission') }}:</dt>
                        <dd data-id="days-since-start"></dd>
                    </dl>
                </div>
                <div class="col col-lg-6">
                    <dl class="dl-horizontal no-margin">
                        <dt>{{ trans('forms.submittedBy') }}:</dt>
                        <dd data-id="submitted-by"></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

@include('templates.generic_table_modal', [
    'modalId'          => 'my-processes-verifiers-modal',
    'title'            => trans('verifiers.verifiers'),
    'tableId'          => 'my-processes-verifiers-table',
    'modalHeaderClass' => 'bg-primary',
    'modalTitleClass'  => 'txt-color-white',
    'titleIcon'        => 'fa fa-users',
    'showCancel'       => true,
    'cancelText'       => trans('forms.back'),
    'showInfo'         => true,
    'infoText'         => trans('general.view'),
])