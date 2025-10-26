<?php namespace PCK\Settings;

use Illuminate\Database\Eloquent\Model;

class SystemSettings extends Model {

    protected $table = 'system_settings';

    public static function getValue($column)
    {
        $systemSettings = self::first();

        return $systemSettings->{$column};
    }

    public static function setValue($column, $value)
    {
        $systemSettings = self::first();

        $systemSettings->{$column} = $value;

        return $systemSettings->save();
    }

    public static function toggle($column)
    {
        $systemSettings = self::first();

        $systemSettings->{$column} = ! $systemSettings->{$column};

        return $systemSettings->save();
    }
}