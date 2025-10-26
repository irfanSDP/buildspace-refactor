<?php namespace PCK\DailyLabourReports;

use Illuminate\Events\Dispatcher;
use PCK\Projects\Project;
use PCK\SiteManagement\SiteManagementUserPermission;
use PCK\Base\BaseModuleRepository;

class DailyLabourReportRepository extends BaseModuleRepository{

	public static function processQuery($user, $project){

    	$query = DailyLabourReport::where("project_id", $project->id);

        if($project->isSubProject())
		{
			$query = DailyLabourReport::where("project_id", $project->parent_project_id);
		} 

		if(SiteManagementUserPermission::isProjectAssignedContractor($user,$project))
		{
			$query = $query->where("contractor_id",$user->company->id);
		}
		
		return $query;
    }

    public function store($project,$inputs)
    {
    	$user = \Confide::user();

		/* To get highest level of location */

		foreach ($inputs as $key => $value) 
		{
		    if (strpos($key, 'locationLevel_') === 0) 
		    {
		        $locations[$key] = $value;
		    }
		}

		foreach ($locations as $key => $value)
		{
			$levels[] = substr($key,14);
		}

		foreach($levels as $level)
		{
			$highestLevel = 0;

			if($level > $highestLevel)
			{
				$highestLevel = $level;
			}
		}	

		$dailyLabourReport = new DailyLabourReport;
		$dailyLabourReport->date = $inputs['date'];
		$dailyLabourReport->bill_column_setting_id = $inputs['type']?? NULL;
		$dailyLabourReport->unit = $inputs['unit']?? NULL;
		$dailyLabourReport->project_structure_location_code_id = $inputs['locationLevel_' . $highestLevel];
		$dailyLabourReport->pre_defined_location_code_id = $inputs['trade'];
		$dailyLabourReport->contractor_id = $inputs['contractor'];
		$dailyLabourReport->work_description = $inputs['work_description'];
		$dailyLabourReport->weather_id = $inputs['weather'];
		$dailyLabourReport->remark = $inputs['remark'];
		$dailyLabourReport->submitted_by = $user->id;
		$dailyLabourReport->project_id = $project->id;
		$dailyLabourReport->save(); 

		$this->saveAttachments($dailyLabourReport, $inputs);

		$labourTypes = ProjectLabourRate::getLabourTypes();

		foreach($labourTypes as $key => $value)
		{
        	$dailyLabourReportLabourRate = new DailyLabourReportLabourRate;
			$dailyLabourReportLabourRate->labour_type = $key;
			$dailyLabourReportLabourRate->normal_working_hours = $inputs['normal_working_hours'];
			$dailyLabourReportLabourRate->normal_rate = $inputs['normal_rate_per_hour_'.$key];
			$dailyLabourReportLabourRate->ot_rate =  $inputs['ot_rate_per_hour_'.$key];
			$dailyLabourReportLabourRate->normal_workers_total = $inputs['number_of_workers_'.$key];
			$dailyLabourReportLabourRate->ot_workers_total = $inputs['ot_number_of_workers_'.$key];
			$dailyLabourReportLabourRate->ot_hours_total = $inputs['ot_hours_'.$key];
			$dailyLabourReportLabourRate->daily_labour_report_id = $dailyLabourReport->id;
			$dailyLabourReportLabourRate->save();
		}
    }

    public function update($project,$inputs,$formId)
    {
    	$user = \Confide::user();

		/* To get highest level of location */

		foreach ($inputs as $key => $value) 
		{
		    if (strpos($key, 'locationLevel_') === 0) 
		    {
		        $locations[$key] = $value;
		    }
		}

		foreach ($locations as $key => $value)
		{
			$levels[] = substr($key,14);
		}

		foreach($levels as $level)
		{
			$highestLevel = 0;

			if($level > $highestLevel)
			{
				$highestLevel = $level;
			}
		}	

		$dailyLabourReport = DailyLabourReport::find($formId);

		$dailyLabourReport->date = $inputs['date'];
		$dailyLabourReport->bill_column_setting_id = $inputs['type']?? NULL;
		$dailyLabourReport->unit = $inputs['unit']?? NULL;
		$dailyLabourReport->project_structure_location_code_id = $inputs['locationLevel_' . $highestLevel];
		$dailyLabourReport->pre_defined_location_code_id = $inputs['trade'];
		$dailyLabourReport->contractor_id = $inputs['contractor'];
		$dailyLabourReport->work_description = $inputs['work_description'];
		$dailyLabourReport->weather_id = $inputs['weather'];
		$dailyLabourReport->remark = $inputs['remark'];
		$dailyLabourReport->submitted_by = $user->id;
		$dailyLabourReport->project_id = $project->id;
		$dailyLabourReport->save(); 

		$this->saveAttachments($dailyLabourReport, $inputs);
		
		$labourTypes = ProjectLabourRate::getLabourTypes();

		$dailyLabourReportLabourRates = DailyLabourReportLabourRate::where('daily_labour_report_id', $dailyLabourReport->id)->get();

		foreach($dailyLabourReportLabourRates as $dailyLabourReportLabourRate)
		{
			foreach($labourTypes as $key => $value)
			{
				if($dailyLabourReportLabourRate->labour_type == $key)
				{
					$dailyLabourReportLabourRate->labour_type = $key;
					$dailyLabourReportLabourRate->normal_working_hours = $inputs['normal_working_hours'];
					$dailyLabourReportLabourRate->normal_rate = $inputs['normal_rate_per_hour_'.$key];
					$dailyLabourReportLabourRate->ot_rate =  $inputs['ot_rate_per_hour_'.$key];
					$dailyLabourReportLabourRate->normal_workers_total = $inputs['number_of_workers_'.$key];
					$dailyLabourReportLabourRate->ot_workers_total = $inputs['ot_number_of_workers_'.$key];
					$dailyLabourReportLabourRate->ot_hours_total = $inputs['ot_hours_'.$key];
					$dailyLabourReportLabourRate->daily_labour_report_id = $dailyLabourReport->id;
					$dailyLabourReportLabourRate->save();
				}
			}
		}
    }

}
