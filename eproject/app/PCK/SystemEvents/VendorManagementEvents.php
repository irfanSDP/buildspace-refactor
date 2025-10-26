<?php namespace PCK\SystemEvents;

class VendorManagementEvents {

    public function vendorWorkCategorySyncWorkCategories($changedWorkCategoryIds)
    {
        \Log::info("Work category [ids:" . implode($changedWorkCategoryIds) . "] mapping updated");

        \Queue::push('PCK\QueueJobs\RecalculateVendorPerformanceEvaluationScores', array( 'workCategoryIds' => $changedWorkCategoryIds ), 'default');
    }
}