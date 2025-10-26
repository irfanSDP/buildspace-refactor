<?php namespace PCK\Inspections;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\Inspections\Inspection;
use PCK\Inspections\InspectionGroupUser;
use PCK\Inspections\InspectionListCategory;
use PCK\Inspections\InspectionGroupInspectionListCategory;

class InspectionRole extends Model {

	protected $fillable = ['project_id', 'name'];

	public static function getRole(Inspection $inspection, User $user)
	{
		$requestForInspection = $inspection->requestForInspection;

		$inspectionListCategory = $inspection->requestForInspection->inspectionListCategory;

		$inspectionListCategoryAssignedToGroup = InspectionListCategory::where('inspection_list_id', '=', $inspectionListCategory->inspection_list_id)
			->where('lft', '<=', $inspectionListCategory->lft)
			->where('rgt', '>=', $inspectionListCategory->rgt)
			->orderBy('lft')
			->first();

		if( ! $inspectionListCategoryAssignedToGroup ) return false;

		$inspectionGroupList = InspectionGroupInspectionListCategory::where('inspection_list_category_id', '=', $inspectionListCategoryAssignedToGroup->id)
			->first();

		if( ! $inspectionGroupList ) return false;

		$groupUser = InspectionGroupUser::where('inspection_group_id', '=', $inspectionGroupList->inspection_group_id)
			->where('user_id', '=', $user->id)
			->first();

		if( ! $groupUser ) return false;

		return $groupUser->role;
	}
}
