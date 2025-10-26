<?php namespace PCK\Helpers;

use Carbon\Carbon;
use InvalidArgumentException;

class DateTime {

    public static function getTimeZoneTime($dateTime, $toTimezone, $fromTimezone = null)
    {
        if( ! $fromTimezone ) $fromTimezone = getenv('TIMEZONE');

        $convertedDateTime = new Carbon($dateTime, $fromTimezone);

        try
        {
            $convertedDateTime->timezone($toTimezone);
        }
        catch(InvalidArgumentException $e)
        {
            // Fallback to default time.
            // This fallback is included because the errors are not yet properly handled if the timezone is updated with an invalid value.
            // Todo: Temporary arrangement. This should not fail silently.
            $convertedDateTime->timezone(getenv('TIMEZONE'));
        }

        return $convertedDateTime;
    }

    public static function getTimeZoneFormat($timestamp)
    {
        if( $timestamp instanceof Carbon ) $timestamp = $timestamp->toDateTimeString();

        foreach(\Config::get('dates') as $format)
        {
            if( Carbon::parse($timestamp)->format($format) === $timestamp ) return $format;
        }

        return null;
    }

    public static function formatDuration($hours, $minutes, $seconds, $fullText = false)
    {
        if ($fullText) {
            $hoursText = trans('time.hours');
            $minutesText = trans('time.minutes');
            $secondsText = trans('time.seconds');
        } else {
            $hoursText = trans('time.hourShort');
            $minutesText = trans('time.minuteShort');
            $secondsText = trans('time.secondShort');
        }

        $data = [];
        if (! empty($hours)) {
            $data[] = $hours . ' ' . $hoursText;
        }
        if (! empty($minutes)) {
            $data[] = $minutes . ' ' . $minutesText;
        }
        if (! empty($seconds)) {
            $data[] = $seconds . ' ' . $secondsText;
        }

        return implode(' ', $data);
    }

    public static function secondsToDuration($seconds, $fullText = false)
    {
        // guard: make sure it's a non-negative integer
        $sec = (int) ($seconds ?? 0);
        if ($sec < 0) {
            $sec = 0;
        }

        if ($sec === 0) {
            return 0;
        }
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime('@' . $sec);
        $diff = $dtF->diff($dtT);

        return self::formatDuration($diff->h, $diff->i, $diff->s, $fullText);
    }
}