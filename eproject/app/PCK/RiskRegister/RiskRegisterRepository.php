<?php namespace PCK\RiskRegister;

use Carbon\Carbon;
use PCK\Base\BaseModuleRepository;
use PCK\DirectedTo\DirectedTo;
use PCK\Projects\Project;
use PCK\Users\User;
use PCK\Verifier\Verifier;
use PCK\RiskRegister\RiskRegister;

class RiskRegisterRepository extends BaseModuleRepository {

    /**
     * Find by id.
     *
     * @param $id
     *
     * @return RiskRegister
     */
    public function find($id)
    {
        $object = RiskRegister::find($id);

        if( $object->message_type != get_class(new RiskRegisterMessage) ) return null;

        return $object;
    }

    public function findMessage($id)
    {
        return RiskRegisterMessage::find($id);
    }

    /**
     * Registers a new risk.
     *
     * @param Project $project
     * @param array   $respondents
     * @param         $input
     *
     * @return RiskRegister
     */
    public function registerNew(Project $project, array $respondents, $input)
    {
        $currentUser = \Confide::user();
        $messageType = get_class(new RiskRegisterMessage);

        $riskRegister                   = new RiskRegister;
        $riskRegister->project_id       = $project->id;
        $riskRegister->reference_number = $input['reference_number'] ? $input['reference_number'] : RiskRegister::getNextReferenceNumber($project, $messageType);
        $riskRegister->subject          = $input['subject'];
        $riskRegister->issuer_id        = $currentUser->id;
        $riskRegister->message_type     = $messageType;
        $riskRegister->save();

        $this->addRisk($riskRegister, $respondents, $input);

        return $riskRegister;
    }

    /**
     * Adds a new risk.
     *
     * @param RiskRegister $riskRegister
     * @param array        $respondents
     * @param              $input
     *
     * @return RiskRegisterMessage
     */
    public function addRisk(RiskRegister $riskRegister, array $respondents, $input)
    {
        $currentUser = \Confide::user();

        $message                             = new RiskRegisterMessage;
        $message->document_control_object_id = $riskRegister->id;
        $message->sequence_number            = RiskRegisterMessage::getNextSequenceNumber($riskRegister);
        $message->composed_by                = $currentUser->id;
        $message->reply_deadline             = $input['reply_deadline'];
        $message->content                    = $input['content'];
        $message->type                       = RiskRegisterMessage::TYPE_RISK;
        $message->probability                = $input['probability'];
        $message->category                   = $input['category'];
        $message->trigger_event              = $input['trigger_event'];
        $message->risk_response              = $input['risk_response'];
        $message->contingency_plan           = $input['contingency_plan'];
        $message->status                     = $input['status'];
        $message->impact                     = $input['impact'];
        $message->detectability              = $input['detectability'];
        $message->importance                 = $input['importance'];

        $message->save();

        $this->saveAttachments($message, $input);

        foreach($respondents as $respondent)
        {
            DirectedTo::directTo($respondent, $message);
        }

        Verifier::setVerifiers($input['verifiers'] ?? array(), $message);

        return $message;
    }

    /**
     * Updates the currently rejected risk.
     *
     * @param RiskRegisterMessage $currentRiskPost
     * @param array               $respondents
     * @param                     $input
     *
     * @return RiskRegisterMessage
     */
    public function reviseRejectedRisk(RiskRegisterMessage $currentRiskPost, array $respondents, $input)
    {
        $message = $currentRiskPost->replicate(array( 'id' ));

        $currentRiskPost->delete();

        $message->reply_deadline   = $input['reply_deadline'];
        $message->content          = $input['content'];
        $message->probability      = $input['probability'];
        $message->category         = $input['category'];
        $message->trigger_event    = $input['trigger_event'];
        $message->risk_response    = $input['risk_response'];
        $message->contingency_plan = $input['contingency_plan'];
        $message->status           = $input['status'];
        $message->impact           = $input['impact'];
        $message->detectability    = $input['detectability'];
        $message->importance       = $input['importance'];

        $message->save();

        $this->saveAttachments($message, $input);

        foreach($respondents as $respondent)
        {
            DirectedTo::directTo($respondent, $message);
        }

        Verifier::setVerifiers($input['verifiers'] ?? array(), $message);

        return $message;
    }

    /**
     * Updates the published risk.
     * Old one before update is still available,
     * but this one will be used as the latest version.
     *
     * @param RiskRegister $riskRegister
     * @param array        $respondents
     * @param              $input
     *
     * @return RiskRegisterMessage
     */
    public function updatePublishedRisk(RiskRegister $riskRegister, array $respondents, $input)
    {
        $currentUser = \Confide::user();

        $message                             = new RiskRegisterMessage;
        $message->document_control_object_id = $riskRegister->id;
        $message->sequence_number            = 0;
        $message->composed_by                = $currentUser->id;
        $message->reply_deadline             = $input['reply_deadline'];
        $message->content                    = $input['content'];
        $message->type                       = RiskRegisterMessage::TYPE_RISK;
        $message->probability                = $input['probability'];
        $message->category                   = $input['category'];
        $message->trigger_event              = $input['trigger_event'];
        $message->risk_response              = $input['risk_response'];
        $message->contingency_plan           = $input['contingency_plan'];
        $message->status                     = $input['status'];
        $message->impact                     = $input['impact'];
        $message->detectability              = $input['detectability'];
        $message->importance                 = $input['importance'];

        $message->save();

        $this->saveAttachments($message, $input);

        foreach($respondents as $respondent)
        {
            DirectedTo::directTo($respondent, $message);
        }

        Verifier::setVerifiers($input['verifiers'] ?? array(), $message);

        return $message;
    }

    /**
     * Adds a comment to the thread.
     *
     * @param RiskRegister $riskRegister
     * @param              $input
     *
     * @return RiskRegisterMessage
     */
    public function addComment(RiskRegister $riskRegister, $input)
    {
        $currentUser = \Confide::user();

        $message                             = new RiskRegisterMessage;
        $message->document_control_object_id = $riskRegister->id;
        $message->sequence_number            = 0;
        $message->composed_by                = $currentUser->id;
        $message->content                    = $input['content'];
        $message->type                       = RiskRegisterMessage::TYPE_COMMENT;

        // Default values.
        $message->reply_deadline   = Carbon::now();
        $message->probability      = 0;
        $message->category         = '';
        $message->trigger_event    = '';
        $message->risk_response    = '';
        $message->contingency_plan = '';
        $message->status           = RiskRegisterMessage::STATUS_OPEN;
        $message->impact           = RiskRegisterMessage::RATING_LOW;
        $message->detectability    = RiskRegisterMessage::RATING_LOW;
        $message->importance       = RiskRegisterMessage::RATING_LOW;

        $message->save();

        $this->saveAttachments($message, $input);

        Verifier::setVerifiers($input['verifiers'] ?? array(), $message);

        return $message;
    }

    /**
     * Updates a comment.
     *
     * @param RiskRegisterMessage $riskRegisterMessage
     * @param                     $input
     *
     * @return RiskRegisterMessage
     */
    public function updateComment(RiskRegisterMessage $riskRegisterMessage, $input)
    {
        $riskRegisterMessage->delete();

        return $this->addComment($riskRegisterMessage->riskRegister, $input);
    }

    public function getPendingApprovalRiskRegisters(User $user, $includeFutureTasks, $project = null)
    {
        $pendingApprovalRiskRegisters = [];
        $proceed = false;

        if($project)
        {
            $tender = $project->latestTender;
            $riskRegisters = $project->riskRegisterRisks;

            if($riskRegisters->isEmpty()) return [];

            foreach($riskRegisters as $riskRegister)
            {
                foreach($riskRegister->messages as $riskRegisterMessage)
                {
                    $proceed = $includeFutureTasks ? Verifier::isAVerifierInline($user, $riskRegisterMessage) : Verifier::isCurrentVerifier($user, $riskRegisterMessage);

                    if($proceed)
                    {
                        $now  = Carbon::now();
                        $then = Carbon::parse($riskRegisterMessage->updated_at);

                        array_push($pendingApprovalRiskRegisters, [
                            'project_reference'        => $project->reference,
                            'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                            'project_id'               => $project->id,
                            'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                            'company_id'               => $project->business_unit_id,
                            'project_title'            => $project->title,
                            'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                            'module'                   => RiskRegister::RISK_REGISTER_MODULE_NAME,
                            'days_pending'             => $then->diffInDays($now),
                            'tender_id'                => $tender->id,
                            'obj_id'                   => $riskRegisterMessage->id,
                            'is_future_task'           => !(Verifier::isCurrentVerifier($user, $riskRegisterMessage)),
                            'route'                    => route('riskRegister.show', [ $project->id, $riskRegister->id ]),
                        ]);
                    }
                }
            }
        }
        else
        {
            $records = Verifier::where('verifier_id', $user->id)->where('object_type', RiskRegisterMessage::class)->get();

            foreach($records as $record)
            {
                $riskRegisterMessage = RiskRegisterMessage::find($record->object_id);

                if( ! $riskRegisterMessage ) continue;

                $project = $riskRegisterMessage->riskRegister->project;
                $proceed = $includeFutureTasks ? Verifier::isAVerifierInline($user, $riskRegisterMessage) : Verifier::isCurrentVerifier($user, $riskRegisterMessage);

                if($project && $proceed)
                {
                    $tender = $project->latestTender;
                    $now    = Carbon::now();
                    $then   = Carbon::parse($riskRegisterMessage->updated_at);

                    array_push($pendingApprovalRiskRegisters, [
                        'project_reference'        => $project->reference,
                        'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                        'project_id'               => $project->id,
                        'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                        'company_id'               => $project->business_unit_id,
                        'project_title'            => $project->title,
                        'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                        'module'                   => RiskRegister::RISK_REGISTER_MODULE_NAME,
                        'days_pending'             => $then->diffInDays($now),
                        'tender_id'                => $tender->id,
                        'obj_id'                   => $riskRegisterMessage->id,
                        'is_future_task'           => !(Verifier::isCurrentVerifier($user, $riskRegisterMessage)),
                        'route'                    => route('riskRegister.show', [ $project->id, $riskRegisterMessage->riskRegister->id ]),
                    ]);
                }
            }
        }

        return $pendingApprovalRiskRegisters;
    }
}