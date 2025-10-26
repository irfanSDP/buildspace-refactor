<?php

use PCK\Verifier\Verifiable;
use PCK\Verifier\Verifier;
use PCK\Verifier\VerifierRepository;

class VerifierController extends \BaseController {

    private $verifierRepository;

    public function __construct(VerifierRepository $verifierRepository)
    {
        $this->verifierRepository = $verifierRepository;
    }

    public function verify($objectId)
    {
        $input  = Input::all();

        $object = $input['class']::find($objectId);
 
        $confirmStatus = isset( $input['approve'] );

        if(isset($input['verifier_remarks'])) Verifier::updateVerifierRemarks($object, $input['verifier_remarks']);
        
        $this->verifierRepository->approve($object, $confirmStatus);
        
        Flash::success(trans('verifiers.verificationSuccess'));

        if(method_exists($object, 'getTopManagementVerifierBackRoute'))
        {
            return $object->getTopManagementVerifierBackRoute();
        }

        if(method_exists($object, 'getPostApprovalRoute'))
        {
            return Redirect::intended($object->getPostApprovalRoute());
        }

        return Redirect::back();
    }

    // Todo: change usage to use verifierRepository.
    public function executeFollowUp(Verifiable $object)
    {
        $this->verifierRepository->executeFollowUp($object);
    }

    public function initiateThread(\PCK\Projects\Project $project)
    {
        $user = Confide::user();

        $input  = Input::all();
        $object = $input['object_type']::find($input['object_id']);

        if( ! $object instanceof Verifiable ) return App::abort(404);

        if( ! \PCK\Forum\ObjectThread::objectHasThread($object) && \PCK\Verifier\Verifier::canInitiateForumThread($user, $object))
        {
            $thread = \PCK\Verifier\Verifier::initForumThread($project, $object);

            return Redirect::route('forum.threads.show', array($project->id, $thread->id));
        }

        return App::abort(404);
    }

}