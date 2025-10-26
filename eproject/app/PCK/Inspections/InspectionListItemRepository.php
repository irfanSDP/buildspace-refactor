<?php namespace PCK\Inspections;

class InspectionListItemRepository
{
    public function getInspectionListItems($inspectionListCategoryId, $parentId = null)
    {
        $inspectionListCategory = InspectionListCategory::find($inspectionListCategoryId);

        return InspectionListItem::getInspectionListItems($inspectionListCategory, $parentId);
    }

    public function getInspectionListItemChildren($inspectionListItemId)
    {
        $inspectionListItem = InspectionListItem::find($inspectionListItemId);

        return $inspectionListItem->children()->get();
    }

    public function cloneListItems(InspectionListCategory $inspectionListCategory, $masterListItemHierarchy)
    {
        foreach($masterListItemHierarchy as $masterListItem)
        {
            $this->createListItem($inspectionListCategory, $masterListItem);
        }
    }

    private function createListItem(InspectionListCategory $inspectionListCategory, InspectionListItem $masterListItem, $parentId = null)
    {
        $inspectionListItem = new InspectionListItem();
        $inspectionListItem->inspection_list_category_id = $inspectionListCategory->id;
        $inspectionListItem->parent_id                   = $parentId;
        $inspectionListItem->description                 = $masterListItem->description;
        $inspectionListItem->priority                    = InspectionListItem::getNextFreePriority($inspectionListCategory->id, $parentId);
        $inspectionListItem->type                        = $masterListItem->type;
        $inspectionListItem->save();

        foreach($masterListItem['children'] as $childListItem)
        {
            $this->createListItem($inspectionListCategory, $childListItem, $inspectionListItem->id);
        }
    }

    public function addItem($inputs)
    {
        $hasParent                = ($inputs['parentId'] != "");
        $parentId                 = $hasParent ? $inputs['parentId'] : null;
        $inspectionListCategoryId = $hasParent ? InspectionListItem::find($inputs['parentId'])->inspection_list_category_id : $inputs['inspectionListCategoryId'];

        $inspectionListItem = new InspectionListItem();
        $inspectionListItem->inspection_list_category_id = $inspectionListCategoryId;
        $inspectionListItem->{$inputs['field']} = $inputs['val'];
        $inspectionListItem->priority = InspectionListItem::getNextFreePriority($inspectionListCategoryId, $parentId);
        $inspectionListItem->save();

        if($hasParent)
        {
            $inspectionListItem = InspectionListItem::find($inspectionListItem->id);
            $parentNode = InspectionListItem::find($inputs['parentId']);
            $inspectionListItem->makeChildOf($parentNode);
        }

        return InspectionListItem::find($inspectionListItem->id);
    }

    public function updateItem($inputs)
    {
        $inspectionListItem = InspectionListItem::find($inputs['id']);
        $originalType       = $inspectionListItem->type;
        $newType            = $inputs['type'];
        $isTypeChanged      = ($originalType != $newType);

        if(($inputs['field'] == 'type') && $isTypeChanged)
        {
            InspectionListItem::purgeChildren($inspectionListItem->id);
        }

        $inspectionListItem = InspectionListItem::find($inputs['id']);
        $inspectionListItem->inspection_list_category_id = $inputs['inspectionListCategoryId'];
        $inspectionListItem->{$inputs['field']} = $inputs['val'];
        $inspectionListItem->save();

        return InspectionListItem::find($inspectionListItem->id);
    }

    public function deleteItem($inspectionListItemId)
    {
        $inspectionListItem = InspectionListItem::find($inspectionListItemId);
        $inspectionListItem->delete();

        InspectionListItem::updatePriority($inspectionListItem);
    }

    public function changeListItemTypeCheck($inspectionListItemId, $inputs)
    {
        $inspectionListItem = InspectionListItem::find($inputs['id']);

        if($inspectionListItem->getDescendants()->count() > 0) return true;

        return false;
    }
}

