<?php

use PCK\Inspections\InspectionListItemRepository;
use PCK\Inspections\InspectionList;
use PCK\Inspections\InspectionListCategory;
use PCK\Inspections\InspectionListItem;
use PCK\Helpers\DBTransaction;

class MasterInspectionListItemsController extends BaseController
{
    private $inspectionListItemRepository;

    public function __construct(InspectionListItemRepository $inspectionListItemRepository)
    {
        $this->inspectionListItemRepository = $inspectionListItemRepository;
    }

    public function getInspectionListItems($inspectionListCategoryId)
    {
        $records             = $this->inspectionListItemRepository->getInspectionListItems($inspectionListCategoryId);
        $inspectionListItems = [];
        $count               = 0;

        foreach($records as $record)
        {
            array_push($inspectionListItems, [
                'indexNo'                     => ++ $count,
                'id'                          => $record->id,
                'inspection_list_category_id' => $record->inspection_list_category_id,
                'parent_id'                   => $record->parent_id,
                'depth'                       => $record->depth,
                'description'                 => $record->description,
                'priority'                    => $record->priority,
                'type'                        => $record->type,
                'route_show'                  => route('master.inspection.list.item.children.get', [$record->id]),
                'route_back'                  => $record->isRoot() ? route('master.inspection.list.items.get', [$record->inspection_list_category_id]) : route('master.inspection.list.item.children.get', [$record->parent_id]),
                'route_delete'                => route('master.inspection.list.item.delete', [$record->id]),
                'route_change_type_check'     => route('master.inspection.list.item.change.type.check', [$record->id]),
                'editable'                    => true,
            ]);
        }

        array_push($inspectionListItems, [
            'indexNo'                     => ++ $count,
            'id'                          => -1,
            'inspection_list_category_id' => $inspectionListCategoryId,
            'parent_id'                   => null,
            'depth'                       => null,
            'description'                 => null,
            'priority'                    => null,
            'type'                        => InspectionListItem::TYPE_ITEM,
            'route_show'                  => null,
            'route_back'                  => null,
            'route_delete'                => null,
            'route_change_type_check'     => null,
            'editable'                    => true,
        ]);

        return Response::json($inspectionListItems);
    }

    public function getInspectionListItemChildren($inspectionListItemId)
    {
        $inspectionListItem  = InspectionListItem::find($inspectionListItemId);
        $records             = $this->inspectionListItemRepository->getInspectionListItemChildren($inspectionListItemId);
        $inspectionListItems = [];
        $count               = 0;

        foreach($records as $record)
        {
            array_push($inspectionListItems, [
                'indexNo'                     => ++ $count,
                'id'                          => $record->id,
                'inspection_list_category_id' => $record->inspection_list_category_id,
                'parent_id'                   => $record->parent_id,
                'depth'                       => $record->depth,
                'description'                 => $record->description,
                'priority'                    => $record->priority,
                'type'                        => $record->type,
                'route_show'                  => route('master.inspection.list.item.children.get', [$record->id]),
                'route_back'                  => route('master.inspection.list.item.children.get', [$record->parent_id]),
                'route_delete'                => route('master.inspection.list.item.delete', [$record->id]),
                'route_change_type_check'     => route('master.inspection.list.item.change.type.check', [$record->id]),
                'editable'                    => true,
            ]);
        }

        array_push($inspectionListItems, [
            'indexNo'                     => ++ $count,
            'id'                          => -1,
            'inspection_list_category_id' => $inspectionListItem->inspection_list_category_id,
            'parent_id'                   => $inspectionListItem->id,
            'depth'                       => null,
            'description'                 => null,
            'priority'                    => null,
            'type'                        => InspectionListItem::TYPE_ITEM,
            'route_show'                  => null,
            'route_back'                  => null,
            'route_delete'                => null,
            'route_change_type_check'     => null,
            'editable'                    => true,
        ]);

        return Response::json($inspectionListItems);
    }

    public function itemAdd()
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

            $count     = $this->inspectionListItemRepository->getInspectionListItems($inputs['inspectionListCategoryId'], $inputs['parentId'])->count();
            $newRecord = $this->inspectionListItemRepository->addItem($inputs);

            $item = [
                'indexNo'                     => ++ $count,
                'id'                          => $newRecord->id,
                'inspection_list_category_id' => $newRecord->inspection_list_category_id,
                'description'                 => $newRecord->description,
                'type'                        => $newRecord->type,
                'parent_id'                   => $newRecord->parent_id,
                'depth'                       => $newRecord->depth,
                'priority'                    => $newRecord->priority,
                'route_show'                  => route('master.inspection.list.item.children.get', [$newRecord->id]),
                'route_back'                  => $newRecord->isRoot() ? route('master.inspection.list.items.get', [$newRecord->inspection_list_category_id]) : route('master.inspection.list.item.children.get', [$newRecord->parent_id]),
                'route_delete'                => route('master.inspection.list.item.delete', [$newRecord->id]),
                'route_change_type_check'     => route('master.inspection.list.item.change.type.check', [$newRecord->id]),
                'editable'                    => true,
            ];

            $emptyRow = [
                'indexNo'                     => ++ $count,
                'id'                          => -1,
                'inspection_list_category_id' => intval($inputs['inspectionListCategoryId']),
                'description'                 => null,
                'type'                        => InspectionListItem::TYPE_ITEM,
                'parent_id'                   => $inputs['parentId'],
                'depth'                       => null,
                'priority'                    => 0,
                'route_show'                  => null,
                'route_back'                  => null,
                'route_delete'                => null,
                'route_change_type_check'     => null,
                'editable'                    => true,
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

    public function itemUpdate()
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;
        $item    = [];

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $item = $this->inspectionListItemRepository->updateItem($inputs);

            $item['route_show']              = route('master.inspection.list.item.children.get', [$item->id]);
            $item['route_back']              = $item->isRoot() ? route('master.inspection.list.items.get', [$item->inspection_list_category_id]) : route('master.inspection.list.item.children.get', [$item->parent_id]);
            $item['route_delete']            = route('master.inspection.list.item.delete', [$item->id]);
            $item['route_change_type_check'] = route('master.inspection.list.item.change.type.check', [$item->id]);

            unset($item['lft'], $item['rgt'], $item['depth'], $item['created_at'], $item['updated_at']);

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

    public function itemDelete($inspectionListItemId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $this->inspectionListItemRepository->deleteItem($inspectionListItemId);

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

    public function changeListItemTypeCheck($inspectionListItemId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $warning = false;
        $success = false;

        try
        {
            $warning = $this->inspectionListItemRepository->changeListItemTypeCheck($inspectionListItemId, $inputs);

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

