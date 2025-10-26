<?php namespace PCK\DigitalStar\ModuleParameters;

use Illuminate\Database\Eloquent\Model;

class DsSubmissionReminderSetting extends Model
{
    protected $table = 'ds_submission_reminder_settings';

    protected $fillable = ['number_of_days_before'];

    public function moduleParameter()
    {
        return $this->belongsTo('PCK\DigitalStar\ModuleParameters\DsModuleParameter', 'ds_module_parameter_id');
    }
}