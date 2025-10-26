<?php

use PCK\ContractGroups\ContractGroupRepository;
use PCK\Forms\DocumentControlObjectForm;
use PCK\Forms\RequestForInformationRequestMessageForm;
use PCK\Forms\RequestForInformationResponseMessageForm;
use PCK\RequestForInformation\RequestForInformation;
use PCK\RequestForInformation\RequestForInformationMessage;
use PCK\RequestForInformation\RequestForInformationRepository;
use PCK\Verifier\Verifier;

class RequestForInformationController extends \BaseController
{

    private $requestForInformationRepository;
    private $contractGroupRepository;
    private $requestForInformationForm;
    private $requestMessageForm;
    private $responseMessageForm;
    private $verifierController;

    public function __construct
    (
        RequestForInformationRepository          $requestForInformationRepository,
        ContractGroupRepository                  $contractGroupRepository,
        DocumentControlObjectForm                $requestForInformationForm,
        RequestForInformationRequestMessageForm  $requestMessageForm,
        RequestForInformationResponseMessageForm $responseMessageForm,
        VerifierController                       $verifierController
    )
    {
        $this->requestForInformationRepository = $requestForInformationRepository;
        $this->contractGroupRepository = $contractGroupRepository;
        $this->requestForInformationForm = $requestForInformationForm;
        $this->requestMessageForm = $requestMessageForm;
        $this->responseMessageForm = $responseMessageForm;
        $this->verifierController = $verifierController;
    }

    public function index($project)
    {
        $user = Confide::user();

        return View::make('request_for_information.index', array(
            'user' => $user,
            'project' => $project,
            'requestsForInformation' => $project->getVisibleRequestsForInformation(),
        ));
    }

    public function create($project)
    {
        $user = Confide::user();
        $userContractGroup = $user->getAssignedCompany($project)->getContractGroup($project);

        $contractGroups = $project->getAssignedGroups(array($userContractGroup->group));

        $verifiers = $user->getAssignedCompany($project)->getVerifierList($project);

        return View::make('request_for_information.create', array(
            'project' => $project,
            'contractGroups' => $contractGroups,
            'verifiers' => $verifiers,
            'defaultReferenceNumber' => RequestForInformation::getNextReferenceNumber($project, get_class(new RequestForInformationMessage)),
        ));
    }

    /**
     * Saves a newly created RFI and its first message.
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

        $this->requestForInformationForm->setParameters($project, get_class(new RequestForInformationMessage));
        $this->requestForInformationForm->validate($input);
        $this->requestMessageForm->validate($input);

        $respondents = array();

        foreach ($input['contract_groups'] as $group) {
            $respondents[] = $this->contractGroupRepository->findById($group);
        }

        $requestForInformation = $this->requestForInformationRepository->issueNew($project, $input['subject'], $input['content'], $input['reply_deadline'], $respondents, $input);

        $this->verifierController->executeFollowUp($requestForInformation->getLastRequest());

        Flash::success(trans('requestForInformation.rfiIssued'));

        return Redirect::route('requestForInformation.index', array($project->id));
    }

    public function show($project, $requestForInformationId)
    {
        $user = Confide::user();
        $userContractGroup = $user->getAssignedCompany($project)->getContractGroup($project);

        $contractGroups = $project->getAssignedGroups(array($userContractGroup->group));

        $requestForInformation = $this->requestForInformationRepository->find($requestForInformationId);

        $verifiers = $user->getAssignedCompany($project)->getVerifierList($project);

        return View::make('request_for_information.show', array(
            'user' => $user,
            'project' => $project,
            'requestForInformation' => $requestForInformation,
            'contractGroups' => $contractGroups,
            'verifiers' => $verifiers,
            'userContractGroup' => $userContractGroup,
        ));
    }

    /**
     * Adds a message to the RFI thread.
     * Message will be a request or response depending on the current state of the thread (answered or not).
     *
     * @param $project
     * @param $requestForInformationId
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Laracasts\Validation\FormValidationException
     */
    public function pushMessage($project, $requestForInformationId)
    {
        $requestForInformation = $this->requestForInformationRepository->find($requestForInformationId);

        $isMessageEdit = Verifier::isRejected($requestForInformation->getLastMessage());

        $user = Confide::user();
        $input = Input::all();

        $input['reply_deadline'] = $project->getAppTimeZoneTime($input['reply_deadline'] ?? null);

        $message = null;

        if ($requestForInformation->canRequest($user)) {
            $this->requestMessageForm->validate($input);

            $respondents = array();

            foreach ($input['contract_groups'] as $group) {
                $respondents[] = $this->contractGroupRepository->findById($group);
            }

            if ($isMessageEdit) {
                $message = $this->requestForInformationRepository->editRequest($requestForInformation, $input['content'], $input['reply_deadline'], $respondents, $input);
            } else {
                $message = $this->requestForInformationRepository->request($requestForInformation, $input['content'], $input['reply_deadline'], $respondents, $input);
            }
        }

        if ($requestForInformation->canRespond($user)) {
            $this->responseMessageForm->validate($input);

            if ($isMessageEdit) {
                $message = $this->requestForInformationRepository->editResponse($requestForInformation->getLastRequest(), $input['content'], $input);
            } else {
                $message = $this->requestForInformationRepository->respond($requestForInformation->getLastRequest(), $input['content'], $input);
            }
        }

        if ($message) $this->verifierController->executeFollowUp($message);

        $isMessageEdit ? Flash::success(trans('requestForInformation.messageRevised')) : Flash::success(trans('requestForInformation.messagePosted'));

        return Redirect::route('requestForInformation.show', array($project->id, $requestForInformationId));
    }

}