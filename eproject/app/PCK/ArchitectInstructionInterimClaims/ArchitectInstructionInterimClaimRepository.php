<?php namespace PCK\ArchitectInstructionInterimClaims;

use PCK\Users\User;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;
use PCK\ArchitectInstructions\ArchitectInstruction;

class ArchitectInstructionInterimClaimRepository extends BaseModuleRepository {

	private $aiInterimClaim;

	protected $events;

	public function __construct(ArchitectInstructionInterimClaim $aiInterimClaim, Dispatcher $events)
	{
		$this->aiInterimClaim = $aiInterimClaim;
		$this->events         = $events;
	}

	public function add(ArchitectInstruction $ai, User $user, array $inputs)
	{
		$ic = $this->aiInterimClaim;

		$ic->architect_instruction_id = $ai->id;
		$ic->interim_claim_id         = $inputs['interim_claim_id'];
		$ic->created_by               = $user->id;
		$ic->subject                  = $inputs['subject'];
		$ic->letter_to_contractor     = $inputs['letter_to_contractor'];

		$ic = $this->save($ic);

		$this->saveAttachments($ic, $inputs);

		$this->sendEmailNotification($ai->project, $ai, [ Role::CONTRACTOR ], 'architect_instruction', 'ai.show');
		$this->sendSystemNotification($ai->project, $ai, [ Role::CONTRACTOR ], 'architect_instruction', 'ai.show');

		return $ic;
	}

	public function save(ArchitectInstructionInterimClaim $instance)
	{
		$instance->save();

		return $instance;
	}

}