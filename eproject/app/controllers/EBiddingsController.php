<?php

use Carbon\Carbon;
use Illuminate\Support\Collection;
use PCK\ContractGroupProjectUsers\ContractGroupProjectUserRepository;
use PCK\EBiddingCommittees\EBiddingCommitteeRepository;
use PCK\EBiddings\EBidding;
use PCK\EBiddings\EBiddingConsoleRepository;
use PCK\EBiddings\EBiddingMode;
use PCK\EBiddings\EBiddingNotificationRepository;
use PCK\EBiddings\EBiddingSessionRepository;
use PCK\EBiddings\EBiddingRepository;
use PCK\EmailReminder\EmailReminder;
use PCK\EmailReminder\EmailReminderRepository;
use PCK\Forms\EBiddingsForm;
use PCK\FormOfTender\FormOfTenderRepository;
use PCK\GeneralSettings\GeneralSetting;
use PCK\Projects\Project;
use PCK\Tenders\TenderRepository;
use PCK\Users\User;
use PCK\Verifier\Verifier;

class EBiddingsController extends \BaseController {

	private $eBiddingsForm;
    private $committeeRepo;
    private $contractRepo;

    protected $eBiddingRepo;
    protected $eBiddingConsoleRepo;
    protected $eBiddingSessionRepo;
    protected $eBiddingReminderRepo;

    protected $ebiddingNotificationRepository;

    protected $tenderRepo;
    protected $formOfTenderRepository;
	
	public function __construct(
        EBiddingsForm $eBiddingsForm,
        EBiddingCommitteeRepository $committeeRepo,
        ContractGroupProjectUserRepository $contractRepo,
        EBiddingRepository $eBiddingRepo,
        EBiddingConsoleRepository $eBiddingConsoleRepo,
        EBiddingSessionRepository $eBiddingSessionRepo,
        EmailReminderRepository $eBiddingReminderRepo,
        EBiddingNotificationRepository $ebiddingNotificationRepository,
        FormOfTenderRepository $formOfTenderRepository,
        TenderRepository $tenderRepo
    ) {
		$this->eBiddingsForm = $eBiddingsForm;
        $this->committeeRepo = $committeeRepo;
        $this->contractRepo = $contractRepo;
        $this->eBiddingRepo = $eBiddingRepo;
        $this->eBiddingConsoleRepo = $eBiddingConsoleRepo;
        $this->eBiddingSessionRepo = $eBiddingSessionRepo;
        $this->eBiddingReminderRepo = $eBiddingReminderRepo;
        $this->ebiddingNotificationRepository = $ebiddingNotificationRepository;
        $this->formOfTenderRepository = $formOfTenderRepository;
        $this->tenderRepo = $tenderRepo;
	}

	public function index($project)
	{
        $eBidding   = EBidding::where('project_id',$project->id)->first();
        $emailReminder = EmailReminder::where('ebidding_id',$eBidding->id)->first();
        $user = Confide::user();
        $created_by = User::find($eBidding->created_by);
        $isCurentUserCreated = $created_by && $user && $created_by->id == $user->id;
        $isEditor = $user->isEditor($project);

        $buCompany  = $project->selectedCompanies()->where('contract_group_id', '=', PCK\ContractGroups\ContractGroup::getIdByGroup(PCK\ContractGroups\Types\Role::PROJECT_OWNER))->first();
        $gcdCompany = $project->selectedCompanies()->where('contract_group_id', '=', PCK\ContractGroups\ContractGroup::getIdByGroup(PCK\ContractGroups\Types\Role::GROUP_CONTRACT))->first();

        $buContractGroup       = null;
        $gcdContractGroup      = null;
        $buAssignedCommittees  = [];
        $gcdAssignedCommittees = [];

        if($buCompany)
        {
            $buContractGroup = $buCompany->getContractGroup($project);
            $buAssignedCommittees = $this->committeeRepo->getAssignedCommitteeByName($project, $buContractGroup);
        }

        if($gcdCompany)
        {
            $gcdContractGroup = $gcdCompany->getContractGroup($project);
            $gcdAssignedCommittees = $this->committeeRepo->getAssignedCommitteeByName($project, $gcdContractGroup);
        }

        $verifierLogs  = Verifier::getAssignedVerifierRecords($eBidding, false);
        $verifierLogsWithTrashed  = Verifier::getAssignedVerifierRecords($eBidding, true);
        $isCurrentVerifier	= Verifier::isCurrentVerifier(\Confide::user(), $eBidding);

        $isVerified = false;
        if($eBidding->status == EBidding::STATUS_APPROVED || $eBidding->status == EBidding::STATUS_REJECT)
		{
			$isVerified = true;
		}

        $currencySymbol = $project->modified_currency_code; // Currency symbol

        $isBidder = $this->eBiddingConsoleRepo->isBidder([
            'eBiddingId' => $eBidding->id,
            'companyId' => $user->company->id,
        ]); // Check if the user is a bidder

        $isCommitteeMember = $this->eBiddingConsoleRepo->isCommitteeMember([
            'projectId' => $project->id,
            'userId' => $user->id,
        ]); // Check if the user is a committee member

        if ($isBidder) {    // Bidder
            $showBudget = $eBidding->set_budget && $eBidding->budget > 0 && $eBidding->show_budget_to_bidder;
        } elseif ($isCommitteeMember) { // Committee member
            $showBudget = $eBidding->set_budget && $eBidding->budget > 0;
        } else {
            $showBudget = false;
        }

        $bidMode = $eBidding->eBiddingMode;

        $bidHistoryTitle = trans('eBiddingConsole.history');    // Bid history title

        return View::make('open_tenders.e_biddings.index', [
            'project' => $project,
            'created_by' => $created_by->name,
            'isCurentUserCreated' => $isCurentUserCreated,
            'eBidding' => $eBidding,
            'verifierLogs' => $verifierLogs,
            'verifierLogsWithTrashed' => $verifierLogsWithTrashed,
            'isCurrentVerifier'=>$isCurrentVerifier,
            'isVerified'=>$isVerified,
            'buContractGroup' => $buContractGroup,
            'gcdContractGroup' => $gcdContractGroup,
            'buAssignedCommittees'=> $buAssignedCommittees, 
            'gcdAssignedCommittees' => $gcdAssignedCommittees,
            'isEditor' => $isEditor,
            'emailReminder' => $emailReminder,
            'currencySymbol' => $currencySymbol,
            'bidHistoryTitle' => $bidHistoryTitle,
            'isBidder' => $isBidder,
            'isCommitteeMember' => $isCommitteeMember,
            'showBudget' => $showBudget,
            'bidMode' => $bidMode->slug,
        ]);
    }

	public function create(Project $project, $tenderId)
	{
        $tender = $this->tenderRepo->find($project, $tenderId);
        if (! $tender) {    // Record not found
            Flash::error(trans('errors.recordNotFound'));
            return Redirect::route('projects.openTender.index', $project->id);
        }

        $isLatestTender = $project->latestTender->id === $tender->id;   // Latest Tender?
        $isClosedTender = $project->isCurrentTenderStatusClosed();      // Closed Tender status
        $eBiddingNotEnabled = ! $project->e_bidding;                    // E-Bidding not yet enabled for project
        $eBiddingModuleEnabled = GeneralSetting::count() > 0 && GeneralSetting::first()->enable_e_bidding;  // E-Bidding module enabled?

        $submittedAwardRecommendation = false;
        $awardRecommendation = $tender->openTenderAwardRecommendtion;
        if ($awardRecommendation) {
            if ($awardRecommendation->isPendingForApproval() || $awardRecommendation->isApproved() || \PCK\Verifier\Verifier::isBeingVerified($awardRecommendation)) {
                $submittedAwardRecommendation = true;
            }
        }

        if (! $isLatestTender || ! $isClosedTender || ! $eBiddingNotEnabled || ! $eBiddingModuleEnabled || $submittedAwardRecommendation) {
            Flash::error(trans('errors.operationIsNotAllowed'));
            return Redirect::route('projects.openTender.show', [$project->id, $tenderId]);
        }

        $selectedTenderRate = $this->tenderRepo->getSelectedSubmittedTenderRate($project);
        if (! $selectedTenderRate['success']) {
            Flash::error($selectedTenderRate['message']);
            return Redirect::route('projects.openTender.show', [$project->id, $tenderId]);
        }
        $selectedTenderRate = $selectedTenderRate['data'];

        $currencyCode = $project->modified_currency_code;

        return View::make('open_tenders.e_biddings.create', array(
            'project' => $project,
            'tenderId' => $tenderId,
            'currencyCode' => $currencyCode,
            'selectedTenderRate' => $selectedTenderRate,
        ));
	}

    private function createOrUpdateEBidding($input, Project $project, $selectedTenderRates)
    {
        // Check if eBidding session hours/minutes/seconds are empty
        $hasHours = ! empty($input['duration_hours']);
        $hasMinutes = ! empty($input['duration_minutes']);
        $hasSeconds = ! empty($input['duration_seconds']);

        if (! $hasMinutes && ! $hasSeconds) {
            if ($hasHours) {
                $input['duration_minutes'] = 0;
                $input['duration_seconds'] = 0;
            } else {
                $input['duration_hours']   = null;
                $input['duration_minutes'] = null;
                $input['duration_seconds'] = null;
            }
        } elseif (! $hasMinutes) {
            // minutes missing but got seconds/hours
            $input['duration_minutes'] = 0;
        } elseif (! $hasSeconds) {
            // seconds missing but got minutes/hours
            $input['duration_seconds'] = 0;
        }

        $decrementPercent = empty($input['decrement_percent']) ? 0 : (float) $input['decrement_percent'];
        $decrementValue = empty($input['decrement_value']) ? 0 : (float) $input['decrement_value'];

        $bidModeSlug = $input['bid_mode'] ?? EBiddingMode::BID_MODE_DECREMENT; // Default to decrement mode
        $bidMode = EBiddingMode::where('slug', $bidModeSlug)->first();

        switch ($bidMode->slug) {
            case EBiddingMode::BID_MODE_ONCE:
                if ($decrementPercent < 1 || $decrementPercent > 99) {
                    $decrementPercent = 1; // Set to minimum valid value
                    $input['decrement_percent'] = $decrementPercent; // Set to minimum valid value
                }
                if ($decrementValue < 1) {
                    $decrementValue = 1; // Set to minimum valid value
                    $input['decrement_value'] = $decrementValue; // Set to minimum valid value
                }
                $input['enable_custom_bid_value'] = true;
                $noTieBid = ! empty($input['enable_no_tie_bid']);
                break;

            case EBiddingMode::BID_MODE_DECREMENT:
            case EBiddingMode::BID_MODE_INCREMENT:
                if (empty($input['enable_custom_bid_value'])) {
                    $input['min_bid_amount_diff'] = null;
                }
                $noTieBid = true;
                break;

            default:
                // Do nothing
        }

        // Validate the input data
        $this->eBiddingsForm->validate($input);

        $data = [
            'status' => EBidding::STATUS_OPEN,  // Set status to open
            'preview_start_time' => $input['preview_start_time'] ?? null,
            'reminder_preview_start_time' => ! empty($input['reminder_preview_start_time']),
            'bidding_start_time' => $input['bidding_start_time'] ?? null,
            'reminder_bidding_start_time' => ! empty($input['reminder_bidding_start_time']),
            'duration_hours' => (int) $input['duration_hours'] ?? 0,
            'duration_minutes' => (int) $input['duration_minutes'] ?? 0,
            'duration_seconds' => (int) $input['duration_seconds'] ?? 0,
            'start_overtime' => (int) $input['start_overtime'] ?? 0,
            'start_overtime_seconds' => (int) $input['start_overtime_seconds'] ?? 0,
            'overtime_period' => (int) $input['overtime_period'] ?? 0,
            'overtime_seconds' => (int) $input['overtime_seconds'] ?? 0,
            'e_bidding_mode_id' => $bidMode->id,
            'set_budget' => ! empty($input['set_budget']),
            'show_budget_to_bidder' => ! empty($input['set_budget']) && ! empty($input['show_budget_to_bidder']),
            'budget' => empty($input['set_budget']) ? 0 : (float) $input['budget'] ?? 0,
            'bid_decrement_percent' => ! empty($input['bid_decrement_percent']),
            'decrement_percent' => $decrementPercent,
            'bid_decrement_value' => ! empty($input['bid_decrement_value']),
            'decrement_value' => $decrementValue,
            'min_bid_amount_diff' => ! empty($input['enable_custom_bid_value']) && ! empty($input['min_bid_amount_diff']) ? (float) $input['min_bid_amount_diff'] : 0,
            'enable_custom_bid_value' => ! empty($input['enable_custom_bid_value']),
            'enable_no_tie_bid' => $noTieBid,
            'enable_zones' => $bidMode->slug == EBiddingMode::BID_MODE_ONCE, // Auto-enable zones if once mode is selected
            'hide_other_bidder_info' => ! empty($input['hide_other_bidder_info']),
        ];

        $eBidding = EBidding::where('project_id', $project->id)->first();

        if(! $eBidding) {
            // Create a new eBidding record
            $user  = Confide::user();
            $data['project_id'] = $project->id;
            $data['created_by'] = $user->id;
            $record = $this->eBiddingRepo->create($data);

            if ($record) {
                // Enable eBidding for the project
                $project->e_bidding = true;
                $project->changeToEBidding();
                $project->save();

                // Create email reminder
                $this->eBiddingReminderRepo->create(['ebidding_id' => $record->id, 'created_by' => $user->id]);

                Flash::success(trans('eBidding.eBiddingEnabled'));

                // Init bid rankings
                $this->eBiddingConsoleRepo->initRankings($record, $selectedTenderRates);

                // Save lowest tender amount at eBidding table for easier reference
                $this->eBiddingConsoleRepo->updateLowestTenderAmount($record);
            }
        } else {
            // Update existing eBidding record
            $update = $this->eBiddingRepo->update($eBidding->id, $data);

            if ($update) {
                $emailReminder = $this->eBiddingReminderRepo->getRecord($eBidding->id);
                if ($emailReminder) {
                    // Delete email reminder recipients
                    $this->eBiddingReminderRepo->deleteRecipients($emailReminder->id);

                    // Reset email reminder status
                    $this->eBiddingReminderRepo->update($eBidding->id, [
                        'status_preview_start_time' => EmailReminder::DRAFT,
                        'status_bidding_start_time' => EmailReminder::DRAFT,
                    ]);
                }

                // Re-init bid rankings
                $this->eBiddingConsoleRepo->initRankings($eBidding, $selectedTenderRates);

                // Save lowest tender amount at eBidding table for easier reference
                $this->eBiddingConsoleRepo->updateLowestTenderAmount($eBidding);
            }
        }
    }

	public function store(Project $project)
	{
        $isClosedTender = $project->isCurrentTenderStatusClosed();      // Closed Tender status
        $eBiddingNotEnabled = ! $project->e_bidding;                    // E-Bidding not yet enabled for project
        $eBiddingModuleEnabled = GeneralSetting::count() > 0 && GeneralSetting::first()->enable_e_bidding;  // E-Bidding module enabled?

        $tender = $project->latestTender;
        $submittedAwardRecommendation = false;
        $awardRecommendation = $tender->openTenderAwardRecommendtion;
        if ($awardRecommendation) {
            if ($awardRecommendation->isPendingForApproval() || $awardRecommendation->isApproved() || \PCK\Verifier\Verifier::isBeingVerified($awardRecommendation)) {
                $submittedAwardRecommendation = true;
            }
        }

        if (! $isClosedTender || ! $eBiddingNotEnabled || ! $eBiddingModuleEnabled || $submittedAwardRecommendation) {
            Flash::error(trans('errors.operationIsNotAllowed'));
            return Redirect::route('projects.openTender.show', [$project->id, $tender->id]);
        }

        $selectedTenderRates = $this->tenderRepo->getSelectedSubmittedTenderRates($project);
        if (! $selectedTenderRates['success']) {
            Flash::error($selectedTenderRates['message']);
            return Redirect::route('projects.openTender.show', [$project->id, $tender->id]);
        }

        $input = Input::all();

        try
        {
            // Create the eBidding record
            $this->createOrUpdateEBidding($input, $project, $selectedTenderRates);
        }
        catch(\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        $eBidding = EBidding::where('project_id', $project->id)->with('eBiddingMode')->first();
        if (! $eBidding) {
            Flash::error(trans('errors.anErrorOccurred'));
            return Redirect::route('projects.openTender.show', [$project->id, $tender->id]);
        }

        if ($eBidding->enable_zones) {    // Zones enabled -> proceed to zones setup
            return Redirect::route('projects.e_bidding.zones.index', [$project->id, $eBidding->id]);
        } else { // Other -> proceed to assign committees
            return Redirect::route('projects.e_bidding.assignCommittees', $project->id);
        }
	}

	public function edit(Project $project,$id)
	{
        $eBidding = EBidding::find($id);
        $verifiers = [];

        $buCompany  = $project->selectedCompanies()->where('contract_group_id', '=', PCK\ContractGroups\ContractGroup::getIdByGroup(PCK\ContractGroups\Types\Role::PROJECT_OWNER))->first();
        $gcdCompany = $project->selectedCompanies()->where('contract_group_id', '=', PCK\ContractGroups\ContractGroup::getIdByGroup(PCK\ContractGroups\Types\Role::GROUP_CONTRACT))->first();

        $buContractGroup       = null;
        $gcdContractGroup      = null;

        if($buCompany)
        {
            $buContractGroup = $buCompany->getContractGroup($project);
            $verifiers = $this->contractRepo->getAssignedUsersByProjectAndContractGroup($project, $buContractGroup);
        }

        if($gcdCompany)
        {
            $gcdContractGroup = $gcdCompany->getContractGroup($project);
            $verifiers = $this->contractRepo->getAssignedUsersByProjectAndContractGroup($project, $gcdContractGroup);
        }

        $user = Confide::user();
        $isEditor = $user->isEditor($project);

        $isEBidding = $project->inEBidding();      // eBidding status
        $eBiddingModuleEnabled = GeneralSetting::count() > 0 && GeneralSetting::first()->enable_e_bidding;  // E-Bidding module enabled?

        $tender = $project->latestTender;
        $submittedAwardRecommendation = false;
        $awardRecommendation = $tender->openTenderAwardRecommendtion;
        if ($awardRecommendation) {
            if ($awardRecommendation->isPendingForApproval() || $awardRecommendation->isApproved() || \PCK\Verifier\Verifier::isBeingVerified($awardRecommendation)) {
                $submittedAwardRecommendation = true;
            }
        }

        $currentTime = Carbon::now();   // Current time
        $biddingStartTime =  Carbon::parse($eBidding->bidding_start_time);  // Bidding start time

        $allowedToEdit = true; // Flag to check if editing is allowed
        if ($currentTime->gte($biddingStartTime)) { // If current time is greater than or equal to bidding start time
            if ($eBidding->status !== EBidding::STATUS_OPEN) {  // Check if eBidding status is not open
                $allowedToEdit = false; // Disallow editing if eBidding is not open
            }
        }

        if (! $isEditor || ! $isEBidding || ! $eBiddingModuleEnabled || $submittedAwardRecommendation || ! $allowedToEdit) {
            Flash::error(trans('errors.operationIsNotAllowed'));
            return Redirect::route('projects.e_bidding.index', [$project->id]);
        }

        $selectedTenderRate = $this->tenderRepo->getSelectedSubmittedTenderRate($project);
        if (! $selectedTenderRate['success']) {
            Flash::error($selectedTenderRate['message']);
            return Redirect::route('projects.openTender.show', [$project->id, $tender->id]);
        }
        $selectedTenderRate = $selectedTenderRate['data'];

        $currencyCode = $project->modified_currency_code;

        $bidMode = $eBidding->eBiddingMode;

        return View::make('open_tenders.e_biddings.edit', array(
            'id'                         => $id,
            'project'                    => $project,
            'verifiers'                  => $verifiers,
            'preview_start_time'         => $eBidding->preview_start_time,
            'reminder_preview_start_time'=> $eBidding->reminder_preview_start_time,
            'bidding_start_time'         => $eBidding->bidding_start_time,
            'reminder_bidding_start_time'=> $eBidding->reminder_bidding_start_time,
            'duration_hours'             => $eBidding->duration_hours,
            'duration_minutes'           => $eBidding->duration_minutes,
            'duration_seconds'            => $eBidding->duration_seconds,
            'start_overtime'             => $eBidding->start_overtime,
            'start_overtime_seconds'     => $eBidding->start_overtime_seconds,
            'overtime_period'            => $eBidding->overtime_period,
            'overtime_seconds'            => $eBidding->overtime_seconds,
            'hideOtherBidderInfo'        => $eBidding->hide_other_bidder_info,
            'set_budget'                 => $eBidding->set_budget,
            'showBudgetToBidder'         => $eBidding->show_budget_to_bidder,
            'budget'                     => $eBidding->budget,
            'bidMode'                    => $bidMode->slug,
            'bid_decrement_percent'      => $eBidding->bid_decrement_percent,
            'decrement_percent'          => $eBidding->decrement_percent,
            'bid_decrement_value'        => $eBidding->bid_decrement_value,
            'decrement_value'            => $eBidding->decrement_value,
            'min_bid_amount_diff'        => $eBidding->min_bid_amount_diff,
            'enableCustomBidValue'       => $eBidding->enable_custom_bid_value,
            'enable_no_tie_bid'          => $eBidding->enable_no_tie_bid,
            'created_by'                 => $eBidding->created_by,
            'currencyCode'               => $currencyCode,
            'selectedTenderRate'         => $selectedTenderRate,
            'backRoute'                  => route('projects.e_bidding.index', [$project]),
        ));
	}

	public function update(Project $project, $eBiddingId)
	{
        $user  = Confide::user();
		$input = Input::all();

		try
		{
            $eBidding = $this->eBiddingRepo->getById($eBiddingId);
            if (! $eBidding) {
                Flash::error(trans('errors.anErrorOccurred'));
                return Redirect::back();
            }

            $eBiddingModuleEnabled = GeneralSetting::count() > 0 && GeneralSetting::first()->enable_e_bidding;  // E-Bidding module enabled?

            $tender = $project->latestTender;
            $submittedAwardRecommendation = false;
            $awardRecommendation = $tender->openTenderAwardRecommendtion;
            if ($awardRecommendation) {
                if ($awardRecommendation->isPendingForApproval() || $awardRecommendation->isApproved() || \PCK\Verifier\Verifier::isBeingVerified($awardRecommendation)) {
                    $submittedAwardRecommendation = true;
                }
            }

            $isEditor = $user->isEditor($project);
            $isEBidding = $project->inEBidding();      // eBidding status
            $currentTime = Carbon::now();   // Current time
            $biddingStartTime =  Carbon::parse($eBidding->bidding_start_time);  // Bidding start time

            $allowedToEdit = true; // Flag to check if editing is allowed
            if ($currentTime->gte($biddingStartTime)) { // If current time is greater than or equal to bidding start time
                if ($eBidding->status !== EBidding::STATUS_OPEN) {  // Check if eBidding status is not open
                    $allowedToEdit = false; // Disallow editing if eBidding is not open
                }
            }

            if (! $isEditor || ! $isEBidding || ! $eBiddingModuleEnabled || $submittedAwardRecommendation || ! $allowedToEdit) {
                Flash::error(trans('errors.operationIsNotAllowed'));
                return Redirect::route('projects.e_bidding.index', [$project->id]);
            }

            $selectedTenderRates = $this->tenderRepo->getSelectedSubmittedTenderRates($project);
            if (! $selectedTenderRates['success']) {
                Flash::error($selectedTenderRates['message']);
                return Redirect::route('projects.openTender.show', [$project->id, $tender->id]);
            }

            // Update the eBidding record
            $this->createOrUpdateEBidding($input, $project, $selectedTenderRates);
		}
		catch(\PCK\Exceptions\ValidationException $e)
		{
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        $eBidding = EBidding::where('project_id', $project->id)->with('eBiddingMode')->first();
        if (! $eBidding) {
            Flash::error(trans('errors.anErrorOccurred'));
            return Redirect::route('projects.openTender.show', [$project->id, $tender->id]);
        }

        Flash::success(trans('eBidding.eBiddingUpdated'));

        if ($eBidding->enable_zones) {    // Zones enabled -> proceed to zones setup
            return Redirect::route('projects.e_bidding.zones.index', [$project->id, $eBidding->id]);
        } else { // Other -> proceed to assign committees
            return Redirect::route('projects.e_bidding.assignCommittees', $project->id);
        }
	}

    public function getVerifier(Project $project)
	{
        $verifiers = new Collection();

        $buCompany  = $project->selectedCompanies()->where('contract_group_id', '=', PCK\ContractGroups\ContractGroup::getIdByGroup(PCK\ContractGroups\Types\Role::PROJECT_OWNER))->first();
        $gcdCompany = $project->selectedCompanies()->where('contract_group_id', '=', PCK\ContractGroups\ContractGroup::getIdByGroup(PCK\ContractGroups\Types\Role::GROUP_CONTRACT))->first();

        $buContractGroup  = null;
        $gcdContractGroup = null;

        if ($buCompany) {
            $buContractGroup = $buCompany->getContractGroup($project);
            $verifiersId = $this->contractRepo->getAssignedUsersByProjectAndContractGroup($project, $buContractGroup);
            
            if (!empty($verifiersId)) {
                $userIds = array_keys($verifiersId);
                $buVerifiers = User::whereIn('id', $userIds)->get();
                $verifiers = $verifiers->merge($buVerifiers);
            }
        }

        if ($gcdCompany) {
            $gcdContractGroup = $gcdCompany->getContractGroup($project);
            $verifiersId = $this->contractRepo->getAssignedUsersByProjectAndContractGroup($project, $gcdContractGroup);
            
            if (!empty($verifiersId)) {
                $userIds = array_keys($verifiersId);
                $gcdVerifiers = User::whereIn('id', $userIds)->get();
                $verifiers = $verifiers->merge($gcdVerifiers);
            }
        }

		return View::make('open_tenders.e_biddings.partials.verifierForm', array('project' => $project,'verifiers' => $verifiers));
	}

    public function assignVerifier(Project $project)
	{
        $ebidding = EBidding::where('project_id', $project->id)->first();
		$input = Input::all();

        $verifiers = Verifier::where('object_id', $ebidding->id)->get();

        foreach ($verifiers as $verifier) {
            $verifier->Delete();
        }

        if ($ebidding) {
            $this->submitForApproval($ebidding,$input);
            Flash::success(trans('eBidding.verifierAssigned'));

            // Notify committee members and bidders
            $this->ebiddingNotificationRepository->notify($ebidding->id);
		}

        return Redirect::route('projects.e_bidding.index', $project->id);
	}

	public function enable(Project $project)
	{
        $result = ['success' => false, 'message' => ''];

		try
		{
            $project->changeToEBidding();
            $project->save();

            $msg = trans('eBidding.eBiddingEnabled');

            Flash::success($msg);
            $result['success'] = true;
		} 
		catch(Exception $e){
            $msg = trans('errors.anErrorOccurred');
			Flash::error($msg);
            $result['message'] = $msg;
		}
        return Response::json($result);
	}
    
	public function disable(Project $project)
	{
        $result = ['success' => false, 'message' => ''];

		try
		{
            $project->changeToClosedTender();
            $project->save();

            $msg = trans('eBidding.eBiddingDisabled');
            Flash::success($msg);
            $result['success'] = true;
            $result['message'] = $msg;
		} 
		catch(Exception $e){
            $msg = trans('errors.anErrorOccurred');
			Flash::error($msg);
            $result['message'] = $msg;
		}
        return Response::json($result);
	}

    public function submitForApproval(EBidding $record, $inputs)
    {
        $verifiers = array_filter($inputs['verifiers'], function($value)
        {
            return $value != "";
        });

        if( empty( $verifiers ) )
        {
            $record->status = EBidding::STATUS_APPROVED;
            $record->save();

			Verifier::setVerifierAsApproved(Confide::user(), $record);
        }
        else
        {
            Verifier::setVerifiers($verifiers, $record);

            $record->status = EBidding::STATUS_PENDING_FOR_APPROVAL;
            $record->save();

            Verifier::sendPendingNotification($record);
        }
    }
}