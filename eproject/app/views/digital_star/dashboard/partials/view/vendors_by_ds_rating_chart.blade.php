@if(\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR))
    @if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($currentUser, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR_DASHBOARD))
        <div class="smart-form">
            <fieldset>
                <div class="row">
                    <section class="col col-6">
                        <label class="label">{{ trans('digitalStar/digitalStar.vendorsByDsRating') }}</label>
                        <div class="col col-xs-12 well">
                            <div id="vendorsByDsRatingChart"></div>
                        </div>
                    </section>
                    <section class="col col-6">
                    </section>
                </div>
            </fieldset>
        </div>
    @endif
@endif