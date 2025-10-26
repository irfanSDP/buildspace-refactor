<?php namespace PCK\Helpers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ModelOperations {

    /**
     * Deletes models in the collection and triggers each deleting/deleted event for said models.
     *
     * @param array|Model $modelsAndCollections
     *
     * @return bool
     */
    public static function deleteWithTrigger($modelsAndCollections)
    {
        $allDeleted = true;

        $modelsAndCollections = Parameter::toArray($modelsAndCollections);

        foreach($modelsAndCollections as $modelOrCollection)
        {
            if( ! self::deleteCollectionWithTrigger(Parameter::toCollection($modelOrCollection)) ) $allDeleted = false;
        }

        return $allDeleted;
    }

    private static function deleteCollectionWithTrigger(Collection $collection)
    {
        $allDeleted = true;

        foreach($collection as $model)
        {
            if( ! $success = $model->forceDelete() ) $allDeleted = false;
        }

        return $allDeleted;
    }

}