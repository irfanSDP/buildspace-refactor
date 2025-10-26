<?php namespace PCK\VendorPerformanceEvaluation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class EvaluationScore extends Model {

    use SoftDeletingTrait;

    protected $table = 'vendor_evaluation_scores';

    protected $fillable = ['vendor_work_category_id', 'company_id', 'vendor_performance_evaluation_id', 'score'];

    public function evaluation()
    {
        return $this->belongsTo('PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation');
    }
}