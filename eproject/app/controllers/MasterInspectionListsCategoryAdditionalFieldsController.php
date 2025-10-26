<?php

use PCK\Inspections\InspectionListCategoryAdditionalField;
use PCK\Inspections\InspectionListCategoryAdditionalFieldRepository;
use PCK\Helpers\DBTransaction;

class MasterInspectionListsCategoryAdditionalFieldsController extends Controller
{
    private $additionalFieldRepository;

    public function __construct(InspectionListCategoryAdditionalFieldRepository $additionalFieldRepository)
    {
        $this->additionalFieldRepository = $additionalFieldRepository;
    }

    public function getAdditionalFields($inspectionListCategoryId)
    {
        $records          = $this->additionalFieldRepository->getAdditionalFields($inspectionListCategoryId);
        $additionalFields = [];
        $count            = 0;

        foreach($records as $record)
        {
            array_push($additionalFields, [
                'indexNo'                     => ++ $count,
                'id'                          => $record->id,
                'inspection_list_category_id' => $record->inspection_list_category_id,
                'name'                        => $record->name,
                'value'                       => $record->value,
                'priority'                    => $record->priority,
                'route_delete'                => route('master.inspection.list.category.additional.field.delete', [$record->id]),
                'editable'                    => true,
            ]);
        }

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

        return $additionalFields;
    }

    public function fieldAdd()
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

            $count     = $this->additionalFieldRepository->getAdditionalFields($inputs['inspectionListCategoryId'])->count();
            $newRecord = $this->additionalFieldRepository->fieldAdd($inputs);

            $item = [
                'indexNo'                     => ++ $count,
                'id'                          => $newRecord->id,
                'inspection_list_category_id' => $newRecord->inspection_list_category_id,
                'name'                        => $newRecord->name,
                'value'                       => $newRecord->value,
                'priority'                    => $newRecord->priority,
                'route_delete'                => route('master.inspection.list.category.additional.field.delete', [$newRecord->id]),
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
        
        return Response::json([
            'success'  => $success,
            'errors'   => $errors,
            'item'     => $item,
            'emptyRow' => $emptyRow,
        ]);
    }

    public function fieldUpdate()
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;
        $item    = [];

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $item = $this->additionalFieldRepository->fieldUpdate($inputs);
            
            $item['route_delete'] = route('master.inspection.list.category.additional.field.delete', [$item->id]);
            $item['editable']     = true;

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

    public function fieldDelete($additionalFieldId)
    {
        $errors  = null;
        $success = false;

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

        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }
}

