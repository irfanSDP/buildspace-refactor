<?php

use PCK\Projects\Project;
use PCK\Inspections\Inspection;
use PCK\Inspections\InspectionResult;
use PCK\Inspections\InspectionItemResult;
use PCK\Inspections\InspectionRole;
use PCK\Forms\InspectionItemResultForm;
use PCK\Base\Helpers;

class InspectionItemResultsController extends \BaseController {

	protected $inspectionItemResultForm;

	public function __construct(InspectionItemResultForm $inspectionItemResultForm)
	{
		$this->inspectionItemResultForm = $inspectionItemResultForm;
	}

	public function update(Project $project, $requestForInspectionId, $inspectionId, $itemId)
	{
		$input = Input::all();

		try
		{
			$this->inspectionItemResultForm->validate(array($input['field'] => $input['value']));
		}
		catch(\Laracasts\Validation\FormValidationException $e)
		{
			return array(
				'success' => false,
				'errors'  => $e->getErrors(),
			);
		}

		$user = \Confide::user();

		$inspection = Inspection::find($inspectionId);

		$role = InspectionRole::getRole($inspection, $user);

		$inspectionResult = InspectionResult::firstOrCreate(array(
			'inspection_id'      => $inspectionId,
        	'inspection_role_id' => $role->id,
		));

		$inspectionItemResult = InspectionItemResult::firstOrNew(array(
			'inspection_result_id'    => $inspectionResult->id,
			'inspection_list_item_id' => $itemId,
		));

		$inspectionItemResult->{$input['field']} = $input['value'];

		$success = $inspectionItemResult->save();

		$inspectionItemResult = InspectionItemResult::find($inspectionItemResult->id);

		return array(
			'success' => $success,
			'rowData' => array($input['field'] => $inspectionItemResult->{$input['field']}),
		);
	}

	public function attachmentsUpdate(Project $project, $requestForInspectionId, $inspectionId, $itemId)
	{
		$input = Input::all();

		$user = \Confide::user();

		$inspection = Inspection::find($inspectionId);

		$role = InspectionRole::getRole($inspection, $user);

		$inspectionResult = InspectionResult::firstOrCreate(array(
			'inspection_id'      => $inspectionId,
			'inspection_role_id' => $role->id,
		));

		$inspectionItemResult = InspectionItemResult::firstOrCreate(array(
			'inspection_result_id'    => $inspectionResult->id,
			'inspection_list_item_id' => $itemId,
		));

		\PCK\Helpers\ModuleAttachment::saveAttachments($inspectionItemResult, $input);

		return array(
			'success' => true,
		);
	}

	public function attachmentsList(Project $project, $requestForInspectionId, $inspectionId, $itemId)
	{
		$user = \Confide::user();

		$inspection = Inspection::find($inspectionId);

		$role = InspectionRole::getRole($inspection, $user);

		$inspectionResult = InspectionResult::where('inspection_id', '=', $inspectionId)
			->where('inspection_role_id', '=', $role->id)
			->first();

		if( ! $inspectionResult ) return array();

		$inspectionItemResult = InspectionItemResult::where('inspection_result_id', '=', $inspectionResult->id)
			->where('inspection_list_item_id', '=', $itemId)
			->first();

		if( ! $inspectionItemResult ) return array();

		$uploadedFiles = $this->getAttachmentDetails($inspectionItemResult);

		$data = array();

		foreach($uploadedFiles as $file)
		{
			$file['imgSrc']      = $file->generateThumbnailURL();
			$file['deleteRoute'] = $file->generateDeleteURL($project->id);
			$file['createdAt']   = Project::find($project->id)->getProjectTimeZoneTime($file->created_at);
			$file['size']	     = Helpers::formatBytes($file->size);

			$data[] = $file;
		}

		return $data;
	}

	public function getUploads(Project $project, $requestForInspectionId, $inspectionId, $itemId)
	{
		$user = \Confide::user();

		$inspection = Inspection::find($inspectionId);

		$role = InspectionRole::getRole($inspection, $user);

		$inspectionResult = InspectionResult::where('inspection_id', '=', $inspectionId)
			->where('inspection_role_id', '=', $role->id)
			->first();

		if( ! $inspectionResult ) return array();

		$inspectionItemResult = InspectionItemResult::where('inspection_result_id', '=', $inspectionResult->id)
			->where('inspection_list_item_id', '=', $itemId)
			->first();

		if( ! $inspectionItemResult ) return array();

		$data = array();

		foreach($inspectionItemResult->getAttachmentDetails() as $upload)
	    {
	        $data[] = array(
	            'filename'    => $upload->filename,
	            'download_url' => $upload->download_url,
	            'uploaded_by'  => $upload->createdBy->name,
	            'uploaded_at'  => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($upload->created_at))->format(\Config::get('dates.created_at')),
	        );
	    }

	    return json_encode($data);
	}

	public function getUploadsByRole(Project $project, $requestForInspectionId, $inspectionId, $itemId)
	{
		$roleId = Input::get('role_id');

		if( ! isset($roleId) ) return array();

		$inspection = Inspection::find($inspectionId);

		$inspectionResult = InspectionResult::where('inspection_id', '=', $inspectionId)
			->where('inspection_role_id', '=', $roleId)
			->first();

		if( ! $inspectionResult ) return array();

		$inspectionItemResult = InspectionItemResult::where('inspection_result_id', '=', $inspectionResult->id)
			->where('inspection_list_item_id', '=', $itemId)
			->first();

		if( ! $inspectionItemResult ) return array();

		$data = array();

		foreach($inspectionItemResult->getAttachmentDetails() as $upload)
	    {
	        $data[] = array(
	            'filename'    => $upload->filename,
	            'download_url' => $upload->download_url,
	            'uploaded_by'  => $upload->createdBy->name,
	            'uploaded_at'  => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($upload->created_at))->format(\Config::get('dates.created_at')),
	        );
	    }

	    return json_encode($data);
	}
}
