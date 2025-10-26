<?php namespace PCK\InterimClaims;

use Carbon\Carbon;
use PCK\Users\User;
use PCK\Projects\Project;
use PCK\Base\BaseModuleRepository;
use PCK\Calendars\CalendarRepository;

class InterimClaimRepository extends BaseModuleRepository {

	private $ic;

	private $calendarRepo;

	public function __construct(
		InterimClaim $ic,
		CalendarRepository $calendarRepo
	)
	{
		$this->ic           = $ic;
		$this->calendarRepo = $calendarRepo;
	}

	public function all(Project $project)
	{
		return $this->ic->where('project_id', '=', $project->id)
			->orderBy('id', 'desc')
			->get(array(
				'id', 'claim_no', 'month', 'year', 'issue_certificate_deadline',
				'amount_claimed', 'amount_granted', 'status', 'created_at', 'updated_at'
			));
	}

	public static function getICCount(Project $project)
	{
		$icObj = new InterimClaim();

		return \DB::table($icObj->getTable())->where('project_id', '=', $project->id)->count();
	}

	public function find(Project $project, $icId)
	{
		return $this->ic->with(array(
			'project.pam2006Detail', 'attachments.file',
			'architectClaimInformation.createdBy',
			'architectClaimInformation.nettAdditionOmissionAttachments.file', 'architectClaimInformation.grossValuesAttachments.file',
			'contractorClaimInformation.createdBy',
			'contractorClaimInformation.nettAdditionOmissionAttachments.file', 'contractorClaimInformation.grossValuesAttachments.file',
			'qsConsultantClaimInformation.createdBy',
			'qsConsultantClaimInformation.nettAdditionOmissionAttachments.file', 'qsConsultantClaimInformation.grossValuesAttachments.file'
		))->where('id', '=', $icId)
			->where('project_id', '=', $project->id)
			->firstOrFail();
	}

	public function getDropDownListing(Project $project)
	{
		return $this->ic->where('project_id', '=', $project->id)
			->where('status', '=', InterimClaim::GRANTED)
			->orderBy('id', 'desc')
			->lists('claim_no', 'id');
	}

	public function findPreviousClaim(Project $project)
	{
		$data = $this->ic->with('architectClaimInformation')
			->where('project_id', '=', $project->id)
			->orderBy('id', 'desc')
			->limit(2)
			->get();

		if ( isset( $data[1] ) )
		{
			return $data[1];
		}

		return null;
	}

	public function add(Project $project, User $user, array $inputs)
	{
		$ic = $this->ic;

		$ic->project_id                 = $project->id;
		$ic->created_by                 = $user->id;
		$ic->claim_no                   = $inputs['claim_no'];
		$ic->issue_certificate_deadline = $this->calendarRepo->calculateFinalDate($project, Carbon::now(), $project->pam2006Detail->period_of_architect_issue_interim_certificate);
		$ic->month                      = $inputs['month'];
		$ic->year                       = $inputs['year'];
		$ic->note                       = $inputs['note'];
		$ic->status                     = InterimClaim::PENDING;
		$ic->claim_counter              = $this->getMaxClaimCounter($project) + 1;

		$ic = $this->save($ic);

		$this->saveAttachments($ic, $inputs);

		return $ic;
	}

	public function getMaxClaimCounter(Project $project)
	{
		return $this->ic->where('project_id', '=', $project->id)
			->max('claim_counter');
	}

	public function getAllGrantedAmount(Project $project, InterimClaim $ic)
	{
		return $this->ic->where('project_id', '=', $project->id)
			->where('status', '=', InterimClaim::GRANTED)
			->where('claim_counter', '<', $ic->claim_counter)
			->sum('amount_granted');
	}

	private function save(InterimClaim $ic)
	{
		$ic->save();

		return $ic;
	}

}