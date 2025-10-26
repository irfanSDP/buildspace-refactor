<?php

use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\VendorCategory\VendorCategory;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\VendorPreQualification\TemplateForm;

class VendorPreQualificationFormLibraryVendorWorkCategoriesController extends \BaseController {

    public function index($vendorGroupId)
    {
        $data = [];

        $relevantWorkCategoryIds = \DB::table('vendor_categories')
            ->join('vendor_category_vendor_work_category', 'vendor_categories.id', '=', 'vendor_category_vendor_work_category.vendor_category_id')
            ->where('vendor_categories.contract_group_category_id', '=', $vendorGroupId)
            ->lists('vendor_work_category_id');

        $records = VendorWorkCategory::whereIn('id', $relevantWorkCategoryIds)
            ->where('hidden', '=', false)
            ->orderBy('name', 'asc')
            ->get();

        foreach($records as $record)
        {
            $form = TemplateForm::getCurrentEditingForm($record->id);
            $templateForm = TemplateForm::getTemplateForm($record->id);
            $vendorCategories = $record->vendorCategories->sortBy('name')->lists('name');

            $data[] = [
                'id'                          => $record->id,
                'name'                        => ! is_null($form) ? $form->weightedNode->name : null,
                'vendorCategoriesArray'       => $vendorCategories,
                'vendorCategories'            => implode(' ', $vendorCategories),
                'workCategory'                => $record->name,
                'status'                      => ! is_null($form) ? TemplateForm::getStatusText($form->status_id) : '',
                'route:approval'              => ( ! is_null($form) ) ? route('vendorPreQualification.formLibrary.form.approval', array($vendorGroupId, $record->id)) : null,
                'route:form'                  => ! is_null($form) ? route('vendorPreQualification.formLibrary.form.node', array($vendorGroupId, $record->id, $form->weighted_node_id)) : null,
                'route:create'                => is_null($form) ? route('vendorPreQualification.formLibrary.form.create', array($vendorGroupId, $record->id)) : null,
                'route:clone'                 => is_null($form) ? route('vendorPreQualification.formLibrary.form.clone-form', array($vendorGroupId, $record->id)) : null,
                'route:edit'                  => ( ! is_null($form) && $form->isDraft() ) ? route('vendorPreQualification.formLibrary.form.edit', array($vendorGroupId, $record->id, $form->id)) : null,
                'route:template'              => ! is_null($templateForm) ? route('vendorPreQualification.formLibrary.form.template', array($vendorGroupId, $record->id)) : null,
                'route:newRevision'           => ( ! is_null($form) && $form->isCompleted() ) ? route('vendorPreQualification.formLibrary.form.newRevision', array($vendorGroupId, $record->id)) : null,
            ];
        }

        //$data[] = ['name' => ''];

        $vendorGroup = ContractGroupCategory::find($vendorGroupId);

        return View::make('vendor_pre_qualification.form_library_vendor_work_categories', compact('vendorGroup', 'data'));
    }
}
