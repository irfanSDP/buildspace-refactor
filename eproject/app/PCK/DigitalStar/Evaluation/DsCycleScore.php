<?php namespace PCK\DigitalStar\Evaluation;

use Illuminate\Database\Eloquent\Model;

class DsCycleScore extends Model {

    protected $table = 'ds_cycle_scores';

    protected $fillable = [
        'ds_cycle_id',
        'company_id',
        'vendor_management_grade_level_id',
        'company_score_weight',
        'company_score_weighted',
        'project_score_weight',
        'project_score_weighted',
        'total_score',
    ];

    public function cycle() {
        return $this->belongsTo('PCK\DigitalStar\Evaluation\DsCycle', 'ds_cycle_id');
    }

    public function company() {
        return $this->belongsTo('PCK\Companies\Company', 'company_id');
    }

    public function vendorManagementGradeLevel()
    {
        return $this->belongsTo('PCK\ModuleParameters\VendorManagement\VendorManagementGradeLevel', 'vendor_management_grade_level_id');
    }
}