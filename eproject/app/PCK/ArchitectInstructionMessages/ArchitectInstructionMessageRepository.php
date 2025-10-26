<?php namespace PCK\ArchitectInstructionMessages;

use PCK\Base\Helpers;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ClauseItems\AttachedClauseItem;
use PCK\ContractGroups\Types\Role;
use PCK\Forms\ArchitectInstructionMessageForm;
use PCK\ArchitectInstructions\ArchitectInstruction;

class ArchitectInstructionMessageRepository extends BaseModuleRepository {

	private $aim;

	protected $events;

	public function __construct(ArchitectInstructionMessage $aim, Dispatcher $events)
	{
		$this->aim    = $aim;
		$this->events = $events;
	}

	public function checkLatestMessagePosterRole($aiId)
	{
		return $this->aim->where('architect_instruction_id', '=', $aiId)
			->orderBy('id', 'desc')
			->first();
	}

	public function add(ArchitectInstruction $ai, array $inputs)
	{
		$user = \Confide::user();

		$aim                           = $this->aim;
		$aim->architect_instruction_id = $ai->id;
		$aim->created_by               = $user->id;
		$aim->subject                  = $inputs['subject'];
		$aim->reason                   = $inputs['reason'];

		if ( $user->hasCompanyProjectRole($ai->project, Role::INSTRUCTION_ISSUER) )
		{
			$aim->type = Role::INSTRUCTION_ISSUER;
		}

		if ( $user->hasCompanyProjectRole($ai->project, Role::CONTRACTOR) )
		{
			$aim->type = Role::CONTRACTOR;
		}

		$aim = $this->save($aim);

		if ( isset( $inputs['clauses'] ) and $user->hasCompanyProjectRole($ai->project, Role::INSTRUCTION_ISSUER) )
		{
            AttachedClauseItem::syncClauses($aim, $inputs['clauses'] ?? array());
		}

		// we will be saving attachment as well if available
		$this->saveAttachments($aim, $inputs);

		// determine which role to be notified
		$sendToRole = ( $aim->type == Role::INSTRUCTION_ISSUER ) ? Role::CONTRACTOR : Role::INSTRUCTION_ISSUER;
		$tabId      = Helpers::generateTabLink($aim->id, ArchitectInstructionMessageForm::accordianId);

		$this->sendEmailNotification($ai->project, $ai, [ $sendToRole ], 'architect_instruction', 'ai.show', $tabId);
		$this->sendSystemNotification($ai->project, $ai, [ $sendToRole ], 'architect_instruction', 'ai.show', $tabId);

		return $aim;
	}

	public function save(ArchitectInstructionMessage $instance)
	{
		$instance->save();

		return $instance;
	}

}