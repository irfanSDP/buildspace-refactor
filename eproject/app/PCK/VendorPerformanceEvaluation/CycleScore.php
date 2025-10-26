<?php namespace PCK\VendorPerformanceEvaluation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class CycleScore extends Model {

    use SoftDeletingTrait;

    protected $table = 'vendor_evaluation_cycle_scores';

    protected $fillable = ['vendor_work_category_id', 'company_id', 'vendor_performance_evaluation_cycle_id', 'score'];

    public function cycle()
    {
        return $this->belongsTo('PCK\VendorPerformanceEvaluation\Cycle');
    }

    public function vendorWorkCategory()
    {
        return $this->belongsTo('PCK\VendorWorkCategory\VendorWorkCategory');
    }
}