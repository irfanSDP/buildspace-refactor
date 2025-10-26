<ul class="app-list app-list-grid">
    @if($project && Confide::user()->canAccessBqEditor($project))
        <li>
            <a href="{{{ Config::get('buildspace.BQ_EDITOR_URL') }}}{{{$project->id}}}" class="app-list-item hover-white">
                <span class="icon-stack">
                    <img src="{{ asset('img/bq_editor-icon.png') }}" alt="BQ Editor" style="width:55px;height:55px;">
                </span>
                <span class="app-list-name">
                    {{{ trans('navigation/shortcuts.bqEditor') }}}
                </span>
            </a>
        </li>
    @endif
    @if($user->allow_access_to_gp || $user->is_gp_admin)
        <?php
            $user = Auth::user();
            $token = base64_encode($user->gp_access_token);

            $url = "https://staging.procurex.asia/#/eproject-login/".$token;
        ?>
        <li>
            <a href="{{{ $url }}}" class="app-list-item hover-white" style="padding-top:0;">
                <img class="buildspace-img" style="width:100%;height:100%;" src="{{ asset('img/procurex_logo.png') }}">
            </a>
        </li>
    @endif
    @if($user->allow_access_to_buildspace)
        @if(!$project)
            @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_PROJECT_BUILDER))
                <li>
                    <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_PROJECT_BUILDER }}}" class="app-list-item hover-white">
                        <span class="icon-stack">
                            <div class="bs-icon-64-project_builder"></div>
                        </span>
                        <span class="app-list-name">
                            {{{ trans('navigation/shortcuts.projectBuilder') }}}
                        </span>
                    </a>
                </li>
            @endif
            @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_TENDERING))
                <li>
                    <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_TENDERING }}}" class="app-list-item hover-white">
                        <span class="icon-stack">
                            <div class="bs-icon-64-tendering"></div>
                        </span>
                        <span class="app-list-name">
                            {{{ trans('navigation/shortcuts.tendering') }}}
                        </span>
                    </a>
                </li>
            @endif
            @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_POST_CONTRACT))
                <li>
                    <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_POST_CONTRACT }}}" class="app-list-item hover-white">
                        <span class="icon-stack">
                            <div class="bs-icon-64-post_contract"></div>
                        </span>
                        <span class="app-list-name">
                            {{{ trans('navigation/shortcuts.postContract') }}}
                        </span>
                    </a>
                </li>
            @endif
            @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_PROJECT_BUILDER_REPORT))
                <li>
                    <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_PROJECT_BUILDER_REPORT }}}" class="app-list-item hover-white">
                        <span class="icon-stack">
                            <div class="bs-icon-64-project_builder"></div>
                        </span>
                        <span class="app-list-name">
                            {{{ trans('navigation/shortcuts.projectBuilderReport') }}}
                        </span>
                    </a>
                </li>
            @endif
            @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_TENDERING_REPORT))
                <li>
                    <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_TENDERING_REPORT }}}" class="app-list-item hover-white">
                        <span class="icon-stack">
                            <div class="bs-icon-64-tendering"></div>
                        </span>
                        <span class="app-list-name">
                            {{{ trans('navigation/shortcuts.tenderingReport') }}}
                        </span>
                    </a>
                </li>
            @endif
            @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_POST_CONTRACT_REPORT))
                <li>
                    <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_POST_CONTRACT_REPORT }}}" class="app-list-item hover-white">
                        <span class="icon-stack">
                            <div class="bs-icon-64-post_contract"></div>
                        </span>
                        <span class="app-list-name">
                            {{{ trans('navigation/shortcuts.postContractReport') }}}
                        </span>
                    </a>
                </li>
            @endif
            @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_PROJECT_MANAGEMENT))
                <li>
                    <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_PROJECT_MANAGEMENT }}}" class="app-list-item hover-white">
                        <span class="icon-stack">
                            <div class="bs-icon-64-project_management"></div>
                        </span>
                        <span class="app-list-name">
                            {{{ trans('navigation/shortcuts.projectManagement') }}}
                        </span>
                    </a>
                </li>
            @endif
        @else
            <?php $allowedProjectStatuses = array(PCK\Buildspace\ProjectMainInformation::STATUS_PRETENDER);
                $isProjectAvailableInModule = ($project->getBsProjectMainInformation() && in_array($project->getBsProjectMainInformation()->status, $allowedProjectStatuses));
            ?>
            @if($isProjectAvailableInModule && $user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_PROJECT_BUILDER) && $user->hasBuildspaceProjectUserPermission($project, PCK\Buildspace\ProjectUserPermission::STATUS_PROJECT_BUILDER))
                <li>
                    <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_PROJECT_BUILDER . "&id={$project->id}" }}}" class="app-list-item hover-white">
                        <span class="icon-stack">
                            <div class="bs-icon-64-project_builder"></div>
                        </span>
                        <span class="app-list-name">
                            {{{ trans('navigation/shortcuts.projectBuilder') }}}
                        </span>
                    </a>
                </li>
            @endif
            <?php $allowedProjectStatuses = array(PCK\Buildspace\ProjectMainInformation::STATUS_TENDERING, PCK\Buildspace\ProjectMainInformation::STATUS_POSTCONTRACT);
                $isProjectAvailableInModule = ($project->getBsProjectMainInformation() && in_array($project->getBsProjectMainInformation()->status, $allowedProjectStatuses));
            ?>
            @if($isProjectAvailableInModule && $user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_TENDERING))
                <li>
                    <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_TENDERING . "&id={$project->id}" }}}" class="app-list-item hover-white">
                        <span class="icon-stack">
                            <div class="bs-icon-64-tendering"></div>
                        </span>
                        <span class="app-list-name">
                            {{{ trans('navigation/shortcuts.tendering') }}}
                        </span>
                    </a>
                </li>
            @endif
            <?php $allowedProjectStatuses = array(PCK\Buildspace\ProjectMainInformation::STATUS_POSTCONTRACT);
                $isProjectAvailableInModule = ($project->getBsProjectMainInformation() && in_array($project->getBsProjectMainInformation()->status, $allowedProjectStatuses));
            ?>
            @if($isProjectAvailableInModule && $user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_POST_CONTRACT))
                <li>
                    <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_POST_CONTRACT . "&id={$project->id}" }}}" class="app-list-item hover-white">
                        <span class="icon-stack">
                            <div class="bs-icon-64-post_contract"></div>
                        </span>
                        <span class="app-list-name">
                            {{{ trans('navigation/shortcuts.postContract') }}}
                        </span>
                    </a>
                </li>
            @endif
            <?php $allowedProjectStatuses = array(PCK\Buildspace\ProjectMainInformation::STATUS_PRETENDER);
                $isProjectAvailableInModule = ($project->getBsProjectMainInformation() && in_array($project->getBsProjectMainInformation()->status, $allowedProjectStatuses));
            ?>
            @if($isProjectAvailableInModule && $isProjectAvailableInModule && $user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_PROJECT_BUILDER_REPORT))
                <li>
                    <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_PROJECT_BUILDER_REPORT . "&id={$project->id}" }}}" class="app-list-item hover-white">
                        <span class="icon-stack">
                            <div class="bs-icon-64-project_builder"></div>
                        </span>
                        <span class="app-list-name">
                            {{{ trans('navigation/shortcuts.projectBuilderReport') }}}
                        </span>
                    </a>
                </li>
            @endif
            <?php $allowedProjectStatuses = array(PCK\Buildspace\ProjectMainInformation::STATUS_TENDERING, PCK\Buildspace\ProjectMainInformation::STATUS_POSTCONTRACT);
                $isProjectAvailableInModule = ($project->getBsProjectMainInformation() && in_array($project->getBsProjectMainInformation()->status, $allowedProjectStatuses));
            ?>
            @if($isProjectAvailableInModule && $user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_TENDERING_REPORT))
                <li>
                    <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_TENDERING_REPORT . "&id={$project->id}" }}}" class="app-list-item hover-white">
                        <span class="icon-stack">
                            <div class="bs-icon-64-tendering"></div>
                        </span>
                        <span class="app-list-name">
                            {{{ trans('navigation/shortcuts.tenderingReport') }}}
                        </span>
                    </a>
                </li>
            @endif
            <?php $allowedProjectStatuses = array(PCK\Buildspace\ProjectMainInformation::STATUS_POSTCONTRACT);
                $isProjectAvailableInModule = ($project->getBsProjectMainInformation() && in_array($project->getBsProjectMainInformation()->status, $allowedProjectStatuses));
            ?>
            @if($isProjectAvailableInModule && $user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_POST_CONTRACT_REPORT))
                <li>
                    <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_POST_CONTRACT_REPORT . "&id={$project->id}" }}}" class="app-list-item hover-white">
                        <span class="icon-stack">
                            <div class="bs-icon-64-post_contract"></div>
                        </span>
                        <span class="app-list-name">
                            {{{ trans('navigation/shortcuts.postContractReport') }}}
                        </span>
                    </a>
                </li>
            @endif
            <?php $allowedProjectStatuses = array(PCK\Buildspace\ProjectMainInformation::STATUS_POSTCONTRACT);
                $isProjectAvailableInModule = ($project->getBsProjectMainInformation() && in_array($project->getBsProjectMainInformation()->status, $allowedProjectStatuses));
            ?>
            @if($isProjectAvailableInModule && $user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_PROJECT_MANAGEMENT))
                <li>
                    <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_PROJECT_MANAGEMENT . "&id={$project->id}" }}}" class="app-list-item hover-white">
                        <span class="icon-stack">
                            <div class="bs-icon-64-project_management"></div>
                        </span>
                        <span class="app-list-name">
                            {{{ trans('navigation/shortcuts.projectManagement') }}}
                        </span>
                    </a>
                </li>
            @endif
        @endif
        @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_RESOURCE_LIBRARY))
            <li>
                <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_RESOURCE_LIBRARY }}}" class="app-list-item hover-white">
                    <span class="icon-stack">
                        <div class="bs-icon-64-library"></div>
                    </span>
                    <span class="app-list-name">
                        {{{ trans('navigation/shortcuts.resourceLibrary') }}}
                    </span>
                </a>
            </li>
        @endif
        @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_SCHEDULE_OF_RATE_LIBRARY))
            <li>
                <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_SCHEDULE_OF_RATE_LIBRARY }}}" class="app-list-item hover-white">
                    <span class="icon-stack">
                        <div class="bs-icon-64-library"></div>
                    </span>
                    <span class="app-list-name">
                        {{{ trans('navigation/shortcuts.scheduleOfRateLibrary') }}}
                    </span>
                </a>
            </li>
        @endif
        @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_BQ_LIBRARY))
            <li>
                <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_BQ_LIBRARY }}}" class="app-list-item hover-white">
                    <span class="icon-stack">
                        <div class="bs-icon-64-library"></div>
                    </span>
                    <span class="app-list-name">
                        {{{ trans('navigation/shortcuts.bqLibrary') }}}
                    </span>
                </a>
            </li>
        @endif
        @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_COMPANY_DIRECTORIES))
            <li>
                <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_COMPANY_DIRECTORIES }}}" class="app-list-item hover-white">
                    <span class="icon-stack">
                        <div class="bs-icon-64-administration"></div>
                    </span>
                    <span class="app-list-name">
                        {{{ trans('navigation/shortcuts.companyDirectory') }}}
                    </span>
                </a>
            </li>
        @endif
        @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_REQUEST_FOR_QUOTATION))
            <li>
                <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_REQUEST_FOR_QUOTATION }}}" class="app-list-item hover-white">
                    <span class="icon-stack">
                        <div class="bs-icon-64-finance"></div>
                    </span>
                    <span class="app-list-name">
                        {{{ trans('navigation/shortcuts.requestForQuotation') }}}
                    </span>
                </a>
            </li>
        @endif
        @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_PURCHASE_ORDER))
            <li>
                <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_PURCHASE_ORDER }}}" class="app-list-item hover-white">
                    <span class="icon-stack">
                        <div class="bs-icon-64-finance"></div>
                    </span>
                    <span class="app-list-name">
                        {{{ trans('navigation/shortcuts.purchaseOrder') }}}
                    </span>
                </a>
            </li>
        @endif
        @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_STOCK_IN))
            <li>
                <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_STOCK_IN }}}" class="app-list-item hover-white">
                    <span class="icon-stack">
                        <div class="bs-icon-64-finance"></div>
                    </span>
                    <span class="app-list-name">
                        {{{ trans('navigation/shortcuts.stockIn') }}}
                    </span>
                </a>
            </li>
        @endif
        @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_STOCK_OUT))
            <li>
                <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_STOCK_OUT }}}" class="app-list-item hover-white">
                    <span class="icon-stack">
                        <div class="bs-icon-64-finance"></div>
                    </span>
                    <span class="app-list-name">
                        {{{ trans('navigation/shortcuts.stockOut') }}}
                    </span>
                </a>
            </li>
        @endif
        @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_RESOURCE_LIBRARY_REPORT))
            <li>
                <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_RESOURCE_LIBRARY_REPORT }}}" class="app-list-item hover-white">
                    <span class="icon-stack">
                        <div class="bs-icon-64-reports"></div>
                    </span>
                    <span class="app-list-name">
                        {{{ trans('navigation/shortcuts.resourceLibraryReport') }}}
                    </span>
                </a>
            </li>
        @endif
        @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_SCHEDULE_OF_RATE_LIBRARY_REPORT))
            <li>
                <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_SCHEDULE_OF_RATE_LIBRARY_REPORT }}}" class="app-list-item hover-white">
                    <span class="icon-stack">
                        <div class="bs-icon-64-reports"></div>
                    </span>
                    <span class="app-list-name">
                        {{{ trans('navigation/shortcuts.scheduleOfRateLibraryReport') }}}
                    </span>
                </a>
            </li>
        @endif
        @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_BQ_LIBRARY_REPORT))
            <li>
                <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_BQ_LIBRARY_REPORT }}}" class="app-list-item hover-white">
                    <span class="icon-stack">
                        <div class="bs-icon-64-reports"></div>
                    </span>
                    <span class="app-list-name">
                        {{{ trans('navigation/shortcuts.bqLibraryReport') }}}
                    </span>
                </a>
            </li>
        @endif
        @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_STOCK_IN_REPORT))
            <li>
                <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_STOCK_IN_REPORT }}}" class="app-list-item hover-white">
                    <span class="icon-stack">
                        <div class="bs-icon-64-reports"></div>
                    </span>
                    <span class="app-list-name">
                        {{{ trans('navigation/shortcuts.stockInReport') }}}
                    </span>
                </a>
            </li>
        @endif
        @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_STOCK_OUT_REPORT))
            <li>
                <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_STOCK_OUT_REPORT }}}" class="app-list-item hover-white">
                    <span class="icon-stack">
                        <div class="bs-icon-64-reports"></div>
                    </span>
                    <span class="app-list-name">
                        {{{ trans('navigation/shortcuts.stockOutReport') }}}
                    </span>
                </a>
            </li>
        @endif
        @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_PRINTING_LAYOUT_SETTING))
            <li>
                <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_PRINTING_LAYOUT_SETTING }}}" class="app-list-item hover-white">
                    <span class="icon-stack">
                        <div class="bs-icon-64-administration"></div>
                    </span>
                    <span class="app-list-name">
                        {{{ trans('navigation/shortcuts.printingLayoutSetting') }}}
                    </span>
                </a>
            </li>
        @endif
        @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_SYSTEM_MAINTENANCE))
            <li>
                <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_SYSTEM_MAINTENANCE }}}" class="app-list-item hover-white">
                    <span class="icon-stack">
                        <div class="bs-icon-64-administration"></div>
                    </span>
                    <span class="app-list-name">
                        {{{ trans('navigation/shortcuts.systemMaintenance') }}}
                    </span>
                </a>
            </li>
        @endif
        @if($user->hasBuildspaceMenuItemAccess(PCK\Buildspace\Menu::BS_APP_NAME_SYSTEM_ADMINISTRATION))
            <li>
                <a href="{{{ getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_SYSTEM_ADMINISTRATION }}}" class="app-list-item hover-white">
                    <span class="icon-stack">
                        <div class="bs-icon-64-administration"></div>
                    </span>
                    <span class="app-list-name">
                        {{{ trans('navigation/shortcuts.systemAdministration') }}}
                    </span>
                </a>
            </li>
        @endif
    @endif
</ul>