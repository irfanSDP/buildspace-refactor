<?php

use PCK\Inspections\InspectionListCategory;
use PCK\Inspections\InspectionListCategoryRepository;
use PCK\Helpers\DBTransaction;

class MasterInspectionListCategoriesController extends Controller
{
    private $inspectionListRepository;
    private $inspectionListCategoryRepository;

    public function __construct(InspectionListCategoryRepository $inspectionListCategoryRepository)
    {
        $this->inspectionListCategoryRepository = $inspectionListCategoryRepository;
    }

    public function getCategoryChildren($inspectionCategoryId)
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
                'route_back'              => $category->isRoot() ? route('master.inspection.list.categories.get', [$category->id]) : route('master.inspection.list.category.children.get', [$category->parent()->first()->id]),
                'route_show'              => $category->isTypeCategory() ? route('master.inspection.list.category.children.get', [$category->id]) : route('master.inspection.list.items.get', [$category->id]),
                'route_delete'            => route('master.inspection.list.category.delete', [$category->id]),
                'route_additonal_fields'  => route('master.inspection.list.category.additional.fields.get', [$category->id]),
                'route_change_type_check' => route('master.inspection.list.category.change.type.check', [$category->id]),
                'editable'                => true,
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

    public function getMasterCategoryChildrenSelection($inspectionCategoryId)
    {
        $categories         = $this->inspectionListCategoryRepository->getCategoryChildren($inspectionCategoryId);
        $categoryChildren   = [];

        foreach($categories as $category)
        {
            array_push($categoryChildren, [
                'id'                      => $category->id,
                'inspection_list_id'      => $category->inspection_list_id,
                'name'                    => $category->name,
                'type'                    => $category->type,
                'parent_id'               => $category->parent_id,
                'depth'                   => $category->depth,
                'priority'                => $category->priority,
                'route_show'              => $category->isTypeCategory() ? route('master.inspection.list.category.children.selection.get', [$category->id]) : null,
                'route_back'              => route('master.inspection.list.category.children.selection.get', [$category->parent()->first()->id]),
            ]);
        }

        return Response::json($categoryChildren);
    }

    public function categoryAdd()
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
                'indexNo'                 => ++ $count,
                'id'                      => $newRecord->id,
                'inspection_list_id'      => $newRecord->inspection_list_id,
                'name'                    => $newRecord->name,
                'type'                    => $newRecord->type,
                'parent_id'               => $newRecord->parent_id,
                'depth'                   => $newRecord->depth,
                'priority'                => $newRecord->priority,
                'route_show'              => $newRecord->isTypeCategory() ? route('master.inspection.list.category.children.get', [$newRecord->id]) : route('master.inspection.list.items.get', [$newRecord->id]),
                'route_back'              => $parentRecord ? $routeBack = route('master.inspection.list.category.children.get', [$parentRecord->id]) : route('master.inspection.list.categories.get', [$inputs['inspectionListId']]),
                'route_delete'            => route('master.inspection.list.category.delete', [$newRecord->id]),
                'route_additonal_fields'  => route('master.inspection.list.category.additional.fields.get', [$newRecord->id]),
                'route_change_type_check' => route('master.inspection.list.category.change.type.check', [$newRecord->id]),
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

    public function categoryUpdate()
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;
        $item    = [];

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $item                            = $this->inspectionListCategoryRepository->updateCategory($inputs);
            $item['route_show']              = $item->isTypeCategory() ? route('master.inspection.list.category.children.get', [$item->id]) : route('master.inspection.list.items.get', [$item->id]);
            $item['route_back']              = route('master.inspection.list.categories.get', [$inputs['inspectionListId']]);
            $item['route_delete']            = route('master.inspection.list.category.delete', [$item->id]);
            $item['route_additonal_fields']  = route('master.inspection.list.category.additional.fields.get', [$item->id]);
            $item['route_change_type_check'] = route('master.inspection.list.category.change.type.check', [$item->id]);
            $item['editable']                = true;

            unset($item['created_at'], $item['updated_at']);

            $transaction->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }
        
        return Response::json([
            'success' => $success,
            'errors'  => $errors,
            'item'    => $item,
        ]);
    }

    public function categoryDelete($inspectionCategoryId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

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
        
        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function changeListCategoryTypeCheck($inspectionCategoryId)
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

