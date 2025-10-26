<?php namespace PCK\EngineerInstructions;

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

			case StatusType::NOT_YET_CONFIRMED:
				$text = StatusType::NOT_YET_CONFIRMED_TEXT;
				break;

			case StatusType::CONFIRMED:
				$text = StatusType::CONFIRMED_TEXT;
				break;

			default:
				throw new \InvalidArgumentException('Invalid Engineer\'s Instruction Status');
		}

		return $text;
	}

}