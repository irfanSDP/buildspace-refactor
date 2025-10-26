<?php namespace PCK\Helpers;

use Illuminate\Database\Eloquent\Collection;

class Arrays {

    /**
     * Adds an element into the array,
     * if it does not exist in the array.
     *
     * @param array $array
     * @param       $value
     * @param null  $key
     *
     * @return bool
     */
    public static function addUnique(array &$array, $value, $key = null)
    {
        if( in_array($value, $array) ) return false;

        if( $key )
        {
            $array[ $key ] = $value;
        }
        else
        {
            $array[] = $value;
        }

        return true;
    }

    /**
     * Returns true if non of the items in the array are empty.
     *
     * @param array $array
     *
     * @return bool
     */
    public static function noneEmpty(array $array)
    {
        foreach($array as $arrayItem)
        {
            if( empty( $arrayItem ) ) return false;
        }

        return true;
    }

    public static function arrayValuesEmpty(array $array)
    {
        return count(array_filter($array)) == 0;
    }

    public static function hasDuplicates(array $array)
    {
        $dupe_array = array();

        foreach($array as $val)
        {
            if( ++$dupe_array[ $val ] > 1 ) return true;
        }

        return false;
    }

    public static function isDuplicate(array $array, $itemKey)
    {
        $processed = array();

        foreach($array as $key => $value)
        {
            if( ( $itemKey == $key ) && in_array($value, $processed) ) return true;

            $processed[ $key ] = $value;
        }

        return false;
    }

    public static function collectionToArray(Collection $collection)
    {
        $array = array();

        foreach($collection as $key => $item)
        {
            $array[ $key ] = $item;
        }

        return $array;
    }

    public static function haveSameValues(...$arrays)
    {
        if(count($arrays) === 1) return true;

        $differences = [];

        foreach($arrays as $key => $currentArray)
        {
            if(!isset($arrays[$key+1])) continue;

            $nextArray = $arrays[$key+1];

            $differences[] = array_diff($currentArray,$nextArray);
            $differences[] = array_diff($nextArray, $currentArray);
        }

        return empty(array_merge(...$differences));
    }
}