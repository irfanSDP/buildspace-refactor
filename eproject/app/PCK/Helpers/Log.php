<?php

namespace PCK\Helpers;

use Carbon\Carbon;
use PCK\Users\User;

class Log {

    public static function present(User $user, $timestamp, $action = null, $elaboration = null)
    {
        if( is_null($action) ) $action = trans('general.updatedBy');

        $updatedAt = Carbon::parse($timestamp)->format(\Config::get('dates.created_and_updated_at_formatting'));

        $string = "<span style='color: blue'>{$updatedAt}</span> {$action} <span style='color: green;'>{$user->name}</span>";

        if( $elaboration && ! empty( trim($elaboration) ) ) $string .= " ({$elaboration})";

        return $string;
    }

}