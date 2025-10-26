<?php namespace PCK\WeatherRecords;

trait StatusTypeTrait {

	public function getStatusAttribute($value)
	{
		return self::getStatusText($value);
	}

	public static function getStatusText($value)
	{
		switch ($value)
		{
			case StatusType::DRAFT:
				$text = StatusType::DRAFT_TEXT;
				break;

			case StatusType::NOT_YET_VERIFY:
				$text = StatusType::NOT_YET_VERIFY_TEXT;
				break;

			case StatusType::VERIFIED:
				$text = StatusType::VERIFIED_TEXT;
				break;

			case StatusType::PREPARING:
				$text = StatusType::PREPARING_TEXT;
				break;

			default:
				throw new \InvalidArgumentException('Invalid Weather Record\'s Type');
		}

		return $text;
	}

}