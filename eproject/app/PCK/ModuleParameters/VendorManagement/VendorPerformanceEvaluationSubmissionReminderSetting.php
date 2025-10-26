<?php namespace PCK\ModuleParameters\VendorManagement;

use Illuminate\Database\Eloquent\Model;

class VendorPerformanceEvaluationSubmissionReminderSetting extends Model
{
    public $timestamps = false;

    protected $table = 'vendor_performance_evaluation_submission_reminder_settings';

    protected $fillable = ['number_of_days_before'];
}