<?php namespace PCK\VendorPerformanceEvaluation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class FormChangeRequest extends Model {

    use SoftDeletingTrait;

    protected $table = 'vendor_performance_evaluation_form_change_requests';

    protected $fillable = ['user_id', 'vendor_performance_evaluation_setup_id', 'remarks'];
}