<?php namespace PCK\Validators;

use Illuminate\Validation\Validator;

class CustomValidation
{
    public function arrayNotEmpty($field, $values, $parameters)
    {
        $data = array();
        
        foreach ( $values as $value )
        {
            if ( empty( $value ) )
            {
                continue;
            }
            
            $data[] = $value;
        }
        
        if ( count($data) === 0 )
        {
            return false;
        }
        
        return true;
    }

    /*
     * Similiar implementation as Validator::validateUnique but LAravel validateUnique
     * is case sensitive but postgresql unique constraint is not case sensitive (certain table depending on unique constraint definition).
     * We have to add custom validation to handle case insensitive unique validation
     */
    public function validateIUnique($attribute, $value, $parameters)
    {
        if (count($parameters) < 1)
        {
            throw new \InvalidArgumentException("Validation rule iunique requires at least 1 parameters.");
        }

        $table = $parameters[0];

        // The second parameter position holds the name of the column that needs to
        // be verified as unique. If this parameter isn't specified we will just
        // assume that this column to be verified shares the attribute's name.
        $column = isset($parameters[1]) ? $parameters[1] : $attribute;

        list($idColumn, $id) = [null, null];

        if (isset($parameters[2]))
        {
            $idColumn = isset($parameters[3]) ? $parameters[3] : 'id';
            list($idColumn, $id) = [$idColumn, $parameters[2]];//get unique id

            if (strtolower($id) == 'null') $id = null;
        }

        $extra = $this->getUniqueExtra($parameters);

        $query = \DB::table($table)->where(\DB::raw('LOWER('.$column.')'), '=', strtolower($value));

        if ( ! is_null($id) && $id != 'NULL')
        {
            $query->where($idColumn ?: 'id', '<>', $id);
        }

        foreach ($extra as $key => $extraValue)
        {
            if ($extraValue === 'NULL')
            {
                $query->whereNull($key);
            }
            elseif ($extraValue === 'NOT_NULL')
            {
                $query->whereNotNull($key);
            }
            else
            {
                $query->where($key, $extraValue);
            }
        }
        
        return $query->count() == 0;
    }

    protected function getUniqueExtra($parameters)
    {
        if (isset($parameters[4]))
        {
            return $this->getExtraConditions(array_slice($parameters, 4));
        }

        return [];
    }

    protected function getExtraConditions(array $segments)
    {
        $extra = [];

        $count = count($segments);

        for ($i = 0; $i < $count; $i = $i + 2)
        {
            $extra[$segments[$i]] = $segments[$i + 1];
        }

        return $extra;
    }
}