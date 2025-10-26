<?php namespace PCK\Base;

use Carbon\Carbon;

class NotificationDateHelper {

	CONST TODAY_TEXT = 'Today';

	CONST YESTERDAY_TEXT = 'Yesterday';

	public static function generateDateFormat($currentDate)
	{
		$currentDate = Carbon::parse($currentDate);

		$displayText = null;

		if ( $currentDate->isToday() )
		{
			$displayText = self::TODAY_TEXT;
		}

		if ( $currentDate->isYesterday() )
		{
			$displayText = self::YESTERDAY_TEXT;
		}

		if ( is_null($displayText) )
		{
			$displayText = $currentDate->format('F d, Y');
		}

		return $displayText;
	}

}