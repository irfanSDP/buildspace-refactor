<?php namespace PCK\Helpers;

class Tables {

    public static function comboExists($tableName, array $fieldAndValues)
    {
        $query = \DB::table($tableName);

        foreach($fieldAndValues as $field => $value)
        {
            if( $value === null )
            {
                $query->whereNull($field);
            }
            else
            {
                $query->where($field, '=', $value);
            }
        }

        return ( $query->count() > 0 );
    }

}