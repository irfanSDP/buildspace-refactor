<?php namespace PCK\VendorPerformanceEvaluation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class FormChangeLog extends Model {

    use SoftDeletingTrait;

    protected $table = 'vendor_performance_evaluation_form_change_logs';

    protected $fillable = ['user_id', 'vendor_performance_evaluation_setup_id', 'old_template_node_id', 'new_template_node_id'];
}