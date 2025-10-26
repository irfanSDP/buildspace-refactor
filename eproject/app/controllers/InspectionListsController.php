<?php

use PCK\Inspections\InspectionListRepository;
use PCK\Inspections\InspectionListCategoryRepository;
use PCK\Inspections\InspectionList;
use PCK\Inspections\InspectionListCategory;
use PCK\Inspections\InspectionListItem;
use PCK\Projects\Project;
use PCK\Helpers\DBTransaction;

class InspectionListsController extends BaseController
{
    private $inspectionListRepository;
    private $inspectionListCategoryRepository;

    public function __construct(InspectionListRepository $inspectionListRepository, InspectionListCategoryRepository $inspectionListCategoryRepository)
    {
        $this->inspectionListRepository         = $inspectionListRepository;
        $this->inspectionListCategoryRepository = $inspectionListCategoryRepository;
    }

    public function index(Project $project)
    {
        $getInspectionListRoute                          = route('project.inspection.lists.get', [$project->id]);
        $inspectionListStoreRoute                        = route('project.inspection.list.store', [$project->id]);
        $inspectionListCategoryStoreRoute                = route('project.inspection.list.category.add', [$project->id]);
        $inspectionListCategoryUpdateRoute               = route('project.inspection.list.category.update', [$project->id]);
        $inspectionListItemStoreRoute                    = route('project.inspection.list.item.add', [$project->id]);
        $inspectionListItemUpdateRoute                   = route('project.inspection.list.item.update', [$project->id]);
        $inspectionListCategoryAdditonalFieldStoreRoute  = route('project.inspection.list.category.additional.field.add', [$project->id]);
        $inspectionListCategoryAdditonalFieldUpdateRoute = route('project.inspection.list.category.additional.field.update', [$project->id]);

        // create inspection list for project if not exists
        $this->inspectionListRepository->createIfNotExists($project);

        $project->load('inspectionLists');

        return View::make('inspections.index', [
            'project'                                         => $project,
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

    public function getProjectInspectionLists(Project $project)
    {
        $inspectionLists = $this->inspectionListRepository->getInspectionLists($project);
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
                'route_update' => route('project.inspection.list.update', [$project->id, $inspectionList['id']]),
                'route_delete' => route('project.inspection.list.delete', [$project->id, $inspectionList['id']]),
                'route_show'   => route('project.inspection.list.categories.get', [$project->id, $inspectionList['id']]),
            ]);
        }

        array_push($items, [
            'indexNo'      => ++ $count,
            'id'           => -1,
            'project_id'   => $project->id,
            'name'         => null,
            'priority'     => null,
            'route_update' => null,
            'route_delete' => null,
            'route_show'   => null,
        ]);

        return $items;
    }

    public function getInspectionListCategories(Project $project, $inspectionListId)
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
                'route_show'              => $category->isTypeCategory() ? route('project.inspection.list.category.children.get', [$project->id, $category->id]) : route('project.inspection.list.items.get', [$project->id, $category->id]),
                'route_back'              => $parentRecord ? route('project.inspection.list.category.children.get', [$parentRecord->id]) : route('project.inspection.list.categories.get', [$project->id, $inspectionListId]),
                'route_delete'            => route('project.inspection.list.category.delete', [$project->id, $category->id]),
                'route_additonal_fields'  => route('project.inspection.list.category.additional.fields.get', [$project->id, $category->id]),
                'route_change_type_check' => route('project.inspection.list.category.change.type.check', [$project->id, $category->id]),
                'editable'                => $category->checkIsEditable(),
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

    public function store(Project $project)
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

            $count     = $this->inspectionListRepository->getInspectionLists($project)->count();
            $newRecord = $this->inspectionListRepository->create($inputs);

            $item = [
                'indexNo'      => ++ $count,
                'id'           => $newRecord->id,
                'project_id'   => $newRecord->project_id,
                'name'         => $newRecord->name,
                'priority'     => $newRecord->priority,
                'route_update' => route('project.inspection.list.update', [$project->id, $newRecord->id]),
                'route_delete' => route('project.inspection.list.delete', [$project->id, $newRecord->id]),
                'route_show'   => route('project.inspection.list.categories.get', [$project->id, $newRecord->id]),
            ];

            $emptyRow = [
                'indexNo'      => ++ $count,
                'id'           => -1,
                'project_id'   => $project->id,
                'name'         => null,
                'priority'     => null,
                'route_update' => null,
                'route_delete' => null,
                'route_show'   => null,
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

    public function update(Project $project, $inspectionListId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;
        $item    = [];

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $item    = $this->inspectionListRepository->update($inspectionListId, $inputs);

            $item['route_update'] = route('project.inspection.list.update', [$project->id, $item->id]);
            $item['route_delete'] = route('project.inspection.list.delete', [$project->id, $item->id]);
            $item['route_show']   = route('project.inspection.list.categories.get', [$project->id, $item->id]);

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

    public function destroy(Project $project, $inspectionListId)
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

