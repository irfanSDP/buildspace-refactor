<?php namespace PCK\ExtensionOfTimes; 

interface StatusType {

	const PENDING = 1;
	const PENDING_TEXT = 'Pending';

	const DRAFT = 2;
	const DRAFT_TEXT = 'Draft';

	const REJECTED = 4;
	const REJECTED_TEXT = 'Rejected';

	const GRANTED = 8;
	const GRANTED_TEXT = 'Granted';

}