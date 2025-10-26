<?php namespace PCK\DailyLabourReports;

use Illuminate\Database\Eloquent\Model;
use PCK\Base\ModuleAttachmentTrait;

class DailyLabourReport extends Model {

	use ModuleAttachmentTrait;
	
	protected $table = 'daily_labour_reports';

	public function project()
	{
		return $this->belongsTo('PCK\Projects\Project','project_id');
	}

	public function contractorCompany()
	{
		return $this->belongsTo('PCK\Companies\Company','contractor_id');
	}

	public function billColumnSetting()
	{
		return $this->belongsTo('PCK\Buildspace\BillColumnSetting','bill_column_setting_id');
	}

	public function projectStructureLocationCode()
	{
		return $this->belongsTo('PCK\Buildspace\ProjectStructureLocationCode','project_structure_location_code_id');
	}

	public function preDefinedLocationCode()
	{
		return $this->belongsTo('PCK\Buildspace\PreDefinedLocationCode','pre_defined_location_code_id');
	}

	public function weather()
	{
		return $this->belongsTo('PCK\Weathers\Weather','weather_id');
	}

	public function submittedUser()
    {
        return $this->belongsTo('PCK\Users\User','submitted_by');
    }

    public function DailyLabourReportLabourRates()
    {
    	return $this->hasMany('PCK\DailyLabourReports\DailyLabourReportLabourRate');
    }
}