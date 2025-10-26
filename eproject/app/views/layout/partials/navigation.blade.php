<aside id="left-panel">
    <div class="login-info">
        <div class="info-card">
            <i class="far fa-3x fa-user-circle" style="font-weight:900;"></i>
            <div class="info-card-text">
                <div class="d-flex align-items-center text-white">
                    <span class="text-truncate text-truncate-sm d-inline-block">
                        {{ $user->name }}
                    </span>
                </div>
                <span class="d-inline-block text-truncate text-truncate-sm">
                    <?php
                    $company = ($project) ? $user->getAssignedCompany($project) : $user->company;
                    ?>
                    @if($company)
                        {{ $company->name }}
                    @endif
                </span>
            </div>
        </div>
    </div>

    <nav id="navigation-menu" class="nav-menu">
        <ul>
            <li class="{{ Request::is('home') ? 'active' : null }}">
                <a href="{{ route('home.index') }}" title="{{ trans('navigation/mainnav.home') }}" class="text-truncate">
                    <i class="fa fa-lg fa-fw fa-home"></i>
                    <span class="menu-item-parent">{{ trans("navigation/mainnav.home") }}</span>
                </a>
            </li>
            @if($user->isTemporaryAccount())
                <li>
                    <a href="javascript:void(0);" class="text-truncate" title='{{ trans("vendorManagement.registration") }}'>
                        <i class="fa fa-sm fa-fw fa-clone"></i>
                        {{ trans("vendorManagement.registration") }}
                    </a>
                    <ul>
                        <li class="{{ Request::is('vendor-registration/overview*') ? 'active' : null }}">
                            <a href="{{ route('vendors.vendorRegistration.index') }}" title='{{ trans("vendorManagement.overview") }}' class="text-truncate">
                                <i class="fa fa-sm fa-user-secret"></i>
                                {{ trans("vendorManagement.overview") }}
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            @if($user->isPermanentAccount())

                @if($currentUser->hasConsultantManagementCompanyRoles() && ($consultantManagementContract || $consultantManagementVendorCategoryRfp))
                    @include('layout.partials.consultant_management_navigation', ['user'=>$currentUser])
                @else
                    <?php $dashboardGroupEBidding = $user->dashboardGroup(\PCK\Dashboard\DashboardGroup::TYPE_E_BIDDING); ?>
                    @if(($user->dashboardGroup() || $dashboardGroupEBidding) && !$project)
                    <li>
                        <a href="javascript:void(0);" title="{{ trans('navigation/mainnav.dashboard') }}" class="text-truncate">
                            <i class="fa fa-lg fa-fw fa-chart-line"></i>
                            <span class="menu-item-parent">{{ trans("navigation/mainnav.dashboard") }}</span>
                        </a>
                        <ul>
                            @if($user->dashboardGroup())
                                <li class="{{ Request::is('dashboard/overview') ? 'active' : null }}">
                                    <a href="{{ route('dashboard.overview') }}" title="{{ trans('navigation/mainnav.overview') }}" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-chart-line"></i>
                                        {{ trans("navigation/mainnav.overview") }}
                                    </a>
                                </li>
                                @if($user->dashboardGroup()->type == \PCK\Dashboard\DashboardGroup::TYPE_DEVELOPER)
                                    <li class="{{ Request::is('dashboard/subsidiaries') ? 'active' : null }}">
                                        <a href="{{ route('dashboard.subsidiaries') }}" title="{{ trans('navigation/mainnav.subsidiaries') }}" class="text-truncate">
                                            <i class="fa fa-sm fa-fw fa-cubes"></i>
                                            {{ trans("navigation/mainnav.subsidiaries") }}
                                        </a>
                                    </li>
                                    <li class="{{ Request::is('dashboard/status-summary') ? 'active' : null }}">
                                        <a href="{{ route('dashboard.status.summary') }}" title="{{ trans('navigation/mainnav.statusSummary') }}" class="text-truncate">
                                            <i class="fa fa-sm fa-fw fa-file-invoice"></i>
                                            {{ trans("navigation/mainnav.statusSummary") }}
                                        </a>
                                    </li>
                                @endif
                            @endif
                            @if($dashboardGroupEBidding)
                                <li class="{{ Request::is('dashboard/ebidding') ? 'active' : null }}">
                                    <a href="{{ route('dashboard.ebidding') }}" title="{{ trans('navigation/mainnav.dashboardEBidding') }}" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-chart-line"></i>
                                        {{ trans("navigation/mainnav.dashboardEBidding") }}
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    @if(!$project)
                        @if($user->company && $user->company->contractGroupCategory->type == \PCK\ContractGroupCategory\ContractGroupCategory::TYPE_EXTERNAL)
                            <li class="{{ Request::is('projects', 'contractor-questionnaires/*') ? 'active' : null }}">
                                <a href="javascript:void(0);" title="{{ trans('navigation/mainnav.projects') }}" class="text-truncate">
                                    <i class="fa fa-lg fa-fw fa-project-diagram"></i>
                                    <span class="menu-item-parent">{{ trans("navigation/mainnav.projects") }}</span>
                                </a>
                                <ul>
                                    <li class="{{ Request::is('projects') ? 'active' : null }}">
                                        <a href="{{ route('projects.index') }}" title="{{ trans('navigation/mainnav.projects') }}" class="text-truncate">
                                            <i class="fa fa-sm fa-fw fa-table"></i>
                                            <span class="menu-item-parent">{{ trans("navigation/mainnav.projects") }}</span>
                                        </a>
                                    </li>
                                    <li class="{{ Request::is('contractor-questionnaires', 'contractor-questionnaires/*') ? 'active' : null }}">
                                        <a href="{{ route('contractor.questionnaires.index') }}" title="{{ trans('general.questionnaires') }}" class="text-truncate">
                                            <i class="fa fa-sm fa-fw fa-tasks"></i>
                                            <span class="menu-item-parent">{{ trans("general.questionnaires") }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @else
                            <li class="{{ Request::is('projects', 'projects/create') ? 'active' : null }}">
                                <a href="{{ route('projects.index') }}" title="{{ trans('navigation/mainnav.projects') }}" class="text-truncate">
                                    <i class="fa fa-lg fa-fw fa-table"></i>
                                    <span class="menu-item-parent">{{ trans("navigation/mainnav.projects") }}</span>
                                </a>
                            </li>
                            @if(\PCK\ModulePermission\ModulePermission::hasAnyPermission($currentUser, [\PCK\ModulePermission\ModulePermission::MODULE_ID_PROJECT_REPORT_DASHBOARD, \PCK\ModulePermission\ModulePermission::MODULE_ID_PROJECT_REPORT_CHARTS]))
                                <li>
                                    <a href="javascript:void(0);" title="{{ trans('projectReport.projectReport') }}" class="text-truncate">
                                        <i class="fa fa-lg fa-fw fa-file-lines"></i>
                                        <span class="menu-item-parent">{{ trans("navigation/mainnav.projectReport") }}</span>
                                    </a>
                                    <ul>
                                        @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_PROJECT_REPORT_DASHBOARD))
                                            <li class="{{ Request::is('projectReport/dashboard*') ? 'active' : null }}">
                                                <a href="{{ route('projectReport.dashboard.index') }}" title="{{ trans('projectReport.dashboard') }}" class="text-truncate">
                                                    <i class="fa fa-sm fa-fw fa-table"></i>
                                                    {{ trans('navigation/mainnav.dashboard') }}
                                                </a>
                                            </li>
                                        @endif
                                        @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_PROJECT_REPORT_CHARTS))
                                            <li class="{{ Request::is('projectReport/charts*') ? 'active' : null }}">
                                                <a href="{{ route('projectReport.charts.showAll') }}" title="{{ trans('navigation/mainnav.charts') }}" class="text-truncate">
                                                    <i class="fa fa-sm fa-fw fa-chart-line"></i>
                                                    {{ trans('navigation/mainnav.charts') }}
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif
                        @endif

                        @if($currentUser->company && $currentUser->company->isContractor() && $user->isGroupAdmin())
                            <li class="{{ Request::is('e-bidding/sessions*') || Request::is('e-bidding/console*') ? 'active' : null }}">
                                <a href="{{ route('e-bidding.sessions.index') }}" title="{{ trans('navigation/mainnav.eBidding') }}" class="text-truncate">
                                    <i class="fa fa-lg fa-fw fa-gavel"></i>
                                    <span class="menu-item-parent">{{ trans('navigation/mainnav.eBidding') }}</span>
                                </a>
                            </li>
                        @endif

                        @if($currentUser->hasConsultantManagementCompanyRoles() && !$consultantManagementContract && (($currentUser->isConsultantManagementParticipantConsultant() && $currentUser->isConsultantManagementConsultantUser()) || !$currentUser->isConsultantManagementParticipantConsultant()))
                        <li class="{{ Request::is('consultant-management/*') ? 'active' : null }}">
                            <a href="javascript:void(0);" class="text-truncate" title='{{{ trans("general.consultantManagement") }}}'>
                                <i class="fa fa-lg fa-fw fa-th-list"></i>
                                {{{ trans('general.consultantManagement') }}}
                            </a>
                            @if($currentUser->isConsultantManagementParticipantConsultant() && $currentUser->isConsultantManagementConsultantUser())
                            <ul>
                                @if($currentUser->is_admin)
                                <li class="{{ (Request::is('consultant-management/consultant-user-management*'))? 'active' : null }}">
                                    <a href="{{ route('consultant.management.consultant.user.management.index') }}" title="{{{ trans('contractManagement.userManagement') }}}" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-users"></i> {{{ trans('contractManagement.userManagement') }}}
                                    </a>
                                </li>
                                @endif
                                <li class="{{ (Request::is('consultant-management/consultant-calling-rfp*'))? 'active' : null }}">
                                    <a href="{{ route('consultant.management.consultant.calling.rfp.index') }}" title='{{{ trans("general.callingRFP") }}}' class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-table"></i> {{{ trans('general.callingRFP') }}}
                                    </a>
                                </li>
                                <li class="{{ (Request::is('consultant-management/consultant-rfp-questionnaire*'))? 'active' : null }}">
                                    <a href="{{ route('consultant.management.consultant.rfp.questionnaire.index') }}" title="{{{ trans('general.questionnaires') }}}" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-tasks"></i> {{{ trans('general.questionnaires') }}}
                                    </a>
                                </li>
                                <li class="{{ (Request::is('consultant-management/consultant-awarded-rfp*'))? 'active' : null }}">
                                    <a href="{{ route('consultant.management.consultant.awarded.rfp.index') }}" title='Awarded RFP' class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-trophy"></i> Awarded RFP
                                    </a>
                                </li>
                            </ul>
                            @elseif(!$currentUser->isConsultantManagementParticipantConsultant())
                            <ul>
                                <li class="{{ (Request::is('consultant-management/contracts*') or Request::is('consultant-management/phase*'))? 'active' : null }}">
                                    <a href="{{ route('consultant.management.contracts.index') }}" title='{{{ trans("navigation/mainnav.developmentPlanMasterlist") }}}' class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-table"></i> {{{ trans('navigation/mainnav.developmentPlanMasterlist') }}}
                                    </a>
                                </li>
                                <li class="{{ (Request::is('consultant-management/reports*'))? 'active' : null }}">
                                    <a href="{{ route('consultant.management.reports.index') }}" title='{{{ trans("navigation/mainnav.reports") }}}' class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-book"></i> {{{ trans('navigation/mainnav.reports') }}}
                                    </a>
                                </li>
                                @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_CONSULTANT_PAYMENT))
                                <li class="{{ (Request::is('consultant-management/consultant-payment*'))? 'active' : null }}">
                                    <a href="{{ route('consultant.management.consultant.payments.index') }}" title="{{ trans('consultantManagement.consultantPayments') }}" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-money-check-alt"></i> {{ trans('consultantManagement.consultantPayments') }}
                                    </a>
                                </li>
                                @endif
                                <li class="{{ (Request::is('consultant-management/loa-templates*'))? 'active' : null }}">
                                    <a href="{{ route('consultant.management.loa.templates.index') }}" title="{{ trans('consultantManagement.loaTemplates') }}" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-file-code"></i> {{ trans('consultantManagement.loaTemplates') }}
                                    </a>
                                </li>
                                <li class="{{ (Request::is('consultant-management/loa-running-number*'))? 'active' : null }}">
                                    <a href="{{ route('consultant.management.loa.running.number.index') }}" title="{{ trans('consultantManagement.loaRunningNumbers') }}" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-sort-numeric-down"></i> {{ trans('consultantManagement.loaRunningNumbers') }}
                                    </a>
                                </li>
                            </ul>
                            @endif
                        </li>
                        @endif
                    <li>
                        <a href="javascript:void(0);" title="{{ trans('navigation/mainnav.systemModules') }}" class="text-truncate">
                            <i class="fa fa-lg fa-fw fa-desktop"></i>
                            <span class="menu-item-parent">{{ trans("navigation/mainnav.systemModules") }}</span>
                        </a>
                        <ul>
                            <li>
                                <a href="javascript:void(0);" class="text-truncate">
                                    <i class="fa fa-sm fa-fw fa-book"></i>
                                    {{ trans("navigation/mainnav.reports") }}
                                </a>
                                <ul>
                                    @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_CONTRACTOR_LISTING))
                                        <li class="{{ Request::is('contractors*') ? 'active' : null }}">
                                            <a href="{{ route('contractors') }}" class="text-truncate">
                                                <i class="fa fa-sm fa-fw fa-list"></i>
                                                {{ trans("navigation/mainnav.contractors") }}
                                            </a>
                                        </li>
                                    @endif
                                    @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_PROJECTS_OVERVIEW))
                                        <li class="{{ Request::is('projects-overview') ? 'active' : null }}">
                                            <a href="{{ route('projectsOverview') }}" class="text-truncate">
                                                <i class="fas fa-sm fa-fw fa-chart-pie"></i>
                                                {{ trans("navigation/mainnav.projectsOverview") }}
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                            @if(!\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT) && $user->canVerifyCompanies())
                                <li class="{{ Request::is('companies/verification*') ? 'active' : null }}">
                                    <a href="{{ route('companies.verification.index') }}" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-university"></i>
                                        {{ trans("navigation/mainnav.companyVerification") }}
                                        @if($unconfirmedCompanyCount > 0)
                                            <span class="badge bg-color-redLight pull-right inbox-badge">
                                                {{ $unconfirmedCompanyCount }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                            @endif
                            @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_FINANCE))
                                <li class="{{ Request::is('finance/*') ? 'active' : null }}">
                                    <a href="{{ route('finance.claim-certificate') }}" class="text-truncate">
                                        <i class="fas fa-sm fa-fw fa-dollar-sign"></i>
                                        {{ trans("navigation/mainnav.financeModule") }}
                                        @if($claimCertCountWithPaymentsPending > 0)
                                            <span class="badge bg-color-orange pull-right inbox-badge">
                                                {{ $claimCertCountWithPaymentsPending }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                            @else
                                @if($user->company->contractGroupCategory->includesContractGroups(array(\PCK\ContractGroups\ContractGroup::getIdByGroup(PCK\ContractGroups\Types\Role::CONTRACTOR))))
                                    <li class="{{ Request::is('contractor/finance/claim-certificate') ? 'active' : null }}">
                                        <a href="{{ route('finance.contractor.claim-certificate', [$user->id]) }}" class="text-truncate">
                                            <i class="fas fa-sm fa-fw fa-dollar-sign"></i>
                                            {{ trans("navigation/mainnav.financeModule") }}
                                            @if($claimCertCountWithPaymentsPending > 0)
                                                <span class="badge bg-color-orange pull-right inbox-badge">
                                                    {{ $claimCertCountWithPaymentsPending }}
                                                </span>
                                            @endif
                                        </a>
                                    </li>
                                @endif
                            @endif
                            @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_COST_DATA) || ($costDataCount > 0))
                                <li class="{{ Request::is('cost-data*') && (!Request::is('cost-data-types')) ? 'active' : null }}">
                                    <a href="{{ route('costData') }}" class="text-truncate">
                                        <i class="fas fa-sm fa-fw fa-map"></i>
                                        {{ trans("navigation/mainnav.costData") }}
                                        @if($costDataCount > 0)
                                            <span class="badge bg-color-orange pull-right inbox-badge">
                                                {{ $costDataCount }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                    @if(\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT))
                    <li>
                        <a href="javascript:void(0);" title="{{ trans('vendorManagement.vendorManagement') }}" class="text-truncate">
                            <i class="fa fa-lg fa-fw fa-users"></i>
                            <span class="menu-item-parent">{{ trans("vendorManagement.vendorManagement") }}</span>
                        </a>
                        <ul>
                            @if($user->company)
                                @if(!$user->company->contractGroupCategory->isTypeInternal() && \PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_LMS_Elearning))
                                    <li>
                                            <a href="https://vcobc.simedarbyproperty.com/ssologin.php?tokenid={{$user->email}}" target="_blank" title="{{ trans('vendorManagement.vocbc') }}" class="text-truncate">
                                            <i class="fa fa-lg fa-fw fa-book"></i>
                                            <span class="menu-item-parent">{{ trans("vendorManagement.vocbc") }}</span>
                                        </a>
                                    </li>
                                @endif
                            @endif
                            @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DASHBOARD))
                            <li class="{{ Request::is('vendor-management-dashboard/vendorStatistics*') ? 'active' : null }}" >
                                <a href="{{ route('vendor.management.dashboard.vendorStatistics') }}" title='{{ trans("vendorManagement.dashboard") }}' class="text-truncate">
                                    <i class="fas fa-chart-line"></i>
                                    {{ trans("vendorManagement.vendorStatisticsDashboard") }}
                                </a>
                            </li>
                            <li class="{{ Request::is('vendor-management-dashboard/vpestatistics*') ? 'active' : null }}" >
                                <a href="{{ route('vendor.management.dashboard.index') }}" title='{{ trans("vendorManagement.dashboard") }}' class="text-truncate">
                                    <i class="fas fa-chart-line"></i>
                                    {{ trans("vendorManagement.vpeStatisticsDashboard") }}
                                </a>
                            </li>
                            @endif

                            @if(\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR))
                                @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR_DASHBOARD))
                                    <li class="{{ Request::is('digital-star-evaluation/dashboard*') ? 'active' : null }}" >
                                        <a href="{{ route('digital-star.dashboard.index') }}" title="{{ trans('digitalStar/navigation/mainnav.digitalStarDashboard') }}" class="text-truncate">
                                            <i class="fas fa-chart-line"></i>
                                            {{ trans('digitalStar/navigation/mainnav.digitalStarDashboard') }}
                                        </a>
                                    </li>
                                @endif
                            @endif

                            @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_VENDOR_PROFILE_VIEW))
                                <li class="{{ Request::is('vendor-profiles*') ? 'active' : null }}">
                                    <a href="{{ route('vendorProfile') }}" title='{{ trans("vendorProfile.vendorProfiles") }}' class="text-truncate">
                                        <i class="fa fa-sm fa-user-secret"></i>
                                        {{ trans("vendorProfile.vendorProfiles") }}
                                    </a>
                                </li>
                            @endif
                            <li>
                                <a href="javascript:void(0);" class="text-truncate">
                                    <i class="fa fa-sm fa-fw fa-users-cog"></i>
                                    {{ trans("vendorManagement.approval") }}
                                </a>
                                <ul>
                                    @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_APPROVAL_REGISTRATION))
                                    <li class="{{ (Request::is('vendor-management/approval/registration-and-pre-qualification*')) ? 'active' : null }}">
                                        <a href="{{ route('vendorManagement.approval.registrationAndPreQualification') }}" title="{{ trans('vendorManagement.registrationAndPreQualification') }}" class="text-truncate">
                                            <i class="fa fa-address-book"></i>
                                            {{ trans('navigation/mainnav.registrationAndPreQ') }}
                                            <span class="badge bg-color-red pull-right inbox-badge">
                                                {{ $pendingVendorRegistrations }}
                                            </span>
                                        </a>
                                    </li>
                                    @endif
                                    <li class="{{ (Request::is('vendor-performance-evaluation/form-approvals/forms*')) ? 'active' : null }}">
                                        <a href="{{ route('vendorPerformanceEvaluation.companyForms.approval') }}" title="{{ trans('vendorManagement.vendorPerformanceEvaluation') }}" class="text-truncate">
                                            <i class="fa fa-exchange-alt"></i>
                                            {{ trans('navigation/mainnav.vendorPerformanceEvaluation') }}
                                            @if($pendingVpeCompanyForms > 0)
                                            <span class="badge bg-color-red pull-right inbox-badge">
                                                {{ $pendingVpeCompanyForms }}
                                            </span>
                                            @endif
                                        </a>
                                    </li>
                                    @if(\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR))
                                        @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR_VERIFIER_COMPANY)
                                            || \PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR_VERIFIER_PROJECT))
                                            <li>
                                                <a href="javascript:void(0);" class="text-truncate">
                                                    <i class="fa fa-sm fa-exchange-alt"></i>
                                                    {{ trans("digitalStar/navigation/mainnav.digitalStar") }}
                                                </a>
                                                <ul>
                                                    @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR_VERIFIER_COMPANY))
                                                        <li class="{{ Request::is('digital-star-evaluation/approvals/company/approve*') ? 'active' : null }}">
                                                            <a href="{{ route('digital-star.approval.company.approve.index') }}" title='{{ trans("digitalStar/navigation/mainnav.company") }}' class="text-truncate">
                                                                <i class="fa fa-sm fa-university"></i>
                                                                {{ trans("digitalStar/navigation/mainnav.company") }}
                                                                @if ($pendingDsEvaluationForms['company-verifier'] > 0)
                                                                    <span class="badge bg-color-red pull-right inbox-badge">
                                                                        {{ $pendingDsEvaluationForms['company-verifier'] }}
                                                                    </span>
                                                                @endif
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR_VERIFIER_PROJECT))
                                                        <li class="{{ Request::is('digital-star-evaluation/approvals/project/approve*') ? 'active' : null }}">
                                                            <a href="{{ route('digital-star.approval.project.approve.index') }}" title='{{ trans("digitalStar/navigation/mainnav.project") }}' class="text-truncate">
                                                                <i class="fa fa-sm fa-table"></i>
                                                                {{ trans("digitalStar/navigation/mainnav.project") }}
                                                                @if ($pendingDsEvaluationForms['project-verifier'] > 0)
                                                                    <span class="badge bg-color-red pull-right inbox-badge">
                                                                        {{ $pendingDsEvaluationForms['project-verifier'] }}
                                                                    </span>
                                                                @endif
                                                            </a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </li>
                                        @endif
                                    @endif
                                </ul>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="text-truncate">
                                    <i class="fa fa-sm fa-fw fa-list"></i>
                                    {{ trans("vendorManagement.lists") }}
                                </a>
                                <ul>
                                    @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_ACTIVE_VENDOR_LIST))
                                        <li class="{{ (Request::is('vendor-management/lists/active-vendor-list*')) ? 'active' : null }}">
                                            <a href="{{ route('vendorManagement.activeVendorList.index') }}" title="{{ trans('vendorManagement.activeVendorList') }}" class="text-truncate">
                                                <i class="fa fa-sm fa-fw fa-user-check"></i>
                                                {{ trans('navigation/mainnav.activeVendorList') }}
                                                <span class="badge bg-color-green pull-right inbox-badge">
                                                    {{ $totalActiveVendors }}
                                                </span>
                                            </a>
                                        </li>
                                    @endif
                                    @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_NOMINATED_WATCH_LIST_VIEW))
                                    <li class="{{ (Request::is('vendor-management/lists/nominated-watch-list*')) ? 'active' : null }}">
                                        <a href="{{ route('vendorManagement.nominatedWatchList') }}" title="{{ trans('vendorManagement.nomineesForWatchList') }}" class="text-truncate">
                                            <i class="fa fa-sm fa-fw fa-user-tag"></i>
                                            {{ trans('navigation/mainnav.nomineesForWatchList') }}
                                            <span class="badge bg-color-orange text-white pull-right inbox-badge">
                                                {{ $totalNomineesForWatchListVendors }}
                                            </span>
                                        </a>
                                    </li>
                                    @endif
                                    @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_WATCH_LIST_VIEW))
                                    <li class="{{ (Request::is('vendor-management/lists/watch-list*')) ? 'active' : null }}">
                                        <a href="{{ route('vendorManagement.watchList.index') }}" title="{{ trans('vendorManagement.watchList') }}" class="text-truncate">
                                            <i class="fa fa-sm fa-fw fa-user-lock"></i>
                                            {{ trans('navigation/mainnav.watchList') }}
                                            <span class="badge bg-color-yellow pull-right inbox-badge">
                                                {{ $totalWatchListVendors }}
                                            </span>
                                        </a>
                                    </li>
                                    @endif
                                    @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DEACTIVATED_VENDOR_LIST))
                                    <li class="{{ (Request::is('vendor-management/lists/deactivated-vendor-list*')) ? 'active' : null }}">
                                        <a href="{{ route('vendorManagement.deactivatedVendorList') }}" title="{{ trans('vendorManagement.deactivatedVendorList') }}" class="text-truncate">
                                            <i class="fa fa-sm fa-fw fa-user-times"></i>
                                            {{ trans('navigation/mainnav.deactivatedVendorList') }}
                                            <span class="badge bg-color-red pull-right inbox-badge">
                                                {{ $totalDeactivedVendors }}
                                            </span>
                                        </a>
                                    </li>
                                    @endif
                                    @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_UNSUCCESSFUL_VENDOR_LIST))
                                    <li class="{{ (Request::is('vendor-management/lists/unsuccessful-vendor-list*')) ? 'active' : null }}">
                                        <a href="{{ route('vendorManagement.unsuccessfulVendorList') }}" title="{{ trans('vendorManagement.unsuccessfulVendorList') }}" class="text-truncate">
                                            <i class="fa fa-sm fa-fw fa-user-slash"></i>
                                            {{ trans('navigation/mainnav.unsuccessfulVendorList') }}
                                            <span class="badge bg-color-blue pull-right inbox-badge">
                                                {{ $totalUnsuccessfullyRegisteredVendors }}
                                            </span>
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="text-truncate">
                                    <i class="fa fa-sm fa-fw fa-exchange-alt"></i>
                                    {{ trans("navigation/mainnav.vendorPerformanceEvaluation") }}
                                </a>
                                <ul>
                                    @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_PERFORMANCE_EVALUATION))
                                        <li class="{{ Request::is('vendor-performance-evaluation/cycles*') ? 'active' : null }}">
                                            <a href="{{ route('vendorPerformanceEvaluation.cycle.index') }}" title='{{ trans("navigation/mainnav.vpeCycles") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-sync"></i>
                                                {{ trans("navigation/mainnav.vpeCycles") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('vendor-performance-evaluation/setups*') ? 'active' : null }}">
                                            <a href="{{ route('vendorPerformanceEvaluation.setups.index') }}" title='{{ trans("navigation/mainnav.setup") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-book"></i>
                                                {{ trans("navigation/mainnav.setup") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('vendor-performance-evaluation/evaluation-removal-requests*') ? 'active' : null }}">
                                            <a href="{{ route('vendorPerformanceEvaluation.evaluations.removalRequest.index') }}" title='{{ trans("navigation/mainnav.removalRequests") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-backspace"></i>
                                                {{ trans("navigation/mainnav.removalRequests") }}
                                                @if($pendingVpeRemovalRequests > 0)
                                                <span class="badge bg-color-red pull-right inbox-badge">
                                                    {{ $pendingVpeRemovalRequests }}
                                                </span>
                                                @endif
                                            </a>
                                        </li>
                                    @endif
                                    @if($currentUser->company_id)
                                        <li class="{{ Request::is('vendor-performance-evaluation/evaluations*') ? 'active' : null }}">
                                            <a href="{{ route('vendorPerformanceEvaluation.index') }}" title='{{ trans("navigation/mainnav.evaluations") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-exchange-alt"></i>
                                                {{ trans("navigation/mainnav.evaluations") }}
                                                @if($pendingEvaluations > 0)
                                                <span class="badge bg-color-red pull-right inbox-badge">
                                                    {{ $pendingEvaluations }}
                                                </span>
                                                @endif
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                            @if(\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR))
                                @if($user->company && $user->isGroupAdmin()
                                    || \PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR)
                                    || \PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR_EVALUATOR_COMPANY)
                                    || \PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR_EVALUATOR_PROJECT))
                                    <li>
                                        <a href="javascript:void(0);" class="text-truncate">
                                            <i class="fa fa-sm fa-exchange-alt"></i>
                                            {{ trans("digitalStar/navigation/mainnav.digitalStar") }}
                                        </a>
                                        <ul>
                                            @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR))
                                                <li class="{{ Request::is('digital-star-evaluation/cycles*') ? 'active' : null }}">
                                                    <a href="{{ route('digital-star.cycle.index') }}" title='{{ trans("navigation/mainnav.vpeCycles") }}' class="text-truncate">
                                                        <i class="fa fa-sm fa-sync"></i>
                                                        {{ trans("navigation/mainnav.vpeCycles") }}
                                                    </a>
                                                </li>
                                                <li class="{{ Request::is('digital-star-evaluation/setups*') ? 'active' : null }}">
                                                    <a href="{{ route('digital-star.setups.index') }}" title='{{ trans("navigation/mainnav.setup") }}' class="text-truncate">
                                                        <i class="fa fa-sm fa-book"></i>
                                                        {{ trans("navigation/mainnav.setup") }}
                                                    </a>
                                                </li>
                                            @endif
                                            @if($user->company && $user->isGroupAdmin()
                                                || \PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR_EVALUATOR_PROJECT))
                                                <li>
                                                    <a href="javascript:void(0);" class="text-truncate">
                                                        <i class="fa fa-sm fa-exchange-alt"></i>
                                                        {{ trans("digitalStar/navigation/mainnav.evaluations") }}
                                                    </a>
                                                    <ul>
                                                        @if($user->company && $user->isGroupAdmin())
                                                            <li class="{{ Request::is('digital-star-evaluation/company-evaluations*') ? 'active' : null }}">
                                                                <a href="{{ route('digital-star.evaluation.company.index') }}" title='{{ trans("digitalStar/navigation/mainnav.company") }}' class="text-truncate">
                                                                    <i class="fa fa-sm fa-university"></i>
                                                                    {{ trans("digitalStar/navigation/mainnav.company") }}
                                                                    @if ($pendingDsEvaluationForms['company-evaluator'] > 0)
                                                                        <span class="badge bg-color-red pull-right inbox-badge">
                                                                            {{ $pendingDsEvaluationForms['company-evaluator'] }}
                                                                        </span>
                                                                    @endif
                                                                </a>
                                                            </li>
                                                        @endif
                                                        @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR_EVALUATOR_PROJECT))
                                                            <li class="{{ Request::is('digital-star-evaluation/project-evaluations*') ? 'active' : null }}">
                                                                <a href="{{ route('digital-star.evaluation.project.index') }}" title='{{ trans("digitalStar/navigation/mainnav.project") }}' class="text-truncate">
                                                                    <i class="fa fa-sm fa-table"></i>
                                                                    {{ trans("digitalStar/navigation/mainnav.project") }}
                                                                    @if ($pendingDsEvaluationForms['project-evaluator'] > 0)
                                                                        <span class="badge bg-color-red pull-right inbox-badge">
                                                                            {{ $pendingDsEvaluationForms['project-evaluator'] }}
                                                                        </span>
                                                                    @endif
                                                                </a>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </li>
                                            @endif

                                            @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR_EVALUATOR_COMPANY)
                                                || \PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR_EVALUATOR_PROJECT))
                                                <li>
                                                    <a href="javascript:void(0);" class="text-truncate">
                                                        <i class="fa fa-sm fa-exchange-alt"></i>
                                                        {{ trans("digitalStar/navigation/mainnav.assignVerifiers") }}
                                                    </a>
                                                    <ul>
                                                        @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR_EVALUATOR_COMPANY))
                                                            <li class="{{ Request::is('digital-star-evaluation/approvals/company/assign-verifiers*') ? 'active' : null }}">
                                                                <a href="{{ route('digital-star.approval.company.assign-verifiers.index') }}" title='{{ trans("digitalStar/navigation/mainnav.company") }}' class="text-truncate">
                                                                    <i class="fa fa-sm fa-university"></i>
                                                                    {{ trans("digitalStar/navigation/mainnav.company") }}
                                                                    @if ($pendingDsEvaluationForms['company-processor'] > 0)
                                                                        <span class="badge bg-color-red pull-right inbox-badge">
                                                                            {{ $pendingDsEvaluationForms['company-processor'] }}
                                                                        </span>
                                                                    @endif
                                                                </a>
                                                            </li>
                                                        @endif

                                                        @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR_EVALUATOR_PROJECT))
                                                            <li class="{{ Request::is('digital-star-evaluation/approvals/project/assign-verifiers*') ? 'active' : null }}">
                                                                <a href="{{ route('digital-star.approval.project.assign-verifiers.index') }}" title='{{ trans("digitalStar/navigation/mainnav.project") }}' class="text-truncate">
                                                                    <i class="fa fa-sm fa-table"></i>
                                                                    {{ trans("digitalStar/navigation/mainnav.project") }}
                                                                    @if ($pendingDsEvaluationForms['project-processor'] > 0)
                                                                        <span class="badge bg-color-red pull-right inbox-badge">
                                                                            {{ $pendingDsEvaluationForms['project-processor'] }}
                                                                        </span>
                                                                    @endif
                                                                </a>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </li>
                                            @endif
                                        </ul>
                                    </li>
                                @endif
                            @endif
                            @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_FORM_TEMPLATES)
                                || (\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR)
                                    && \PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR_TEMPLATE)))
                                <li>
                                    <a href="javascript:void(0);" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-clone"></i>
                                        {{ trans("navigation/mainnav.formTemplates") }}
                                    </a>
                                    <ul>
                                        @if (\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_FORM_TEMPLATES))
                                            <li class="{{ (Request::is('vendor_registration/forms_library*')) ? 'active' : null }}">
                                                <a href="{{ route('vendor.registration.forms.library.index') }}" class="text-truncate">
                                                    <i class="fa fa-address-book"></i>
                                                    {{ trans('navigation/mainnav.vendorRegistration') }}
                                                </a>
                                            </li>
                                            @if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_PRE_QUALIFICATION'))
                                            <li class="{{ Request::is('vendor-pre-qualification/form-library*') ? 'active' : null }}">
                                                <a href="{{ route('vendorPreQualification.formLibrary.index') }}" title='{{ trans("navigation/mainnav.vendorPreQualification") }}' class="text-truncate">
                                                    <i class="fa fa-sm fa-address-card"></i>
                                                    {{ trans("navigation/mainnav.vendorPreQualification") }}
                                                </a>
                                            </li>
                                            @endif
                                            <li class="{{ Request::is('vendor-performance-evaluation/template-forms*') ? 'active' : null }}">
                                                <a href="{{ route('vendorPerformanceEvaluation.templateForms') }}" title='{{ trans("navigation/mainnav.vendorPerformanceEvaluation") }}' class="text-truncate">
                                                    <i class="fa fa-sm fa-exchange-alt"></i>
                                                    {{ trans("navigation/mainnav.vendorPerformanceEvaluation") }}
                                                </a>
                                            </li>
                                        @endif
                                        @if (\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR))
                                            @if (\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR_TEMPLATE))
                                                <li class="{{ Request::is('digital-star-evaluation/template-forms*') ? 'active' : null }}">
                                                    <a href="{{ route('digital-star.templateForm') }}" title='{{ trans("digitalStar/navigation/mainnav.digitalStar") }}' class="text-truncate">
                                                        <i class="fa fa-sm fa-exchange-alt"></i>
                                                        {{ trans("digitalStar/navigation/mainnav.digitalStar") }}
                                                    </a>
                                                </li>
                                            @endif
                                        @endif
                                    </ul>
                                </li>
                            @endif
                            @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_FORM_TEMPLATES))
                                <li>
                                    <a href="javascript:void(0);" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-clone"></i>
                                        {{ trans("navigation/mainnav.formTemplateMapping") }}
                                    </a>
                                    <ul>
                                        <li class="{{ Request::is('vendor_registration/form_mappings*') ? 'active' : null }}">
                                            <a href="{{ route('vendor.registration.form.mapping.index') }}" title='{{ trans("navigation/mainnav.vendorRegistration") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-address-book"></i>
                                                {{ trans("navigation/mainnav.vendorRegistration") }}
                                            </a>
                                        </li>
                                        {{-- // Temporary hidden
                                        @if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_PRE_QUALIFICATION'))
                                        <li class="{{ Request::is('vendor-pre-qualification/form-mappings*') ? 'active' : null }}">
                                            <a href="{{ route('vendorPreQualification.formMapping') }}" title='{{ trans("navigation/mainnav.vendorPreQualification") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-address-card"></i>
                                                {{ trans("navigation/mainnav.vendorPreQualification") }}
                                            </a>
                                        </li>
                                        @endif --}}
                                    </ul>
                                </li>
                            @endif
                            @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_PAYMENT))
                                <li class="{{ Request::is('vendor_registration/payment/master-list*') ? 'active' : null }}">
                                    <a href="{{ route('vendor.registration.payments.master.list.index') }}" title='{{ trans("vendorManagement.vendorPaymentMasterList") }}' class="text-truncate">
                                        <i class="fas fa-university"></i>
                                        {{ trans("vendorManagement.vendorPaymentMasterList") }}
                                    </a>
                                </li>
                            @endif
                            @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_GRADE_MAINTENANCE))
                                <li>
                                    <a href="javascript:void(0);" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-certificate"></i>
                                        {{ trans("vendorManagement.vendorManagementGrades") }}
                                    </a>
                                    <ul>
                                        <li class="{{ Request::is('vendor_management_grade*') ? 'active' : null }}">
                                            <a href="{{ route('vendor.management.grade.index') }}" title='{{ trans("vendorManagement.vendorManagementGrades") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-certificate"></i>
                                                {{ trans("vendorManagement.grades") }}
                                            </a>
                                        </li>
                                        {{-- <li class="{{ Request::is('vendor-pre-qualification/grades') ? 'active' : null }}">
                                            <a href="{{ route('vendorPreQualification.grades') }}" title='{{ trans("vendorManagement.vendorPreQualificationGrades") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-address-card"></i>
                                                {{ trans("navigation/mainnav.vendorPreQualification") }}
                                            </a>
                                        </li> --}}
                                    </ul>
                                </li>
                            @endif
                        </ul>
                    </li>
                    @endif
                    <li>
                        <a href="javascript:void(0);" title="{{ trans('navigation/mainnav.maintenance') }}" class="text-truncate">
                            <i class="fa fa-lg fa-fw fa-cogs"></i>
                            <span class="menu-item-parent">{{ trans("navigation/mainnav.maintenance") }}</span>
                        </a>
                        <ul>
                            <li>
                                <a href="javascript:void(0);" class="text-truncate">
                                    <i class="fa fa-sm fa-fw fa-clone"></i>
                                    {{ trans("navigation/mainnav.templates") }}
                                </a>
                                <ul>
                                    @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_TECHNICAL_EVALUATION_TEMPLATE))
                                        <li class="{{ Request::is('technical-evaluation') ? 'active' : null }}">
                                            <a href="{{ route('technicalEvaluation.sets') }}" class="text-truncate">
                                                <i class="fa fa-sm fa-fw fa-object-group"></i>
                                                {{ trans("navigation/mainnav.technicalEvaluation") }}
                                            </a>
                                        </li>
                                    @endif
                                    @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_FORM_OF_TENDER_TEMPLATE))
                                        <li class="{{ Request::is('template/form_of_tender*') ? 'active' : null }}">
                                            <a href="{{ route('form_of_tender.template.selection') }}" class="text-truncate">
                                                <i class="far fa-sm fa-fw fa-file-alt"></i>
                                                {{ trans("navigation/mainnav.formOfTender") }}
                                            </a>
                                        </li>
                                    @endif
                                    @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_TENDER_DOCUMENTS_TEMPLATE))
                                        <li class="{{ Request::is('template/tender-documents/*') ? 'active' : null }}">
                                            <a href="{{ route('tender_documents.template.directory') }}" class="text-truncate">
                                                <i class="fa fa-sm fa-fw fa-folder"></i>
                                                {{ trans("navigation/mainnav.templateTenderDocumentFolders") }}
                                            </a>
                                        </li>
                                    @endif
                                    @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_MASTER_COST_DATA))
                                        <li class="{{ Request::is('cost-data/master') ? 'active' : null }}">
                                            <a href="{{ route('costData.master') }}" class="text-truncate">
                                                <i class="far fa-sm fa-fw fa-map"></i>
                                                {{ trans("navigation/mainnav.masterCostData") }}
                                                @if($masterCostDataCount > 0)
                                                    <span class="badge bg-color-orange pull-right inbox-badge">
                                                        {{ $masterCostDataCount }}
                                                    </span>
                                                @endif
                                            </a>
                                        </li>
                                    @endif
                                    @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_LETTER_OF_AWARD))
                                        <li class="{{ Request::is('letter_of_award_template*') ? 'active' : null }}">
                                            <a href="{{ route('letterOfAward.templates.selection') }}" class="text-truncate">
                                                <i class="far fa-sm fa-fw fa-file-alt"></i>
                                                {{ trans('letterOfAward.letterOfAward') }}
                                            </a>
                                        </li>
                                    @endif
                                    @if(\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_INPSECTION) && \PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_INSPECTION_TEMPLATE))
                                        <li class="{{ Request::is('master-inspection-list') ? 'active' : null }}">
                                            <a href="{{ route('master.inspection.list.index') }}" class="text-truncate">
                                            <i class="fa fa-list-alt"></i>
                                                {{ trans('navigation/mainnav.masterInspectionList') }}
                                            </a>
                                        </li>
                                    @endif
                                    @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_PROJECT_REPORT_TEMPLATE))
                                        <li class="{{ Request::is('project_report_template*') ? 'active' : null }}">
                                            <a href="{{ route('projectReport.template.index') }}" class="text-truncate">
                                                <i class="far fa-sm fa-fw fa-file-alt"></i>
                                                {{ trans('navigation/mainnav.projectReport') }}
                                            </a>
                                        </li>
                                    @endif
                                    @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_PROJECT_REPORT_CHART_TEMPLATE))
                                        <li class="{{ Request::is('project_report_chart_template*') ? 'active' : null }}">
                                            <a href="{{ route('projectReport.chart.template.index') }}" class="text-truncate">
                                                <i class="fa fa-chart-line"></i>
                                                {{ trans('navigation/mainnav.projectReportChart') }}
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                            @if($user->isSuperAdmin())
                                <li class="{{ Request::is('modules/permissions') ? 'active' : null }}">
                                    <a href="{{ route('module.permissions.index') }}" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-key"></i>
                                        {{ trans("navigation/mainnav.modulePermissions") }}
                                    </a>
                                </li>
                                <li class="{{ (Request::is('dashboard/group*')) ? 'active' : null }}">
                                    <a href="{{ route('dashboard.group.index') }}" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-layer-group"></i>
                                        {{ trans("navigation/mainnav.dashboardGroups") }}
                                    </a>
                                </li>
                                <li class="{{ !Request::is('companies/verification') && Request::is('companies*') ? 'active' : null }}">
                                    <a href="{{ route('companies') }}" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-building"></i>
                                        {{ trans("navigation/mainnav.companies") }}
                                        <span class="badge bg-color-green pull-right inbox-badge">
                                            {{ $companyCount }}
                                        </span>
                                    </a>
                                </li>
                            @endif
                            @if(\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT))
                            <li>
                                <a href="javascript:void(0);" class="text-truncate">
                                    <i class="fas fa-sm fa-fw fa-users-cog"></i>
                                    {{ trans("navigation/mainnav.vendorManagement") }}
                                </a>
                                <ul>
                                    @if($currentUser->isSuperAdmin())
                                        <li class="{{ Request::is('vendor-management-dashboard*') ? 'active' : null }}" >
                                            <a href="{{ route('vendorManagement.users') }}" title='{{ trans("vendorManagement.manageUsers") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-fw fa-users-cog"></i>
                                                {{ trans("navigation/mainnav.manageUsers") }}
                                            </a>
                                        </li>
                                    @endif
                                    @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($user, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_SETTINGS_AND_MAINTENANCE))
                                        <li class="{{ Request::is('vendor-groups/internal*') ? 'active' : null }}">
                                            <a href="{{ route('vendorGroups.internal.index') }}" title='{{ trans("contractGroupCategories.internalVendorGroups") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-users"></i>
                                                {{ trans("contractGroupCategories.internalVendorGroups") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('vendor-groups/external*') ? 'active' : null }}">
                                            <a href="{{ route('vendorGroups.external.index') }}" title='{{ trans("contractGroupCategories.externalVendorGroups") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-users"></i>
                                                {{ trans("contractGroupCategories.externalVendorGroups") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('vendor-work-categories*') ? 'active' : null }}">
                                            <a href="{{ route('vendorWorkCategories.index') }}" class="text-truncate">
                                                <i class="fa fa-sm fa-fw fa-tools"></i>
                                                {{ trans("navigation/mainnav.vendorWorkCategories") }}
                                            </a>
                                        </li>
                                        <li class="{{ (Request::is('email_notification_settings')) ? 'active' : null }}">
                                            <a href="{{ route('email.notification.settings.index') }}" class="text-truncate">
                                                <i class="far fa-envelope"></i>
                                                {{ trans('emailNotificationSettings.emailNotificationSettings') }}
                                            </a>
                                        </li>
                                        <li class="{{ (Request::is('payment-settings*')) ? 'active' : null }}">
                                            <a href="{{ route('payment.settings.index') }}" class="text-truncate">
                                                <i class="fas fa-credit-card"></i>
                                                {{ trans('payment.paymentSettings') }}
                                            </a>
                                        </li>
                                        <li class="{{ (Request::is('building-information-modelling-level*')) ? 'active' : null }}">
                                            <a href="{{ route('buildingInformationModellingLevel.index') }}" class="text-truncate">
                                                <i class="fas fa-building"></i>
                                                {{ trans('buildingInformationModelling.bimLevels') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="text-truncate">
                                                <i class="fas fa-building"></i>
                                                {{ trans("navigation/mainnav.cidb") }}
                                            </a>
                                            <ul>
                                                <li class="{{ (Request::is('cidb-grades*')) ? 'active' : null }}">
                                                    <a href="{{ route('cidb_grades.index') }}" class="text-truncate">
                                                        <i class="fas fa-building"></i>
                                                        {{ trans('cidbGrades.grade') }}
                                                    </a>
                                                </li>
                                                <li class="{{ (Request::is('cidb-codes*')) ? 'active' : null }}">
                                                    <a href="{{ route('cidb_codes.index') }}" class="text-truncate">
                                                        <i class="fas fa-building"></i>
                                                        {{ trans('cidbCodes.code') }}
                                                    </a>
                                                </li>
                                            </ul>
                                            </li>
                                        <li class="{{ Request::is('vendor-details/settings*') ? 'active' : null }}">
                                            <a href="{{ route('vendorRegistration.vendorDetails.settings.edit') }}" title='{{ trans("vendorManagement.vendorDetails") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-user-secret"></i>
                                                {{ trans("vendorManagement.vendorDetails") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('company-personnel/settings*') ? 'active' : null }}">
                                            <a href="{{ route('company.personnel.settings.edit') }}" title='{{ trans("vendorManagement.companyPersonnel") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-user-secret"></i>
                                                {{ trans("vendorManagement.companyPersonnel") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('project-track-record/settings*') ? 'active' : null }}">
                                            <a href="{{ route('project.track.record.settings.edit') }}" title='{{ trans("vendorManagement.projectTrackRecord") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-user-secret"></i>
                                                {{ trans("vendorManagement.projectTrackRecord") }}
                                            </a>
                                        </li>
                                        @if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_SUPPLIER_CREDIT_FACILITIES'))
                                        <li class="{{ Request::is('supplier-credit-facility/settings*') ? 'active' : null }}">
                                            <a href="{{ route('supplier.credit.facility.settings.edit') }}" title='{{ trans("vendorManagement.supplierCreditFacilities") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-user-secret"></i>
                                                {{ trans("vendorManagement.supplierCreditFacilities") }}
                                            </a>
                                        </li>
                                        @endif
                                        <li class="{{ Request::is('vendor_profile_module_parameter*') ? 'active' : null }}">
                                            <a href="{{ route('vendor.profile.module.parameter.edit') }}" title='{{ trans("vendorManagement.vendorProfileModuleParameter") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-user-secret"></i>
                                                {{ trans("navigation/mainnav.vendorProfile") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('vendor_registration_and_prequalification_module_parameter*') ? 'active' : null }}">
                                            <a href="{{ route('vendor.registration.and.prequalification.module.parameter.edit') }}" title='{{ trans("vendorManagement.vendorPerformanceEvaluationModuleParameter") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-address-book"></i>
                                                {{ trans("navigation/mainnav.registrationAndPreQ") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('vendor_performance_evaluation_module_parameter*') ? 'active' : null }}">
                                            <a href="{{ route('vendor.performance.evaluation.module.parameter.edit') }}" title='{{ trans("vendorManagement.vendorPerformanceEvaluationModuleParameter") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-exchange-alt"></i>
                                                {{ trans("navigation/mainnav.vendorPerformanceEvaluation") }}
                                            </a>
                                        </li>
                                        @if(\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR))
                                            <li class="{{ Request::is('digital-star-module-parameter*') ? 'active' : null }}">
                                                <a href="{{ route('digital-star.module-parameter.edit') }}" title='{{ trans("digitalStar/navigation/mainnav.moduleParameter") }}' class="text-truncate">
                                                    <i class="fa fa-sm fa-exchange-alt"></i>
                                                    {{ trans("digitalStar/navigation/mainnav.digitalStar") }}
                                                </a>
                                            </li>
                                        @endif
                                        <li class="{{ Request::is('login-request-form/settings') ? 'active' : null }}">
                                            <a href="{{ route('loginRequestForm.settings.edit') }}" title='{{ trans("vendorManagement.loginRequestFormSettings") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-sign-in-alt"></i>
                                                {{ trans("vendorManagement.loginRequestFormSettings") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('business-entity-types/*') ? 'active' : null }}">
                                            <a href="{{ route('businessEntityTypes.index') }}" class="text-truncate">
                                                <i class="fa fa-sm fa-fw fa-users"></i>
                                                {{ trans("navigation/mainnav.businessEntityTypes") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('property-developers/*') ? 'active' : null }}">
                                            <a href="{{ route('propertyDevelopers.index') }}" class="text-truncate">
                                                <i class="fa fa-sm fa-fw fa-users"></i>
                                                {{ trans("navigation/mainnav.propertyDevelopers") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('vendor-performance-evaluation/project-removal-reasons') ? 'active' : null }}">
                                            <a href="{{ route('vendorPerformanceEvaluation.projectRemovalReasons.index') }}" title='{{ trans("navigation/mainnav.vpeProjectRemovalReasons") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-backspace"></i>
                                                {{ trans("navigation/mainnav.vpeProjectRemovalReasons_short") }}
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                            @endif
                            @if($user->isSuperAdmin())
                                <li class="{{ Request::is('maintenance/consultant-management*') ? 'active' : null }}">
                                    <a href="javascript:void(0);" class="text-truncate">
                                        <i class="fas fa-sm fa-fw fa-users-cog"></i>
                                        {{ trans("navigation/mainnav.consultantManagement") }}
                                    </a>
                                    <ul>
                                        <li class="{{ Request::is('maintenance/consultant-management/roles') ? 'active' : null }}">
                                            <a href="{{ route('consultant.management.maintenance.roles.index') }}" class="text-truncate">
                                                <i class="fa fa-sm fa-users"></i>
                                                {{ trans("inspection.roles") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('maintenance/consultant-management/development-type*') ? 'active' : null }}">
                                            <a href="{{ route('consultant.management.maintenance.development.type.index') }}" class="text-truncate">
                                                <i class="fa fa-sm fa-city"></i>
                                                {{ trans("navigation/mainnav.developmentTypes") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('maintenance/consultant-management/product-type*') ? 'active' : null }}">
                                            <a href="{{ route('consultant.management.maintenance.product.type.index') }}" class="text-truncate">
                                                <i class="fa fa-sm fa-city"></i>
                                                {{ trans("navigation/mainnav.productTypes") }}
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-users"></i>
                                        {{ trans("navigation/mainnav.contractGroupCategories") }}
                                    </a>
                                    <ul>
                                        <li class="{{ Request::is('vendor-groups/internal*') ? 'active' : null }}">
                                            <a href="{{ route('vendorGroups.internal.index') }}" title='{{ trans("contractGroupCategories.internalVendorGroups") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-users"></i>
                                                {{ trans("contractGroupCategories.internalVendorGroups") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('vendor-groups/external*') ? 'active' : null }}">
                                            <a href="{{ route('vendorGroups.external.index') }}" title='{{ trans("contractGroupCategories.externalVendorGroups") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-users"></i>
                                                {{ trans("contractGroupCategories.externalVendorGroups") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('contract-group-categories/privileges') ? 'active' : null }}">
                                            <a href="{{ route('contractGroupCategories.privileges.index') }}" title='{{ trans("contractGroupCategories.dashboardPermissions") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-chart-line"></i>
                                                {{ trans("contractGroupCategories.dashboardPermissions") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('contract-group-categories/match') ? 'active' : null }}">
                                            <a href="{{ route('contractGroupCategories.match') }}" title='{{ trans("contractGroupCategories.contractGroupRoles") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-link"></i>
                                                {{ trans("contractGroupCategories.contractGroupRoles") }}
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="{{ Request::is('all_users*') ? 'active' : null }}">
                                    <a href="{{ route('users.all.index') }}" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-users"></i>
                                        {{ trans("navigation/mainnav.allUsers") }}
                                    </a>
                                </li>
                                <li class="{{ Request::is('contracts*') ? 'active' : null }}">
                                    <a href="{{ route('contracts') }}" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-list"></i>
                                        {{ trans("navigation/mainnav.contracts") }}
                                    </a>
                                </li>
                                <li class="{{ Request::is('countries*') ? 'active' : null }}">
                                    <a href="{{ route('countries') }}" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-flag"></i>
                                        {{ trans("navigation/mainnav.countries") }}
                                    </a>
                                </li>
                                <li class="{{ Request::is('calendars') ? 'active' : null }}">
                                    <a href="{{ route('calendars') }}" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-calendar"></i>
                                        {{ trans("navigation/mainnav.calendars") }}
                                    </a>
                                </li>
                                <li class="{{ Request::is('work-categories') ? 'active' : null }}">
                                    <a href="{{ route('workCategories.index') }}" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-wrench"></i>
                                        {{ trans("navigation/mainnav.workCategories") }}
                                    </a>
                                </li>
                                <li class="{{ Request::is('my_company_profiles') ? 'active' : null }}">
                                    <a href="{{ route('myCompanyProfiles.edit') }}" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-briefcase"></i>
                                        {{ trans("navigation/mainnav.myCompProfile") }}
                                    </a>
                                </li>
                                <li class="{{ Request::is('procurement-methods*') ? 'active' : null }}">
                                    <a href="{{ route('procurement-methods.index') }}" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-cube"></i>
                                        {{ trans("navigation/mainnav.procurementMethods") }}
                                    </a>
                                </li>
                                <li class="{{ (Request::is('apportionment_types')) ? 'active' : null }}">
                                    <a href="{{ route('apportionment.types.index') }}" class="text-truncate">
                                    <i class="fa fa-list-alt"></i>
                                        {{ trans("navigation/mainnav.apportionmentTypes") }}
                                    </a>
                                </li>
                                <li class="{{ (Request::is('cost-data-types')) ? 'active' : null }}">
                                    <a href="{{ route('costDataTypes.index') }}" class="text-truncate">
                                    <i class="fa fa-list"></i>
                                        {{ trans("navigation/mainnav.costDataTypes") }}
                                    </a>
                                </li>

                                <li class="{{ Request::is('settings*') ? 'active' : null }}">
                                    <a href="javascript:void(0);" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-cogs"></i>
                                        {{ trans("navigation/mainnav.settings") }}
                                    </a>
                                    <ul>
                                        <li class="{{ Request::is('general-settings') ? 'active' : null }}">
                                            <a href="{{ route('general_settings.index') }}" class="text-truncate">
                                                <i class="fa fa-sm fa-cog"></i>
                                                {{ trans("navigation/mainnav.generalSettings") }}
                                            </a>
                                        </li>
                                        <li class="{{ (Request::is('settings')) ? 'active' : null }}">
                                            <a href="{{ route('user.settings.edit') }}" class="text-truncate">
                                                <i class="fa fa-sm fa-language"></i>
                                                {{ trans("navigation/mainnav.language") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('theme-settings*') ? 'active' : '' }}">
                                            <a href="{{ route('theme.settings.edit') }}" class="text-truncate">
                                                <i class="fa fa-sm fa-th-large"></i>
                                                {{ trans("navigation/mainnav.theme") }}
                                            </a>
                                        </li>
                                        <li class="{{ (Request::is('email-settings*')) ? 'active' : null }}">
                                            <a href="{{ route('email.setttings.edit') }}" class="text-truncate">
                                                <i class="far fa-envelope"></i>
                                                {{ trans("navigation/mainnav.email") }}
                                            </a>
                                        </li>
                                        <li class="{{ (Request::is('payment-gateway/settings*')) ? 'active' : null }}">
                                            <a href="{{ route('payment-gateway.settings.edit') }}" class="text-truncate">
                                                <i class="far fa-credit-card"></i>
                                                {{ trans("navigation/mainnav.paymentGatewaySettings") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('scheduled_maintenance*') ? 'active' : '' }}">
                                            <a href="{{ route('scheduled_maintenance.index') }}" class="text-truncate">
                                                <i class="fa fa-sm fa-calendar"></i>
                                                {{ trans("navigation/mainnav.scheduledMaintenance") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('email_announcements*') ? 'active' : '' }}">
                                            <a href="{{ route('email_announcements.main') }}" class="text-truncate">
                                                <i class="fa fa-sm fa-bullhorn"></i>
                                                {{ trans("navigation/mainnav.emailAnnouncement") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('open_tender_news*') ? 'active' : '' }}">
                                            <a href="{{ route('open_tender_news.index') }}" class="text-truncate">
                                                <i class="fa fa-sm fa-newspaper"></i>
                                                {{ trans("navigation/mainnav.openTenderNews") }}
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('open_tender_banners*') ? 'active' : '' }}">
                                            <a href="{{ route('open_tender_banners.index') }}" class="text-truncate">
                                                <i class="fa fa-sm fa-image"></i>
                                                {{ trans("navigation/mainnav.openTenderBanners") }}
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @else
                                <li class="{{ Request::is('settings*') ? 'active' : null }}">
                                    <a href="javascript:void(0);" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-cogs"></i>
                                        {{ trans("navigation/mainnav.settings") }}
                                    </a>
                                    <ul>
                                        <li class="{{ (Request::is('settings')) ? 'active' : null }}">
                                            <a href="{{ route('user.settings.edit') }}" class="text-truncate">
                                                <i class="fa fa-sm fa-language"></i>
                                                {{ trans("navigation/mainnav.language") }}
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="{{ (Request::is('companies/my_company') or Request::is('companies/'.$user->company_id.'/edit')) ? 'active' : null }}">
                                    <a href="{{ route('companies.profile') }}" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-university"></i>
                                        {{ trans("navigation/mainnav.myCompany") }}
                                    </a>
                                </li>
                                @if ( $user->isGroupAdmin() )
                                    <li class="{{ Request::is('companies/'.$user->company_id.'/users') ? 'active' : null }}">
                                        <a href="{{ route('companies.users', array($user->company_id)) }}" class="text-truncate">
                                            <i class="fa fa-sm fa-fw fa-list"></i>
                                            {{ trans("navigation/mainnav.manageUsers") }}
                                        </a>
                                    </li>
                                @endif
                                @if($user->hasCompanyRoles(array( PCK\ContractGroups\Types\Role::PROJECT_OWNER )) && $user->isGroupAdmin())
                                    <li class="{{ Request::is('subsidiaries') ? 'active' : null }}">
                                        <a href="{{ route('subsidiaries.index') }}" class="text-truncate">
                                            <i class="fa fa-sm fa-fw fa-cubes"></i>
                                            {{ trans("navigation/mainnav.subsidiaries") }}
                                        </a>
                                    </li>
                                @endif
                                @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_DEFECTS))
                                    <li class="{{ Request::is('defect-categories') ? 'active' : null }}">
                                        <a href="{{ route('defect-categories') }}" class="text-truncate">
                                            <i class="fa fa-sm fa-fw fa-tools"></i>
                                            {{ trans("navigation/mainnav.defects") }}
                                        </a>
                                    </li>
                                @endif
                                @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_RFV_CATEGORY))
                                    <li class="{{ Request::is('rfv-categories*') ? 'active' : null }}">
                                        <a href="{{ route('requestForVariation.categories.index') }}">
                                            <i class="fa fa-sm fa-fw fa-file-alt"></i>
                                            {{ trans('modulePermissions.rfvCategories') }}
                                        </a>
                                    </li>
                                @endif
                                @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_WEATHERS))
                                    <li class="{{ Request::is('weathers') ? 'active' : null }}">
                                        <a href="{{ route('weathers') }}" class="text-truncate">
                                            <i class="fa fa-sm fa-cloud"></i>
                                            {{ trans("navigation/mainnav.weathers") }}
                                        </a>
                                    </li>
                                @endif
                                <li>
                                    <a href="javascript:void(0);" title="{{ trans('navigation/mainnav.siteDiary') }}" class="text-truncate">
                                        <i class="fa fa-lg fa-fw fa-book"></i>
                                        <span class="menu-item-parent">{{ trans("navigation/mainnav.siteDiary") }}</span>
                                    </a>
                                    <ul>
                                        @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_SITE_DIARY_MAINTENANCE))
                                        <li class="{{ Request::is('projects/'.$currentProjectId.'/rejected_materials') ? 'active' : null }}">
                                            <a href="{{ route('rejected-materials.index') }}" class="text-truncate">
                                                <i class="fa fa-sm fa-tools"></i>
                                                {{ trans("navigation/mainnav.rejected_materials") }}
                                            </a>
                                        </li>
                                        @endif
                                        @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_SITE_DIARY_MAINTENANCE))
                                        <li class="{{ Request::is('projects/'.$currentProjectId.'/labours') ? 'active' : null }}">
                                            <a href="{{ route('labours.index') }}" class="text-truncate">
                                                <i class="fa fa-sm fa-users"></i>
                                                {{trans("navigation/mainnav.labours")}}
                                            </a>
                                        </li>
                                        @endif
                                        @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_SITE_DIARY_MAINTENANCE))
                                        <li class="{{ Request::is('projects/'.$currentProjectId.'/machinery') ? 'active' : null }}">
                                            <a href="{{ route('machinery.index') }}" class="text-truncate">
                                            <i class="fa fa-sm fa-truck-pickup"></i>
                                                {{trans("navigation/mainnav.machinery")}}
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </li>
                                @if(\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT) && $currentUser->company->vendorRegistration)
                                <li>
                                    <a href="javascript:void(0);" class="text-truncate" title='{{ trans("vendorManagement.registration") }}'>
                                        <i class="fa fa-sm fa-fw fa-clone"></i>
                                        {{ trans("vendorManagement.registration") }}
                                    </a>
                                    <ul>
                                        <li class="{{ Request::is('vendor-registration*') ? 'active' : null }}">
                                            <a href="{{ route('vendors.vendorRegistration.index') }}" title='{{ trans("vendorManagement.overview") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-user-secret"></i>
                                                {{ trans("vendorManagement.overview") }}
                                            </a>
                                        </li>
                                        @if($currentUser->company->finalVendorRegistration && $currentUser->company->vendorProfile)
                                        <li class="{{ Request::is('vendor-profiles*') ? 'active' : null }}">
                                            <a href="{{ route('vendorProfile.show', array($currentUser->company_id)) }}" title='{{ trans("vendorProfile.vendorProfile") }}' class="text-truncate">
                                                <i class="fa fa-sm fa-user-secret"></i>
                                                {{ trans("vendorProfile.vendorProfile") }}
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </li>
                                @endif
                            @endif
                            @if($user->isSuperAdmin() && (!$licensingDisabled))
                            <li class="{{ (Request::is('license')) ? 'active' : null }}">
                                <a href="{{ route('license.index') }}" class="text-truncate">
                                    <i class="fa fa-sm fa-id-card"></i>
                                    {{ trans("navigation/mainnav.licensing") }}
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    @if(\PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_ORDERS))
                        <li class="{{ Request::is('order') ? 'active' : null }}">
                            <a href="{{ route('order.index') }}" title="{{ trans('navigation/mainnav.orders') }}" class="text-truncate">
                                <i class="fa fa-lg fa-fw fa-th-list"></i>
                                <span class="menu-item-parent">{{ trans("navigation/mainnav.orders") }}</span>
                            </a>
                        </li>
                    @endif

                    @if( $project && $user->getAssignedCompany($project) )
                        <li class="{{ Request::is('projects/'.$currentProjectId) ? 'active' : null }}">
                            <a href="javascript:void(0);" title="{{ trans('navigation/projectnav.projectDashboard') }}" class="text-truncate">
                                <i class="fa fa-lg fa-fw fa-tachometer-alt"></i>
                                <span class="menu-item-parent">{{ trans("navigation/projectnav.projectDashboard") }}</span>
                            </a>
                            <ul>
                                <li class="{{ (Request::is('projects/'.$currentProjectId) || Request::is('projects/'.$currentProjectId.'/post-contract-info-edit')) ? 'active' : null }}">
                                    <a href="{{ route('projects.show', array($currentProjectId)) }}" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-tachometer-alt"></i>
                                        {{ trans("navigation/projectnav.projectDashboard") }}
                                    </a>
                                </li>
                                @if(!$user->hasCompanyProjectRole($project, \PCK\ContractGroups\Types\Role::CONTRACTOR))
                                    @if($project->isMainProject())
                                        <li class="{{ Request::is('projects/'.$currentProjectId.'/sub-packages*') ? 'active' : null }}">
                                            <a href="{{ route('projects.subPackages.index', array( $project->id )) }}" class="text-truncate">
                                                <i class="fa fa-sm fa-fw fa-gift"></i>
                                                {{ trans("navigation/projectnav.subPackages") }}
                                            </a>
                                        </li>
                                    @else
                                        <li>
                                            <a href="{{ route('projects.show', array($project->parent_project_id)) }}" class="text-truncate">
                                                <i class="fa fa-sm fa-fw fa-anchor"></i>
                                                {{ trans("navigation/projectnav.mainProject") }}
                                            </a>
                                        </li>
                                    @endif
                                @endif

                                @if ( $user->company && ($user->hasCompanyProjectRole($project, array(\PCK\ContractGroups\Types\Role::PROJECT_OWNER, \PCK\ContractGroups\Types\Role::GROUP_CONTRACT))) and $user->isGroupAdmin())
                                    <li class="{{ Request::is('projects/'.$currentProjectId.'/assign_companies') ? 'active' : null }}">
                                        <a href="{{ route('projects.company.assignment', array($currentProjectId)) }}" class="text-truncate">
                                            <i class="fa fa-sm fa-fw fa-university"></i>
                                            {{ trans("navigation/projectnav.assignCompany") }}
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                        <li>
                            <a href="javascript:void(0);" title="{{ trans('navigation/projectnav.users') }}" class="text-truncate">
                                <i class="fa fa-lg fa-fw fa-users"></i>
                                <span class="menu-item-parent">{{ trans("navigation/projectnav.users") }}</span>
                            </a>
                            <ul>
                                @if ( $user->isGroupAdmin()
                                && $user->company
                                && ((!$user->hasCompanyProjectRole($project, PCK\ContractGroups\Types\Role::CONTRACTOR))
                                    || ($user->hasCompanyProjectRole($project, \PCK\ContractGroups\Types\Role::CONTRACTOR)
                                        && !in_array($project->status_id, PCK\Projects\Project::tenderingStagesStatus()))) )
                                    <li class="{{ Request::is('projects/'.$currentProjectId.'/assign_users') ? 'active' : null }}">
                                        <a href="{{ route('projects.assignUsers', array($currentProjectId)) }}" class="text-truncate">
                                            {{ trans("navigation/projectnav.projectUsers") }}
                                        </a>
                                    </li>
                                @endif
                                @if(\PCK\IndonesiaCivilContract\UserPermission::isUserPermissionManager($project, $currentUser))
                                    <li class="{{ Request::is('*/indonesia-civil-contract/user-permissions*') ? 'active' : null }}">
                                        <a href="{{ route('indonesiaCivilContract.permissions.index', array($project->id)) }}" class="text-truncate">
                                            {{ trans("navigation/projectnav.indonesiaCivilContract") }}
                                        </a>
                                    </li>
                                @endif
                                @if(\PCK\ContractManagementModule\UserPermission\ContractManagementUserPermission::isUserManager($currentUser, $project))
                                    <li class="{{ Request::is('projects/'.$currentProjectId.'/contract-management/permissions*') ? 'active' : null }}">
                                        <a href="{{ route('contractManagement.permissions.index', array($project->id)) }}" class="text-truncate">
                                            {{ trans('contractManagement.contractManagement') }}
                                        </a>
                                    </li>
                                @endif
                                @if ($isAdminUserOfProjectOwnerOrGCD && !$user->isSuperAdmin() && ($project->status_id == PCK\Projects\Project::STATUS_TYPE_POST_CONTRACT))
                                    <li class="{{ Request::is('projects/'.$currentProjectId.'/request-for-variation/user_permission*') ? 'active' : null }}">
                                        <a href="{{ route('requestForVariation.user.permissions.index', array($project->id)) }}" class="text-truncate" >
                                            {{ trans('requestForVariation.requestForVariation') }}
                                        </a>
                                    </li>
                                @endif
                                @if($project->isMainProject() && $buCompany && in_array($user->id, $buCompany->getActiveUsers()->lists('id')) && $user->isGroupAdmin())
                                    <li class="{{ Request::is('projects/'.$currentProjectId.'/site-management/permissions') ? 'active' : null }}">
                                        <a href="{{ route('site-management.permissions.index', array($project->id)) }}" class="text-truncate">
                                            {{ trans("siteManagement.site_management") }}
                                        </a>
                                    </li>
                                @endif
                                @if ($isAdminUserOfProjectOwnerOrGCD_LA)
                                    <li class="{{ Request::is('projects/'.$currentProjectId.'/letter-of-award/user_permission*') ? 'active' : null }}">
                                        <a href="{{ route('letterOfAward.user.permissions.index', array($project->id)) }}" class="text-truncate">
                                            {{ trans('letterOfAward.letterOfAward') }}
                                        </a>
                                    </li>
                                @endif
                                @if(\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_INPSECTION) && \PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_INSPECTION))
                                    <li class="{{ Request::is('projects/'.$currentProjectId.'/inspection/user-management') ? 'active' : null }}">
                                        <a href="{{ route('inspection.userManagement', array($project->id)) }}" class="text-truncate">
                                            {{ trans('inspection.inspection') }}
                                        </a>
                                    </li>
                                @endif
                                @if($buCompany && $buCompany->isCompanyAdmin($user) && in_array($user->id, $buCompany->getActiveUsers()->lists('id')))
                                    <li class="{{ Request::is('projects/'.$currentProjectId.'/project_report/user_permissions*') ? 'active' : null }}">
                                        <a href="{{ route('projectReport.userPermissions.index', array($project->id)) }}" class="text-truncate">
                                            {{ trans('navigation/mainnav.projectReport') }}
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                        <li>
                            <a href="javascript:void(0);" title="{{ trans('navigation/projectnav.tendering') }}" class="text-truncate">
                                <i class="fa fa-lg fa-fw fa-gavel"></i>
                                <span class="menu-item-parent">{{ trans("navigation/projectnav.tendering") }}</span>
                            </a>
                            <ul>
                                @include('layout.partials.rec_of_tenderer_menu')

                                @if($user->company)
                                    @if((! $project->latestTender->isFirstTender()) || $project->showOpenTender())
                                        @if ($user->hasCompanyProjectRole($project, PCK\Filters\OpenTenderFilters::accessRoles($project)))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/open_tenders*') ? 'active' : null }}">
                                                <a href="{{ route('projects.openTender.index', array($currentProjectId)) }}" title="{{{ trans('navigation/projectnav.openTender') }}}" class="text-truncate">
                                                    <i class="fa fa-sm fa-fw fa-star"></i>
                                                    {{ trans("navigation/projectnav.openTender") }}
                                                </a>
                                            </li>
                                        @endif
                                        @if($project->e_bidding == "true")
                                            @if (($project->isEbiddingApproved() && $user->isEbiddingCommittee($project->id))
                                                || $user->isEditor($project)
                                                || $user->isCurrentVerifier($project->id)
                                            )
                                                <li class="{{ Request::is('projects/'.$currentProjectId.'/e_bidding*') ? 'active' : null }}">
                                                    <a href="{{ route('projects.e_bidding.index', array($currentProjectId)) }}" title="{{{ trans('navigation/projectnav.eBidding') }}}" class="text-truncate">
                                                        <i class="fas fa-sm fa-gavel"></i>
                                                        {{ trans("navigation/projectnav.eBidding") }}
                                                    </a>
                                                </li>
                                            @endif
                                        @endif
                                        @if ($project->technicalEvaluationEnabled() && $user->hasCompanyProjectRole($project, \PCK\Filters\TechnicalEvaluationFilters::accessRoles()))
                                            <li class="{{ Request::is('*/technical-evaluation/results/*') ? 'active' : null }}">
                                                <a href="{{ route('technicalEvaluation.results.index', array($currentProjectId)) }}" title="{{{ trans('navigation/projectnav.technicalOpening') }}}" class="text-truncate">
                                                    <i class="fa fa-sm fa-fw fa-balance-scale"></i>
                                                    {{ trans("navigation/projectnav.technicalOpening") }}
                                                </a>
                                            </li>
                                        @endif
                                    @endif
                                @endif
                                @if ($isUserAssignedToLetterOfAward)
                                <li class="{{ Request::is('projects/'.$currentProjectId.'/letter-of-award/letterOfAward*') ? 'active' : null }}">
                                    <a href="{{ route('letterOfAward.index', array($project->id)) }}" title="{{{ trans('letterOfAward.letterOfAward') }}}" class="text-truncate">
                                        <i class="far fa-sm fa-fw fa-file-alt"></i>
                                        {{ trans('letterOfAward.letterOfAward') }}
                                    </a>
                                </li>
                                @endif
                                @include('layout.partials.contractor_questionnaire_menu')
                            </ul>
                        </li>
                        @if($user->hasCompanyProjectRole($project, PCK\ContractGroups\Types\Role::CONTRACTOR) && $user->isGroupAdmin() && $project->showOpenTender())
                            <li class="{{ Request::is('projects/'.$currentProjectId.'/submit_tender*') ? 'active' : null }}">
                                <a href="{{ route('projects.submitTender', array($currentProjectId)) }}" class="text-truncate">
                                    <i class="fa fa-sm fa-fw fa-upload"></i>
                                    {{ trans("navigation/projectnav.submitTender") }}
                                </a>
                            </li>
                        @endif
                        <li>
                            <a href="javascript:void(0);" title="{{ trans('navigation/projectnav.postContract') }}" class="text-truncate">
                                <i class="fa fa-lg fa-fw fa-handshake"></i>
                                <span class="menu-item-parent">{{ trans("navigation/projectnav.postContract") }}</span>
                            </a>
                            <ul>
                                @if($project->isPostContract() && $currentUser->hasCompanyProjectRole($project, $project->contractorClaimAccessGroups()))
                                    <li class="{{ Request::is('projects/'.$currentProjectId.'/submit-claims') ? 'active' : null }}">
                                        <a href="{{ route('projects.contractorClaims', array($currentProjectId)) }}" class="text-truncate">
                                            <i class="fa fa-sm fa-fw fa-upload"></i>
                                            {{ trans("navigation/projectnav.submitClaims") }}
                                        </a>
                                    </li>
                                @endif
                                <li>
                                    <a href="javascript:void(0);" class="text-truncate">
                                        <i class="far fa-sm fa-fw fa-file-alt"></i>
                                        {{ trans("contractManagement.contractManagement") }}
                                    </a>

                                    <ul>
                                        @if(\PCK\ContractManagementModule\UserPermission\ContractManagementUserPermission::isAssigned(PCK\Buildspace\PostContractClaim::TYPE_LETTER_OF_AWARD, $currentUser, $project))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/contract-management/letter-of-award') ? 'active' : null }}">
                                                <a href="{{ route('contractManagement.letterOfAward.index', array($project->id)) }}" class="text-truncate">
                                                    <i class="fa fa-sm fa-fw fa-envelope"></i>
                                                    {{ trans('contractManagement.publishToPostContract') }}
                                                    <span class="badge bg-color-yellow pull-right">{{ count($pendingContractManagementReviews[PCK\Buildspace\PostContractClaim::TYPE_LETTER_OF_AWARD] ?? array()) }}</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if(\PCK\ContractManagementModule\UserPermission\ContractManagementUserPermission::isAssigned(PCK\Buildspace\PostContractClaim::TYPE_CLAIM_CERTIFICATE, $currentUser, $project))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/contract-management/claim-certificate') ? 'active' : null }}">
                                                <a href="{{ route('contractManagement.claimCertificate.index', array($project->id)) }}" class="text-truncate">
                                                    <i class="fa fa-sm fa-fw fa-certificate"></i>
                                                    {{ trans('contractManagement.claimCertificate') }}
                                                    <span class="badge bg-color-yellow pull-right">{{ count($pendingContractManagementReviews[PCK\Buildspace\PostContractClaim::TYPE_CLAIM_CERTIFICATE] ?? array()) }}</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if(\PCK\ContractManagementModule\UserPermission\ContractManagementUserPermission::isAssigned(PCK\Buildspace\PostContractClaim::TYPE_VARIATION_ORDER, $currentUser, $project))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/contract-management/variation-order') ? 'active' : null }}">
                                                <a href="{{ route('contractManagement.variationOrder.index', array($project->id)) }}" class="text-truncate">
                                                    <i class="fa fa-sm fa-fw fa-exchange-alt"></i>
                                                    {{ trans('contractManagement.variationOrder') }}
                                                    <span class="badge bg-color-yellow pull-right">{{ count($pendingContractManagementReviews[PCK\Buildspace\PostContractClaim::TYPE_VARIATION_ORDER] ?? array()) }}</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if(\PCK\ContractManagementModule\UserPermission\ContractManagementUserPermission::isAssigned(PCK\Buildspace\PostContractClaim::TYPE_ADVANCED_PAYMENT, $currentUser, $project))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/contract-management/advanced-payment') ? 'active' : null }}">
                                                <a href="{{ route('contractManagement.advancedPayment.index', array($project->id)) }}" class="text-truncate">
                                                    <i class="fa fa-sm fa-fw fa-fast-forward"></i>
                                                    {{ trans('navigation/mainnav.advancedPayment') }}
                                                    <span class="badge bg-color-yellow pull-right">{{ count($pendingContractManagementReviews[PCK\Buildspace\PostContractClaim::TYPE_ADVANCED_PAYMENT] ?? array()) }}</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if(\PCK\ContractManagementModule\UserPermission\ContractManagementUserPermission::isAssigned(PCK\Buildspace\PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE, $currentUser, $project))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/contract-management/material-on-site') ? 'active' : null }}">
                                                <a href="{{ route('contractManagement.materialOnSite.index', array($project->id)) }}" class="text-truncate">
                                                    <i class="fa fa-sm fa-fw fa-cubes"></i>
                                                    {{ trans('contractManagement.materialOnSite') }}
                                                    <span class="badge bg-color-yellow pull-right">{{ count($pendingContractManagementReviews[PCK\Buildspace\PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE] ?? array()) }}</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if(\PCK\ContractManagementModule\UserPermission\ContractManagementUserPermission::isAssigned(PCK\Buildspace\PostContractClaim::TYPE_DEPOSIT, $currentUser, $project))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/contract-management/deposit') ? 'active' : null }}">
                                                <a href="{{ route('contractManagement.deposit.index', array($project->id)) }}" class="text-truncate">
                                                    <i class="fa fa-sm fa-fw fa-arrow-left"></i>
                                                    {{ trans('contractManagement.deposit') }}
                                                    <span class="badge bg-color-yellow pull-right">{{ count($pendingContractManagementReviews[PCK\Buildspace\PostContractClaim::TYPE_DEPOSIT] ?? array()) }}</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if(\PCK\ContractManagementModule\UserPermission\ContractManagementUserPermission::isAssigned(PCK\Buildspace\PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM, $currentUser, $project))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/contract-management/out-of-contract-items') ? 'active' : null }}">
                                                <a href="{{ route('contractManagement.outOfContractItems.index', array($project->id)) }}" class="text-truncate">
                                                    <i class="fa fa-sm fa-fw fa-user-secret"></i>
                                                    {{ trans('contractManagement.outOfContractItems') }}
                                                    <span class="badge bg-color-yellow pull-right">{{ count($pendingContractManagementReviews[PCK\Buildspace\PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM] ?? array()) }}</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if(\PCK\ContractManagementModule\UserPermission\ContractManagementUserPermission::isAssigned(PCK\Buildspace\PostContractClaim::TYPE_WORK_ON_BEHALF, $currentUser, $project))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/contract-management/work-on-behalf') ? 'active' : null }}">
                                                <a href="{{ route('contractManagement.workOnBehalf.index', array($project->id)) }}" class="text-truncate">
                                                    <i class="fa fa-sm fa-fw fa-wrench"></i>
                                                    {{ trans('contractManagement.workOnBehalf') }}
                                                    <span class="badge bg-color-yellow pull-right">{{ count($pendingContractManagementReviews[PCK\Buildspace\PostContractClaim::TYPE_WORK_ON_BEHALF] ?? array()) }}</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if(\PCK\ContractManagementModule\UserPermission\ContractManagementUserPermission::isAssigned(PCK\Buildspace\PostContractClaim::TYPE_PURCHASE_ON_BEHALF, $currentUser, $project))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/contract-management/purchase-on-behalf') ? 'active' : null }}">
                                                <a href="{{ route('contractManagement.purchaseOnBehalf.index', array($project->id)) }}" class="text-truncate">
                                                    <i class="fa fa-sm fa-fw fa-shopping-bag"></i>
                                                    {{ trans('navigation/mainnav.purchaseOnBehalf') }}
                                                    <span class="badge bg-color-yellow pull-right">{{ count($pendingContractManagementReviews[PCK\Buildspace\PostContractClaim::TYPE_PURCHASE_ON_BEHALF] ?? array()) }}</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if(\PCK\ContractManagementModule\UserPermission\ContractManagementUserPermission::isAssigned(PCK\Buildspace\PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE, $currentUser, $project))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/contract-management/work-on-behalf-back-charge') ? 'active' : null }}">
                                                <a href="{{ route('contractManagement.workOnBehalfBackCharge.index', array($project->id)) }}" class="text-truncate">
                                                    </i><i class="fa fa-sm fa-fw fa-wrench"></i>
                                                    {{ trans('navigation/mainnav.workOnBehalfBackCharge') }}
                                                    <span class="badge bg-color-yellow pull-right">{{ count($pendingContractManagementReviews[PCK\Buildspace\PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE] ?? array()) }}</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if(\PCK\ContractManagementModule\UserPermission\ContractManagementUserPermission::isAssigned(PCK\Buildspace\PostContractClaim::TYPE_PENALTY, $currentUser, $project))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/contract-management/penalty') ? 'active' : null }}">
                                                <a href="{{ route('contractManagement.penalty.index', array($project->id)) }}" class="text-truncate">
                                                    <i class="fa fa-sm fa-fw fa-gavel"></i>
                                                    {{ trans('contractManagement.penalty') }}
                                                    <span class="badge bg-color-yellow pull-right">{{ count($pendingContractManagementReviews[PCK\Buildspace\PostContractClaim::TYPE_PENALTY] ?? array()) }}</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if(\PCK\ContractManagementModule\UserPermission\ContractManagementUserPermission::isAssigned(PCK\Buildspace\PostContractClaim::TYPE_WATER_DEPOSIT, $currentUser, $project))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/contract-management/water-deposit') ? 'active' : null }}">
                                                <a href="{{ route('contractManagement.waterDeposit.index', array($project->id)) }}" class="text-truncate">
                                                    <i class="fa fa-sm fa-fw fa-tint"></i>
                                                    {{ trans('contractManagement.utilities') }}
                                                    <span class="badge bg-color-yellow pull-right">{{ count($pendingContractManagementReviews[PCK\Buildspace\PostContractClaim::TYPE_WATER_DEPOSIT] ?? array()) }}</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if(\PCK\ContractManagementModule\UserPermission\ContractManagementUserPermission::isAssigned(PCK\Buildspace\PostContractClaim::TYPE_PERMIT, $currentUser, $project))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/contract-management/permit') ? 'active' : null }}">
                                                <a href="{{ route('contractManagement.permit.index', array($project->id)) }}" class="text-truncate">
                                                    <i class="far fa-sm fa-fw fa-file-alt"></i>
                                                    {{ trans('contractManagement.permit') }}
                                                    <span class="badge bg-color-yellow pull-right">{{ count($pendingContractManagementReviews[PCK\Buildspace\PostContractClaim::TYPE_PERMIT] ?? array()) }}</span>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="text-truncate">
                                        <i class="fa fa-sm fa-fw fa-gavel"></i>
                                        {{ trans("navigation/projectnav.contractualClaim") }}
                                    </a>
                                    <ul>
                                        @foreach ($postContractMenu ?? array() as $menu)
                                            <?php $html = route($menu->route_name, array( $currentProjectId )); $segment = Request::segment(4); ?>

                                            <?php $class = strpos($html, $segment) ? 'active' : null; ?>

                                            @if (is_numeric($segment))
                                                <?php $class = null; ?>
                                            @endif

                                            <li class="{{{ $class }}}">
                                                <a href="{{ ! empty($menu->route_name) ? $html : "javascript:void(0);" }}" class="text-truncate">
                                                    @if(strlen(trans("navigation/projectnav.{$menu->name}")) > 18)
                                                        {{ trans("navigation/projectnav.{$menu->name}-short") }}
                                                    @else
                                                        {{ trans("navigation/projectnav.{$menu->name}") }}
                                                    @endif
                                                    <span class="badge bg-color-green pull-right inbox-badge">
                                                        {{ $contractualClaimCount[$menu->name] }}
                                                    </span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                                @if ($isProjectOwnnerOrConsultant && $isUserAssignedToRFV && (!$user->isSuperAdmin() && ($project->status_id == PCK\Projects\Project::STATUS_TYPE_POST_CONTRACT)))
                                    <li class="{{ Request::is('projects/'.$currentProjectId.'/request-for-variation/rfv-form*') ? 'active' : null }}">
                                        <a href="{{ route('requestForVariation.index', array($project->id)) }}" class="text-truncate">
                                            <i class="fa fa-lg fa-fw fa-table"></i>
                                            {{ trans('requestForVariation.requestForVariation') }}
                                            @if ($approvedRfvCount > 0)
                                                <span class="badge bg-color-orange pull-right inbox-badge">
                                                    {{ $approvedRfvCount }}
                                                </span>
                                            @endif
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                        <li>
                            <a href="javascript:void(0);" title="{{ trans('navigation/projectnav.siteModules') }}" class="text-truncate">
                                <i class="fa fa-lg fa-fw fa-sitemap"></i>
                                <span class="menu-item-parent">{{ trans("navigation/projectnav.siteModules") }}</span>
                            </a>
                            <ul>
                                <li>
                                    <a href="javascript:void(0);" class="text-truncate">
                                        <i class="fa fa-sm fa-building"></i>
                                        {{ trans("navigation/projectnav.siteManagement") }}
                                    </a>
                                    <ul>
                                        @if(PCK\SiteManagement\SiteManagementUserPermission::isAssigned(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $currentUser, $project)||
                                            PCK\SiteManagement\SiteManagementUserPermission::isProjectAssignedContractor($currentUser,$project))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/site-management/site-management-defect*') ? 'active' : null }}">
                                                <a href="{{ route('site-management-defect.index', array($project->id)) }}" class="text-truncate">
                                                    <i class="far fa-sm fa-fw fa-file-alt"></i>
                                                    {{ trans("siteManagement.defect") }}
                                                    &nbsp;
                                                    <span class="badge bg-color-green pull-right inbox-badge">
                                                        {{$countDefectListing}}
                                                    </span>
                                                </a>
                                            </li>
                                        @endif
                                        @if(PCK\SiteManagement\SiteManagementUserPermission::isViewer(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_SITE_DIARY, $currentUser, $project)||
                                            PCK\SiteManagement\SiteManagementUserPermission::isSubmitter(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_SITE_DIARY, $currentUser, $project)||
                                            PCK\SiteManagement\SiteManagementUserPermission::isVerifier(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_SITE_DIARY, $currentUser, $project)||
                                            PCK\SiteManagement\SiteManagementUserPermission::isProjectAssignedContractor($currentUser,$project))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/site-management/site-management-site-diary*') ? 'active' : null }}">
                                                <a href="{{ route('site-management-site-diary.index', array($project->id)) }}" class="text-truncate">
                                                    <i class="far fa-sm fa-fw fa-file-alt"></i>
                                                    {{ trans("siteManagement.site_diary") }}
                                                    &nbsp;
                                                    <span class="badge bg-color-green pull-right inbox-badge">
                                                        {{$countSiteDiaryListing}}
                                                    </span>
                                                </a>
                                            </li>
                                        @endif
                                        @if(PCK\SiteManagement\SiteManagementUserPermission::isAssigned(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_LABOUR_REPORTS, $currentUser, $project)||
                                            PCK\SiteManagement\SiteManagementUserPermission::isProjectAssignedContractor($currentUser,$project))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/site-management/daily-labour-report') ? 'active' : null }}">
                                                <a href="{{ route('daily-labour-report.index', array($project->id)) }}" class="text-truncate">
                                                    <i class="far fa-sm fa-fw fa-file-alt"></i>
                                                    {{ trans("siteManagement.daily_labour_reports") }}
                                                    &nbsp;
                                                    <span class="badge bg-color-green pull-right inbox-badge">
                                                        {{$countDailyLabourReportListing}}
                                                    </span>
                                                </a>
                                            </li>
                                        @endif
                                        @if(PCK\SiteManagement\SiteManagementUserPermission::isViewer(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_INSTRUCTION_TO_CONTRACTOR, $currentUser, $project)||
                                            PCK\SiteManagement\SiteManagementUserPermission::isSubmitter(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_INSTRUCTION_TO_CONTRACTOR, $currentUser, $project)||
                                            PCK\SiteManagement\SiteManagementUserPermission::isVerifier(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_INSTRUCTION_TO_CONTRACTOR, $currentUser, $project)||
                                            PCK\SiteManagement\SiteManagementUserPermission::isProjectAssignedContractor($currentUser,$project))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/site-management/instruction-to-contractor') ? 'active' : null }}">
                                                <a href="{{ route('instruction-to-contractor.index', array($project->id)) }}" class="text-truncate">
                                                    <i class="far fa-sm fa-fw fa-file-alt"></i>
                                                    {{ trans("siteManagement.instruction_to_contractor") }}
                                                    &nbsp;
                                                    <span class="badge bg-color-green pull-right inbox-badge">
                                                        {{$countInstructionsToContractorListing}}
                                                    </span>
                                                </a>
                                            </li>
                                        @endif
                                        @if(PCK\SiteManagement\SiteManagementUserPermission::isAssigned(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DAILY_REPORT, $currentUser, $project)||
                                            PCK\SiteManagement\SiteManagementUserPermission::isProjectAssignedContractor($currentUser,$project))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/site-management/daily-report') ? 'active' : null }}">
                                                <a href="{{ route('daily-report.index', array($project->id)) }}" class="text-truncate">
                                                    <i class="far fa-sm fa-fw fa-file-alt"></i>
                                                    {{ trans("dailyreport.daily-report") }}
                                                    &nbsp;
                                                   <span class="badge bg-color-green pull-right inbox-badge">
                                                        {{$countDailyReportListing}}
                                                    </span>
                                                </a>
                                            </li>
                                        @endif
                                        <?php
                                        $bsProjectMainInformation = $project->getBsProjectMainInformation();
                                        ?>
                                        @if(PCK\SiteManagement\SiteManagementUserPermission::isAssigned(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_UPDATE_SITE_PROGRESS, $currentUser, $project) && $bsProjectMainInformation && $bsProjectMainInformation->status == PCK\Buildspace\ProjectMainInformation::STATUS_POSTCONTRACT)
                                            <li>
                                                <a href="{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_EPROJECT_SITE_PROGRESS . "&id={$project->id}" }}" class="text-truncate">
                                                    <i class="far fa-sm fa-fw fa-file-alt"></i>
                                                    {{ trans("siteManagement.update_site_progress") }}
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="text-truncate">
                                        <i class="fa fa-sm fa-building"></i>
                                        {{ trans("navigation/projectnav.inspection") }}
                                    </a>
                                    <ul>
                                        @if(\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_INPSECTION) && \PCK\ModulePermission\ModulePermission::hasPermission($currentUser, \PCK\ModulePermission\ModulePermission::MODULE_ID_INSPECTION))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/inspection-list') ? 'active' : null }}">
                                                <a href="{{ route('project.inspection.list.index', [$currentProjectId]) }}" class="text-truncate">
                                                    <i class="fa fa-sm fa-fw fa-thumbtack"></i>
                                                    {{ trans("inspection.inspectionLists") }}
                                                </a>
                                            </li>
                                        @endif
                                        @if(\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_INPSECTION) && \PCK\Filters\InspectionFilters::hasModuleAccess($project, $currentUser))
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/request-for-inspection*') ? 'active' : null }}">
                                                <a href="{{ route('inspection.request', array($currentProjectId)) }}" class="text-truncate">
                                                    <i class="fa fa-sm fa-fw fa-search"></i>
                                                    {{ trans("inspection.inspection") }}
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="javascript:void(0);" title="{{ trans('projectReport.projectReport') }}" class="text-truncate">
                                <i class="fa fa-lg fa-fw fa-file-lines"></i>
                                <span class="menu-item-parent">{{ trans("navigation/projectnav.projectReport") }}</span>
                            </a>
                            <ul>
                                @if(\PCK\ProjectReport\ProjectReportUserPermission::hasProjectReportPermission($project, $user, [
                                    \PCK\ProjectReport\ProjectReportUserPermission::IDENTIFIER_SUBMIT_REPORT,
                                    \PCK\ProjectReport\ProjectReportUserPermission::IDENTIFIER_VERIFY_REPORT
                                ]))
                                    <li class="{{ Request::is('projects/'.$currentProjectId.'/project_report/submit*') ? 'active' : null }}">
                                        <a href="{{ route('projectReport.index', [$project->id]) }}" class="text-truncate">
                                            <i class="fa fa-sm fa-fw fa-file-lines"></i> {{ trans('navigation/projectnav.submitProjectReport') }}
                                        </a>
                                    </li>
                                @endif
                                @if(\PCK\ProjectReport\ProjectReportUserPermission::hasProjectReportPermission($project, $user, [
                                    \PCK\ProjectReport\ProjectReportUserPermission::IDENTIFIER_EDIT_REMINDER,
                                ]))
                                    <li class="{{ Request::is('projects/'.$currentProjectId.'/project_report/notification*') ? 'active' : null }}">
                                        <a href="{{ route('projectReport.notification.reportTypes', [$project->id, 'permission_type' => 'reminder']) }}" class="text-truncate">
                                            <i class="fa fa-sm fa-fw fa-bell"></i> {{ trans('navigation/projectnav.projectReportReminder') }}
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                        <li>
                            <a href="javascript:void(0);" title="{{ trans('navigation/projectnav.documents') }}" class="text-truncate">
                                <i class="fa fa-lg fa-fw fa-folder-open"></i>
                                <span class="menu-item-parent">{{ trans("navigation/projectnav.documents") }}</span>
                            </a>
                            <ul>
                                @if ($user->isSuperAdmin() || $user->hasCompanyProjectRole($project, array(
                                        PCK\ContractGroups\Types\Role::GROUP_CONTRACT,
                                        PCK\ContractGroups\Types\Role::PROJECT_OWNER,
                                        $project->getCallingTenderRole(),
                                        PCK\ContractGroups\Types\Role::CONTRACTOR,
                                    )))
                                    <li class="{{ Request::is('projects/'.$currentProjectId.'/tenderDocument', 'projects/'.$currentProjectId.'/myTenderDocumentFolder*') ? 'active' : null }}">
                                        <a href="{{ route('projects.tenderDocument.index', array($currentProjectId)) }}" class="text-truncate">
                                            <i class="far fa-sm fa-fw fa-file-pdf"></i>
                                            {{ trans("navigation/projectnav.tenderDocuments") }}
                                        </a>
                                    </li>
                                @endif
                                {{-- Will not show to Contractor the Project Document Menu when in Tendering Stages, but will show to all once has been pushed to Post Contract --}}
                                @if( $documentManagementFolders && $user->company && (! $user->hasCompanyProjectRole($project, PCK\ContractGroups\Types\Role::CONTRACTOR) || $project->isPostContract()) )
                                    <li>
                                        <a href="javascript:void(0);" class="text-truncate">
                                            <i class="far fa-sm fa-fw fa-file-word"></i>
                                            {{ trans("navigation/projectnav.projectDocument") }}
                                        </a>

                                        <ul>
                                            @foreach ($documentManagementFolders as $folder)
                                                <li class="{{ $node && $node->root_id == $folder->id && Request::is('projects/'.$currentProjectId.'/projectDocument*', 'projects/'.$currentProjectId.'/myProjectDocumentFolder*') ? 'active' : null }}">
                                                    <a href="{{ route('projectDocument.index', array($project->id, $folder->id))}}" class="text-truncate">
                                                        {{ $folder->name }}
                                                        <span class="badge bg-color-green pull-right inbox-badge">
                                                            @if($user->getAssignedCompany($project))
                                                                {{ count($folder->getAllAccessibleFiles($user->getAssignedCompany($project)->getContractGroup($project))) }}
                                                            @else
                                                                0
                                                            @endif
                                                        </span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endif
                                @if($user->company && (! $user->hasCompanyProjectRole($project, PCK\ContractGroups\Types\Role::CONTRACTOR) || $project->isPostContract()))
                                    <li>
                                        <a href="javascript:void(0);" class="text-truncate">
                                            <i class="far fa-sm fa-fw fa-folder-open"></i>
                                            {{ trans("navigation/projectnav.documentControl") }}
                                        </a>

                                        <ul>
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/request-for-information*') ? 'active' : null }}">
                                                <a href="{{ route('requestForInformation.index', array($currentProjectId)) }}" class="text-truncate">
                                                    <i class="fa fa-sm fa-fw fa-comments"></i>
                                                    {{ trans("navigation/projectnav.requestForInformation") }}
                                                    <span class="badge bg-color-green pull-right inbox-badge">
                                                        {{ count($project->getVisibleRequestsForInformation()) }}
                                                    </span>
                                                </a>
                                            </li>
                                            <li class="{{ Request::is('projects/'.$currentProjectId.'/risk-register*') ? 'active' : null }}">
                                                <a href="{{ route('riskRegister.index', array($currentProjectId)) }}" class="text-truncate">
                                                    <i class="fa fa-sm fa-fw fa-exclamation-triangle"></i>
                                                    {{ trans("navigation/projectnav.riskRegister") }}
                                                    <span class="badge bg-color-green pull-right inbox-badge">
                                                        {{ count($project->getVisibleRiskRegisterRisks()) }}
                                                    </span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                @endif
                            </ul>
                        </li>
                        @if(!$currentUser->isSuperAdmin())
                            <li class="{{ Request::is('projects/'.$currentProjectId.'/forum/threads*') ? 'active' : null }}">
                                <a href="{{ route('forum.threads', array($currentProjectId)) }}" class="text-truncate">
                                    <i class="fa fa-lg fa-fw fa-thumbtack"></i>
                                    <span class="menu-item-parent">{{ trans("navigation/projectnav.forum") }}</span>
                                    @if($unreadThreads > 0)
                                        <span class="badge bg-color-orange pull-right inbox-badge">
                                            {{ $unreadThreads }}
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @endif
                    @endif
                    <li class="{{ Request::is('folders/*') ? 'active' : null }}">
                        <a href="{{ route('folders', array(0)) }}" class="text-truncate">
                            <i class="fa fa-lg fa-fw fa-hdd"></i> {{ trans("folders.eProjectDrive") }}
                        </a>
                    </li>
                    @if(!$project && $currentUser->isSuperAdmin())
                    <li class="{{ Request::is('api-v2/*') ? 'active' : null }}">
                        <a href="{{ route('api.v2.clients.index') }}" class="text-truncate" title='{{ trans("general.externalApplications") }}'>
                            <i class="fa fa-lg fa-fw fa-network-wired"></i>
                            API V2
                        </a>
                    </li>
                    @if($isVendorMigrationModeEnabled)
                    <li class="{{ Request::is('vm-vendor-migration*') ? 'active' : null }}">
                        <a href="{{ route('vm.vendor.migration.index') }}" class="text-truncate" title='{{ trans("vendorManagement.vmVendorMigration") }}'>
                            <i class="fa fa-lg fa-fw fa-users"></i>
                            {{ trans('vendorManagement.vmVendorMigration') }}
                        </a>
                    </li>
                    @endif
                    <li class="{{ Request::is('log') ? 'active' : null }}">
                        <a href="javascript:void(0);" class="text-truncate" title='{{ trans("general.logs") }}'>
                            <i class="fa fa-lg fa-fw fa-clipboard-list"></i>
                            {{ trans('general.logs') }}
                        </a>
                        <ul>
                            <li class="{{ Request::is('log/authentication') ? 'active' : null }}">
                                <a href="{{ route('log.authentication.index') }}" title='{{ trans("general.userAuthentications") }}' class="text-truncate">
                                    <i class="fa fa-sm fa-fw fa-user-shield"></i> {{ trans('general.userAuthentications') }}
                                </a>
                            </li>
                            <li class="{{ Request::is('log/access') ? 'active' : null }}">
                                <a href="{{ route('log.access.index') }}" title='{{ trans("general.userAccessLogs") }}' class="text-truncate">
                                    <i class="fa fa-sm fa-fw fa-user-tag"></i> {{ trans('general.userAccessLogs') }}
                                </a>
                            </li>
                            <li class="{{ Request::is('log/project_report/notification') ? 'active' : null }}">
                                <a href="{{ route('log.projectReport.notification.index') }}" title='{{ trans('navigation/mainnav.projectReport') }}' class="text-truncate">
                                    <i class="fa fa-sm fa-fw fa-bell"></i> {{ trans('navigation/mainnav.projectReport') }} {{ trans('navigation/mainnav.projectReportNotification') }}
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    @if($currentUser->isSuperAdmin() && !$project && getenv('ENABLE_SDP_MIGRATION', false))
                    <li class="{{ Request::is('app-migration') ? 'active' : null }}">
                        <a href="javascript:void(0);" class="text-truncate" title='App Migration'>
                            <i class="fa fa-lg fa-fw fa-file-import"></i>
                            App Migration
                        </a>
                        <ul>
                            <li class="{{ Request::is('app-migration/sdp-masterlist') ? 'active' : null }}">
                                <a href="{{ route('app.migration.sdp.index') }}" title='SDP' class="text-truncate">
                                    SDP One Drive
                                </a>
                            </li>
                            <li class="{{ Request::is('app-migration/vendor/create') ? 'active' : null }}">
                                <a href="{{ route('app.migration.vendor.create') }}" title='Create Company Vendor' class="text-truncate">
                                    Create Company Vendor
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    @if(!$project && $currentUser->isSuperAdmin() && getenv('ENABLE_S4HANA_INTEGRATION', false))
                    <li class="{{ Request::is('app-integration') ? 'active' : null }}">
                        <a href="javascript:void(0);" class="text-truncate" title='{{ trans("navigation/mainnav.appIntegration") }}'>
                            <i class="fa fa-lg fa-fw fa-compress-arrows-alt"></i>
                            {{ trans("navigation/mainnav.appIntegration") }}
                        </a>
                        <ul>
                            <li class="{{ Request::is('app-integration/s4hana') ? 'active' : null }}">
                                <a href="{{ route('app.integration.s4hana.index') }}" title='SAP S/4Hana' class="text-truncate">
                                    SAP S/4Hana
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                @endif

            @endif
        </ul>
    </nav>

</aside>