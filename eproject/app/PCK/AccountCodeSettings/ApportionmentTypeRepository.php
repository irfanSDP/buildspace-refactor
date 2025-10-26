<?php namespace PCK\AccountCodeSettings;

class ApportionmentTypeRepository
{
    public function getApportionmentTypesTableData()
    {
        $data = [];
        $count = 0;

        foreach (ApportionmentType::all() as $apportionmentType)
        {
            array_push($data, [
                'count'                => ++ $count,
                'id'                   => $apportionmentType->id,
                'name'                 => $apportionmentType->name,
                'route_editable_check' => route('apportionment.editable.check', [$apportionmentType->id]),
                'route_update'         => route('apportionment.type.update', [$apportionmentType->id]),
                'route_delete'         => route('apportionment.type.delete', [$apportionmentType->id]),
            ]);
        }

        return $data;
    }

    public function createNewApportionmentType($inputs)
    {
        $success = false;

        try
        {
            $apportionmentType = new ApportionmentType();
            $apportionmentType->name = $inputs['name'];
            $apportionmentType->save();

            $success = true;
        }
        catch(\Exception $exception)
        {
            \Log::error('ApportionmentTypeRepository@createNewApportionmentType() : ' . $exception->getMessage());
        }

        return $success;
    }

    public function updateApportionmentType($apportionmentTypeId, $inputs)
    {
        $success = false;

        try
        {
            $apportionmentType = ApportionmentType::find($apportionmentTypeId);
            $apportionmentType->name = $inputs['name'];
            $apportionmentType->save();

            $success = true;
        }
        catch(\Exception $exception)
        {
            \Log::error('ApportionmentTypeRepository@updateApportionType() : ' . $exception->getMessage());
        }

        return $success;
    }

    public function deleteApportionmentType($apportionmentTypeId)
    {
        $success = false;

        try
        {
            $apportionmentType = ApportionmentType::find($apportionmentTypeId);
            $apportionmentType->delete();

            $success = true;
        }
        catch(\Exception $exception)
        {
            \Log::error('ApportionmentTypeRepository@deleteApportionmentType() : ' . $exception->getMessage());
        }

        return $success;
    }
}

