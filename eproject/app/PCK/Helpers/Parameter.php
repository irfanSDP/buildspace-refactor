<?php namespace PCK\Helpers;

use Illuminate\Database\Eloquent\Collection;

class Parameter {

    const LOWER_CASE = 1;
    const UPPER_CASE = 2;
    const REMOVE     = 3;

    /**
     * Returns the input in an array if it was not already an array.
     *
     * @param $input
     *
     * @return array
     */
    public static function toArray($input)
    {
        if( $input == null )
        {
            return array();
        }

        if( ! is_array($input) )
        {
            $input = array( $input );
        }

        return $input;
    }

    /**
     * Returns the input in a collection if it was not already in one.
     *
     * @param $input
     *
     * @return Collection
     */
    public static function toCollection($input)
    {
        if( ! $input )
        {
            return new Collection();
        }

        if( ! ( $input instanceof Collection ) )
        {
            $input = new Collection(array( $input ));
        }

        return $input;
    }

}