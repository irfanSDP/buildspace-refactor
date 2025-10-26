<?php

use PCK\Inspections\InspectionListRepository;
use PCK\Inspections\InspectionListCategoryRepository;
use PCK\Inspections\InspectionList;
use PCK\Inspections\InspectionListCategory;
use PCK\Inspections\InspectionListItem;
use PCK\Projects\Project;
use PCK\Helpers\DBTransaction;

class MasterInspectionListsController extends BaseController
{
    private $inspectionListRepository;
    private $inspectionListCategoryRepository;

    public function __construct(InspectionListRepository $inspectionListRepository, InspectionListCategoryRepository $inspectionListCategoryRepository)
    {
        $this->inspectionListRepository = $inspectionListRepository;
        $this->inspectionListCategoryRepository = $inspectionListCategoryRepository;
    }

    public function index()
    {
        $getInspectionListRoute                          = route('master.inspection.lists.get');
        $inspectionListStoreRoute                        = route('master.inspection.list.store');
        $inspectionListCategoryStoreRoute                = route('master.inspection.list.category.add');
        $inspectionListCategoryUpdateRoute               = route('master.inspection.list.category.update');
        $inspectionListItemStoreRoute                    = route('master.inspection.list.item.add');
        $inspectionListItemUpdateRoute                   = route('master.inspection.list.item.update');
        $inspectionListCategoryAdditonalFieldStoreRoute  = route('master.inspection.list.category.additional.field.add');
        $inspectionListCategoryAdditonalFieldUpdateRoute = route('master.inspection.list.category.additional.field.update');

        return View::make('inspections.index', [
            'getInspectionListRoute'                          => $getInspectionListRoute,
            'inspectionListStoreRoute'                        => $inspectionListStoreRoute,
            'inspectionListCategoryStoreRoute'                => $inspectionListCategoryStoreRoute,
            'inspectionListCategoryUpdateRoute'               => $inspectionListCategoryUpdateRoute,
            'inspectionListItemStoreRoute'                    => $inspectionListItemStoreRoute,
            'inspectionListItemUpdateRoute'                   => $inspectionListItemUpdateRoute,
            'inspectionListCategoryAdditonalFieldStoreRoute'  => $inspectionListCategoryAdditonalFieldStoreRoute,
            'inspectionListCategoryAdditonalFieldUpdateRoute' => $inspectionListCategoryAdditonalFieldUpdateRoute,
            'categoryTypes'                                   => InspectionListCategory::getInspectionListCategoryTypes(),
            'listItemTypes'                                   => InspectionListItem::getInspectionListItemTypes(),
        ]);
    }

    public function getMasterInspectionLists()
    {
        $inspectionLists = $this->inspectionListRepository->getInspectionLists();
        $items           = [];
        $count           = 0;

        foreach($inspectionLists as $inspectionList)
        {
            array_push($items, [
                'indexNo'      => ++ $count,
                'id'           => $inspectionList->id,
                'project_id'   => $inspectionList->project_id,
                'name'         => $inspectionList->name,
                'priority'     => $inspectionList->priority,
                'route_update' => route('master.inspection.list.update', [$inspectionList['id']]),
                'route_delete' => route('master.inspection.list.delete', [$inspectionList['id']]),
                'route_show'   => route('master.inspection.list.categories.get', [$inspectionList['id']]),
            ]);
        }

        array_push($items, [
            'indexNo'      => ++ $count,
            'id'           => -1,
            'project_id'   => null,
            'name'         => null,
            'priority'     => null,
            'route_update' => null,
            'route_delete' => null,
            'route_show'   => null,
        ]);

        return Response::json($items);
    }

    public function getMasterInspectionListsSelection()
    {
        $inspectionLists = $this->inspectionListRepository->getInspectionLists();
        $items           = [];
        
        foreach($inspectionLists as $inspectionList)
        {
            array_push($items, [
                'id'         => $inspectionList->id,
                'name'       => $inspectionList->name, 
                'route_show' => route('master.inspection.list.categories.selection.get', [$inspectionList['id']]),
            ]);
        }

        return Response::json($items);
    }

    public function getMasterInspectionListCategoriesSelection($inspectionListId)
    {
        $categories               = $this->inspectionListCategoryRepository->getInspectionListCategories($inspectionListId);
        $inspectionListCategories = [];

        foreach($categories as $category)
        {
            $parentRecord = $category->parent()->first();

            array_push($inspectionListCategories, [
                'id'                      => $category->id,
                'inspection_list_id'      => $category->inspection_list_id,
                'name'                    => $category->name,
                'type'                    => $category->type,
                'parent_id'               => $category->parent_id,
                'depth'                   => $category->depth,
                'priority'                => $category->priority,
                'route_show'              => $category->isTypeCategory() ? route('master.inspection.list.category.children.selection.get', [$category->id]) : null,
                'route_back'              => $parentRecord ? route('master.inspection.list.category.children.selection.get', [$parentRecord->id]) : route('master.inspection.list.categories.selection.get', [$category->inspection_list_id]),
            ]);
        }

        return Response::json($inspectionListCategories);
    }

    public function getInspectionListCategories($inspectionListId)
    {
        $categories               = $this->inspectionListCategoryRepository->getInspectionListCategories($inspectionListId);
        $count                    = 0;
        $inspectionListCategories = [];

        foreach($categories as $category)
        {
            $parentRecord = $category->parent()->first();

            array_push($inspectionListCategories, [
                'indexNo'                 => ++ $count,
                'id'                      => $category->id,
                'inspection_list_id'      => $category->inspection_list_id,
                'name'                    => $category->name,
                'type'                    => $category->type,
                'parent_id'               => $category->parent_id,
                'depth'                   => $category->depth,
                'priority'                => $category->priority,
                'route_show'              => $category->isTypeCategory() ? route('master.inspection.list.category.children.get', [$category->id]) : route('master.inspection.list.items.get', [$category->id]),
                'route_back'              => $parentRecord ? route('master.inspection.list.category.children.get', [$parentRecord->id]) : route('master.inspection.list.categories.get', [$inspectionListId]),
                'route_delete'            => route('master.inspection.list.category.delete', [$category->id]),
                'route_additonal_fields'  => route('master.inspection.list.category.additional.fields.get', [$category->id]),
                'route_change_type_check' => route('master.inspection.list.category.change.type.check', [$category->id]),
                'editable'                => true,
            ]);
        }

        array_push($inspectionListCategories, [
            'indexNo'                 => ++ $count,
            'id'                      => -1,
            'inspection_list_id'      => intval($inspectionListId),
            'name'                    => null,
            'type'                    => InspectionListCategory::TYPE_INSPECTION_CATEGORY,
            'parent_id'               => null,
            'depth'                   => null,
            'priority'                => null,
            'route_show'              => null,
            'route_back'              => null,
            'route_delete'            => null,
            'route_additonal_fields'  => null,
            'route_change_type_check' => null,
            'editable'                => true,
        ]);

        return Response::json($inspectionListCategories);
    }

    public function store()
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

            $count     = $this->inspectionListRepository->getInspectionLists()->count();
            $newRecord = $this->inspectionListRepository->create($inputs);

            $item = [
                'indexNo'      => ++ $count,
                'id'           => $newRecord->id,
                'project_id'   => $newRecord->project_id,
                'name'         => $newRecord->name,
                'priority'     => $newRecord->priority,
                'route_update' => route('master.inspection.list.update', [$newRecord->id]),
                'route_delete' => route('master.inspection.list.delete', [$newRecord->id]),
                'route_show'   => route('master.inspection.list.categories.get', [$newRecord->id]),
            ];

            $emptyRow = [
                'indexNo'      => ++ $count,
                'id'           => -1,
                'project_id'   => null,
                'name'         => null,
                'priority'     => null,
                'route_update' => null,
                'route_delete' => null,
                'route_show'   => null,
            ];

            $transaction->commit();

            $success   = true;
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

    public function update($inspectionListId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;
        $item    = [];

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $item = $this->inspectionListRepository->update($inspectionListId, $inputs);

            $item['route_update'] = route('master.inspection.list.update', [$item->id]);
            $item['route_delete'] = route('master.inspection.list.delete', [$item->id]);
            $item['route_show']   = route('master.inspection.list.categories.get', [$item->id]);

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

    public function destroy($inspectionListId)
    {
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $this->inspectionListRepository->destroy($inspectionListId);

            $transaction->commit();
            
            $success = true;
        }
        catch(\Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }

        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }
}

