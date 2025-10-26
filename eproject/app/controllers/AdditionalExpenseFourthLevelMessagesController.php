<?php

use PCK\ContractGroups\Types\Role;
use PCK\Forms\AEMessageFourthLevelContractorForm;
use PCK\Forms\AEMessageFourthLevelArchitectQsForm;
use PCK\AdditionalExpenses\AdditionalExpenseRepository;
use PCK\AdditionalExpenseFourthLevelMessages\AdditionalExpenseFourthLevelMessageRepository;

class AdditionalExpenseFourthLevelMessagesController extends \BaseController {

    private $aeRepo;

    private $aeFourthLevelMessageRepo;

    private $aeMessageFourthLevelArchitectQsForm;

    private $aeMessageFourthLevelContractorForm;

    public function __construct(
        AdditionalExpenseRepository $aeRepository,
        AdditionalExpenseFourthLevelMessageRepository $aeFourthLevelMessageRepo,
        AEMessageFourthLevelArchitectQsForm $aeMessageFourthLevelArchitectQsForm,
        AEMessageFourthLevelContractorForm $aeMessageFourthLevelContractorForm
    )
    {
        $this->aeRepo = $aeRepository;
        $this->aeFourthLevelMessageRepo = $aeFourthLevelMessageRepo;
        $this->aeMessageFourthLevelArchitectQsForm = $aeMessageFourthLevelArchitectQsForm;
        $this->aeMessageFourthLevelContractorForm = $aeMessageFourthLevelContractorForm;
    }

    /**
     * Show the form for creating a new Additional Expense Fourth Level Message.
     *
     * @param $project
     * @param $aeId
     *
     * @return Response
     */
    public function create($project, $aeId)
    {
        $user = Confide::user();
        $ae = $this->aeRepo->find($project, $aeId);
        $aeLastArchitectMessage = $this->aeFourthLevelMessageRepo->checkLatestMessageByArchitect($aeId);
        $uploadedFiles = $this->getAttachmentDetails();

        return View::make('additional_expense_fourth_level_messages.create', compact('user', 'ae', 'aeLastArchitectMessage', 'uploadedFiles'));
    }

    /**
     * Store a newly created Additional Expense Fourth Level Message in storage.
     *
     * @param $project
     * @param $aeId
     *
     * @return Response
     */
    public function store($project, $aeId)
    {
        $user = Confide::user();
        $ae = $this->aeRepo->find($project, $aeId);

        $inputs = Input::all();

        if( $user->hasCompanyProjectRole($project, array( Role::INSTRUCTION_ISSUER, Role::CLAIM_VERIFIER )) )
        {
            $this->aeMessageFourthLevelArchitectQsForm->validate($inputs);
        }

        if( $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
        {
            $this->aeMessageFourthLevelContractorForm->validate($inputs);
        }

        $this->aeFourthLevelMessageRepo->add($user, $ae, $inputs);

        Flash::success('Successfully replied Step Four Message!');

        return Redirect::route('ae.show', array( $ae->project_id, $ae->id ));
    }

}