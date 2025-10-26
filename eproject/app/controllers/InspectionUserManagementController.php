<?php

use PCK\Projects\Project;

class InspectionUserManagementController extends \BaseController {

	public function edit(Project $project)
	{
		return View::make('inspections.user_management', compact('project'));
	}

}
