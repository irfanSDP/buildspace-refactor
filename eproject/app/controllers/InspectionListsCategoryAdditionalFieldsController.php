<?php

use PCK\Inspections\InspectionListCategory;
use PCK\Inspections\InspectionListCategoryAdditionalField;
use PCK\Inspections\InspectionListCategoryAdditionalFieldRepository;
use PCK\Projects\Project;
use PCK\Helpers\DBTransaction;

class InspectionListsCategoryAdditionalFieldsController extends Controller
{
    private $additionalFieldRepository;

    public function __construct(InspectionListCategoryAdditionalFieldRepository $additionalFieldRepository)
    {
        $this->additionalFieldRepository = $additionalFieldRepository;
    }

    public function getAdditionalFields(Project $project, $inspectionListCategoryId)
    {
        $inspectionListCategory = InspectionListCategory::find($inspectionListCategoryId);
        $records                = $this->additionalFieldRepository->getAdditionalFields($inspectionListCategory->id);
        $additionalFields       = [];
        $count                  = 0;

        foreach($records as $record)
        {
            array_push($additionalFields, [
                'indexNo'                     => ++ $count,
                'id'                          => $record->id,
                'inspection_list_category_id' => $record->inspection_list_category_id,
                'name'                        => $record->name,
                'value'                       => $record->value,
                'priority'                    => $record->priority,
                'route_delete'                => route('project.inspection.list.category.additional.field.delete', [$project->id, $record->id]),
                'editable'                    => $inspectionListCategory->checkIsEditable(),
            ]);
        }

        if($inspectionListCategory->checkIsEditable())
        {
            array_push($additionalFields, [
                'indexNo'                     => ++ $count,
                'id'                          => -1,
                'inspection_list_category_id' => $inspectionListCategoryId,
                'name'                        => null,
                'value'                       => null,
                'priority'                    => null,
                'route_delete'                => null,
                'editable'                    => true,
            ]);
        }

        return $additionalFields;
    }

    public function fieldAdd(Project $project)
    {
        $inputs   = Input::all();
        $errors   = null;
        $success  = false;
        $item     = [];
        $emptyRow = [];

        $inspectionListCategory = InspectionListCategory::find($inputs['inspectionListCategoryId']);

        if($inspectionListCategory->checkIsEditable())
        {
            try
            {
                $transaction = new DBTransaction();
                $transaction->begin();
    
                $count     = $this->additionalFieldRepository->getAdditionalFields($inputs['inspectionListCategoryId'])->count();
                $newRecord = $this->additionalFieldRepository->fieldAdd($inputs);
    
                $item = [
                    'indexNo'                     => ++ $count,
                    'id'                          => $newRecord->id,
                    'inspection_list_category_id' => $newRecord->inspection_list_category_id,
                    'name'                        => $newRecord->name,
                    'value'                       => $newRecord->value,
                    'priority'                    => $newRecord->priority,
                    'route_delete'                => route('project.inspection.list.category.additional.field.delete', [$project->id, $newRecord->id]),
                    'editable'                    => true,
                ];
    
                $emptyRow = [
                    'indexNo'                     => ++ $count,
                    'id'                          => -1,
                    'inspection_list_category_id' => $inputs['inspectionListCategoryId'],
                    'name'                        => null,
                    'value'                       => null,
                    'priority'                    => null,
                    'route_delete'                => null,
                    'editable'                    => true,
                ];
    
                $transaction->commit();
    
                $success   = true;
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
            'success'  => $success,
            'errors'   => $errors,
            'item'     => $item,
            'emptyRow' => $emptyRow,
        ]);
    }

    public function fieldUpdate(Project $project)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;
        $item    = [];

        $inspectionListCategory = InspectionListCategory::find($inputs['inspectionListCategoryId']);

        if($inspectionListCategory->checkIsEditable())
        {
            try
            {
                $transaction = new DBTransaction();
                $transaction->begin();

                $item = $this->additionalFieldRepository->fieldUpdate($inputs);

                $item['route_delete'] = route('project.inspection.list.category.additional.field.delete', [$project, $item->id]);
                $item['editable']     = $inspectionListCategory->checkIsEditable();

                unset($item['created_at'], $item['updated_at']);

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

    public function fieldDelete(Project $project, $additionalFieldId)
    {
        $errors  = null;
        $success = false;

        $additionalField = InspectionListCategoryAdditionalField::find($additionalFieldId);

        if($additionalField->inspectionListCategory->checkIsEditable())
        {
            try
            {
                $transaction = new DBTransaction();
                $transaction->begin();
    
                $this->additionalFieldRepository->fieldDelete($additionalFieldId);
    
                $transaction->commit();
                
                $success = true;
            }
            catch(\Exception $e)
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
}

