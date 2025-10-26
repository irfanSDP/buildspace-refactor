<?php namespace PCK\Inspections;

class InspectionListCategoryAdditionalFieldRepository
{
    public function getAdditionalFields($inspectionListCategoryId)
    {
        $inspectionListCategory = InspectionListCategory::find($inspectionListCategoryId);

        return $inspectionListCategory->additionalFields;
    }

    public function cloneAdditonalFields(InspectionListCategory $masterInspectionListCategory, InspectionListCategory $inspectionListCategory)
    {
        foreach($masterInspectionListCategory->additionalFields as $field)
        {
            $additionalField                              = new InspectionListCategoryAdditionalField();
            $additionalField->inspection_list_category_id = $inspectionListCategory->id;
            $additionalField->name                        = $field->name;
            $additionalField->value                       = $field->value;
            $additionalField->priority                    = InspectionListCategoryAdditionalField::getNextFreePriority($inspectionListCategory->id);
            $additionalField->save();
        }
    }

    public function fieldAdd($inputs)
    {
        $additionalField                              = new InspectionListCategoryAdditionalField();
        $additionalField->inspection_list_category_id = $inputs['inspectionListCategoryId'];
        $additionalField->{$inputs['field']}          = $inputs['val'];
        $additionalField->priority                    = InspectionListCategoryAdditionalField::getNextFreePriority($inputs['inspectionListCategoryId']);
        $additionalField->save();

        return InspectionListCategoryAdditionalField::find($additionalField->id);
    }

    public function fieldUpdate($inputs)
    {
        InspectionListCategoryAdditionalField::where('id', $inputs['id'])->update([$inputs['field'] => $inputs['val']]);

        return InspectionListCategoryAdditionalField::find($inputs['id']);
    }

    public function fieldDelete($additionalFieldId)
    {
        $additionalField = InspectionListCategoryAdditionalField::find($additionalFieldId);
        $additionalField->delete();

        InspectionListCategoryAdditionalField::updatePriority($additionalField);
    }
}

