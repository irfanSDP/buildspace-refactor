<?php namespace PCK\ArchitectInstructions; 

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

			case StatusType::PENDING:
				$text = StatusType::PENDING_TEXT;
				break;

			case StatusType::NOT_COMPLIED:
				$text = StatusType::NOT_COMPLIED_TEXT;
				break;

			case StatusType::COMPLIED:
				$text = StatusType::COMPLIED_TEXT;
				break;

			case StatusType::WITH_OUTSTANDING_WORKS:
				$text = StatusType::WITH_OUTSTANDING_WORKS_TEXT;
				break;

			default:
				throw new \InvalidArgumentException('Invalid AI\'s Type');
		}

		return $text;
	}

}