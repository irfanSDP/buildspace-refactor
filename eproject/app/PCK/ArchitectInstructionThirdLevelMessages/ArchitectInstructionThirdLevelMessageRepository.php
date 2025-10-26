<?php namespace PCK\ArchitectInstructionThirdLevelMessages;

use PCK\Users\User;
use PCK\Base\Helpers;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;
use PCK\Forms\AIMessageThirdLevelArchitectForm;
use PCK\ArchitectInstructions\ArchitectInstruction;

class ArchitectInstructionThirdLevelMessageRepository extends BaseModuleRepository {

	private $aimThirdLevel;

	protected $events;

	public function __construct(ArchitectInstructionThirdLevelMessage $aimThirdLevel, Dispatcher $events)
	{
		$this->aimThirdLevel = $aimThirdLevel;
		$this->events        = $events;
	}

	public function checkLatestMessagePosterRole($aiId)
	{
		return $this->aimThirdLevel->where('architect_instruction_id', '=', $aiId)
			->orderBy('id', 'desc')
			->first();
	}

	public function add(User $user, ArchitectInstruction $ai, array $inputs)
	{
		$aimTL                           = $this->aimThirdLevel;
		$aimTL->architect_instruction_id = $ai->id;
		$aimTL->created_by               = $user->id;
		$aimTL->subject                  = $inputs['subject'];
		$aimTL->reason                   = $inputs['reason'];

		if ( $user->hasCompanyProjectRole($ai->project, Role::INSTRUCTION_ISSUER) )
		{
			$aimTL->compliance_status = $inputs['compliance_status'];
			$aimTL->type              = Role::INSTRUCTION_ISSUER;
			$sendToRole               = Role::CONTRACTOR;
		}

		if ( $user->hasCompanyProjectRole($ai->project, Role::CONTRACTOR) )
		{
			$aimTL->compliance_date = date('Y-m-d', strtotime($inputs['compliance_date']));
			$aimTL->type            = Role::CONTRACTOR;
			$sendToRole             = Role::INSTRUCTION_ISSUER;
		}

		$aimTL = $this->save($aimTL);

		$this->saveAttachments($aimTL, $inputs);

		$tabId = Helpers::generateTabLink($aimTL->id, AIMessageThirdLevelArchitectForm::accordianId);

		$this->sendEmailNotification($ai->project, $ai, [ $sendToRole ], 'architect_instruction', 'ai.show', $tabId);
		$this->sendSystemNotification($ai->project, $ai, [ $sendToRole ], 'architect_instruction', 'ai.show', $tabId);

		return $aimTL;
	}

	public function save(ArchitectInstructionThirdLevelMessage $aimThirdLevel)
	{
		$aimThirdLevel->save();

		return $aimThirdLevel;
	}

}