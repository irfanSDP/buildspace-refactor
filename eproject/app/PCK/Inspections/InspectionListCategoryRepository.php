<?php namespace PCK\Inspections;

use PCK\Projects\Project;

class InspectionListCategoryRepository
{
    private $inspectionListRepository;
    private $inspectionListItemRepository;
    private $additionalFieldRepository;

    public function __construct(Project $project, InspectionListRepository $inspectionListRepository, InspectionListItemRepository $inspectionListItemRepository, InspectionListCategoryAdditionalFieldRepository $additionalFieldRepository)
    {
        $this->inspectionListRepository     = $inspectionListRepository;
        $this->inspectionListItemRepository = $inspectionListItemRepository;
        $this->additionalFieldRepository    = $additionalFieldRepository;
    }
    public function getInspectionListCategories($inspectionListId, $parentId = null)
    {
        $inspectionList = InspectionList::find($inspectionListId);

        return InspectionListCategory::getInspectionCategories($inspectionList, $parentId);
    }

    public function getCategoryChildren($inspectionCategoryId)
    {
        $inspectionListCategory = InspectionListCategory::find($inspectionCategoryId);

        return $inspectionListCategory->children()->get();
    }

    public function cloneToProjectInspectionList(Project $project, $inputs)
    {
        $selectedInspectionListCategoryIds = $inputs['selectedInspectionListCategoryIds'];

        foreach($selectedInspectionListCategoryIds as $inspectionListCategoryId)
        {
            $inspectionListCategory = InspectionListCategory::find($inspectionListCategoryId);
            $hierarchy              = $inspectionListCategory->getDescendantsAndSelf()->toHierarchy();

            $this->cloneCategories($project->inspectionLists->first(), $hierarchy);
        }
    }

    private function cloneCategories(InspectionList $inspectionList, $masterListCategoryHierarchy)
    {
        foreach($masterListCategoryHierarchy as $masterCategory)
        {
            $this->createCategory($inspectionList, $masterCategory);
        }
    }

    private function createCategory(InspectionList $inspectionList, InspectionListCategory $masterCategory, $parentId = null)
    {
        $category = new InspectionListCategory();
        $category->inspection_list_id = $inspectionList->id;
        $category->parent_id          = $parentId;
        $category->name               = $masterCategory->name;
        $category->type               = $masterCategory->type;
        $category->priority           = InspectionListCategory::getNextFreePriority($inspectionList->id, $parentId);
        $category->save();

        if($category->isTypeListItem())
        {
            // clone list items
            $rootInspectionListItems = InspectionListItem::getRootInspetionListItems($masterCategory);

            foreach($rootInspectionListItems as $rootListItem)
            {
                $hierarchy = $rootListItem->getDescendantsAndSelf()->toHierarchy();
                $this->inspectionListItemRepository->cloneListItems($category, $hierarchy);
            }

            // clone additional fields
            $this->additionalFieldRepository->cloneAdditonalFields($masterCategory, $category);
        }

        foreach($masterCategory['children'] as $childCategory)
        {
            $this->createCategory($inspectionList, $childCategory, $category->id);
        }
    }

    public function addCategory($inputs)
    {
        $hasParent        = ($inputs['parentId'] != "");
        $parentId         = $hasParent ? $inputs['parentId'] : null;
        $inspectionListId = $hasParent ? InspectionListCategory::find($inputs['parentId'])->inspection_list_id : $inputs['inspectionListId'];

        $category = new InspectionListCategory();
        $category->inspection_list_id = $inspectionListId;
        $category->{$inputs['field']} = $inputs['val'];
        $category->priority = InspectionListCategory::getNextFreePriority($inspectionListId, $parentId);
        $category->save();

        if($hasParent)
        {
            $category = InspectionListCategory::find($category->id);
            $parentNode = InspectionListCategory::find($inputs['parentId']);
            $category->makeChildOf($parentNode);
        }

        return InspectionListCategory::find($category->id);
    }

    public function updateCategory($inputs)
    {
        $category      = InspectionListCategory::find($inputs['id']);
        $originalType  = $category->type;
        $newType       = $inputs['type'];
        $isTypeChanged = ($originalType != $newType);

        if(($inputs['field'] == 'type') && $isTypeChanged)
        {
            if($newType == InspectionListCategory::TYPE_INSPECTION_CATEGORY)
            {
                InspectionListItem::purge($category->id);
                InspectionListCategoryAdditionalField::purge($category->id);
            }
            else
            {
                InspectionListCategory::purgeChildren($category->id);
            }
        }

        $category = InspectionListCategory::find($inputs['id']);
        $category->inspection_list_id = $inputs['inspectionListId'];
        $category->{$inputs['field']} = $inputs['val'];
        $category->save();

        return InspectionListCategory::find($category->id);
    }

    public function deleteCategory($inspectionCategoryId)
    {
        $category = InspectionListCategory::find($inspectionCategoryId);
        $category->delete();

        InspectionListCategory::updatePriority($category);
    }

    public function changeListCategoryTypeCheck($inspectionCategoryId, $inputs)
    {
        $inspectionListCategory = InspectionListCategory::find($inputs['id']);

        if($inputs['type'] == InspectionListCategory::TYPE_INSPECTION_CATEGORY)
        {
            if($inspectionListCategory->inspectionListItems->count() > 0) return true;
            if($inspectionListCategory->additionalFields->count() > 0) return true;
        }
        else
        {
            if($inspectionListCategory->getDescendants()->count() > 0) return true;
        }

        return false;
    }
}

