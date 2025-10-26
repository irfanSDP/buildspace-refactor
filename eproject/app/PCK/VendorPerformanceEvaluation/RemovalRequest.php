<?php namespace PCK\VendorPerformanceEvaluation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class RemovalRequest extends Model {

    use SoftDeletingTrait;

    protected $table = 'vendor_performance_evaluation_removal_requests';

    protected $fillable = [
        'company_id',
        'vendor_performance_evaluation_id',
        'user_id',
        'vendor_performance_evaluation_project_removal_reason_id',
        'vendor_performance_evaluation_project_removal_reason_text',
        'removed_at',
        'action_by',
        'request_remarks',
        'dismissal_remarks',
        'deleted_at',
    ];

    public function vendorPerformanceEvaluation()
    {
        return $this->belongsTo('PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation');
    }

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company');
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

    public function projectRemovalReason()
    {
        return $this->belongsTo('PCK\VendorPerformanceEvaluation\ProjectRemovalReason', 'vendor_performance_evaluation_project_removal_reason_id', 'id', 'vendor_performance_evaluation_project_removal_reasons');
    }

    public static function getPendingRemovalRequestsCount()
    {
        $query = "SELECT vperr.id AS id
                  FROM vendor_performance_evaluation_removal_requests vperr 
                  INNER JOIN vendor_performance_evaluations vpe ON vpe.id = vperr.vendor_performance_evaluation_id 
                  INNER JOIN projects p ON p.id = vpe.project_id 
                  WHERE vpe.deleted_at IS NULL
                  AND vpe.status_id = " . VendorPerformanceEvaluation::STATUS_IN_PROGRESS . "
                  AND vperr.deleted_at IS NULL
                  AND vperr.evaluation_removed IS FALSE
                  AND p.deleted_at IS NULL
                  ORDER BY vperr.created_at ASC;";

        $queryResults = \DB::select(\DB::raw($query));

        return count($queryResults);
    }
}