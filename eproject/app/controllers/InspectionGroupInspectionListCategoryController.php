<?php

use PCK\Projects\Project;
use PCK\Inspections\InspectionGroup;
use PCK\Inspections\InspectionGroupInspectionListCategory;
use PCK\Inspections\InspectionListCategory;
use PCK\Inspections\InspectionListRepository;

class InspectionGroupInspectionListCategoryController extends \BaseController {

	private $inspectionListRepository;

	public function __construct(InspectionListRepository $inspectionListRepository)
	{
		$this->inspectionListRepository = $inspectionListRepository;
	}

	public function index(Project $project)
	{
		$groupId 	 = Input::get('group_id');
		$assignedIds = InspectionGroupInspectionListCategory::where('inspection_group_id', '=', $groupId)->lists('inspection_list_category_id');
		$records 	 = InspectionListCategory::whereIn('id', $assignedIds)->orderBy('created_at', 'desc')->get();
		$data 		 = array();

		foreach($records as $record)
		{
			$data[] = array(
				'id'   => $record->id,
				'name' => $record->name,
			);
		}

		return $data;
	}

	public function notIndex(Project $project)
	{
		$groupId = Input::get('group_id');
		$data    = array();

		if(is_null($project->inspectionLists->first())) return array();

		$assignedIds = InspectionGroupInspectionListCategory::where('inspection_group_id', '=', $groupId)->lists('inspection_list_category_id');

		$listCategoriesAssignedToOtherGroups = InspectionGroupInspectionListCategory::whereHas('group', function($query) use ($project){
			$query->whereHas('project', function($query) use ($project){
				$query->where('project_id', '=', $project->id);
			});
		})->lists('inspection_list_category_id');

		$inspectionListCategories = InspectionListCategory::where('inspection_list_id', $project->inspectionLists->first()->id)
			->whereNull('parent_id')
			->whereNotIn('id', $assignedIds)
			->whereNotIn('id', $listCategoriesAssignedToOtherGroups)
			->orderBy('inspection_list_id', 'ASC')
			->orderBy('priority', 'ASC')
			->get();

		foreach($inspectionListCategories as $category)
		{
			array_push($data, [
				'id'   => $category->id,
				'name' => $category->name,
			]);
		}

		return $data;
	}

	public function update(Project $project)
	{
		$groupId 		 = Input::get('group_id');
		$listCategoryIds = is_null(Input::get('list_category_ids')) ? [] : Input::get('list_category_ids');
		$ids 			 = array();

		foreach($listCategoryIds  as $categoryId)
		{
			$record = InspectionGroupInspectionListCategory::firstOrNew(array(
				'inspection_list_category_id' => $categoryId,
				'inspection_group_id'		  => $groupId,
			));

			$record->save();
			
			array_push($ids, $record->id);
		}

		InspectionGroupInspectionListCategory::where('inspection_group_id', '=', $groupId)
			->whereNotIn('id', $ids)
			->delete();

		return array(
			'success' => true
		);
	}

}