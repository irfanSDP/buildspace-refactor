<?php namespace PCK\Helpers;

use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter;

class VpeHelper
{
    public static function getCurrentVendorManagementGrade()
    {
        $vpeModuleParameter = VendorPerformanceEvaluationModuleParameter::first();

        if (! $vpeModuleParameter) {
            return null;
        }
        if (! $vpeModuleParameter->vendorManagementGrade) {
            return null;
        }
        return $vpeModuleParameter->vendorManagementGrade;
    }
}