<?php namespace PCK\TenderRecommendationOfTendererInformation;

abstract class ContractorCommitmentStatus {

	CONST OK = 1;
	CONST PENDING = 2;
	CONST REJECT = 4;
	CONST TENDER_OK = 8;
	CONST TENDER_WITHDRAW = 16;

	public static function getRecommendOfTendererDropDownListing($locale = null)
	{
		return array(
			self::OK	  => self::getText(self::OK, $locale),
			self::PENDING => self::getText(self::PENDING, $locale),
			self::REJECT  => self::getText(self::REJECT, $locale),
		);
	}

	public static function getListOfTendererDropDownListing($locale = null)
	{
		return self::getRecommendOfTendererDropDownListing($locale);
	}

	public static function getCallingTenderDropDownListing($locale = null)
	{
		return array(
			self::TENDER_OK		  => self::getText(self::TENDER_OK, $locale),
			self::TENDER_WITHDRAW => self::getText(self::TENDER_WITHDRAW, $locale),
		);
	}

	public static function getText($key, $locale = null)
	{
		$text = null;

		switch ($key)
		{
			case self::OK:
				$text = is_null($locale) ? trans('forms.yes') : trans('forms.yes', [], 'messages', $locale);
				break;

			case self::PENDING:
				$text = is_null($locale) ? trans('forms.pending') : trans('forms.pending', [], 'messages', $locale);
				break;

			case self::REJECT:
				$text = is_null($locale) ? trans('forms.no') : trans('forms.no', [], 'messages', $locale);
				break;

			case self::TENDER_OK:
				$text = is_null($locale) ? trans('forms.yes') : trans('forms.yes', [], 'messages', $locale);
				break;

			case self::TENDER_WITHDRAW:
				$text = is_null($locale) ? trans('tenders.withdraw') : trans('tenders.withdraw', [], 'messages', $locale);
				break;
		}

		return $text;
	}
}