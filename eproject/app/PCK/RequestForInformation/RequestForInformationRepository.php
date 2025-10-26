<?php namespace PCK\RequestForInformation;

use Carbon\Carbon;
use PCK\Base\BaseModuleRepository;
use PCK\DirectedTo\DirectedTo;
use PCK\Projects\Project;
use PCK\Users\User;
use PCK\Verifier\Verifier;

class RequestForInformationRepository extends BaseModuleRepository {

    /**
     * Find by id.
     *
     * @param $id
     *
     * @return RequestForInformation
     */
    public function find($id)
    {
        return RequestForInformation::find($id);
    }

    /**
     * Issues a new Request for Information with the attached first message.
     *
     * @param Project $project
     * @param         $subject
     * @param         $messageContent
     * @param         $replyDeadline
     * @param array   $respondents
     * @param         $input
     *
     * @return RequestForInformation
     */
    public function issueNew(Project $project, $subject, $messageContent, $replyDeadline, array $respondents, $input)
    {
        $currentUser = \Confide::user();
        $messageType = get_class(new RequestForInformationMessage);

        $rfi                   = new RequestForInformation();
        $rfi->project_id       = $project->id;
        $rfi->reference_number = $input['reference_number'] ? $input['reference_number'] : RequestForInformation::getNextReferenceNumber($project, $messageType);
        $rfi->subject          = $subject;
        $rfi->issuer_id        = $currentUser->id;
        $rfi->message_type     = $messageType;
        $rfi->save();

        $this->request($rfi, $messageContent, $replyDeadline, $respondents, $input);

        return $rfi;
    }

    /**
     * Pushes a request message node to the Request for Information thread.
     *
     * @param RequestForInformation $requestForInformation
     * @param                       $messageContent
     * @param                       $replyDeadline
     * @param array                 $respondents
     * @param                       $input
     *
     * @return RequestForInformationMessage
     */
    public function request(RequestForInformation $requestForInformation, $messageContent, $replyDeadline, array $respondents, $input)
    {
        $currentUser = \Confide::user();

        $message                             = new RequestForInformationMessage();
        $message->document_control_object_id = $requestForInformation->id;
        $message->sequence_number            = RequestForInformationMessage::getNextSequenceNumber($requestForInformation);
        $message->composed_by                = $currentUser->id;
        $message->reply_deadline             = $replyDeadline;
        $message->content                    = $messageContent;
        $message->type                       = RequestForInformationMessage::TYPE_REQUEST;

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
     * Replaces the current request message node with a new one.
     *
     * @param RequestForInformation $requestForInformation
     * @param                       $messageContent
     * @param                       $replyDeadline
     * @param array                 $respondents
     * @param                       $input
     *
     * @return RequestForInformationMessage
     */
    public function editRequest(RequestForInformation $requestForInformation, $messageContent, $replyDeadline, array $respondents, $input)
    {
        $requestForInformation->getLastMessage()->delete();

        return $this->request($requestForInformation, $messageContent, $replyDeadline, $respondents, $input);
    }

    /**
     * Pushes a response message node to the Request for Information thread.
     *
     * @param RequestForInformationMessage $requestMessage
     * @param                              $messageContent
     * @param                              $input
     *
     * @return RequestForInformationMessage
     */
    public function respond(RequestForInformationMessage $requestMessage, $messageContent, $input)
    {
        $currentUser = \Confide::user();

        $message                             = new RequestForInformationMessage();
        $message->document_control_object_id = $requestMessage->requestForInformation->id;
        $message->sequence_number            = RequestForInformationMessage::getNextSequenceNumber($requestMessage->requestForInformation);
        $message->composed_by                = $currentUser->id;
        $message->reply_deadline             = Carbon::now();
        $message->content                    = $messageContent;
        $message->type                       = RequestForInformationMessage::TYPE_RESPONSE;
        $message->response_to                = $requestMessage->id;
        $message->cost_impact                = $input['cost_impact'] ? true : false;
        $message->schedule_impact            = $input['schedule_impact'] ? true : false;

        $message->save();

        $this->saveAttachments($message, $input);

        Verifier::setVerifiers($input['verifiers'] ?? array(), $message);

        return $message;
    }

    /**
     * Replaces the current response message node with a new one.
     *
     * @param RequestForInformationMessage $requestMessage
     * @param                              $messageContent
     * @param                              $input
     *
     * @return RequestForInformationMessage
     */
    public function editResponse(RequestForInformationMessage $requestMessage, $messageContent, $input)
    {
        $requestMessage->requestForInformation->getLastMessage()->delete();

        return $this->respond($requestMessage, $messageContent, $input);
    }

    public function getPendingApprovalRequestForInformation(User $user, $includeFutureTasks, $project = null)
    {
        $pendingApprovalRequestForInformations = [];
        $proceed = false;

        if($project)
        {
            $tender = $project->latestTender;
            $requestsForInformations = $project->requestsForInformation;

            if($requestsForInformations->isEmpty()) return [];

            foreach($requestsForInformations as $rfi)
            {
                foreach($rfi->messages as $rfiMessage)
                {
                    $proceed = $includeFutureTasks ? Verifier::isAVerifierInline($user, $rfiMessage) : Verifier::isCurrentVerifier($user, $rfiMessage);

                    if($proceed)
                    {
                        $now    = Carbon::now();
                        $then   = Carbon::parse($rfiMessage->updated_at);

                        array_push($pendingApprovalRequestForInformations, [
                            'project_reference'        => $project->reference,
                            'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                            'project_id'               => $project->id,
                            'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                            'company_id'               => $project->business_unit_id,
                            'project_title'            => $project->title,
                            'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                            'module'                   => RequestForInformation::REQUEST_FOR_INFORMATION_MODULE_NAME,
                            'days_pending'             => $then->diffInDays($now),
                            'tender_id'                => $tender->id,
                            'obj_id'                   => $rfiMessage->id,
                            'is_future_task'           => !(Verifier::isCurrentVerifier($user, $rfiMessage)),
                            'route'                    => route('requestForInformation.show', [ $project->id, $rfi->id ]),
                        ]);
                    }
                }
            }
        }
        else
        {
            $records = Verifier::where('verifier_id', $user->id)->where('object_type', RequestForInformationMessage::class)->get();

            foreach($records as $record)
            {
                $rfiMessage = RequestForInformationMessage::find($record->object_id);

                if( ! $rfiMessage ) continue;

                $project = $rfiMessage->requestForInformation->project;
                $proceed = $includeFutureTasks ? Verifier::isAVerifierInline($user, $rfiMessage) : Verifier::isCurrentVerifier($user, $rfiMessage);

                if($project && $proceed)
                {
                    $tender = $project->latestTender;
                    $now    = Carbon::now();
                    $then   = Carbon::parse($rfiMessage->updated_at);

                    array_push($pendingApprovalRequestForInformations, [
                        'project_reference'        => $project->reference,
                        'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                        'project_id'               => $project->id,
                        'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                        'company_id'               => $project->business_unit_id,
                        'project_title'            => $project->title,
                        'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                        'module'                   => RequestForInformation::REQUEST_FOR_INFORMATION_MODULE_NAME,
                        'days_pending'             => $then->diffInDays($now),
                        'tender_id'                => $tender->id,
                        'obj_id'                   => $rfiMessage->id,
                        'is_future_task'           => !(Verifier::isCurrentVerifier($user, $rfiMessage)),
                        'route'                    => route('requestForInformation.show', [ $project->id, $rfiMessage->requestForInformation->id ]),
                    ]);
                }
            }
        }

        return $pendingApprovalRequestForInformations;
    }
}