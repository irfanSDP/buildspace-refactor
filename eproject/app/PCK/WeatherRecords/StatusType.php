<?php namespace PCK\WeatherRecords;

interface StatusType {

	const DRAFT = 1;
	const DRAFT_TEXT = 'Draft';

	const NOT_YET_VERIFY = 2;
	const NOT_YET_VERIFY_TEXT = 'Not yet verify';

	const VERIFIED = 4;
	const VERIFIED_TEXT = 'Verified';

	const PREPARING = 8;
	const PREPARING_TEXT = 'Preparing';

}