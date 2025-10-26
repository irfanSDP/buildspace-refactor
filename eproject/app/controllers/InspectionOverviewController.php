<?php
use PCK\Users\User;
use PCK\Projects\Project;
use PCK\Inspections\RequestForInspection;
use PCK\Inspections\Inspection;
use PCK\Inspections\InspectionListItem;
use PCK\Inspections\InspectionRole;
use PCK\Inspections\InspectionResult;
use PCK\Inspections\InspectionItemResult;

class InspectionOverviewController extends \BaseController {

	public function show(Project $project, $requestForInspectionId)
	{
		$requestForInspection = RequestForInspection::find($requestForInspectionId);

		$inspectionItemResults = $requestForInspection->getInspectionItemResults();

        $listItems = InspectionListItem::where('inspection_list_category_id', '=', $requestForInspection->inspection_list_category_id)
        	->orderBy('lft')
        	->get();

		$roles = InspectionRole::where('project_id', '=', $project->id)->orderBy('created_at', 'asc')->get();

		$listItemData = array();

		foreach($listItems as $listItem)
		{
			$row = array(
				'id'         	   => $listItem->id,
				'description'	   => $listItem->description,
				'depth'       	   => $listItem->depth,
                'type'        	   => $listItem->type,
			);

			foreach($requestForInspection->inspections as $inspection)
			{
				foreach($roles as $role)
				{
					$row["progress_status-{$inspection->id}-{$role->id}"]  = $listItem->isTypeItem() ? ($inspectionItemResults[ $listItem->id ][ $inspection->id ][ $role->id ][ 'progress_status' ] ?? number_format(0,2)) : null;
					$row["remarks-{$inspection->id}-{$role->id}"]          = $inspectionItemResults[ $listItem->id ][ $inspection->id ][ $role->id ][ 'remarks' ] ?? "";
					$row["attachmentCount-{$inspection->id}-{$role->id}"]  = isset($inspectionItemResults[ $listItem->id ][ $inspection->id ][ $role->id ]['inspection_item_result_id']) ? count($this->getAttachmentDetails(InspectionItemResult::find($inspectionItemResults[ $listItem->id ][ $inspection->id ][ $role->id ]['inspection_item_result_id']))) : 0;
					$row["route:getUploads-{$inspection->id}-{$role->id}"] = route('inspection.inspect.item.role.uploads', array($project->id, $requestForInspectionId, $inspection->id, $listItem->id)) . '?' . http_build_query(['role_id' => $role->id]);
				}
			}

			$listItemData[] = $row;
		}

		return $listItemData;
	}

	public function getUploads(Project $project, $requestForInspectionId, $inspectionId, $itemId, $roleId)
	{
		$user = \Confide::user();

		$inspection = Inspection::find($inspectionId);

		$role = InspectionRole::find($roleId);

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
}
