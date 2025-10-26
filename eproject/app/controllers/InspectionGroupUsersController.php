<?php

use PCK\Projects\Project;
use PCK\Inspections\InspectionGroup;
use PCK\Inspections\InspectionRole;
use PCK\Inspections\InspectionGroupUser;

class InspectionGroupUsersController extends \BaseController {

	public function index(Project $project)
	{
		$groupId = Input::get('group_id');
		$roleId  = Input::get('role_id');

		$assignedUserIds = InspectionGroupUser::where('inspection_group_id', '=', $groupId)
			->where('inspection_role_id', '=', $roleId)
			->lists('user_id');

		$data = array();

		$userIdsAssignedToOtherRoles = InspectionGroupUser::where('inspection_group_id', '=', $groupId)
			->where('inspection_role_id', '!=', $roleId)
			->lists('user_id');

		$users = $project->getProjectUsers(false)->reject(function($user) use ($userIdsAssignedToOtherRoles){
			return in_array($user->id, $userIdsAssignedToOtherRoles);
		});

		foreach($users as $user)
		{
			$data[] = array(
				'id'           => $user->id,
				'name'         => $user->name,
				'email'        => $user->name,
				'assigned'     => in_array($user->id, $assignedUserIds),
				'route:update' => route('inspection.groups.users.update', array($project->id, $user->id)),
			);
		}

		return $data;
	}

	public function update(Project $project, $userId)
	{
		$groupId  = Input::get('group_id');
		$roleId   = Input::get('role_id');
		$assigned = Input::get('assigned') === "true";

		if($assigned)
		{
			InspectionGroupUser::firstOrCreate(array(
				'inspection_group_id' => $groupId,
				'inspection_role_id' => $roleId,
				'user_id' => $userId,
			));
		}
		else
		{
			$record = InspectionGroupUser::where('inspection_group_id', '=', $groupId)
				->where('inspection_role_id', '=', $roleId)
				->where('user_id', '=', $userId)
				->first();

			if( ! is_null($record) ) $record->delete();
		}

		return array(
			'assigned' => $assigned
		);
	}

}
