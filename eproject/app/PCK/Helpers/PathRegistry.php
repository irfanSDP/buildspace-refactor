<?php namespace PCK\Helpers;

use PCK\Helpers\Files;

class PathRegistry {

    public static function artisan()
    {
        return base_path()
            .DIRECTORY_SEPARATOR
            .'artisan';
    }

    public static function vendorPerformanceEvaluationFormReportsDir($cycleId)
    {
        return storage_path()
            .DIRECTORY_SEPARATOR
            .'reports'
            .DIRECTORY_SEPARATOR
            .'vendor-performance-evaluation-forms'
            .DIRECTORY_SEPARATOR
            ."cycle-{$cycleId}"
            .DIRECTORY_SEPARATOR;
    }

    public static function vendorPerformanceEvaluationFormReportsProgressLog($cycleId)
    {
        $dir = self::vendorPerformanceEvaluationFormReportsDir($cycleId);

        return $dir."_log.".Files::EXTENSION_LOG;
    }

    public static function vendorPerformanceEvaluationFormReports($cycleId)
    {
        $dir = self::vendorPerformanceEvaluationFormReportsDir($cycleId);

        return $dir."Forms.".Files::EXTENSION_ZIP;
    }
}