@if(\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR))
    @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($currentUser, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR_DASHBOARD))
        <div class="smart-form">
            <fieldset>
                <div class="row">
                    <section class="col col-xs-12 col-md-6">
                        <label class="label">{{ trans('digitalStar/digitalStar.companyEvaluation') }}</label>
                        <div id="company-form-completion"></div>
                    </section>
                    <section class="col col-xs-12 col-md-6">
                        <label class="label">{{ trans('digitalStar/digitalStar.projectEvaluation') }}</label>
                        <div id="project-form-completion"></div>
                    </section>
                </div>
            </fieldset>
        </div>
    @endif
@endif