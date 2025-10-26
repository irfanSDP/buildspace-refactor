<?php

use PCK\Projects\Project;
use PCK\Inspections\InspectionGroup;
use PCK\Inspections\InspectionGroupInspectionListCategory;
use PCK\Forms\InspectionGroupForm;

class InspectionGroupsController extends \BaseController {

	protected $inspectionGroupForm;

	public function __construct(InspectionGroupForm $inspectionGroupForm)
	{
		$this->inspectionGroupForm = $inspectionGroupForm;
	}

	public function index(Project $project)
	{
		$records = DB::table('inspection_groups')
			->where('project_id', '=', $project->id)
			->orderBy('created_at', 'asc')
			->get();

		$data = array();

		foreach($records as $record)
		{
			$data[] = array(
				'id'           => $record->id,
				'name'         => $record->name,
				'route:update' => route('inspection.groups.update', array($project->id, $record->id)),
				'route:delete' => route('inspection.groups.delete', array($project->id, $record->id)),
			);
		}

		$data[] = [];

		return $data;
	}

	public function store(Project $project)
	{
		$input = Input::all();

		$input['project_id'] = $project->id;

		$this->inspectionGroupForm->validate($input);

		$data = null;

		if( $this->inspectionGroupForm->success )
		{
			$group = InspectionGroup::create($input);

			$data = array(
				'id'           => $group->id,
				'name'         => $group->name,
				'route:update' => route('inspection.groups.update', array($project->id, $group->id)),
				'route:delete' => route('inspection.groups.delete', array($project->id, $group->id)),
			);
		}

		return array(
			'success' => $this->inspectionGroupForm->success,
			'errors'  => $this->inspectionGroupForm->getErrors(),
			'data'    => $data,
		);
	}

	public function update(Project $project, $id)
	{
		$input = [
			Input::get('field') => Input::get('value'),
			'project_id'        => $project->id,
		];

		$this->inspectionGroupForm->validate($input);

		$data = null;

		if( $this->inspectionGroupForm->success )
		{
			$group = InspectionGroup::find($id);

			$field = Input::get('field');

			$group->{$field} = Input::get('value');

			$group->save();

			$data = array(
				$field => $group->{$field},
			);
		}

		return array(
			'success' => $this->inspectionGroupForm->success,
			'errors'  => $this->inspectionGroupForm->getErrors(),
			'data'    => $data,
		);
	}

	public function destroy(Project $project, $id)
	{
		$group = InspectionGroup::find($id);

		$success  = false;
		$errorMsg = null;

		$relatedRecordsCount = InspectionGroupInspectionListCategory::where('inspection_group_id', '=', $id)
			->count();

		if( $relatedRecordsCount > 0 )
		{
			$errorMsg = trans('forms.cannotBeDeleted');
		}
		else
		{
			try
			{
				$success = $group->delete();
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