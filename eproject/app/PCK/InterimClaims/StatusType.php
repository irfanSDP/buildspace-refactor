<?php namespace PCK\InterimClaims;

interface StatusType {

	const PENDING = 1;
	const PENDING_TEXT = 'Pending';

	const REJECTED = 2;
	const REJECTED_TEXT = 'Rejected';

	const GRANTED = 4;
	const GRANTED_TEXT = 'Granted';

}