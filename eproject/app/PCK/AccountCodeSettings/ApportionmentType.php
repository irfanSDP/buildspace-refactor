<?php namespace PCK\AccountCodeSettings;

use Illuminate\Database\Eloquent\Model;

class ApportionmentType extends Model
{
    protected $table = 'apportionment_types';

    public static function nameIsUnique($name)
    {
        return ( self::where('name', '=', $name)->count() == 0 );
    }
}

