<?php

use PCK\Projects\Project;
use PCK\Inspections\InspectionRole;
use PCK\Inspections\InspectionResult;
use PCK\Forms\InspectionRoleForm;

class InspectionRolesController extends \BaseController {

	protected $inspectionRoleForm;

	public function __construct(InspectionRoleForm $inspectionRoleForm)
	{
		$this->inspectionRoleForm = $inspectionRoleForm;
	}

	public function index(Project $project)
	{
		$records = DB::table('inspection_roles')
			->where('project_id', '=', $project->id)
			->orderBy('created_at', 'asc')
			->get();

		$submitForApprovalRow = array(
			'id'       => 'submitters',
			'name'     => trans('inspection.submitForApproval'),
			'editable' => false,
		);
		$verifiersRow = array(
			'id'       => 'verifiers',
			'name'     => trans('inspection.verifiers'),
			'editable' => false,
		);

		$data = array(
			$submitForApprovalRow,
			$verifiersRow
		);

		foreach($records as $record)
		{
			$data[] = array(
				'id'                     => $record->id,
				'name'                   => $record->name,
				'can_request_inspection' => $record->can_request_inspection,
				'route:update'           => route('inspection.roles.update', array($project->id, $record->id)),
				'route:delete'           => route('inspection.roles.delete', array($project->id, $record->id)),
			);
		}

		$data[] = [];

		return $data;
	}

	public function store(Project $project)
	{
		$input = Input::all();

		$input['project_id'] = $project->id;

		$this->inspectionRoleForm->validate($input);

		$data = null;

		if( $this->inspectionRoleForm->success )
		{
			$role = InspectionRole::create($input);

			$data = array(
				'id'                     => $role->id,
				'name'                   => $role->name,
				'can_request_inspection' => false,
				'route:update'           => route('inspection.roles.update', array($project->id, $role->id)),
				'route:delete'           => route('inspection.roles.delete', array($project->id, $role->id)),
			);
		}

		return array(
			'success' => $this->inspectionRoleForm->success,
			'errors'  => $this->inspectionRoleForm->getErrors(),
			'data'    => $data,
		);
	}

	public function update(Project $project, $id)
	{
		$input = [
			Input::get('field') => Input::get('value'),
			'project_id'        => $project->id,
		];

		$this->inspectionRoleForm->validate($input);

		$data = null;

		if( $this->inspectionRoleForm->success )
		{
			$role = InspectionRole::find($id);

			$field = Input::get('field');

			$role->{$field} = Input::get('value');

			$role->save();

			$data = array(
				$field => $role->{$field},
			);
		}

		return array(
			'success' => $this->inspectionRoleForm->success,
			'errors'  => $this->inspectionRoleForm->getErrors(),
			'data'    => $data,
		);
	}

	public function destroy(Project $project, $id)
	{
		$role = InspectionRole::find($id);

		$success  = false;
		$errorMsg = null;

		$relatedRecordsCount = InspectionResult::where('inspection_role_id', '=', $id)
			->count();

		if( $relatedRecordsCount > 0 )
		{
			$errorMsg = trans('forms.cannotBeDeleted');
		}
		else
		{
			try
			{
				$success = $role->delete();
			}
			catch(\Exception $e)
			{
				$errorMsg = trans('forms.anErrorOccured');
			}
		}

		return array(
			'success'  => $success,
			'errorMsg' => $errorMsg,
		);
	}
}
