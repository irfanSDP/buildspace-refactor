<?php namespace PCK\Conversations;

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

			case StatusType::SENT:
				$text = StatusType::SENT_TEXT;
				break;

			case StatusType::INBOX:
				$text = StatusType::INBOX_TEXT;
				break;

			default:
				throw new \InvalidArgumentException('Invalid Interim Claim\'s Type');
		}

		return $text;
	}

}