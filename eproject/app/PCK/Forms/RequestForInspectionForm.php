<?php namespace PCK\Forms;

use PCK\Inspections\InspectionListCategory;
use PCK\Inspections\InspectionGroupUser;
use PCK\Inspections\InspectionGroupInspectionListCategory;
use Illuminate\Support\MessageBag;

class RequestForInspectionForm extends CustomFormValidator {

    protected $rules = [
        'location_id'                 => 'required|integer',
        'inspection_list_category_id' => 'required|integer',
        'ready_for_inspection_date'   => 'required|date',
    ];

    public function setRules($formData)
    {
        $this->messages = [
            'location_id.required'                 => trans('inspection.locationIdRequired'),
            'inspection_list_category_id.required' => trans('inspection.listCategoryIdRequired'),
        ];
    }

    public function postParentValidation($formData)
    {
        $inspectionListCategory = InspectionListCategory::find($formData['inspection_list_category_id']);

        $messageBag = new MessageBag();

        if( $inspectionListCategory->type != InspectionListCategory::TYPE_INSPECTION_LIST )
        {
            $messageBag->add('inspection_list_category_id', 'Please select items of type "LIST"');
        }

        $groupIds = InspectionGroupUser::where('user_id', '=', \Confide::user()->id)->lists('inspection_group_id');

        $assignedRootListIds = InspectionGroupInspectionListCategory::whereIn('inspection_group_id', $groupIds)->lists('inspection_list_category_id');

        $rootOfSelectedList = InspectionListCategory::where('inspection_list_id', '=', $inspectionListCategory->inspection_list_id)
            ->where('lft', '<=', $inspectionListCategory->lft)
            ->where('rgt', '>=', $inspectionListCategory->rgt)
            ->where('depth', '=', 0)
            ->first();

        if( ! in_array($rootOfSelectedList->id, $assignedRootListIds) )
        {
            $messageBag->add('inspection_list_category_id', 'The list has to be assigned to your group before it can be selected');
        }

        return $messageBag;
    }
}