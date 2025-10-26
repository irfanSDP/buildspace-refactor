<?php namespace PCK\EngineerInstructions;

interface StatusType {

	const DRAFT = 1;
	const DRAFT_TEXT = 'Draft';

	const NOT_YET_CONFIRMED = 2;
	const NOT_YET_CONFIRMED_TEXT = 'Not Yet Confirmed by AI';

	const CONFIRMED = 4;
	const CONFIRMED_TEXT = 'Confirmed by AI';

}