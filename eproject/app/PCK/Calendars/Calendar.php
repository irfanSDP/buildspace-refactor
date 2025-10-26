<?php namespace PCK\Calendars;

use Illuminate\Database\Eloquent\Model;

class Calendar extends Model {

	const EVENT_TYPE_PUBLIC = 1;
	const EVENT_TYPE_STATE = 2;
	const EVENT_TYPE_OTHERS = 4;
	const EVENT_TYPE_PUBLIC_TEXT = 'Public Holiday';
	const EVENT_TYPE_STATE_TEXT = 'State Holiday';
	const EVENT_TYPE_OTHERS_TEXT = 'Others';

	public function country()
	{
		return $this->belongsTo('PCK\Countries\Country');
	}

	public function state()
	{
		return $this->belongsTo('PCK\States\State');
	}

	public static function getEventColor($eventType)
	{
		switch ($eventType)
		{
			case self::EVENT_TYPE_PUBLIC:
				return ( 'bg-color-greenLight txt-color-white' );
			case self::EVENT_TYPE_STATE:
				return ( 'bg-color-blue txt-color-white' );
			default:
				return ( 'bg-color-blueLight txt-color-white' );
		}
	}

	public static function getEventTypeText($eventType)
	{
		switch ($eventType)
		{
			case self::EVENT_TYPE_PUBLIC:
				return self::EVENT_TYPE_PUBLIC_TEXT;
			case self::EVENT_TYPE_STATE:
				return self::EVENT_TYPE_STATE_TEXT;
			default:
				return self::EVENT_TYPE_OTHERS_TEXT;
		}
	}
}