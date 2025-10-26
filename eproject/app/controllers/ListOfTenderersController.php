<?php

use PCK\Tenders\Tender;
use PCK\TenderListOfTendererInformation\TenderListOfTendererInformationRepository;

class ListOfTenderersController extends \BaseController {

	public $tenderListOfTendererInformationRepository;

	public function __construct(TenderListOfTendererInformationRepository $tenderListOfTendererInformationRepository)
	{
		$this->tenderListOfTendererInformationRepository = $tenderListOfTendererInformationRepository;
	}

	public function initiateThread(\PCK\Projects\Project $project, $tenderId)
    {
        $user = Confide::user();

        $tender = Tender::find($tenderId);

        $object = $tender->listOfTendererInformation;

        if( ! $object ) return App::abort(404);

        $isCurrentVerifier = $object->isBeingValidated() && in_array($user->id, $object->latestVerifier->lists('id'));

        if( ! \PCK\Forum\ObjectThread::objectHasThread($object) && $isCurrentVerifier)
        {
            $thread = \PCK\Forum\Thread::init($project, $user, $object, trans('projects.listofTenderer'));

            $this->tenderListOfTendererInformationRepository->syncApprovalForumUsers($object);            

            return Redirect::route('forum.threads.show', array($project->id, $thread->id));
        }

        return App::abort(404);
    }

}