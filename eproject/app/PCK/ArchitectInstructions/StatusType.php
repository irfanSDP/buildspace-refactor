<?php namespace PCK\ArchitectInstructions;

interface StatusType {

	const PENDING = 1;
	const PENDING_TEXT = 'Pending';

	const DRAFT = 2;
	const DRAFT_TEXT = 'Draft';

	const NOT_COMPLIED = 4;
	const NOT_COMPLIED_TEXT = 'Not Complied';

	const COMPLIED = 8;
	const COMPLIED_TEXT = 'Complied';

	const WITH_OUTSTANDING_WORKS = 16;
	const WITH_OUTSTANDING_WORKS_TEXT = 'With Outstanding Works';

}