<?php

use PCK\Projects\Project;
use PCK\Inspections\InspectionGroup;
use PCK\Inspections\InspectionRole;
use PCK\Inspections\InspectionSubmitter;

class InspectionSubmittersController extends \BaseController {

	public function index(Project $project)
	{
		$groupId = Input::get('group_id');
		$roleId  = Input::get('role_id');

		$assignedUserIds = InspectionSubmitter::where('inspection_group_id', '=', $groupId)
			->lists('user_id');

		$data = array();

		$users = $project->getProjectUsers(false);

		foreach($users as $user)
		{
			$data[] = array(
				'id'           => $user->id,
				'name'         => $user->name,
				'email'        => $user->name,
				'assigned'     => in_array($user->id, $assignedUserIds),
				'route:update' => route('inspection.submitters.update', array($project->id, $user->id)),
			);
		}

		return $data;
	}

	public function update(Project $project, $userId)
	{
		$groupId  = Input::get('group_id');
		$assigned = Input::get('assigned') === "true";

		if($assigned)
		{
			InspectionSubmitter::firstOrCreate(array(
				'inspection_group_id' => $groupId,
				'user_id'             => $userId,
			));
		}
		else
		{
			$record = InspectionSubmitter::where('inspection_group_id', '=', $groupId)
				->where('user_id', '=', $userId)
				->first();

			if( ! is_null($record) ) $record->delete();
		}

		return array(
			'assigned' => $assigned
		);
	}


}
