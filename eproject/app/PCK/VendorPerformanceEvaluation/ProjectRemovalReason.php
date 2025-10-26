<?php namespace PCK\VendorPerformanceEvaluation;

use Illuminate\Database\Eloquent\Model;

class ProjectRemovalReason extends Model {

    protected $table = 'vendor_performance_evaluation_project_removal_reasons';

    protected $fillable = ['name'];
}