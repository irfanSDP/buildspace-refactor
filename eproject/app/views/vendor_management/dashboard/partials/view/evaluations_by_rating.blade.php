<header>{{ trans('vendorManagement.totalVendorPerformanceEvaluationByRating') }}</header>
<label class="label">{{ trans('vendorManagement.byVendorGroup') }}</label>
<div id="vendor-group-total-evaluations-by-rating"></div>
@if(\PCK\VendorManagement\VendorManagementUserPermission::hasPermission($currentUser, \PCK\VendorManagement\VendorManagementUserPermission::TYPE_SETTINGS_AND_MAINTENANCE))
<div id="vpe-grade-link" hidden>
    <a href="{{ route('vendor.performance.evaluation.module.parameter.edit') }}">{{ trans('vendorManagement.noVPEGradeSet') }}</a>
</div>
@endif
<label class="label">{{ trans('vendorManagement.byVendorCategory') }}</label>
<div id="vendor-category-total-evaluations-by-rating"></div>