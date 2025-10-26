<?php namespace PCK\Conversations;

interface StatusType {

	const DRAFT = 1;
	const DRAFT_TEXT = 'Draft';

	const SENT = 2;
	const SENT_TEXT = 'Sent';

	const INBOX = 4;
	const INBOX_TEXT = 'Inbox';

}