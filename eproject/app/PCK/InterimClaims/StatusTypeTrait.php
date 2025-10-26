<?php namespace PCK\InterimClaims;

trait StatusTypeTrait {

	public function getStatusAttribute($value)
	{
		return self::getStatusText($value);
	}

	public static function getStatusText($value)
	{
		switch ($value)
		{
			case StatusType::PENDING:
				$text = StatusType::PENDING_TEXT;
				break;

			case StatusType::REJECTED:
				$text = StatusType::REJECTED_TEXT;
				break;

			case StatusType::GRANTED:
				$text = StatusType::GRANTED_TEXT;
				break;

			default:
				throw new \InvalidArgumentException('Invalid Interim Claim\'s Type');
		}

		return $text;
	}

}