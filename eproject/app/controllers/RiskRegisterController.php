<?php

use PCK\ContractGroups\ContractGroupRepository;
use PCK\Forms\DocumentControlObjectForm;
use PCK\Forms\RiskRegisterCommentForm;
use PCK\Forms\RiskRegisterRiskForm;
use PCK\RiskRegister\RiskRegister;
use PCK\RiskRegister\RiskRegisterMessage;
use PCK\RiskRegister\RiskRegisterRepository;

class RiskRegisterController extends \BaseController
{

    private $riskRegisterForm;
    private $riskRegisterRiskForm;
    private $contractGroupRepository;
    private $riskRegisterRepository;
    private $verifierController;
    private $riskRegisterCommentForm;

    public function __construct
    (
        DocumentControlObjectForm $riskRegisterForm,
        RiskRegisterRiskForm      $riskRegisterRiskForm,
        RiskRegisterCommentForm   $riskRegisterCommentForm,
        ContractGroupRepository   $contractGroupRepository,
        RiskRegisterRepository    $riskRegisterRepository,
        VerifierController        $verifierController
    )
    {
        $this->riskRegisterForm = $riskRegisterForm;
        $this->riskRegisterRiskForm = $riskRegisterRiskForm;
        $this->contractGroupRepository = $contractGroupRepository;
        $this->riskRegisterRepository = $riskRegisterRepository;
        $this->verifierController = $verifierController;
        $this->riskRegisterCommentForm = $riskRegisterCommentForm;
    }

    public function index($project)
    {
        $user = Confide::user();

        return View::make('risk_register.index', array(
            'user' => $user,
            'project' => $project,
            'risks' => $project->getVisibleRiskRegisterRisks(),
        ));
    }

    public function create($project)
    {
        $user = Confide::user();
        $userContractGroup = $user->getAssignedCompany($project)->getContractGroup($project);

        $contractGroups = $project->getAssignedGroups(array($userContractGroup->group));

        $verifiers = $user->getAssignedCompany($project)->getVerifierList($project);
        $arbitraryRatings = RiskRegisterMessage::getArbitraryRatings();
        $statusList = RiskRegisterMessage::getStatusList();

        return View::make('risk_register.create', array(
            'project' => $project,
            'contractGroups' => $contractGroups,
            'verifiers' => $verifiers,
            'arbitraryRatings' => $arbitraryRatings,
            'statusList' => $statusList,
            'defaultReferenceNumber' => RiskRegister::getNextReferenceNumber($project, get_class(new RiskRegisterMessage)),
        ));
    }

    /**
     * Saves a newly created RiskRegister and its first message.
     *
     * @param $project
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Laracasts\Validation\FormValidationException
     */
    public function store($project)
    {
        $input = Input::all();

        $input['reply_deadline'] = $project->getAppTimeZoneTime(\Carbon\Carbon::parse($input['reply_deadline']) ?? null);

        $input['reference_number'] = ltrim($input['reference_number'], '0');

        $this->riskRegisterForm->setParameters($project, get_class(new RiskRegisterMessage));
        $this->riskRegisterForm->validate($input);
        $this->riskRegisterRiskForm->validate($input);

        $respondents = array();

        foreach ($input['contract_groups'] as $group) {
            $respondents[] = $this->contractGroupRepository->findById($group);
        }

        $riskRegister = $this->riskRegisterRepository->registerNew($project, $respondents, $input);

        $this->verifierController->executeFollowUp($riskRegister->getLatestRisk());

        Flash::success(trans('riskRegister.riskRegistered'));

        return Redirect::route('riskRegister.index', array($project->id));
    }

    public function show($project, $riskRegisterId)
    {
        $user = Confide::user();
        $userContractGroup = $user->getAssignedCompany($project)->getContractGroup($project);

        $contractGroups = $project->getAssignedGroups(array($userContractGroup->group));

        $risk = $this->riskRegisterRepository->find($riskRegisterId);

        $verifiers = $user->getAssignedCompany($project)->getActiveUsers()
            ->reject(function ($verifier) use ($user) {
                return ($verifier->id == $user->id);
            });

        $arbitraryRatings = RiskRegisterMessage::getArbitraryRatings();
        $statusList = RiskRegisterMessage::getStatusList();

        return View::make('risk_register.show', array(
            'user' => $user,
            'project' => $project,
            'risk' => $risk,
            'contractGroups' => $contractGroups,
            'verifiers' => $verifiers,
            'userContractGroup' => $userContractGroup,
            'arbitraryRatings' => $arbitraryRatings,
            'statusList' => $statusList,
        ));
    }

    public function reviseRejectedRisk($project, $riskRegisterMessageId)
    {
        $input = Input::all();

        $this->riskRegisterRiskForm->validate($input);

        $respondents = array();

        foreach ($input['contract_groups'] as $group) {
            $respondents[] = $this->contractGroupRepository->findById($group);
        }

        $riskRegisterMessage = $this->riskRegisterRepository->findMessage($riskRegisterMessageId);

        $riskRegisterMessage = $this->riskRegisterRepository->reviseRejectedRisk($riskRegisterMessage, $respondents, $input);

        $this->verifierController->executeFollowUp($riskRegisterMessage);

        Flash::success(trans('riskRegister.riskRegistered'));

        return Redirect::route('riskRegister.index', array($project->id));
    }

    public function updatePublishedRisk($project, $riskRegisterId)
    {
        $input = Input::all();

        $this->riskRegisterRiskForm->validate($input);

        $respondents = array();

        foreach ($input['contract_groups'] as $group) {
            $respondents[] = $this->contractGroupRepository->findById($group);
        }

        $riskRegister = $this->riskRegisterRepository->find($riskRegisterId);

        $latestRiskPost = $this->riskRegisterRepository->updatePublishedRisk($riskRegister, $respondents, $input);

        $this->verifierController->executeFollowUp($latestRiskPost);

        Flash::success(trans('riskRegister.riskRegistered'));

        return Redirect::route('riskRegister.index', array($project->id));
    }

    /**
     * Adds a message to the Risk Register thread.
     *
     * @param $project
     * @param $riskRegisterId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addComment($project, $riskRegisterId)
    {
        $input = Input::all();

        $this->riskRegisterCommentForm->validate($input);

        $riskRegister = $this->riskRegisterRepository->find($riskRegisterId);

        $comment = $this->riskRegisterRepository->addComment($riskRegister, $input);

        $this->verifierController->executeFollowUp($comment);

        Flash::success(trans('riskRegister.riskRegistered'));

        return Redirect::route('riskRegister.index', array($project->id));
    }

    public function updateComment($project, $riskRegisterMessageId)
    {
        $input = Input::all();

        $this->riskRegisterCommentForm->validate($input);

        $riskRegisterMessage = $this->riskRegisterRepository->findMessage($riskRegisterMessageId);

        $comment = $this->riskRegisterRepository->updateComment($riskRegisterMessage, $input);

        $this->verifierController->executeFollowUp($comment);

        Flash::success(trans('riskRegister.riskRegistered'));

        return Redirect::route('riskRegister.index', array($project->id));
    }

}