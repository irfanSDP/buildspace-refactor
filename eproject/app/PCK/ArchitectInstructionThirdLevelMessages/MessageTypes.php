<?php namespace PCK\ArchitectInstructionThirdLevelMessages;

interface MessageTypes {

	const TYPE_YES = 'Yes';

	const TYPE_NO_OUTSTANDING = 'No. There are still outstanding works to be carried out in order to comply with the AI.';

	const TYPE_NO_DID_NOT_COMPLY = 'No. The Contractor did not comply with the AI by the deadline. The Employer may employ and pay other Person to execute any work which may be necessary to give effect to the AI.';

}