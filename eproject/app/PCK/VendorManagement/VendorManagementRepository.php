<?php namespace PCK\VendorManagement;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use PCK\Users\User;
use PCK\Projects\Project;
use PCK\VendorRegistration\VendorRegistration;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;
use PCK\Verifier\Verifier;

class VendorManagementRepository
{
    public function getPendingVendorManagementTasks(User $user, $includeFutureTasks, Project $project = null)
    {
        return $this->getPendingVendorRegistrations($user, $includeFutureTasks)->merge($this->getPendingVendorPerformanceEvaluationCompanyForm($user, $includeFutureTasks));
    }

    private function getPendingVendorRegistrations(User $user, $includeFutureTasks)
    {
        $additonalCondition = $includeFutureTasks ? '' : 'AND v.rank = 1';

        $query = "WITH verifiers_cte AS (
                      SELECT RANK() OVER (PARTITION BY object_id ORDER BY sequence_number ASC) AS rank, *
                      FROM verifiers
                      WHERE object_type = '" . VendorRegistration::class . "'
                      AND approved IS NULL
                      AND verified_at IS NULL
                      AND deleted_at IS NULL
                      ORDER BY object_id ASC, updated_at ASC
                  )
                  SELECT v.object_id
                  FROM verifiers_cte v
                  WHERE v.verifier_id = {$user->id}
                  {$additonalCondition}
                  ORDER BY v.updated_at ASC;";

        $vendorRegistrationIds = array_column(DB::select(DB::raw($query)), 'object_id');

        if(empty($vendorRegistrationIds)) return new Collection();

        return VendorRegistration::whereIn('id', $vendorRegistrationIds)
                ->where('status', VendorRegistration::STATUS_PENDING_VERIFICATION)
                ->get();
    }

    private function getPendingVendorPerformanceEvaluationCompanyForm(User $user, $includeFutureTasks)
    {
        $additonalCondition = $includeFutureTasks ? '' : 'AND v.rank = 1';

        $query = "WITH verifiers_cte AS (
                      SELECT RANK() OVER (PARTITION BY v.object_id ORDER BY v.sequence_number ASC) AS rank, v.*
	                  FROM verifiers v
	                  INNER JOIN vendor_performance_evaluation_company_forms cf ON cf.id = v.object_id 
	                  INNER JOIN vendor_performance_evaluations vpe ON vpe.id = cf.vendor_performance_evaluation_id 
	                  INNER JOIN vendor_performance_evaluation_cycles c ON c.id = vpe.vendor_performance_evaluation_cycle_id 
	                  WHERE object_type = 'PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm'
	                  AND v.approved IS NULL
	                  AND v.verified_at IS NULL
	                  AND v.deleted_at IS NULL
	                  AND cf.deleted_at IS NULL
	                  AND cf.status_id = " . VendorPerformanceEvaluationCompanyForm::STATUS_PENDING_VERIFICATION . "
	                  AND vpe.deleted_at IS NULL
	                  AND vpe.status_id <> " . VendorPerformanceEvaluation::STATUS_COMPLETED . "
	                  AND c.is_completed IS FALSE
	                  ORDER BY v.object_id ASC, v.updated_at ASC
                  )
                  SELECT * 
                  FROM verifiers_cte v
                  WHERE v.verifier_id = {$user->id}
                  {$additonalCondition}
                  ORDER BY v.updated_at ASC;";

        $companyFormIds = array_column(DB::select(DB::raw($query)), 'object_id');

        if(empty($companyFormIds)) return new Collection();

        return VendorPerformanceEvaluationCompanyForm::whereIn('id', $companyFormIds)->get();
    }
}