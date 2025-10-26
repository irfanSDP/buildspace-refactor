<?php namespace PCK\RequestForVariation;

use PCK\Helpers\DBTransaction;
use PCK\Users\User;
use PCK\RequestForVariation\RequestForVariationCategory;

class RequestForVariationCategoryRepository
{
    public function getRfvCategories()
    {
        $rfvCategories = [];

        foreach(RequestForVariationCategory::orderBy('id', 'ASC')->get() as $rfvCategory)
        {
            array_push($rfvCategories, [
                'id'                     => $rfvCategory->id,
                'description'            => $rfvCategory->name,
                'kpi_limit'              => $rfvCategory->kpi_limit,
                'route_category_update'  => route('requestForVariation.category.update', [$rfvCategory->id]),
                'route_category_delete'  => route('requestForVariation.category.delete', [$rfvCategory->id]),
                'route_kpi_limit_update' => route('requestForVariation.category.kpi.edit', [$rfvCategory->id]),
                'route_editable_check'   => route('requestForVariation.category.editable.check', [$rfvCategory->id]),
            ]);
        }

        return $rfvCategories;
    }

    public function createNewRfvCategory($inputs)
    {
        $success = false;

        try
        {
            $requestForVariationCategory = new RequestForVariationCategory();
            $requestForVariationCategory->name = $inputs['description'];
            $requestForVariationCategory->save();

            $success = true;
        }
        catch(\Exception $exception)
        {
            \Log::error('RequestForVariationCategoryRepository@createNewRfvCategory() : ' . $exception->getMessage());
        }

        return $success;
    }

    public function updateRfvCategory($rfvCategoryId, $inputs)
    {
        $success = false;

        try
        {
            $rfvCategory = RequestForVariationCategory::find($rfvCategoryId);
            $rfvCategory->name = $inputs['description'];
            $rfvCategory->save();

            $success = true;
        }
        catch(\Exception $exception)
        {
            \Log::error('RequestForVariationCategoryRepository@updateRfvCategory() : ' . $exception->getMessage());
        }

        return $success;
    }

    public function deleteRfvCategory($rfvCategoryId)
    {
        $success = false;

        try
        {
            $rfvCategory = RequestForVariationCategory::find($rfvCategoryId);
            $rfvCategory->delete();

            $success = true;
        }
        catch(\Exception $exception)
        {
            \Log::error('RequestForVariationCategoryRepository@deleteRfvCategory() : ' . $exception->getMessage());
        }

        return $success;
    }

    public function kpiLimitUpdate($rfvCategoryId, $inputs)
    {
        try
        {
            $disableKpiLimit = isset($inputs['disableKpiLimit']);
            $kpiLimit = $disableKpiLimit ? null : $inputs['kpiLimit'];
            $transaction = new DBTransaction();

            $transaction->begin();

            $rfvCategory = RequestForVariationCategory::find($rfvCategoryId);

            if($rfvCategory->kpi_limit == $kpiLimit) return;

            $rfvCategory->kpi_limit = $kpiLimit;
            $rfvCategory->save();

            // refresh the model
            $rfvCategory = RequestForVariationCategory::find($rfvCategoryId);

            RequestForVariationCategoryKpiLimitUpdateLog::createEntry($rfvCategory, $rfvCategory->kpi_limit, $inputs['remarks']);

            $transaction->commit();
        }
        catch(\Exception $exception)
        {
            \Log::error('RequestForVariationCategoryRepository@kpiLimitUpdate() : ' . $exception->getMessage());
            $transaction->rollback();
        }
    }
}

