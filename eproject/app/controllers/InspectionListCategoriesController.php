<?php

use PCK\Inspections\InspectionListCategory;
use PCK\Inspections\InspectionListCategoryRepository;
use PCK\Projects\Project;
use PCK\Helpers\DBTransaction;

class InspectionListCategoriesController extends Controller
{
    private $inspectionListCategoryRepository;

    public function __construct(Project $project, InspectionListCategoryRepository $inspectionListCategoryRepository)
    {
        $this->inspectionListCategoryRepository = $inspectionListCategoryRepository;
    }

    public function getCategoryChildren(Project $project, $inspectionCategoryId)
    {
        $inspectionCategory = InspectionListCategory::find($inspectionCategoryId);
        $categories         = $this->inspectionListCategoryRepository->getCategoryChildren($inspectionCategoryId);
        $categoryChildren   = [];
        $count              = 0;

        foreach($categories as $category)
        {
            array_push($categoryChildren, [
                'indexNo'                 => ++ $count,
                'id'                      => $category->id,
                'inspection_list_id'      => $category->inspection_list_id,
                'name'                    => $category->name,
                'type'                    => $category->type,
                'parent_id'               => $category->parent_id,
                'depth'                   => $category->depth,
                'priority'                => $category->priority,
                'route_back'              => $category->isRoot() ? route('project.inspection.list.categories.get', [$project->id, $category->id]) : route('project.inspection.list.category.children.get', [$project->id, $category->parent()->first()->id]),
                'route_show'              => $category->isTypeCategory() ? route('project.inspection.list.category.children.get', [$project->id, $category->id]) : route('project.inspection.list.items.get', [$project->id, $category->id]),
                'route_delete'            => route('project.inspection.list.category.delete', [$project->id, $category->id]),
                'route_additonal_fields'  => route('project.inspection.list.category.additional.fields.get', [$project->id, $category->id]),
                'route_change_type_check' => route('project.inspection.list.category.change.type.check', [$project->id, $category->id]),
                'editable'                => $category->checkIsEditable() && (!$category->hasUneditableChildren()),
            ]);
        }

            array_push($categoryChildren, [
                'indexNo'                 => count($categoryChildren) + 1,
                'id'                      => -1,
                'inspection_list_id'      => $inspectionCategory->inspection_list_id,
                'name'                    => null,
                'type'                    => InspectionListCategory::TYPE_INSPECTION_CATEGORY,
                'parent_id'               => $inspectionCategory->id,
                'depth'                   => null,
                'priority'                => null,
                'route_back'              => null,
                'route_show'              => null,
                'route_delete'            => null,
                'route_additonal_fields'  => null,
                'route_change_type_check' => null,
                'editable'                => true,
            ]);

        return Response::json($categoryChildren);
    }

    public function cloneToProjectInspectionList(Project $project)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $this->inspectionListCategoryRepository->cloneToProjectInspectionList($project, $inputs);

            $transaction->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }
        
        return Response::json([
            'success'  => $success,
            'errors'   => $errors,
        ]);
    }

    public function categoryAdd(Project $project)
    {
        $inputs   = Input::all();
        $errors   = null;
        $success  = false;
        $item     = [];
        $emptyRow = [];

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $count       = $this->inspectionListCategoryRepository->getInspectionListCategories($inputs['inspectionListId'], $inputs['parentId'])->count();
            $newRecord   = $this->inspectionListCategoryRepository->addCategory($inputs);
            $routeBack   = null;

            $parentRecord = $newRecord->parent()->first();

            $item = [
                'indexNo'                 => ++ $count ,
                'id'                      => $newRecord->id,
                'inspection_list_id'      => $newRecord->inspection_list_id,
                'name'                    => $newRecord->name,
                'type'                    => $newRecord->type,
                'parent_id'               => $newRecord->parent_id,
                'depth'                   => $newRecord->depth,
                'priority'                => $newRecord->priority,
                'route_show'              => $newRecord->isTypeCategory() ? route('project.inspection.list.category.children.get', [$project->id, $newRecord->id]) : route('project.inspection.list.items.get', [$project->id, $newRecord->id]),
                'route_back'              => $parentRecord ? $routeBack = route('project.inspection.list.category.children.get', [$project->id, $parentRecord->id]) : route('project.inspection.list.categories.get', [$project->id, $inputs['inspectionListId']]),
                'route_delete'            => route('project.inspection.list.category.delete', [$project->id, $newRecord->id]),
                'route_additonal_fields'  => route('project.inspection.list.category.additional.fields.get', [$project->id, $newRecord->id]),
                'route_change_type_check' => route('project.inspection.list.category.change.type.check', [$project->id, $newRecord->id]),
                'editable'                => true,
            ];

            $emptyRow = [
                'indexNo'                 => ++ $count,
                'id'                      => -1,
                'inspection_list_id'      => intval($inputs['inspectionListId']),
                'name'                    => null,
                'type'                    => InspectionListCategory::TYPE_INSPECTION_CATEGORY,
                'parent_id'               => $inputs['parentId'],
                'depth'                   => null,
                'priority'                => null,
                'route_show'              => null,
                'route_back'              => null,
                'route_delete'            => null,
                'route_additonal_fields'  => null,
                'route_change_type_check' => null,
                'editable'                => true,
            ];

            $transaction->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }
        
        return Response::json([
            'success'  => $success,
            'errors'   => $errors,
            'item'     => $item,
            'emptyRow' => $emptyRow,
        ]);
    }

    public function categoryUpdate(Project $project)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;
        $item    = [];

        $inspectionListCategory = InspectionListCategory::find($inputs['id']);
        $hasUneditableChildren  = ($inputs['field'] == 'type') ? $inspectionListCategory->hasUneditableChildren() : false;

        if($inspectionListCategory->checkIsEditable() && !$hasUneditableChildren)
        {
            try
            {
                $transaction = new DBTransaction();
                $transaction->begin();

                $item                            = $this->inspectionListCategoryRepository->updateCategory($inputs);
                $item['route_show']              = $item->isTypeCategory() ? route('project.inspection.list.category.children.get', [$project->id, $item->id]) : route('project.inspection.list.items.get', [$project->id, $item->id]);
                $item['route_back']              = route('project.inspection.list.categories.get', [$project->id, $inputs['inspectionListId']]);
                $item['route_delete']            = route('project.inspection.list.category.delete', [$project->id, $item->id]);
                $item['route_additonal_fields']  = route('project.inspection.list.category.additional.fields.get', [$project->id, $item->id]);
                $item['route_change_type_check'] = route('project.inspection.list.category.change.type.check', [$project->id, $item->id]);
                $item['editable']                = $item->checkIsEditable() && (!$item->hasUneditableChildren());

                $transaction->commit();

                $success = true;
            }
            catch(Exception $e)
            {
                $transaction->rollback();
                $errors = $e->getMessage();
            }
        }
        else
        {
            $errors = trans('inspection.beingUsedInProject');
        }
        
        return Response::json([
            'success' => $success,
            'errors'  => $errors,
            'item'    => $item,
        ]);
    }

    public function categoryDelete(Project $project, $inspectionCategoryId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        $inspectionListCategory = InspectionListCategory::find($inputs['id']);

        if($inspectionListCategory->checkIsEditable() && !$inspectionListCategory->hasUneditableChildren())
        {
            try
            {
                $transaction = new DBTransaction();
                $transaction->begin();

                $this->inspectionListCategoryRepository->deleteCategory($inspectionCategoryId);

                $transaction->commit();

                $success = true;
            }
            catch(Exception $e)
            {
                $transaction->rollback();
                $errors = $e->getMessage();
            }
        }
        else
        {
            $errors = trans('inspection.beingUsedInProject');
        }
        
        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function changeListCategoryTypeCheck(Project $project, $inspectionCategoryId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $warning = false;
        $success = false;

        try
        {
            $warning = $this->inspectionListCategoryRepository->changeListCategoryTypeCheck($inspectionCategoryId, $inputs);

            $success = true;
        }
        catch(Exception $e)
        {
            $errors = $e->getMessage();
        }
        
        return Response::json([
            'success' => $success,
            'warning' => $warning,
            'errors'  => $errors,
        ]);
    }
}

