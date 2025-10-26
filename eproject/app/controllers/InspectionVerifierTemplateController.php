<?php

use PCK\Projects\Project;
use PCK\Users\User;
use PCK\Inspections\InspectionGroup;
use PCK\Inspections\InspectionRole;
use PCK\Inspections\InspectionVerifierTemplate;

class InspectionVerifierTemplateController extends \BaseController {

	public function index(Project $project)
	{
		$groupId = Input::get('group_id');
		$roleId  = Input::get('role_id');

		$assignedUserIds = InspectionVerifierTemplate::where('inspection_group_id', '=', $groupId)
			->orderBy('priority')
			->lists('user_id');

		$users = User::whereIn('id', $assignedUserIds)->get();

		$data = array();

		foreach($assignedUserIds as $userId)
		{
			$user = $users->find($userId);

			$data[] = array(
				'id'   => $user->id,
				'name' => $user->name,
			);
		}

		return $data;
	}

	public function unassignedIndex(Project $project)
	{
		$groupId = Input::get('group_id');
		$roleId  = Input::get('role_id');

		$assignedUserIds = InspectionVerifierTemplate::where('inspection_group_id', '=', $groupId)
			->lists('user_id');

		$users = $project->getProjectUsers(false);

		$users = $users->reject(function($user) use ($assignedUserIds){
			return in_array($user->id, $assignedUserIds);
		});

		$data = array();

		foreach($users as $user)
		{
			$data[] = array(
				'id'   => $user->id,
				'name' => $user->name,
			);
		}

		return $data;
	}

	public function update(Project $project)
	{
		$groupId = Input::get('group_id');
		$userIds = Input::get('user_ids') ?? array();

		InspectionVerifierTemplate::where('inspection_group_id', '=', $groupId)
			->whereNotIn('user_id', $userIds)
			->delete();

		$priority = 0;

		foreach($userIds as $userId)
		{
			$record = InspectionVerifierTemplate::firstOrNew(array(
				'inspection_group_id' => $groupId,
				'user_id'             => $userId,
			));

			$record->priority = ++$priority;
			$record->save();
		}

		return array(
			'success' => true
		);
	}

}
